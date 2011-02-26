<?php
$core = new phpwm_core();

class phpwm_core{
	public $rootWin;
	public $_args = array();
	function __construct(){
		$this->_args = $GLOBALS['_PHPWM'];
		if ($this->_args['action']){
			$this->{$this->_args['action']}($this->_args);
		} else if ($this->_args['response_type']){
			$this->{$this->_args['response_type']}($this->_args);
		} else {
			echo "No action specified\n";
		}

	}
	function event_map_request($arrArgs){
		var_export($arrArgs);
		phpwm_window_map($arrArgs['id']);
		echo "**** window list****". var_export(phpwm_window_list(), 1);
	}
	function event_configure_request($arrArgs){

		//create a new window first.
		echo "configure request for window class of ".phpwm_window_get_class($arrArgs['id'])."\n";
		switch(phpwm_window_get_class($arrArgs['id'])){
			// 0 = app
			// 1 = frame
			// 2 = widgit
			case 1:
				echo "is a frame\n";
				phpwm_resize_window($arrArgs['id'],200, 200);
				phpwm_window_border($arrArgs['id'], 6);
				//phpwm_window_map($arrArgs['id']);
				break;
			default:
				echo "is not a frame\n";
				$newId = phpwm_window_generate_id();
				//				phpwm_resize_window($arrArgs['id'],200, 200);
				//				phpwm_window_border($arrArgs['id'], 1);
				phpwm_window_create_window($newId, 200, 200, 10, 10, 1);
				phpwm_reparent_window($arrArgs['id'], $newId);
		}

	}
	function takeOwnership($arrArgs){
		$newx = floor(rand(0,200));
		$newy = floor(rand(0,200));
		echo "moving {$arrArgs['id']} to $newx, $newy\n";
		phpwm_window_border($arrArgs['id'], 4);
		phpwm_move_window($arrArgs['id'], $newx, $newy);
	}

	function ConfigureRequest($arrArgs){
		phpwm_resize_window($arrArgs['window'],$arrArgs['width'], $arrArgs['height']);
		phpwm_move_window($arrArgs['window'],rand(0,100), rand(0,100));
		phpwm_window_border($arrArgs['window'], 6);
		phpwm_window_subscribe_events($arrArgs['window']);
	}
	function ConfigureNotify($arrArgs){

	}
	function MapRequest($arrArgs){
		phpwm_window_map($arrArgs['window']);
	}
	function EnterNotify($arrArgs){
		phpwm_raise_window($arrArgs['event']);
	}
	function LeaveNotify($arrArgs) {
		//		phpwm_lower_window($arrArgs['event']);
	}
	function ButtonPress($arrArgs){
		phpwm_report_button_press($arrArgs['event'], $arrArgs['time']);
	}
	function ButtonRelease($arrArgs){
		if ($arrArgs['time'] - phpwm_get_last_button_press($arrArgs['event']) < 50){
			phpwm_report_button_release($arrArgs['event'], $arrArgs['time']);
			$this->Maximize($arrArgs['event']);
		} else {
			phpwm_report_button_release($arrArgs['event'], $arrArgs['time']);
		}
	}

	function Maximize($intWindowid){
		phpwm_resize_window($intWindowid, phpwm_config_width_in_pixels()-12, phpwm_config_height_in_pixels()-12);
		phpwm_move_window($intWindowid, 0, 0);

	}
}

?>