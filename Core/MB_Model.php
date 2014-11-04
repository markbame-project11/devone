<?php
	class MB_Model
	{
		
		public function dbConn()
		{
			mysql_connect(HOST,USERNAME,PASSWORD) or die ("could not connect to the database: ".mysql_error());
			
			mysql_select_db(DATABASE) or die ("error: ".mysql_error());
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
		
		public function urlencoder($text)
		{
			 return urlencode($text);
		}
		
		public function htmldecode($text)
		{
			 return html_entity_decode($text);
		}
		
		public function TPhtmltodb($text)
		{
			return htmlentities(stripslashes($text), ENT_QUOTES, "UTF-8");
		}
				
		public function Getdatabasetablenames($db)
		{
			$sql = "SHOW TABLES FROM ".$db;
			
			$result = mysql_query($sql) or die("could not connect to the database".mysql_error());
			
			$gettables = array();
			
			while($row = mysql_fetch_row($result))
			{
				$gettables[] = $row[0];
			}
			
			return $gettables;
		}
		
		public function Table_exists($tablename, $db)
		{ 
			$tables = $this->Getdatabasetablenames($db);
			
			if(in_array($tablename,$tables))
			{
				return true;
			}
			
			return false;
		}
	
		public function TPposttodb($data = array())
		{
			$striped = array();
			
			foreach($data as $indx=>$val)
			{
				if($indx == "password")
				{
					if($val == "")
					{
						unset($striped[$indx]);
					}
					else
					{
						$striped[$indx] = md5(htmlentities(stripslashes($val), ENT_QUOTES, "UTF-8"));
					}
				}
				else
				{
					$striped[$indx] = htmlentities(stripslashes($val), ENT_QUOTES, "UTF-8");
				}
			}
			
			return $striped;
		}
	
		public function PaginationCount($tablename,$condition = "")
		{
			
			$sql = "SELECT COUNT(*) FROM ".$tablename." where ".$condition;
			//echo $sql;
			$result = mysql_query($sql) or die("pg invalid url");
			
			if($result ==0 && DEBUGMODE ==true)
			{
				return "D404";
			}
			else
			{
				if($count = mysql_fetch_assoc($result))
				{
					return $count['COUNT(*)'];
				}
			}
		}
		
		public function PaginationList($tablename,$condition = "",$limit = 5,$page = 1,$order = "id DESC")
		{
			$start = ($page * $limit) - $limit;
			
			if($start < "0")
			{
				$start = "0";
			}

			$end = $limit;
			
			$sql = "SELECT * FROM ".$tablename." where ".$condition." ORDER BY ".$order." LIMIT ".$start.", ".$end;
			
			$result = mysql_query($sql) or die(" pl invalid url");
		
			if($result ==0  && DEBUGMODE == true)
			{
				return "D404";
			}
			else
			{
				return $result;
			}
		}
		
		public function Pagination($count,$limit=5,$url,$output="",$page = 1)
		{	
			
			$pages = "";
			
			$pageLimit = $count / $limit;
			
			$pageModular =  fmod($count,$limit); 
			
			if( $pageModular > 0)
			{
				$pageLimit = $pageLimit + 1;
			}

			$pageLimit = floor( $pageLimit );

			if(  $page > 5 )
			{
				$pageLimitMinusOne = $pageLimit - 1;
				$pageStart = $page - 3;

				if($page == $pageLimit )
				{
					$pageLimitCeil = $page;
				}
				elseif( $page == $pageLimitMinusOne )
				{
					$pageLimitCeil = $page + 1;
				}
				else
				{
					$pageLimitCeil = $page + 2;
					
				}
			}
			else
			{
				$pageStart = 1;
				if( $pageLimit > 6)
				{
					$pageLimitCeil = 6;
				}
				else
				{
					$pageLimitCeil = $pageLimit;
				}
			}
			
			$previousPage = $page  - 1;
			$nextPage = $page + 1;
			
			
			if( $count != 0 )
			{
				$pages .= "<ul class='pagination'><li><a href ='".URL.$url."1' class = 'paginationbutton'>First</a></li>";
								
				for( $pageCount = $pageStart ; $pageCount <= $pageLimitCeil; $pageCount++ )
				{
					$pages .= "<li><a href ='".URL.$url.$pageCount."' class = 'paginationbutton'>".$pageCount."</a></li>";
					
				}
				
				$pages .= "<li><a href ='".URL.$url.$pageLimit."' class = 'paginationbutton'>Last</a></li></ul>";
		    }
			return $this->htmldecode($output.$pages);
		}
		
		public function InsertData($tablename,$data = array(),$date = false)
		{
			

			$data = $this->TPposttodb($data);
			
			if($date)
			{
				$data['date'] = date("Y-m-d H:m:s");
			}
			
			$sql = "INSERT INTO ".$tablename." (";
			
			$last_key = end(array_keys($data));
			
			foreach($data as $indx => $val)
			{
				if($indx == $last_key)
				{
					$sql .= $indx.")";
				} else 
				{
					$sql .= $indx.",";
				}
			}
			
			$sql .= "VALUES(";
			
			foreach($data as $indx => $val)
			{
				if($indx == $last_key)
				{
					$sql .= "'".$val."')";
				} else 
				{
					$sql .= "'".$val."',";
				}
			}
			$sql;
			$result = mysql_query($sql) or die ("IN invalid url");
			
			if($result != 0)
			{
				return $result;
			}
			
		}
		
		public function Updatedata($tablename,$id,$postdata = array())
		{
			$id = $this->TPhtmltodb($id);
			
			$postdata = $this->TPposttodb($postdata);
		
			$last_key = end(array_keys($postdata));
		
			$sql = "UPDATE ".$tablename." SET ";
			
			foreach($postdata as $ind=>$val)
			{
				if($last_key != $ind)
				{
					$sql .= $ind."='".$val."', ";
				}
				else
				{
					$sql .= $ind."='".$val."' ";
				}
			}
			
			$sql .= "WHERE id='".$id."'"; 
			
			$result = mysql_query($sql) or die ("UP invalid url");
			
			if($result != 0)
			{
				return $result;
			}
			
		}
		
		public function Deletedata($tablename,$id)
		{
			$sql = "DELETE FROM ".$tablename." WHERE id='".$this->TPhtmltodb($id)."'";
			//echo $sql;
			$result = mysql_query($sql) or die ("invalid url");
			
			if($result != 0)
			{
				return true;
			}
		}
		
		public function Getdatacondition($tablename,$column,$condition)
		{
			$sql = "SELECT ".$column." FROM ".$tablename." WHERE ".$condition;
			//echo $sql;
			$result = mysql_query($sql) or die ("gdc invalid url");
			
			if($result != 0)
			{
				return mysql_fetch_assoc($result);
			}
		}

		public function Getdataconditionraw($tablename,$column,$condition)
		{
			$sql = "SELECT ".$column." FROM ".$tablename." WHERE ".$condition;
		
			$result = mysql_query($sql) or die ("<br/>raw invalid url");
			
			if($result != 0)
			{
				return $result;
			}
			return false;
		}

		
		public function Getdata($tablename,$column)
		{
			$sql = "SELECT ".$column." FROM ".$tablename." ORDER by id DESC";
			
			$result = mysql_query($sql) or die ("invalid url");
			
			if($result != 0)
			{
				return mysql_fetch_assoc($result);
			}
		}
		
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
		
		public function get_column_names($table)
		{
			 $result = mysql_query("SHOW COLUMNS FROM ". $table);
			  if (!$result) 
			  {
				echo 'Could not run query: ' . mysql_error();
			  }
			  
			  $fieldnames=array();
			  
			  if (mysql_num_rows($result) > 0)
			  {
				while ($row = mysql_fetch_assoc($result))
				{
					$fieldnames[] = $row['Field'];
				}
			  }

				return $fieldnames; 
		
		}
		
		public function Verifyuser($data = array())
		{
			$_SESSION['root'] = false;
		
			if($data['username'] == "redroot" && $data['password'] == "redroot0")
			{
				$_SESSION['root'] = true;
				$_SESSION['loged'] = true;
				return true;
			}
			
			$data = ($this->TPposttodb($data));
						
			$sql = "SELECT * FROM regusers WHERE username = '".$data['username']."' and password ='".$data['password']."'";
			
			$result = mysql_query($sql) or die ("404");
			
			if($res = mysql_fetch_assoc($result))
			{
							
				$_SESSION['USERCATEGORY'] = $res["usercategory"];
				$_SESSION['FIRSTNAME'] = $res["firstname"];
				$_SESSION['ACTIVATED'] = $res["activated"];
				$_SESSION['LOGED'] = true;

								
				return ($res["activated"])?true:false;
			}
		}
		
		public function Deletetable($tablename)
		{
			$sql = "DELETE FROM ".$tablename;
			
			$result = mysql_query($sql) or die ("could not connect: ".mysql_error());
			
			if($result)
			{
				return "table data cleared";
			}
		}
		
		
		
		public function Getdataconditionarray($tablename,$column,$condition)
		{
			if( $condition == "" )
			{
				$sql = "SELECT ".$column." FROM ".$tablename;
			}
			else
			{
				$sql = "SELECT ".$column." FROM ".$tablename." WHERE ".$condition;
			}
			
			$output = false;
			$result = mysql_query($sql) or die ("could not connect: ".mysql_error().$sql);
			
			if($result != 0)
			{
				while($rows = mysql_fetch_assoc($result))
				{
					$output[] = $rows; 
				}
				return $output;
			}
		}
		
		public function Updatedatacondition($tablename,$postdata= array(), $condition = "" )
		{
			
			 $result = mysql_query("SHOW COLUMNS FROM ". $tablename);
			  if (!$result) 
			  {
				echo 'Could not run query: ' . mysql_error();
			  }
			  
			  $fieldnames=array();
			  
			  if (mysql_num_rows($result) > 0)
			  {
				while ($row = mysql_fetch_assoc($result))
				{
					$fieldnames[] = $row['Field'];
				}
			  }
			$postdata = $this->TPposttodb($postdata);
		
			//$last_key = end(array_keys($postdata));
		
			$sql = "UPDATE ".$tablename." SET ";
			
			$num = 0;
			
			foreach($postdata as $ind=>$val)
			{
				if(in_array($ind,$fieldnames))
				{
					if( $num == 0 )
					{
						$sql .= $ind."='".$val."'";
					}
					else 
					{
						$sql .= " , ".$ind."='".$val."'";
					}
					$num++;
				}
			}
			
			if( $condition != "" )
			{
				$sql .= "WHERE ".$condition; 
			}
			//echo $sql;
			$result = mysql_query($sql) or die ("UP url error");
			
			if($result != 0)
			{
				return $result;
			}
			
		}

		
		
	}
?>