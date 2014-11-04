<?php
	session_start();
	if(isset($_SESSION['FIRSTNAME']) && isset($_SESSION['LOGED'])  && ($_SESSION['ACTIVATED']=="1"))
	{
		
		$types = array('image/jpeg', 'image/gif', 'image/png'); 

		if (in_array($_FILES['myfile']['type'], $types))
		 {
			if ( 1048576 > filesize( $file['tmp_name'] ) ) 
			{
				$dir = "";

				if(isset($_POST['fn']))
				{
					$dir = $_POST['fn']."/";
				}

				$output_dir = "Uploads/".$dir;
				
				if(isset($_FILES["myfile"]))
				{
				  
				    if ($_FILES["myfile"]["error"] > 0)
				    {
				   		echo "Error: " . $_FILES["file"]["error"] . "<br>";
				    }
				    else
				    {
				    	$newname = date("YmdHis").getToken("8").".jpg";  
				      
				        move_uploaded_file($_FILES["myfile"]["tmp_name"],$output_dir.$newname);
				 
				     	echo "File Uploaded :<div id='filename'>".$newname."</div>";
				    }
				 
				}
			}
			else
			{
				echo "File too large! We have a 1MB limit";
			}
		}
		else
		{
			echo "Invalid File! We only allow JPG, GIF, and PNG filetypes.";
		}
	}

	function getToken($length)
	{
		    $token = "";
		    $codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
		    $codeAlphabet.= "abcdefghijklmnopqrstuvwxyz";
		    $codeAlphabet.= "0123456789";
		    for($i=0;$i<$length;$i++){
		        $token .= $codeAlphabet[crypto_rand_secure(0,strlen($codeAlphabet))];
		    }
		    return $token;
	}

	function crypto_rand_secure($min, $max) {
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