<?php 
class xterm {
	function __construct($core, $arrArgs){
		$strApp =  "/usr/bin/xterm -display {$arrArgs['display']}";
		$strCommand = "nohup {$strApp} > /dev/null 2> /dev/null & echo $!";
		$PID = shell_exec($strCommand);
	}
}

?>