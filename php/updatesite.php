<?php
	$rownum = $_REQUEST["rownum"];

	$conn = pg_connect("host=localhost user=postgres password=postgres dbname=configdb");
	if(!$conn){
		echo "ERROR CONNECTING TO dbconfig";
		return;
	}

	$result = pg_query("SELECT comando FROM enlaces");
	if(!$result){
		echo "ERROR FETCHING THE LINKS TABLE";
		return;
	}

	$rows = pg_fetch_all($result);

	$comando = $rows[$rownum]["comando"];
	$comando = str_replace("./", "/var/www", $comando);
	$result = system($comando, $retval);
	echo "DONE";
?>