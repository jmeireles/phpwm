<?php
/*
 *  phpwm -- the PHP Based Window Manager
 *  Copyright (C) 2011  Davin C. Thompson <dthompso99@gmail.com>
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
define("PHPWM_STATE_NORMAL", 0);
define("PHPWM_STATE_MAXIMIZED", 1);
define("PHPWM_STATE_MINIMIZED", 2);

define("PHPWM_DRAG_NORMAL", 0);
define("PHPWM_DRAG_DRAGGING", 1);

$core = new phpwm_core();
class phpwm_core{
	public $rootWin;
	public $_args = array();
	function __construct(){
		$this->_args = $GLOBALS['_PHPWM'];
		if (isset($this->_args['action'])){
			$this->{$this->_args['action']}($this->_args);
		} else if ($this->_args['response_type']){
			$this->{$this->_args['response_type']}($this->_args);
		} else {
			echo "No action specified\n";
		}

	}
	function application_startup($arrArgs){
		$strApp =  "/usr/bin/xterm -display {$arrArgs['display']}";
		$strCommand = "nohup {$strApp} > /dev/null 2> /dev/null & echo $!";
		echo "Spawn: $strCommand \n";
		$PID = shell_exec($strCommand);
		echo($PID);
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
		phpwm_set_window_state($arrArgs['window'], PHPWM_STATE_NORMAL);
		phpwm_set_drag_state($arrArgs['window'], PHPWM_DRAG_NORMAL);
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
		echo "time since last press:".($arrArgs['time'] - phpwm_get_last_button_press($arrArgs['event']))."\n";
		if (($arrArgs['time'] - phpwm_get_last_button_press($arrArgs['event'])) < 150){
			phpwm_report_button_press($arrArgs['event'], $arrArgs['time']);
			$this->doubleclick($arrArgs);
		} else {
			phpwm_report_button_press($arrArgs['event'], $arrArgs['time']);
			$this->singleclick($arrArgs);
		}
	}
	function ButtonRelease($arrArgs){
		phpwm_report_button_release($arrArgs['event'], $arrArgs['time']);
		if (phpwm_get_drag_state($arrArgs['event'])==PHPWM_DRAG_DRAGGING){
			phpwm_set_drag_state($arrArgs['event'], PHPWM_DRAG_NORMAL);
		}
	}
	function MotionNotify($arrArgs){
		echo "Motion: event:{$arrArgs['event']} child:{$arrArgs['child']} state: ".phpwm_get_drag_state($arrArgs['event'])."\n";
		if (phpwm_get_drag_state($arrArgs['event'])==PHPWM_DRAG_DRAGGING){
			phpwm_move_window($arrArgs['event'], $arrArgs['event_x'], $arrArgs['event_y']);
		}
	}
	function singleclick($arrArgs){
		//see if the button is still down
		if (phpwm_get_last_button_release($arrArgs['event']) < phpwm_get_last_button_press($arrArgs['event'])){
			echo "Button is still down on : {$arrArgs['event']}\n";
			phpwm_set_drag_state($arrArgs['event'], PHPWM_DRAG_DRAGGING);
			echo "drag state set to: ".phpwm_get_drag_state($arrArgs['event'])."\n";
		} else {
			echo "Button has been released\n";
			phpwm_set_drag_state($arrArgs['event'], PHPWM_DRAG_NORMAL);
		}
	}

	function doubleclick($arrArgs){
		echo "Double Click Event \n";
		if (phpwm_get_window_state($arrArgs['event'])==PHPWM_STATE_NORMAL){
			$this->Maximize($arrArgs['event']);
		} else {
			$this->restore($arrArgs['event']);
		}
	}

	function Maximize($intWindowid){
		echo "set max\n";
		phpwm_set_window_state($intWindowid, PHPWM_STATE_MAXIMIZED);
		phpwm_resize_window($intWindowid, phpwm_config_width_in_pixels()-12, phpwm_config_height_in_pixels()-12);
		phpwm_move_window($intWindowid, 0, 0);
	}
	function restore($intWindowid){
		echo "set restored\n";
		phpwm_set_window_state($intWindowid, PHPWM_STATE_NORMAL);
		phpwm_resize_window($intWindowid, floor(phpwm_config_width_in_pixels()/2), floor(phpwm_config_height_in_pixels()/2));
		phpwm_move_window($intWindowid, 20, 20);
	}
	
}

?>