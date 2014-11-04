<?php
	session_start();

	if(isset($_SESSION['FIRSTNAME']) && isset($_SESSION['LOGED'])  && ($_SESSION['ACTIVATED']=="1"))
	{
		
		mysql_connect($_SESSION["HOST"],$_SESSION["USERNAME"],$_SESSION["PASSWORD"]) or die ("could not connect to the database: ".mysql_error());
		
		mysql_select_db($_SESSION["DATABASE"]) or die ("error: ".mysql_error());

		$sql = "SELECT datevisited,SUM(visits) FROM tracker GROUP BY datevisited";

		$result = mysql_query($sql) or die ("IN invalid url".mysql_error());

		$retval = "";
		while($row = mysql_fetch_assoc($result))
		{
			$lineset[] = array($row['datevisited'],(int) $row['SUM(visits)']);
		}
		
		echo json_encode($lineset);
	}
?>