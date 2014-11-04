<?php
	session_start();
	if(isset($_SESSION['FIRSTNAME']) && isset($_SESSION['LOGED'])  && ($_SESSION['ACTIVATED']=="1"))
	{

		$url = $_POST['path'];

		if(preg_match('/[0-9a-zA-Z]{1,26}.[jpg][pni][gf]/i',$url,$match)) {
    		
    		$delfile = dirname(__FILE__)."/Uploads/". $_POST['dir'].$match[0];
    		unlink($delfile);
		}

	}

?>