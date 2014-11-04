<?php
/*Author-engrMarkbameMartires*/
	class MB_Controller extends MB_Model
	{
		 public function __construct()
		{
			$this->dbConn();
		}

		public function sQuerry($kind,$params = array(),$aux="",$aux2="",$params2=array())
		{
			switch ($kind) {
			    case Getsingledata:
			        return $this->Getdatacondition($params['tablename'],$params['column'],$params['condition']);
			        break;
			    case PaginationList:
			        return $this->PaginationList($params['tablename'],$params['condition'],$params['limit'],$params['page']);
			        break;
			    case PaginationCount:
			        return $this->PaginationCount($params['tablename'],$params['condition']);
			        break;
			    case Pagination:
			        return $this->Pagination($params['count'],$params['limit'],$params['url'],$params['output'],$params['page']);
			        break;
			    case Verifyuser:
			        return $this->Verifyuser($params);
			        break;
			    case InsertData:
			        return $this->InsertData($aux,$params);
			        break;
			    case UpdateData:
			        return $this->Updatedata($aux,$aux2,$params);
			        break;
			    case DeleteData:
			        return $this->Deletedata($aux,$params['id']);
			        break;
			    case GetDataCond:
			        return $this->Getdataconditionraw($params['tablename'],$params['column'],$params['condition']);
			        break;
			    case ListMaker:
			        return $this->listmaker($params,$params2,$aux);
			        break;
			}
		}

		public function is_bot(){
				$botlist = array("Teoma", "alexa", "froogle", "Gigabot", "inktomi",
					"looksmart", "URL_Spider_SQL", "Firefly", "NationalDirectory",
					"Ask Jeeves", "TECNOSEEK", "InfoSeek", "WebFindBot", "girafabot",
					"crawler", "www.galaxy.com", "Googlebot", "Scooter", "Slurp",
					"msnbot", "appie", "FAST", "WebBug", "Spade", "ZyBorg", "rabaz",
					"Baiduspider", "Feedfetcher-Google", "TechnoratiSnoop", "Rankivabot",
					"Mediapartners-Google", "Sogou web spider", "WebAlta Crawler","TweetmemeBot",
					"Butterfly","Twitturls","Me.dium","Twiceler","Purebot","facebookexternalhit",
					"Yandex","CatchBot","W3C_Validator","Jigsaw","PostRank","Purebot","Twitterbot",
					"Voyager","zelist");

				foreach($botlist as $bot){
					if(strpos($_SERVER['HTTP_USER_AGENT'],$bot)!==false)
					return "true";	// Is a bot
				}
				return "false";	// Not a bot
			}

		public function Analytics()
		{
			$tablename = "tracker";
			$data = array();
			$data['ip'] = $_SERVER['REMOTE_ADDR'];
			$data['query_string'] = substr($_SERVER['QUERY_STRING'], 4);
			$data['http_referer'] = $_SERVER['HTTP_REFERER'];
			$data['http_user_agent'] =$_SERVER['HTTP_USER_AGENT'];
			$data['remote_host'] = $_SERVER['REMOTE_HOST'];
			$data['request_uri'] = $_SERVER['REQUEST_URI'];
			$data['isbot'] = $this->is_bot();
			$data['time'] = date("H:i:s");
			$data['datevisited'] = date("Y-m-d");

			if($data['request_uri'] != "/dev3/Cont/Themes/default/css/style.css" && $data['query_string'] != "")
			{	
				$this->InsertData($tablename,$data,false);	
			}
		}
		
		public function Loadview($viewfolder,$viewname, $data = array(), $return = false)
		{	
			
			$data["URL"] = URL;

			$viewfile = MB_ROOT."Cont/Themes/".$viewfolder."/html/".$viewname.".html";
			
			if(!file_exists($viewfile))
            {	
				if(DEBUGMODE)
				{
					echo "error: no view file found @: ".$viewfile;
					exit;
				}
			}	
			
			$userinterface = file_get_contents($viewfile);
			
			$patterns = array();
			
			$replacements = array();
			
			foreach($data as $item => $value)
			{
				$patterns[] = "/{".$item."}/";
				$replacements[] = $this->htmldecode($value);
			}
			
			$numberofitems = count($patterns);
			
			while($numberofitems != 0)
			{
				$numberofitems--;
				$userinterface = preg_replace($patterns, $replacements, $userinterface );				
			}
			
			if(!$return)
			{
				echo $userinterface;
				exit;
			}
			
			return $userinterface;
		}

		public function listmaker($fetchinfo=array(),$headers=array(),$method)
		{	
			$cdata = array();
			$cdata['tablename']=$fetchinfo['tablename'];
			$cdata['column']=$fetchinfo['column'];
			$cdata['condition']=$fetchinfo['condition'];
			$cdata['limit']=$fetchinfo['limit'];
			$cdata['page']=$fetchinfo['pagenumber'];
			$cdata['methodid']=$fetchinfo['methodid'];
			$getcont = $this->sQuerry("PaginationList",$cdata);
			
			if($getcont)
			{

				$output =	'<table class="table table-striped table-hover ">
						  <thead>
						    <tr>
						     
						      ';

						      foreach ($headers as $hdrs) {
						      	 $output .= '<th>'.$hdrs.'</th>';
						      }


				$output .=  '<th>action</th>
						    </tr>
						  </thead>
						  <tbody>';

				while($rows = mysql_fetch_assoc($getcont))
				{
					if($rows["status"] =="2")
					{
						$class ="success";
					}
					elseif($rows["status"] =="inactive")
					{
						$class ="danger";
					}
					else
					{
						$class ="";
					}

						$output .=	'<tr class ="'.$class.'">';
				  
								      foreach ($rows as $key => $value) 
								      {
								      		if(in_array($key, $headers))
								      		{
								      			$output .= '<td>'.$rows[$key].'</td>';
								      		}
								      }

						$output .=  '<td><a href="{URL}/mbadmin/Edit/'.$rows["id"].'/'.$method.'">Edit</a>/<a href="{URL}/mbadmin/Delete/'.$rows["id"].'/'.$method.'" onclick="return confirm(\''."confirm delete?".'\')">Delete</a></td>
								    </tr>';
				}
				
				if($fetchinfo['id'] == "none")
				{
					$urlid = "";
				}
				else
				{
					$urlid = $fetchinfo['id']."/";
				}

				 $output .=	'</tbody></table>';
				 $cdata['count'] = $this->sQuerry("PaginationCount",$cdata);
				 $cdata['url'] ="/mbadmin/".$method."/";
				 $cdata['output'] =$output;
				 $cdata['page'] ="1";
				 $data['CONTENT'] = "<h5 class='well well-sm col-lg-2'>"
				 					.ucfirst($method)
				 					."</h5><h5 class='well well-sm col-lg-2' style = 'margin-left:5px;'>Total Entry &raquo; ".$cdata['count']
				 					."</h5>"
				 					.'<a type="button" class="btn btn-default" style="margin:10px 0 0 5px;width:150px;height:35px" href="'.URL.'/mbadmin/add/'.$urlid.$method.'/'.$cdata['methodid'].'">Add Entry</a>'
				 					.$this->sQuerry("Pagination",$cdata);
		
				return  $data['CONTENT'];
			}
		}
		
		public function searchForm( $searchOption = array() , $url  )
		{
			$searchContent = "";
			if(count($searchOption) != 0 )
			{
				$searchContent = '
				<div class = "search_content" >
					<div>
					<form action = "'.$url.'user/1" method = "post">
						<div class = "search_label">
							Search by:
						</div>
						<div class = "search_by" >
							<select id = "search_by" name = "search_by" >';
			
						foreach( $searchOption  as $value => $name )
						{
							$searchContent .= '<option value = "'.$value.'" >'.$name.'</option>';
						}
				
				$searchContent .= '</select>
						</div>
						<div class = "search_input" >
							<input type = "text" name = "search_value" id = "search_value" maxlength = "255"   value = "" />
						</div>
						<input type = "submit" class ="searchadminbutton" value = "Search" />
					</form>
					</div>
					<div>
					<form action = "'.$url.'user/1" method = "post">
						<input type = "hidden" name = "clearsearch" id = "clearsearch" value = "1" >
						<input type = "submit" class ="searchadminbutton" value = "Clear Search" />
					</form>
					</div>
				</div>';			
			}
			return $searchContent;
		}
		

		/////////////////////////////////////////utils:start


		public function read_folder_directory($dir = "") 
		{ 
			$foldernames = array();
			
			if ($handle = opendir($dir)) 
			{
				while (false !== ($entry = readdir($handle)))
				{
					if ($entry != "." && $entry != "..")
					{
						$foldernames[] = $entry;
					}
				}
				
				closedir($handle);
			}
			
			return $foldernames;
		} 

		public function checkmethod($method)
		{
			if($method == "")
			{
				return "home";
			}

			if($method == "Index")
			{
				header('Location: '.URL."/mbadmin/adminindex/");
			}

			return $method;
		}

		public function checkpagenumber($pnumber)
		{
			if($pnumber == "")
			{
				return "1";
			}
			return $pnumber;
		}


		public function adminindex($rawdate)
		{
			return $this->success("welcome to admin!");
		}
		
		public function autotime($postdate)
		{
				$date = str_replace("/", "-",date('Y-m-d H:i:s'));
				$xdate = explode("-",$date);
				$xdate2 = explode(" ",$xdate[2]);
				$xdate3 = explode(":",$xdate2[1]);

				$hours = $xdate3[0];
				$minutes = $xdate3[1];
				$seconds = $xdate3[2];

				$cdate = explode("-",$postdate);
				$days = $cdate[2];
				$months = $cdate[1];
				$years = $cdate[0];

				$time = mktime($hours, $minutes, $seconds, $months, $days, $years);

				return date('Y-m-d H:i:s', $time);
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

		public function warning($message = "Warning!")
		{
			$warning = '
						<div class="alert alert-dismissable alert-warning">
						  <button type="button" class="close" data-dismiss="alert">X</button>
						  <strong>Warning! '.$message.'</strong>.
						</div>
						';
			return $warning;
		}

		
		public function success($message = "Success!")
		{
			$sucess = '
						<div class="alert alert-dismissable alert-success">
						  <button type="button" class="close" data-dismiss="alert">X</button>
						  <strong>Success! '.$message.'</strong>.
						</div>
						';
			return $sucess;
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

		public function useralert($user)
		{
			return '<div class="well well-sm">
					  <strong>&raquo; '.$user.'</strong>
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

		public function cleanInput($input) {

			  $search = array(
			    '@<script[^>]*?>.*?</script>@si',   // Strip out javascript
			    '@<[\/\!]*?[^<>]*?>@si',            // Strip out HTML tags
			    '@<style[^>]*?>.*?</style>@siU',    // Strip style tags properly
			    '@<![\s\S]*?--[ \t\n\r]*>@'         // Strip multi-line comments
			  );

			    $output = preg_replace($search, '', $input);
			    return $output;
		 }

		public function sanitize($input) {
		    if (is_array($input)) {
		        foreach($input as $var=>$val) {
		            $output[$var] = $this->sanitize($val);
		        }
		    }
		    else {
		        if (get_magic_quotes_gpc()) {
		            $input = stripslashes($input);
		        }
		        $input  = $this->cleanInput($input);
		        $output = mysql_real_escape_string($input);
		    }
		    return $output;
		}

/////////////////////////////////////////utils:end
	}
?>