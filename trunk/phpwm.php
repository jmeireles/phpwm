<?php
require_once 'classes/class.events.php';
require_once 'classes/class.window.php';
require_once 'classes/class.graphics.php';
require_once 'classes/class.common.php';
/*
 * make the connection global for now
 */
set_time_limit(0);
$wm = new phpwm("127.0.0.1:1");

class phpwm{
	public $xcb;
	public $root = array();
	public $windows = array();
	public $_args = array();
	public $_ports = array();
	function __construct($strDisplay){
		$this->_parseArgs($_SERVER['argv']);
		$this->xcb = xcb_init($this->_args['display']);
		$this->root['id'] = xcb_root_id($this->xcb);
		$this->_firstPort = 9500;
		$this->_events = new phpwm_events($this);
		$this->init_main_socket();
		$this->manageRoot();
		$this->_startup();
		$this->_xcbEventLoop();
		$this->socket_loop();
	}
	function registerEvent($id, $type, $object){
		if (!isset($this->_events->_callbacks[$type])){
			$this->_events->_callbacks[$type]=array();
		}
		array_push($this->_events->_callbacks[$type], array("id"=>$id, "callback"=>$object));
	}
	function _startup(){
		$this->_execute("xterm");
		$this->_execute("xsetroot \-mod 16 18 \-fg rgb:54/6/6 \-bg grey20");
		$this->_execute("xsetroot \-cursor_name left_ptr \-fg white \-bg black");
		$arrDir = scandir("./autostart");
		var_export($this->_args);
		foreach($arrDir as $strFile){
			if (substr($strFile, -3) == "php"){
				//				include_once './autostart/'.$strFile;
				//				$strClassName = substr($strFile, 0, -4);
				//				$objApp = new $strClassName($this, $arrArgs);
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
		echo "Setting events on {$this->root['id']}";
		xcb_configure_window_events($this->xcb, $this->root['id'], array(1048576, 131072, 524288));
		//xcb_configure_window_events_root($this->root['id']);
		//removed 131072
		//register for events on the root window

	}

	function init_main_socket(){
		//initialize the sockets before any forking happens.
		$this->main_socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		socket_bind($this->main_socket,'127.0.0.1',$this->_firstPort);
		$this->_ports[$this->_firstPort] = "phpwm";
		socket_listen($this->main_socket);
		//socket_set_nonblock($this->main_socket);
	}

	function socket_loop(){
		while(true)
		{
			if(($newc = socket_accept($this->main_socket)) !== false)
			{
				$input = socket_read($spawn, 1024) or die("Could not read input\n");
				$input = trim($input);
				var_export($input);
				$this->execute_event(unserialise($input));
				socket_close($newc);
				//				echo "Client $newc has connected\n";
				//				$clients[] = $newc;
				//				foreach($clients as $client){
				//
				//				}
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

	function execute_event($strData){
		$evt = unserialize($strData);
		switch($evt['event_type']){
			case "xcb_events":
				if (method_exists($this->_events, "evt_".$evt['data']['response_type'])){
					//echo "***Event {$e['response_type']} start \n";
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
			pcntl_wait($status);
		} else {
			echo "Fork Successfull\n";
			//socket_listen($this->main_socket);
			while ($e = xcb_wait_for_event($this->xcb)){
				$socket = 
				$strCmd = serialize(array("event_type"=>$this->_ports[$port], "data"=>$e));
				socket_write($this->main_socket, $strCmd, strlen($strCmd));
//				socket_close($socket);

			}
		}
	}
}



?>
