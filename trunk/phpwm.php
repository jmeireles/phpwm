<?php
require_once 'classes/class.events.php';
require_once 'classes/class.window.php';
require_once 'classes/class.graphics.php';
require_once 'classes/class.common.php';
require_once 'define.php';
/*
 * make the connection global for now
 */
set_time_limit(0);
$wm = new phpwm("127.0.0.1:0");

class phpwm{
	public $xcb;
	public $root = array();
	public $windows = array();
	public $_args = array();
	public $_ports = array();
	public $_colors = array();
	public $_fonts = array();
	public $_atoms = array();

	public $shutdown = false;
	function __construct($strDisplay){
		$this->_checkReq();
		$this->_parseArgs($_SERVER['argv']);
		//$this->xcb = xcb_init(isset($this->_args['display'])?$this->_args['display']:"127.0.0.1:0.0");
		var_export($this->_args);
		if ($this->xcb = isset($this->_args['display'])?xcb_init($this->_args['display']):xcb_init()) {
			echo "xcb is ".$this->xcb ."\n";
			$this->root['id'] = xcb_root_id($this->xcb);
			$this->_firstPort = 9000+rand(0,1000);
			$this->_events = new phpwm_events($this);
			$this->_prefetchAtoms();
			$this->init_colors();
			$this->manageRoot();
			$this->_startup();
			$this->_xcbEventLoop();
		} else {
			echo "Unable to init xcb\n";
			exit;
		}
		//$this->socket_loop();
	}
	function _checkReq(){
		$abort = false;
		if (!function_exists("msg_get_queue")){
			echo "php is requred to installed with System V semaphore support (--enable-sysvmsg)\n";
			$abort = true;
		}
		if (!function_exists("xcb_init")){
			echo "the XCB extension is required\n";
			$abort = true;			
		}
		if (!function_exists("pcntl_fork")){
			echo "php is required to be insalled with Process Control support (--enable-pcntl)\n";
			$abort = true;
		}
		
		
		if ($abort == true){
			exit;
		}
	}
	function registerEvent($id, $type, $object){
		if (!isset($this->_events->_callbacks[$type])){
			$this->_events->_callbacks[$type]=array();
		}
		array_push($this->_events->_callbacks[$type], array("id"=>$id, "callback"=>$object));
	}
	function _startup(){
		echo "running startup\n";
		$this->_execute("xterm");
		$this->_execute("xsetroot \-mod 16 18 \-fg rgb:54/6/6 \-bg grey20");
		$this->_execute("xsetroot \-cursor_name left_ptr \-fg white \-bg black");
		$arrDir = scandir("./autostart");
		foreach($arrDir as $strFile){
			if (substr($strFile, -3) == "php"){
				include_once './autostart/'.$strFile;
				$strClassName = substr($strFile, 0, -4);
				$objApp = new $strClassName($this, $arrArgs);
			}
		}
	}
	//spawn a new process
	function _execute($strApp){
		if (isset($this->_args['display'])){
			$disp = "-display {$this->_args['display']}";
		} else {
			$disp = "";
		}
		$strCommand = "nohup {$strApp} {$disp}> /dev/null 2> /dev/null & echo $!";
		//echo "Running: $strCommand\n";
		$PID = shell_exec($strCommand);
	}
	function _parseArgs($arrArgs){
		foreach($arrArgs as $arg){
			if (substr($arg, 0, 2)=="--"){
				list($key, $val) = explode("=", substr($arg,2));
				$this->_args[$key]=$val;
			}
		}
	}
	function manageRoot(){
		//register for events on the root window
		$arrWindows = xcb_query_tree($this->xcb, $this->root['id']);
		foreach($arrWindows as $win){
			//xcb_unmap_window($this->xcb, $win);
			usleep(1000);
			$this->windows[$win]= new phpwm_window($this, $win);
			usleep(1000);
			$this->windows[$win]->configure(array("window"=>$win, "x"=>50, "y"=>50, "width"=>300, "height"=>300));
			usleep(1000);
			$this->windows[$win]->map();
			usleep(1000);
			$this->windows[$win]->configureFrame(array("window"=>$win, "x"=>50, "y"=>50, "width"=>306, "height"=>306));
			usleep(1000);
		}
		usleep(3000);
		xcb_configure_window_events_root($this->xcb, $this->root['id']);

	}
	function init_colors(){
		$arrColors = array("Red", "Blue", "Green", "Black", "White");
		if (!isset($this->_colormap)){
			$this->_colormap = xcb_get_default_colormap($this->xcb);
		}
		foreach($arrColors as $color){
			$this->_colors[$color] = xcb_alloc_named_color($this->xcb, $this->_colormap, $color);
		}
	}
	function getFont($strFont){
		if (isset($this->_fonts[$strFont])){
			return $this->_fonts[$strFont];
		} else {
			$this->_fonts[$strFont] = xcb_generate_id($this->xcb);
			xcb_open_font($this->xcb, $this->_fonts[$strFont], $strFont);
			return $this->_fonts[$strFont];
		}
	}
	//need to look up most atoms at startup, we get a hang condition doing it mid-app;
	function _prefetchAtoms(){
		$arrAtoms = array(37, 39);
		foreach($arrAtoms as $atom){
			$this->getAtom($atom);
		}
	}
	function getAtom($intAtom){
		foreach($this->_atoms as $name=>$id){
			if ($id == $intAtom){
				return $name;
			}
		}
		echo "Core: atom $intAtom not in cache, fetching\n";
		usleep(3000); //fighting a race here...
		$name = xcb_get_atom_name($this->xcb, $intAtom);
		$this->_atoms[$name]= $intAtom;
	}

	function socket_loop(){
		$ipc = msg_get_queue($this->_firstPort) ;
		while(!$this->shutdown){
			$stat = msg_stat_queue( $ipc );
			if ( $stat['msg_qnum']>0 ) {
				msg_receive($ipc, 0, $msgtype, 1024, $data, true);
				//echo "Server:  Recieved event {$data['data']['response_type']}\n";
				$this->execute_event($data);
				echo "Server:  Finished event {$data['data']['response_type']}\n";
			}
		}
	}


	function getNextPort(){
		for ($i=$this->_firstPort; $i < $this->_firstPort+1000; $i++){
			if (!isset($this->_ports[$i])){
				return $i;
			}
		}
	}

	function execute_event($evt){
		//$evt = unserialize(trim($strData));
		switch($evt['event_type']){
			case "xcb_events":
				if (method_exists($this->_events, "evt_".$evt['data']['response_type'])){
					//echo "***Event ". $evt['data']['response_type'] ." start \n";
					$this->_events->{"evt_".$evt['data']['response_type']}($evt['data']);
				} else {
					echo "No Method for {$evt['data']['response_type']} event \n";
					var_export($evt['data']);
				}
				break;
			default:
				var_export($evt);
		}
	}

	function _xcbEventLoop(){
		$port = $this->getNextPort();
		$this->_ports[$port]="xcb_events";
		$pid = pcntl_fork();
		if ($pid == -1) {
			die('\ncould not fork\n');
		} else if ($pid){
			//pcntl_wait($status);
			$this->socket_loop();
		} else {
			$ipc = msg_get_queue($this->_firstPort) ;
			while ($e = xcb_wait_for_event($this->xcb)){
				//echo "Client: sending event {$e['response_type']}\n";
				msg_send($ipc, 1, array("event_type"=>$this->_ports[$port], "data"=>$e), 1);
			}
		}
	}
}



?>
