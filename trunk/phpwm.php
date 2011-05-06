<?php
require_once 'classes/class.events.php';
require_once 'classes/class.window.php';
require_once 'classes/class.graphics.php';
require_once 'classes/class.common.php';
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
	public $shutdown = false;
	function __construct($strDisplay){
		$this->_parseArgs($_SERVER['argv']);
		//$this->xcb = xcb_init(isset($this->_args['display'])?$this->_args['display']:"127.0.0.1:0.0");
		if ($this->xcb = xcb_init()) {
			echo "xcb is ".$this->xcb ."\n";
			$this->root['id'] = xcb_root_id($this->xcb);
			$this->_firstPort = 9000+rand(0,1000);
			$this->_events = new phpwm_events($this);
			$this->manageRoot();
			$this->init_main_socket();
			$this->_startup();
			$this->_xcbEventLoop();
		} else {
			echo "Unable to init xcb\n";
			exit;
		}
		//$this->socket_loop();
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
		echo "Setting events on {$this->root['id']}\n";
		//xcb_configure_window_events_root($this->xcb, $this->root['id']);
		//xcb_configure_window_events($this->xcb, $this->root['id'], array(1048576, 131072, 524288));
		xcb_configure_window_events_root($this->xcb, $this->root['id']);
		//removed 131072
		//register for events on the root window

	}
	function init_main_socket(){
	}
	function socket_loop(){
		$ipc = msg_get_queue($this->_firstPort) ;
		while(!$this->shutdown){
			$stat = msg_stat_queue( $ipc );
			if ( $stat['msg_qnum']>0 ) {
				msg_receive($ipc, 0, $msgtype, 1024, $data, true);
				echo "Server:  Recieved event {$data['data']['response_type']}\n";
				$this->execute_event($data);
				echo "Server:  Finished event {$data['data']['response_type']}\n";
			}
		}
	}

	function init_main_socket_old(){
		//initialize the sockets before any forking happens.
		$this->main_socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		socket_bind($this->main_socket,'127.0.0.1',$this->_firstPort);
		$this->_ports[$this->_firstPort] = "phpwm";
		socket_listen($this->main_socket);
		socket_set_option($this->main_socket, SOL_SOCKET, SO_REUSEADDR, 1);
		socket_set_option($this->main_socket, SOL_SOCKET, SO_DEBUG, 1);
		//socket_set_nonblock($this->main_socket);
	}

	function socket_loop_old(){
		echo "\nstarting main socket loop\n";
		while(!$this->shutdown)
		{
			if(($client = socket_accept($this->main_socket)) !== false) {
				//socket_set_block($this->main_socket);
				$input = "";
				echo "Master: recieving\n";

				/**
				 while($buffer=socket_read($client,512)){
				 $input .= $buffer;
				 }
				 **/
				if (false !== ($bytes = socket_recv($client, $input, 1024, MSG_WAITALL))) {
					echo "Master: Read $bytes bytes from socket_recv(). Closing socket...\n";
				} else {
					echo "Master: socket_recv() failed; reason: " . socket_strerror(socket_last_error($client)) . "\n";
				}
					
				echo "Master: recieved ".strlen($input)." bytes input\n";
				socket_shutdown($client);
				socket_close($client);
					
				//$input = socket_read($client, 1024, PHP_NORMAL_READ) or die("\nCould not read input\n");
				$this->execute_event(trim($input));
				//socket_set_nonblock($this->main_socket);
				echo "Master: Cycle complete\n";
			} else if (socket_last_error($this->main_socket) != 0){
				echo "Master: error on master socket : ".socket_strerror(socket_last_error($this->main_socket))."\n";
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
					echo "***Event ". $evt['data']['response_type'] ." start \n";
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
				echo "Client: sending event {$e['response_type']}\n";
				msg_send($ipc, 1, array("event_type"=>$this->_ports[$port], "data"=>$e), 1);
			}


			/**   Socket based... was too slow
			 echo "Client: Fork Successfull -- using ".$this->xcb."\n";
			 //socket_listen($this->main_socket);
			 while ($e = xcb_wait_for_event($this->xcb)){
				echo "Client: event rcvd\n";
				//echo "\n\tevent recieved: ".var_export($e, 1)."\n";
				if ($socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) {
				if (socket_connect($socket, "127.0.0.1", $this->_firstPort)){
				$strCmd = serialize(array("event_type"=>$this->_ports[$port], "data"=>$e))."\n";
				echo "Clent: writing ".strlen($strCmd)." bytes to socket\n";
				if (socket_write($socket, $strCmd, strlen($strCmd)) == false) {
				if (socket_last_error($socket) != 0) {
				echo "Client: Socket Wrote Error".socket_strerror(socket_last_error($socket))."\n";
				}
				echo "Client: unable to write command : ".socket_strerror(socket_last_error($socket))."\n";
				} else {
				echo "Client: wrote to socket\n";
				}

				} else {
				echo "Client: unable to connect to parent socket\n";
				}
				echo "Client: closing socket\n";
				socket_close($socket);
				} else {
				echo "Client: unable to create socket\n";
				}


				}
				**/
		}
	}
}



?>
