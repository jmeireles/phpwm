<?php 
class taskbar {
	function __construct($core, $args){
		$this->rootWindow = phpwm_window_generate_id();
		phpwm_window_create_window($this->rootWindow, 0, 0, phpwm_config_width_in_pixels(),20, 2);
		phpwm_window_border($this->rootWindow, 0);
		phpwm_window_map($this->rootWindow);
	}
}

?>