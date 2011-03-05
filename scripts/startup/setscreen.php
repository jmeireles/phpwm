<?php
class setscreen {
	function __construct($core, $arrArgs){
		if (isset($arrArgs['display'])){
			$disp = "-display {$arrArgs['display']}";
		}
		$this->runApp("xsetroot \-mod 16 16 \-fg rgb:54/6/6 \-bg grey20 {$disp}");
		$this->runApp("xsetroot \-cursor_name left_ptr \-fg white \-bg black {$disp}");
		$arrWindows = phpwm_window_list();
		foreach ($arrWindows as $win){
			phpwm_window_subscribe_events($win);
		}

	}
	function runApp($strApp){
		$strCommand = "nohup {$strApp} > /dev/null 2> /dev/null & echo $!";
		$PID = shell_exec($strCommand);
	}
}

?>