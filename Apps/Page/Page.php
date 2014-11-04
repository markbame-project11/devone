<?php
	
	class Page extends MB_Controller
	{
		public function Index($method,$params = array())
		{	

			$method = $this->sanitize($method);
			$pagenumber = $this->sanitize($params[0]);
			
			if($method == "")
			{
				$method = "home";
			}

			if($params[0] == "")
			{
				$pagenumber = "1";
			}

			$_SESSION['PAGE'] = $pagenumber;

			$data = array();
			$qdata = array();
			$qdata['tablename']="pages";
			$qdata['column']="*";
			$qdata['condition']="pagename ='".$method."'";

			$fromdb = $this->sQuerry("Getsingledata",$qdata);

			if($fromdb)
			{

				if(isset($_SESSION['USERCATEGORY']) && $_SESSION['USERCATEGORY'] != "guest")
				{

					if($_SESSION['USERCATEGORY'] == "admin")
					{
						$data['DROPMENU'] = '<li><a href="{URL}/mbadmin">Admin</a></li>';
					}

					$data['DROPMENU'] .= '<li><a href="{URL}/Logout">Logout</a></li>';
				}
				else
				{
					$data['DROPMENU'] ="&nbsp;";
				}


				if($fromdb['plugin'] == "none")
				{
						$cdata = array();
						$cdata['tablename']="contents";
						$cdata['column']="*";
						$cdata['condition']="pagesid ='".$fromdb['id']."'";
						$cdata['limit']=LISTLIMIT;
						$cdata['page']=$pagenumber;
						$getcont = $this->sQuerry("PaginationList",$cdata);
						$output = "";
						if($getcont)
						{
							while($rows = mysql_fetch_assoc($getcont))
							{
								$output .=  '<blockquote>
											 <a href="{URL}/article/'.$rows["title"].'/'.$rows["id"].'" class ="arttitle">'.$rows["title"].'</a>
									 		 <small class="artdesc">'.substr($rows["shortdescription"], 0,370).'</small>
											  <small><strong>'.$rows["author"].', </strong><cite title="Source Title">'.date('D, d M Y h:i:s', strtotime ($rows["date"])).'</cite></small>
											</blockquote>';
							}

							 
							 $cdata['count'] = $this->sQuerry("PaginationCount",$cdata);
							 $cdata['url'] ="/".$method."/pages/";
							 $cdata['output'] =$output;
							 $cdata['page'] ="1";
							 $data['CONTENT'] = $this->sQuerry("Pagination",$cdata);
						}
				}
				else
				{	
					if($fromdb['status'] == "active")
					{	
					
						if($fromdb['privilege'] == $_SESSION['USERCATEGORY'] || ($_SESSION['USERCATEGORY'] =="admin"))
						{
							$data['CONTENT'] = $this->$fromdb['plugin']($params);
						}
						else
						{	
							$data['CONTENT'] =  $this->$method($params);
						}
					}
					else
					{
						$data['CONTENT'] = $this->error("IAP 404");
					}
				}

				
			}
			else
			{
				
				$data['CONTENT'] = $this->error();
			}

			if(isset($_SESSION['FIRSTNAME']))
			{
				$data['LOGIN'] = $this->useralert(ucfirst($_SESSION['USERCATEGORY'])." ".ucfirst($_SESSION['FIRSTNAME']));
			}
			else
			{
				$data['USER'] = "Guest";
				$data['LOGIN'] = $this->getfilecont(MB_ROOT."Cont/Themes/".THEME."/html/","loginform.html");
				
			}
			
				$data['LOGIN'] = "";

				$slideimages = $this->read_folder_directory(MB_ROOT."Lib/upload/Uploads/gallery/slide/");
				$data['CAMERA'] ="";
				foreach ($slideimages as $slm) {
					$data['CAMERA'] .='<div data-src="{URL}/Lib/upload/Uploads/gallery/slide/'.$slm.'"></div>';
				}

				$rdimg = $this->read_folder_directory(MB_ROOT."Lib/upload/Uploads/gallery/RD/");
				$data['RD'] = '<img class = "RDimg" src="{URL}/Lib/upload/Uploads/gallery/RD/'.$rdimg[0].'" />';

				$data['RIGHTIMAGES'] = "";
				$logoimgs = $this->read_folder_directory(MB_ROOT."Lib/upload/Uploads/gallery/Logos/");
				foreach ($logoimgs as $lgo) {
					$data['RIGHTIMAGES'] .='<img class = "logos" src="{URL}/Lib/upload/Uploads/gallery/Logos/'.$lgo.'"/>';
				}
				
				$bannerimg = $this->read_folder_directory(MB_ROOT."Lib/upload/Uploads/gallery/banner/");
				$data['BANNERIMG'] = $bannerimg[0];

				$data['VISITS'] = $this->PaginationCount("tracker","id!='0'");

				$data['TITLE'] = TITLE;

				$pdata = array();
				$pdata['tablename']="pages";
				$pdata['column']="*";
				$pdata['condition']="status ='active' AND plugin = 'none' ORDER BY navorder ASC";
				$getpcont = $this->sQuerry("GetDataCond",$pdata);
				$data['NAVLINKS'] = "";


				if($getpcont)
				{
					while($row = mysql_fetch_assoc($getpcont))
					{
						$data['NAVLINKS'] .= '<li><a href="{URL}/'.$row['pagename'].'">'.ucfirst($row['pagename']).'</a></li>';
					}
				}

				$tdata = array();
				$tdata['tablename']="general";
				$tdata['column']="*";
				$tdata['condition']="function ='theme' AND status = 'active'";
				$gettcont = $this->sQuerry("GetDataCond",$tdata);

				if($gettcont)
				{
					if($row = mysql_fetch_assoc($gettcont))
					{
						$theme = $row['value'];
					}
				}
				else
				{
					if(DEBUGMODE)
					{	
						echo "invalid theme!<br/>";
					}
					exit;
				}


				

				//$this->Analytics();
				$_SESSION['THEME'] = $theme;

				$this->Loadview($theme,$theme,$data,false);

		}

		public function Article($d=array())
		{
			
			$qdata = array();
			$qdata['tablename']="contents";
			$qdata['column']="*";
			$qdata['condition']="id ='".$d[0]."'";

			$d = $this->sQuerry("Getsingledata",$qdata);

			return $this->Loadview(THEME,"article",$d,true);
		}

		public function Home($d = array())
		{	
			$qdata = array();
			$qdata['tablename']="pages";
			$qdata['column']="*";
			$qdata['condition']="pagename ='latest'";

			$fromdb = $this->sQuerry("Getsingledata",$qdata);

			if($fromdb)
			{
				$pagenumber = "1";
				$cdata = array();
				$cdata['tablename']="contents";
				$cdata['column']="*";
				$cdata['condition']="pagesid ='".$fromdb['id']."'";
				$cdata['limit']=5;
				$cdata['page']=$pagenumber;
				$getcont = $this->sQuerry("PaginationList",$cdata);
				$output = "";
				if($getcont)
				{
					while($rows = mysql_fetch_assoc($getcont))
					{
						$output .=  '<blockquote>
									  <a href="{URL}/article/'.$rows["title"].'/'.$rows["id"].'" class ="arttitle">'.$rows["title"].'</a>
									  <small class="artdesc">'.substr($rows["shortdescription"], 0,370).'</small>
									  <small><strong>'.$rows["author"].', </strong><cite title="Source Title">'.date('D, d M Y h:i:s', strtotime ($rows["date"])).'</cite></small>
									</blockquote>';
					}

					 $d['LATEST'] = $output;
				}
			}

			return $this->Loadview(THEME,"home",$d,true);
		}
	
		public function dateexpander($rawdate)
		{
			$date = new DateTime();
			date_default_timezone_set("Asia/Manila");
			
			$date = str_replace("/", "-",$rawdate);
			$xdate = explode("-",$date);

			$hours = 0;
			$minutes = 0;
			$seconds = 0;
			$days = $xdate[2];
			$months = $xdate[1];
			$years = $xdate[0];

			$time = mktime($hours, $minutes, $seconds, $months, $days, $years);
			return date('Y-m-d H:i:s', $time);
		}

		public function logout(){
			session_destroy();
			header('Location: '.URL);
		}

		public function mblogin($data=array())
		{
			return $this->Loadview(THEME,"loginform",$data,true);
		}




		public function login($data=array())
		{
			
			if(isset($_POST['username'])  && isset($_POST['password'])  && ($_POST['username'] != "") && ($_POST['password'] != ""))
			{	
				
				if($this->sQuerry("Verifyuser",$_POST))
				{	
					if(isset($_SESSION['CURURL']))
					{
						header('Location: '.$_SESSION['CURURL']);
					}
					else
					{
						header('Location: '.URL);
					}
				}
				else
				{
					return $this->error("Invalid Login!");
				}
			}
			else
			{
				return $this->error("Invalid Login!");
			}

		}

		function isLoggedIn()
		{

			if(isset($_SESSION['FIRSTNAME']) && isset($_SESSION['LOGED'])  && ($_SESSION['ACTIVATED']=="1"))
			{
				return true;
			}
			else
			{
				return false;
			}

		}
		
		public function error($message = "PAGE NOT FOUND - 404")
		{
			$error = '
						<div class="alert alert-dismissable alert-danger">
						  <button type="button" class="close" data-dismiss="alert">X</button>
						  <strong>Error! '.$message.'</strong>.
						</div>
						';
			return $error;
		}

		public function sucess($message = "Success!")
		{
			$error = '
						<div class="alert alert-dismissable alert-success">
						  <button type="button" class="close" data-dismiss="alert">X</button>
						  <strong>Message! '.$message.'</strong>.
						</div>
						';
			return $error;
		}

		public function useralert($user)
		{
			return '<div class="alert alert-dismissable alert-info col-lg-12">
					  <strong>&raquo; Welcome '.$user.'!</strong>
					</div>
					';
		}


		public function getfilecont($url,$filename)
		{
			$viewfile = $url.$filename;
			
			if(!file_exists($viewfile))
            {	
				if(DEBUGMODE)
				{
					echo "error: no view file found @: ".$viewfile;
					exit;
				}
			}	
			
			return file_get_contents($viewfile);
		}

		public function crypto_rand_secure($min, $max) {
	        $range = $max - $min;
	        if ($range < 0) return $min; // not so random...
	        $log = log($range, 2);
	        $bytes = (int) ($log / 8) + 1; // length in bytes
	        $bits = (int) $log + 1; // length in bits
	        $filter = (int) (1 << $bits) - 1; // set all lower bits to 1
	        do {
	            $rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes)));
	            $rnd = $rnd & $filter; // discard irrelevant bits
	        } while ($rnd >= $range);
	        return $min + $rnd;
		}

		public function getToken($length){
		    $token = "";
		    $codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
		    $codeAlphabet.= "abcdefghijklmnopqrstuvwxyz";
		    $codeAlphabet.= "0123456789";
		    for($i=0;$i<$length;$i++){
		        $token .= $codeAlphabet[$this->crypto_rand_secure(0,strlen($codeAlphabet))];
		    }
		    return $token;
		}

		

	}
?>