<?php
	class MB_Uri  extends MB_Model 
	{
		private $MBCpath = array();
		static $_instance = false;
		var $countparam = 0;

		public function __construct()
		{	
			$this->Engage();
		}
		
		public function Engage()
		{
			$getpath = "";
			
			if(isset($_SERVER['PATH_INFO']))
			{
				$getpath = $_SERVER['PATH_INFO'];
			}
			elseif(isset($_SERVER['QUERY_STRING']))
			{
				$getpath = $_SERVER['QUERY_STRING'];
			}
			elseif(isset($_SERVER['argv'][1]))
			{
				$getpath = $_SERVER['argv'][1];
			}
			
			$new_path = str_replace("url=", "", $getpath);
			
			$this->MBCpath = explode('/', $new_path);
			
			$this->countparam = count($this->MBCpath);

			
			
		}
		
		public function getcontrollername()
		{
	
			if($this->MBCpath[0] == NULL)
			{
				$this->MBCpath[0] = "Home";			
			}
			
			return $this->MBCpath[0];	
		}
		
		public function getmethodname()
		{
			if($this->countparam != 1)
			{			
				return $this->MBCpath[1];
			}	
			else
			{
				return "Index";
			}
		}
		
		public function getparameters()
		{			
			array_shift($this->MBCpath);
			array_shift($this->MBCpath);
			return $this->MBCpath;
		}

		
		
	}
?>