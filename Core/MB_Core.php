<?php
/*Author-engrMarkbameMartires*/
	$coredir = array(
						MB_ROOT."/Core/MB_Model.php",
						MB_ROOT."/Core/MB_Controller.php",	
						MB_ROOT."/Core/MB_Uri.php"
					);
					
	foreach($coredir as $dir)
	{
		if(!file_exists($dir))
		{
			echo "File missing: <font color = 'red'>".$dir.".</font><br>";
			exit;
		}
		else
		{
			require_once($dir);
		}
	}

	class MB_Core
	{
	
		public function Run()
		{	
			
			if(!isset($_SESSION['started']))
			{
				 session_start();
				 $_SESSION['started'] = true;

				 if(!isset($_SESSION['USERCATEGORY']))
				 {
				 	$_SESSION['USERCATEGORY'] = "guest";
				 }

				if(DEBUGMODE)
				{
					echo "<br/>session started: ".$_SESSION['started'];
				}

			}
		
			$dataconf = array();
			$uri = new MB_Uri;
			
			$cont = $uri->getcontrollername();
			$method = $uri->getmethodname();
			$params = $uri->getparameters();

			$dataconf['cont'] = $cont;

			if(DEBUGMODE)
			{
				echo "<br/>controller: ".$cont;
				echo "<br/>method: ".$method;
				echo "<br/>params: ";
				print_r($params);
			}
			
			foreach($params as $pi => $pv)
			{
				$params[$pi] = htmlentities(stripslashes($pv), ENT_QUOTES, "UTF-8");
			}
					
			
			$this->Defineconfig($dataconf);

			$_SESSION["HOST"] = HOST;
			$_SESSION["USERNAME"] = USERNAME;
			$_SESSION["PASSWORD"] = PASSWORD;
			$_SESSION["DATABASE"] = DATABASE;
			
			$cont = ucfirst($cont);

			$theme = new MB_Model;
			$theme->dbConn(); 
			$thm = $arrayName = array();
			$thm = $theme->Getdatacondition("general","value","function='theme'");

			define(THEME,$thm['value']);
			
			if($cont !="Mbadmin")
			{
				$this->parseURI($cont,$method,$params);
			}
			else
			{
				$this->parseAdminURI($cont,$method,$params);
			}
			
		}
		
		public function parseAdminURI($currApp,$currMeth,$currPar=array())
		{		
			$currApp = ucfirst($currApp);
			$currMeth = ucfirst($currMeth);

			if($currApp != "Login")
			{	
				if($currApp != "Cont")
				{
					if($currApp != "Home")
					{
						$_SESSION['CURURL'] = URL."/".$currApp."/".$currMeth."/".implode("/",$currPar);
					}	
				}	
			}
			
			$folder = MB_ROOT."Apps/Admin/Admin.php";
			
			if(!file_exists($folder))
			{
				$this->error(DEBUGMODE, $folder);	
				
			}

			include($folder);
					
			$cont = new Admin;
			$cont->Index($currApp,$currMeth,$currPar);
			
		}

		public function parseURI($currApp,$currMeth,$currPar=array())
		{		
			$currApp = ucfirst($currApp);
			$currMeth = ucfirst($currMeth);

			if($currApp != "Login")
			{	
				if($currApp != "Cont")
				{
					if($currApp != "Home")
					{
						$_SESSION['CURURL'] = URL."/".$currApp."/".$currMeth."/".implode("/",$currPar);
					}	
				}	
			}
			
			$folder = MB_ROOT."Apps/Page/Page.php";
			
			if(!file_exists($folder))
			{
				$this->error(DEBUGMODE, $folder);	
				
			}

			include($folder);
					
			$cont = new Page;
			$cont->Index($currApp,$currPar);
			
		}

		public function Defineconfig($data = array())
		{
			
			include(MB_CONF);
		
			foreach($config as $confindx=>$confvalue)
			{
				define(strtoupper($confindx),$confvalue);
			}

			if(DEBUGMODE)
			{
				echo "<br/>";
				print_r(get_defined_vars());
			}
		}
			
		public function error($debugmode, $errorname)
		{
			if($debugmode)
			{
				echo "<br/><font color='red'>error: file ".$errorname." does not exist";
				exit;
			}
			else
			{
				header("Location:".URL."Error/Index/"."404");
			}
		}
		
	}

?>