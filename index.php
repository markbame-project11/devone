<?php
/*Author-engrMarkbameMartires*/	
	$MB_ROOT = getcwd()."/";
	$MB_CONF = $MB_ROOT."Core/MB_Config.php";
		
	define("MB_ROOT", $MB_ROOT);
	define("MB_CONF", $MB_CONF);
	define("DEBUGMODE", false);
	
	$core = MB_ROOT."Core/MB_Core.php";

	if(!file_exists($MB_CONF))
	{
		echo "error: <font color = 'red'>config file does not exist!</font><br>";
		exit;
	}
		
	if(!file_exists($core))
	{
		echo "File missing: <font color = 'red'>".$core.".</font><br>";
		exit;
	}
	
	require_once(MB_ROOT."Core/MB_Core.php");
	
	
	$core = new MB_Core;
	
	$core->Run();
	
	exit;
?>