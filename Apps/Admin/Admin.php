<?php
	
	class Admin extends MB_Controller
	{
	
		public function Index($cont,$method,$params = array())
		{	
			
			$this->isAdmin();
			$method = $this->sanitize($method);
			$method = $this->checkmethod($method);//check if url method exist-> else return 
			$pagenumber = $this->sanitize($params[0]);
			$pagenumber = $this->checkpagenumber($params[0]);
			$params = $this->sanitize($params);
			$params['method'] = $method;

			$_SESSION['PAGE'] = $pagenumber;
			$data = array();
			$data['TITLE'] = "Admin";
			$data['DROPMENU'] = $this->dropmenu();
			$data['UPLOADDIR'] = "contents";
			
			if(isset($_SESSION['FIRSTNAME']))
			{
				$data['LOGIN'] = $this->useralert(ucfirst($_SESSION['USERCATEGORY'])." ".ucfirst($_SESSION['FIRSTNAME']));
			}

			$qdata = array();
			$qdata['tablename']="pages";
			$qdata['column']="*";
			$qdata['condition']=" pagename ='".$method."'";

			$fromdb = $this->sQuerry("Getsingledata",$qdata);

			$qdata['column']="pagename,plugin";
			$qdata['condition']=" plugin = 'none'";

			$getpages = $this->sQuerry("GetDataCond",$qdata);
			if($getpages)
			{
				$data['RIGHTMENU'] = $this->rightmenu($getpages);
			}


			if($fromdb)
			{
				if($fromdb['plugin'] == "none")
				{
						unset($_SESSION['table']);
						$fetchinfo = array();
						$fetchinfo['tablename']="contents";
						$fetchinfo['column']="*";
						$fetchinfo['condition']="pagesid ='".$fromdb['id']."'";
						$fetchinfo['limit']="10";
						$fetchinfo['pagenumber']= $pagenumber;
						$fetchinfo['id'] = $fromdb['id'];
						
						$headers = array(
								"id",
								"title",
								"author",
								"date",
								"status",
							);

					   $data['CONTENT'] = $this->sQuerry("ListMaker",$fetchinfo,$method,"",$headers);
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
							$data['CONTENT'] =  $this->error("APO 404");
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

			$this->Loadview("admin","admin",$data,false);

		}
/////////////////////////////////////////generate:start
		public function generate($data =array())
		{	
				
			 $resp = $this->htmltemp($data[0],"update");
			 $resp = $this->htmltemp($data[0],"insert");
			 return $resp;

		}

		public function htmltemp($tablename,$purpose)
		{
			$tables = $this->Getdatabasetablenames("dev");
			
			if(!in_array($tablename, $tables))
			{
				return $this->error($tablename." table does not exist!");
				exit;
			}
			
			$fname = ($purpose=="update") ? "edit" : "add" ;

			 $viewfile =MB_ROOT."Cont/Themes/admin/html/".$fname.$tablename.".html";

			if(file_exists($viewfile))
            {	
				
				return $this->warning($fname.$tablename.".html - file already exist!");
				exit;
			}

			$columns = $this->get_column_names($tablename);
			$genfile = '<form class="form-horizontal" action = "{URL}/mbadmin/'.$purpose.'" method = "post">
						  <fieldset>
						    <legend>'.ucfirst($fname).'</legend>';


			foreach ($columns as $key => $value) {

				$retVal = ($purpose=="update") ? "{".$value."}" : "" ;
				$addon = ($purpose=="update") ? '<input type="hidden" class="form-control" name="id" value="{id}">' : "" ;
				$type = ($value=="birthdate") ? "date" : "text" ;
				
				if($value == "id" ||  $value == "lastlogin")
				{

				}
				elseif($value == "status")
				{
					$genfile .= '<div class="form-group">
							      <label for="select" class="col-lg-2 control-label">Status</label>
							        <div class="col-lg-6">
							          <select class="form-control" id="select" name = "status">
							            <option value = "active">active</option>
							            <option value = "inactive">inactive</option>
							          </select>
							        </div>
							     </div>';
				}
				elseif($value == "confirmed")
				{
					$genfile .= '<div class="form-group">
							      <label for="select" class="col-lg-2 control-label">Confirmed</label>
							        <div class="col-lg-6">
							          <select class="form-control" id="select" name = "confirmed">
							            <option value = "0">0</option>
							            <option value = "1">1</option>
							          </select>
							        </div>
							     </div>';
				}
				elseif($value == "photo")
				{
					$genfile .= '<div class="form-group">
							      <label for="select" class="col-lg-2 control-label">Photo</label>
							        <div class="col-lg-6">
							          <input type="'.$type.'" class="form-control" name="photo" id="photo" value="'.$retVal.'">
							         <button type="button" id="elearningphoto" class="btn btn-warning pull-right" style = "margin-top:20px">Upload</button>
							          <img src = "'.$retVal.'" id="photoview" style="width:170px;height:200px;border:1pxs solid #dadada;margin-top:20px">
							        </div>
							     </div>';
				}
				elseif($value == "password")
				{
					if($fname =="edit")
					{
						$genfile .= ' <div class="form-group">
									     <button type="button" class="btn btn-warning col-lg-offset-6" id="changepassword">Change Password</button>
									  </div>
									  <div class="form-group" id ="passwordholder">
									  </div>';
					}
					else
					{
						$genfile .= '<div class="form-group">
								       <label for="" class="col-lg-2 control-label">Password</label>
								       <div class="col-lg-6">
								         <input type="password" class="form-control" name="password" id="password" value="" >
								       </div>
								      </div>

								     <div class="form-group">
								       <label for="" class="col-lg-2 control-label">Retype Password</label>
								       <div class="col-lg-6">
								         <input type="password" class="form-control" name="repassword" id="repassword" value="" >
								       </div>
								     </div>';
					}
				}
				else
				{
					$genfile .='<div class="form-group">
							      <label for="" class="col-lg-2 control-label">'.ucfirst($value).'</label>
							      <div class="col-lg-6">
							        <input type="'.$type.'" class="form-control" name="'.$value.'" value="'.$retVal.'">							  
							      </div>
							    </div>';
				}
			}

			$genfile .=  '
						  <input type="hidden" class="form-control" name="method" value="{method}">
						  '.$addon.'
						    <div class="form-group">
						      <div class="col-lg-60 col-lg-offset-6">
						        <a class="btn btn-default" href="{URL}/mbadmin/{method}">Cancel</a>
						        <button type="submit" class="btn btn-primary" id="submitgen">Save</button>
						      </div>
						    </div> 
						  </fieldset>
						  </form>
						  <script>
						  	$("#elearningphoto").click(function() { 
 								
						       	$("#dr").val("photos");
						       	
						        $("#upload").modal("show");
						    });	
						  </script>

						  ';


			$fp = fopen(MB_ROOT."/Cont/Themes/admin/html/".$fname.$tablename.".html","wb");
			fwrite($fp,$genfile);
			fclose($fp);
			return $this->success("file generation complete!");
		}
/////////////////////////////////////////generate:end

/////////////////////////////////////////elearning:start
		public function mbelearning($data =array())
		{	
			$data['REGSTUDENTS'] = $this->PaginationCount("regstudents","id != '0'");


			return $this->Loadview("admin","elearning",$data,true);
			
		}

		public function regstudent($data =array())
		{	
			
			$_SESSION['table'] = "regstudents";
			$fetchinfo = array();
			$fetchinfo['tablename']="regstudents";
			$fetchinfo['column']="*";
			$fetchinfo['condition']="id != '0'";
			$fetchinfo['limit']="10";
			$fetchinfo['pagenumber']= $this->sanitize($data[0]);
			$fetchinfo['id'] = "none";

			$headers = array(
					"username",
					"firstname",
					"middlename",
					"lastname",
					"confirmed",
					"email",
					"lastlogin"
				);


			return $this->sQuerry("ListMaker",$fetchinfo,$data['method'],"",$headers);
			
		}

		public function exams($data =array())
		{	
			
			$_SESSION['table'] = "exams";
			$fetchinfo = array();
			$fetchinfo['tablename']="exams";
			$fetchinfo['column']="*";
			$fetchinfo['condition']="id != '0'";
			$fetchinfo['limit']="10";
			$fetchinfo['pagenumber']= $this->sanitize($data[0]);
			$fetchinfo['id'] = "none";

			$headers = array(
					"question",
					"answer",
					"points"
				);


			return $this->sQuerry("ListMaker",$fetchinfo,$data['method'],"",$headers);
			
		}
/////////////////////////////////////////elearning:end

/////////////////////////////////////////analytics:start
		public function analytics($data =array())
		{	
			
			return '<div id="analyticschart" style="height:600px;width:900px;"></div>';
			
		}
/////////////////////////////////////////analytics:end

/////////////////////////////////////////theme:start
		public function theme($data =array())
		{
			
			if($data[0] != "")
			{
				$_SESSION['THEME'] = $data[0];
				$themearray = array();
				$themearray['value'] = $data[0];
				$fromdb = $this->sQuerry("UpdateData",$themearray,"general","1");
			}

			$dir =  MB_ROOT."Cont/Themes";
			$files = array_diff(scandir($dir), array('..', '.'));
			$themfolders = "";

			$hidtheme = array("admin","portal2");

			foreach ($files as $key) 
			{
				if(!in_array($key, $hidtheme))
				$themfolders .= '<div class = "themeholder"><h5>'.ucfirst($key).'</h5>
								<img src = "'.URL.'/Cont/Themes/'.$key.'/thumbnail/themethumb.jpg" class = "themethumb">
								<a type="button" class="btn btn-warning" href="'.URL.'/mbadmin/theme/'.$key.'">Activate</a></div>';
			}

			$curtheme ='<div class="well well-sm">Current Theme: '.ucfirst($_SESSION['THEME']).'</div>';

			return $curtheme.$themfolders;			
		}
/////////////////////////////////////////theme:end

/////////////////////////////////////////users:start
		public function users($data =array())
		{
	
				$_SESSION['table'] = "regusers";
				$fetchinfo = array();
				$fetchinfo['tablename']="regusers";
				$fetchinfo['column']="*";
				$fetchinfo['condition']="id != '0'";
				$fetchinfo['limit']="10";
				$fetchinfo['pagenumber']= $this->sanitize($data[0]);
				$fetchinfo['id'] = "none";

				$headers = array(
						"usercategory",
						"username",
						"firstname",
						"lastname",
						"activated",
						"confirmed",
						"date"
					);


				return $this->sQuerry("ListMaker",$fetchinfo,$data['method'],"",$headers);

		}
/////////////////////////////////////////users:end

/////////////////////////////////////////shop:start
		public function mbshop($data =array())
		{
			if($this->isLoggedIn())
			{	
				return $this->error("Under maintenance!");

			}
			else
			{
				return $this->error("Unauthorized User!");
			}
		}
/////////////////////////////////////////shop:end

/////////////////////////////////////////pagemanagement-CRUD:start
		public function managepages($data =array())
		{
				
			$_SESSION['table'] = "pages";
			$fetchinfo = array();
			$fetchinfo['tablename']="pages";
			$fetchinfo['column']="*";
			$fetchinfo['condition']="plugin ='none'";
			$fetchinfo['limit']="10";
			$fetchinfo['pagenumber']= $this->sanitize($data[0]);
			$fetchinfo['id'] = "none";

			$headers = array(
					"id",
					"pagename",
					"date",
					"status",
					"navorder"
				);


			return $this->sQuerry("ListMaker",$fetchinfo,$data['method'],"",$headers);
		}
/////////////////////////////////////////pagemanagement-CRUD:end

/////////////////////////////////////////gallery:start
		public function gallery($data =array())
		{
			
			$galldir = "";
			if($data[0]!="")
			{
				
				if($data[0] == "deleteimage")
				{
					$delfile =  MB_ROOT."Lib/upload/Uploads/gallery/".$data[1]."/".$data[2];
					unlink($delfile);
					return $this->success('image deleted <a href = "{URL}/mbadmin/gallery/'.$data[1].'/">  Back</a>');
				}
				elseif($data[0] == "deletefolder")
				{
					$delfolder =  MB_ROOT."Lib/upload/Uploads/gallery/".$data[1];
					$this->rrmdir($delfolder);

					return $this->success('folder deleted <a href = "{URL}/mbadmin/gallery/">  Back</a>');
				}
				elseif($data[0] == "addfolder")
				{
					$mkfile =  MB_ROOT."Lib/upload/Uploads/gallery/".$data[1];
					mkdir($mkfile, 0777);
				}
				else
				{	
					$galldir = "/".$data[0];
				}
			}

			$dir =  MB_ROOT."Lib/upload/Uploads/gallery".$galldir;
			
			$files = array_diff(scandir($dir), array('..', '.'));
			
			$htmlfolders = "";
				
			foreach ($files as $key) 
			{
				
				if(is_dir($dir."/".$key))
				{
					$htmlfolders .= '
									<div class ="galleryfolder">
									<a type="button" class="close delimage" data-dismiss="modal" aria-hidden="true" 
									style="margin:0 5px -21px 0;opacity:.5" id = "'.$key.'" 
									onclick="return confirm(\'confirm delete?\')" href="{URL}/mbadmin/gallery/deletefolder/'.$key.'">x</a>
									<a href="{URL}/mbadmin/gallery/'.$key.'" style="margin:20px 0 0 0;float:right;width:100%;"><h5 style="text-align:center;margin:5px 0 10px 0;">'.ucfirst($key)
									.'</h5></a>
									<img src = "'.URL.'/Cont/Themes/admin/images/galfolder.jpg" style = "width:90px;height:75px;float:left;border:1px solid orange;margin:0 0 0 5px">
									</div>';
				}
				else
				{
					$htmlfolders .= '<div class ="galleryfolder galimgholder">
									<a type="button" class="close delimage" data-dismiss="modal" aria-hidden="true" 
									style="margin-bottom:-21px;opacity:.5" id = "'.$key.'" 
									onclick="return confirm(\'confirm delete?\')" href="{URL}/mbadmin/gallery/deleteimage/'.$data[0].'/'.$key.'">x</a>
									<img src="{URL}/Lib/upload/Uploads/gallery/'
									.$data[0].'/'.$key.'" class="thumb"><span class="label label-default" >'
									.substr($key, 0,14).'</span></div>					
									';
				}
			}

			if($data[0] !="")
			{
				return '<div class="form-group">
						<a href = "{URL}/mbadmin/gallery/" class="btn btn-default">Back</a>
						<button type="button" class="btn btn-warning" style="margin-left:5px;" id="uploadgalimage">
							Upload
						</button>
						</div>'
						.$htmlfolders;
			}
			else
			{ 
				return '<div class="form-group">
						<button type="button" class="btn btn-warning" style="margin-left:5px;" id="makegalfolder">
							Create folder
						</button>
						<input type = "text" id ="newfolder" placeholder = "Folder Name" style="width:200px" class="form-control pull-left">
						</div>'
						.$htmlfolders;
			}
		}

		public function rrmdir($dir)
		 {
		   if (is_dir($dir)) {
		     $objects = scandir($dir);
		     foreach ($objects as $object) {
		       if ($object != "." && $object != "..") {
		         if (filetype($dir."/".$object) == "dir") rrmdir($dir."/".$object); else unlink($dir."/".$object);
		       }
		     }
		     reset($objects);
		     rmdir($dir);
		   }
 		}
/////////////////////////////////////////gallery:end

/////////////////////////////////////////page-CRUD:start
		public function insert($data =array())
		{	

			if(isset($_SESSION['table']))
			{
				$table = $_SESSION['table'];	
			}
			else
			{	
				$table ="contents";
			}
			
			if($this->isLoggedIn())
			{	
				if($_POST['date'] =="")
				{
					$_POST['date']= date('Y-m-d H:i:s');
				}
				else
				{
					$_POST['date'] = $this->autotime($_POST['date']);
				}

				$method = $_POST['method'];
				unset($_POST['method']);
				unset($_POST['repassword']);

				$this->sQuerry("InsertData",$_POST, $table);
				header('Location: '.URL."/mbadmin/".$method);
			}
			else
			{
				return $this->error("Unauthorized User!");
			}
		}

		public function add($data =array())
		{
			if(isset($_SESSION['table']))
			{
				
				$fileext = $_SESSION['table'];
			}
			else
			{
				$fileext="";
			}

				
			$fromdb = array();
			$fromdb['methodid'] = $data[0];
			$fromdb['methodname'] = $data[1];
			$fromdb['method'] = $data[0];
			return $this->Loadview("admin","add".$fileext,$fromdb,true);	
			
		}

		public function update($data =array())
		{
			if(isset($_SESSION['table']))
			{
				$table = $_SESSION['table'];
				$fileext = $_SESSION['table'];
			}
			else
			{
				$fileext="";
				$table ="contents";
			}

				
			$id = $_POST['id'];
			$method = $_POST['method'];
			unset($_POST['id']);
			unset($_POST['method']);
			unset($_POST['repassword']);

			$fromdb = $this->sQuerry("UpdateData",$_POST,$table,$id);
			header('Location: '.URL."/mbadmin/".$method);
			
		}

		public function edit($data =array())
		{	
			
			if(isset($_SESSION['table']))
			{
				$table = $_SESSION['table'];
				$fileext = $_SESSION['table'];
			}
			else
			{
				$fileext="";
				$table ="contents";
			}
				
			$qdata = array();
			$qdata['tablename']=$table;
			$qdata['column']="*";
			$qdata['condition']="id ='".$data[0]."'";
			
			$fromdb = $this->sQuerry("Getsingledata",$qdata);
			
			if($fromdb)
			{	
				$fromdb['method'] = $data[1];
				return $this->Loadview("admin","edit".$fileext,$fromdb,true);
			}
			
		}

		public function delete($data =array())
		{
			if(isset($_SESSION['table']))
			{
				$table = $_SESSION['table'];
				$fileext = $_SESSION['table'];
			}
			else
			{
				$fileext="";
				$table ="contents";
			}

			$params['id'] = $data[0];
				
			$this->sQuerry("DeleteData",$params,$table);
			header('Location: '.URL."/mbadmin/".$data[1]);
			
		}
/////////////////////////////////////////page-CRUD:end

/////////////////////////////////////////auth:start
		public function logout(){
			session_destroy();
			header('Location: '.URL);
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

		public function isLoggedIn()
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

		public function isAdmin()
		{
			if(isset($_SESSION))
			{	
				if($_SESSION['USERCATEGORY'] != "admin" || !isset($_SESSION['LOGED'])  || ($_SESSION['ACTIVATED']!="1"))
				{	
					header('Location: '.URL);
				}
				
			}
		}
/////////////////////////////////////////auth:end

		public function dropmenu()
		{
			$dropmenu =	'<li><a href="{URL}/mbadmin/Managepages"> Manage Pages</a></li>';
			//$dropmenu .= '<li><a href="{URL}/mbadmin/Manageemail"> Manage Email</a></li>';
			$dropmenu .= '<li><a href="{URL}/mbadmin/Analytics"> Analytics</a></li>';
			$dropmenu .= '<li><a href="{URL}/mbadmin/Theme"> Themes</a></li>';
			$dropmenu .= '<li class="divider"></li>';
			$dropmenu .= '<li><a href="{URL}/"> View Website</a></li>';
			$dropmenu .= '<li><a href="{URL}/Logout"> Logout</a></li>';
			return $dropmenu;
		}

		public function rightmenu($page)
		{
			$pages ="";
			

			while($rows = mysql_fetch_assoc($page))
			{
					$pages .= '<li><a href="{URL}/mbadmin/'.$rows['pagename'].'">'.$rows['pagename'].'</a></li>';		
			}
			
			
			$ret = '<ul class="nav nav-pills nav-stacked" style="max-width: 300px;">
						  <li class="dropdown">
						    <a class="dropdown-toggle" data-toggle="dropdown" href="#">
						      Pages <span class="caret"></span>
						    </a>
						    <ul class="dropdown-menu">
						    '.$pages;

			$shop = '<li class="dropdown">
						    <a class="dropdown-toggle" data-toggle="dropdown" href="#">
						     Shop <span class="caret"></span>
						    </a>
						    <ul class="dropdown-menu">
							    <li><a href="{URL}/mbadmin/shopitems">Item List</a></li>
							    <li><a href="{URL}/mbadmin/shopitemcategories">Item Categories</a></li>
							    <li><a href="{URL}/mbadmin/shopaccounts">User Accounts</a></li>
							    <li><a href="{URL}/mbadmin/shopinventory">Inventory</a></li>
							    <li><a href="{URL}/mbadmin/shopsales">Sales</a></li>
						    </ul>
						  </li>';

			$elearning = '<li class="dropdown">
						    <a class="dropdown-toggle" data-toggle="dropdown" href="#">
						      E-learning <span class="caret"></span>
						    </a>
						    <ul class="dropdown-menu">
							    <li><a href="{URL}/mbadmin/regstudent">Student Registry</a></li>
							    <li><a href="{URL}/mbadmin/classes">Classes</a></li>
							    <li><a href="{URL}/mbadmin/classcategories">Class Categories</a></li>
							    <li><a href="{URL}/mbadmin/exams">Exams</a></li>
							    <li><a href="{URL}/mbadmin/examcategories">Exam Categories</a></li>
							    <li><a href="{URL}/mbadmin/records">Records</a></li>
						    </ul>
						  </li>';

			$auction = '<li class="dropdown">
						    <a class="dropdown-toggle" data-toggle="dropdown" href="#">
						     Auction <span class="caret"></span>
						    </a>
						    <ul class="dropdown-menu">
							    <li><a href="{URL}/mbadmin/auctionitems">Item List</a></li>
							    <li><a href="{URL}/mbadmin/auctionitemcategories">Item Categories</a></li>
							    <li><a href="{URL}/mbadmin/auctionaccounts">User Accounts</a></li>
							    <li><a href="{URL}/mbadmin/auctionbids">Bids</a></li>
							    <li><a href="{URL}/mbadmin/auctioninventory">Inventory</a></li>
							    <li><a href="{URL}/mbadmin/auctionsales">Sales</a></li>
						    </ul>
						  </li>';
							  
						       
				$ret .=	'	 </ul>
						  </li>
						  <li><a href="{URL}/mbadmin/Users">Users</a></li>
						  <li><a href="{URL}/mbadmin/Gallery">Gallery</a></li>
						  
						  ';
						  
				//$ret .= $elearning;
				//$ret .= $shop;
				//$ret .= $auction;
						  
						    
						
				$ret .=	'  
						</ul>';

				return $ret;
		}
	}
?>