<?php
class xterm {
	function __construct($core, $arrArgs){
		$this->runApp("/usr/bin/xterm -display {$arrArgs['display']}");

	}
	function runApp($strApp){
		$strCommand = "nohup {$strApp} > /dev/null 2> /dev/null & echo $!";
		$PID = shell_exec($strCommand);
	}
}

?>