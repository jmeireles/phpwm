<?php 
error_reporting(0);
$handle = fopen("/usr/include/xcb/xproto.h", "r");
$toggle = 0;
if ($handle) {
    while (($line = fgets($handle)) !== false) {
    	if (substr($line, 0, 1) == "}"){
    		$toggle = 0;
    	}
    	if ($toggle){
    		list($name, $val) = explode("=", trim($line, " ,"));
    		if (isset($val)){
    			echo "define('".trim($name)."', ".intval(trim($val)).");\n";
    		}
    	}
        	if (substr($line, 0, strlen("typedef enum")) == "typedef enum"){
    		$name = trim(substr($line, strlen("typedef enum")));
    		echo "//".$name."\n";
    		$toggle = 1;
    	}
    }
    if (!feof($handle)) {
        echo "Error: unexpected fgets() fail\n";
    }
    fclose($handle);
}
?>