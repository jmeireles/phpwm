<?php
class phpwm_events{
	function __construct($objCore){
		$this->core = $objCore;
	}

	function doubleclick_button_1($arrArgs){
		if (phpwm_get_window_state($arrArgs['event'])==PHPWM_STATE_NORMAL){
			$this->core->Maximize($arrArgs['event']);
		} else {
			$this->core->Restore($arrArgs['event']);
		}
	}
	function singleclick_button_1($arrArgs){
		if (phpwm_get_last_button_release($arrArgs['event']) < phpwm_get_last_button_press($arrArgs['event'])){
			echo "Button is still down on : {$arrArgs['event']}\n";
			phpwm_set_drag_state($arrArgs['event'], PHPWM_DRAG_DRAGGING);
		} else {
			echo "Button has been released\n";
			phpwm_set_drag_state($arrArgs['event'], PHPWM_DRAG_NORMAL);
		}
	}
	function singleclick_button_2($arrArgs){

	}
	function singleclick_button_3($arrArgs){

	}
	function singleclick_button_4($arrArgs){
		if (phpwm_get_window_state($arrArgs['event'])==PHPWM_STATE_NORMAL){
			$arrGeom = phpwm_get_geometry($arrArgs['event']);
			phpwm_resize_window($arrArgs['event'], $arrGeom['width']+10, $arrGeom['height']+10);
		}
	}
	function singleclick_button_5($arrArgs){
		if (phpwm_get_window_state($arrArgs['event'])==PHPWM_STATE_NORMAL){
			$arrGeom = phpwm_get_geometry($arrArgs['event']);
			phpwm_resize_window($arrArgs['event'], $arrGeom['width']-10, $arrGeom['height']-10);
		}
	}
	function singleclick_button_6($arrArgs){
		$this->core->MaximizeLeft($arrArgs['event']);
	}
	function singleclick_button_7($arrArgs){
		$this->core->MaximizeRight($arrArgs['event']);
	}
}
?>