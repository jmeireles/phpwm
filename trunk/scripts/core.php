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
require_once 'scripts/events.php';
define("PHPWM_STATE_NORMAL", 0);
define("PHPWM_STATE_MAXIMIZED", 1);
define("PHPWM_STATE_MINIMIZED", 2);
define("PHPWM_STATE_LEFT", 3);
define("PHPWM_STATE_RIGHT", 4);

define("PHPWM_DRAG_NORMAL", 0);
define("PHPWM_DRAG_DRAGGING", 1);

$core = new phpwm_core();
class phpwm_core{
	public $rootWin, $events;
	public $_args = array();
	function __construct(){
		$this->events = new phpwm_events($this);
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
		$arrDir = scandir("./scripts/startup");
		foreach($arrDir as $strFile){
			if (substr($strFile, -3) == "php"){
				include_once './scripts/startup/'.$strFile;
				$strClassName = substr($strFile, 0, -4);
				$objApp = new $strClassName($this, $arrArgs);
			}
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
	/*
	 * Minimize and Maximize functions
	 */
	function Maximize($intWindowid){
		$arrGeom = phpwm_get_geometry($intWindowid);
		phpwm_set_last_window_pos($intWindowid, $arrGeom['x'], $arrGeom['y'], $arrGeom['width'], $arrGeom['height']);
		phpwm_set_window_state($intWindowid, PHPWM_STATE_MAXIMIZED);
		phpwm_resize_window($intWindowid, phpwm_config_width_in_pixels()-12, phpwm_config_height_in_pixels()-12);
		phpwm_move_window($intWindowid, 0, 0);
	}
	function MaximizeLeft($intWindowid){
		$arrGeom = phpwm_get_geometry($intWindowid);
		phpwm_set_last_window_pos($intWindowid, $arrGeom['x'], $arrGeom['y'], $arrGeom['width'], $arrGeom['height']);
		phpwm_set_window_state($intWindowid, PHPWM_STATE_LEFT);
		phpwm_resize_window($intWindowid, floor((phpwm_config_width_in_pixels()-12)/2), phpwm_config_height_in_pixels()-12);
		phpwm_move_window($intWindowid, 0, 0);
	}
	function MaximizeRight($intWindowid){
		$arrGeom = phpwm_get_geometry($intWindowid);
		phpwm_set_last_window_pos($intWindowid, $arrGeom['x'], $arrGeom['y'], $arrGeom['width'], $arrGeom['height']);
		phpwm_set_window_state($intWindowid, PHPWM_STATE_RIGHT);
		phpwm_resize_window($intWindowid, floor((phpwm_config_width_in_pixels()-12)/2), phpwm_config_height_in_pixels()-12);
		phpwm_move_window($intWindowid, floor(phpwm_config_width_in_pixels()/2), 0);
	}
	function Restore($intWindowid){
		$arrPos = phpwm_get_last_window_pos($intWindowid);
		phpwm_set_window_state($intWindowid, PHPWM_STATE_NORMAL);
		phpwm_resize_window($intWindowid, floor(phpwm_config_width_in_pixels()/2), floor(phpwm_config_height_in_pixels()/2));
		phpwm_move_window($intWindowid, $arrPos['x'], $arrPos['y']);
	}

	/**
	 * From here down, these are the raw events that xcb sends.
	 */
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
		echo "Button Clicked: ".$arrArgs['detail']."\n";
		switch($arrArgs['detail']){
			case 1:
				echo "time since last press:".($arrArgs['time'] - phpwm_get_last_button_press($arrArgs['event']))."\n";
				if (($arrArgs['time'] - phpwm_get_last_button_press($arrArgs['event'])) < 150){
					phpwm_report_button_press($arrArgs['event'], $arrArgs['time']);
					$this->events->doubleclick_button_1($arrArgs);
				} else {
					phpwm_report_button_press($arrArgs['event'], $arrArgs['time']);
					$this->events->singleclick_button_1($arrArgs);
				}
				break;
			case 2:
				$this->events->singleclick_button_2($arrArgs);
				//middle mouse button click
				break;
			case 3:
				$this->events->singleclick_button_3($arrArgs);
				//right mouse button click
				break;
			case 4:
				$this->events->singleclick_button_4($arrArgs);
				break;
			case 5:
				$this->events->singleclick_button_5($arrArgs);
				break;
			case 6:
				$this->events->singleclick_button_6($arrArgs);
				break;
			case 7:
				$this->events->singleclick_button_7($arrArgs);
				break;
		}

	}

	function ButtonRelease($arrArgs){
		phpwm_report_button_release($arrArgs['event'], $arrArgs['time']);
		if (phpwm_get_drag_state($arrArgs['event'])==PHPWM_DRAG_DRAGGING){
			phpwm_set_drag_state($arrArgs['event'], PHPWM_DRAG_NORMAL);
		}
	}

	function MotionNotify($arrArgs){

	}

	function CreateNotify($arrArgs){

	}
	function MapNotify($arrArgs){

	}
}

?>