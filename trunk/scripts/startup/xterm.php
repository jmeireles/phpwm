<?php
class xterm {
	function __construct($core, $arrArgs){
	if (isset($arrArgs['display'])){
			$disp = "-display {$arrArgs['display']}";
		}
		$this->runApp("/usr/bin/xterm {$disp}");

	}
	function runApp($strApp){
		$strCommand = "nohup {$strApp} > /dev/null 2> /dev/null & echo $!";
		$PID = shell_exec($strCommand);
	}
}

?>