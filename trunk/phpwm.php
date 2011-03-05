<?php
require_once 'classes/class.events.php';
require_once 'classes/class.window.php';
require_once 'classes/class.graphics.php';
require_once 'classes/class.common.php';
$wm = new phpwm("127.0.0.1:1");

class phpwm{
	public $root = array();
	public $windows = array();
	public $_args = array();
	function __construct($strDisplay){
		$this->_parseArgs($_SERVER['argv']);
		xcb_init($strDisplay);
		$this->root['id'] = xcb_root_id();
		$this->manageRoot();
		$this->_startup();
		$this->mainEventLoop();
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
		echo "Running: $strCommand\n";
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
		xcb_configure_window_events($this->root['id'], array(1048576, 131072, 524288));
		//xcb_configure_window_events_root($this->root['id']);
		//removed 131072
		//register for events on the root window

	}
	function mainEventLoop(){
		$this->_events = new phpwm_events($this);
		while ($e = xcb_wait_for_event()){
			if (method_exists($this->_events, "evt_".$e['response_type'])){
				//echo "***Event {$e['response_type']} start \n";
				$this->_events->{"evt_".$e['response_type']}($e);
			} else {
				echo "No Method for {$e['response_type']} event \n";
				var_export($e);
			}
				
		}
	}
}



?>
