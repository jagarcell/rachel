<?php
	// GET THE ROW NUMBER TO DELETE
	$rownum = $_REQUEST["rownum"];

	// CONNECT TO PGSQL DATABASE
	$conn = pg_connect("host=localhost dbname=configdb user=postgres password=postgres");

	// REPORT IF THERE IS AN ERROR CONNECTING TO THE DATABASE
	if($conn === false)
	{
		// IF ERROR CONNECTING RETURN
		echo "ERROR CONNECTING TO THE CONFIG DATABASE";
		return;
	}

	// AS WE CONNECTED TO THE DATABASE LET'S QUERY IT
	$result = pg_query("SELECT comando FROM enlaces");

	// IF THERE IS AN ERROR FETCHING THE TABLE ...
	if($result === false)
	{
		// ... REPORT IT AND RETURN
		echo "ERROR FETCHING THE TABLE";
		return;
	}

	// IF THE FETCHING WAS OK LET'S GET THE
	// ROWS WITH THE INFO OF THE OFFLINE SITES
	$rows = pg_fetch_all($result);

	// GET THE comando STRING FROM THE TABLE
	$comando = $rows[$rownum]["comando"];

	// SPLIT THE comando STRING TO GET THE DIRECTORY
	$comandoParts = explode("/", $comando);

	// ASSEMBLY THE DIRECTORY
	$dir = $comandoParts[count($comandoParts) - 2];
	$dir = explode(" ", $dir)[0];
	$fullDir = "/var/www/$dir";

	$result = pg_query("DELETE FROM enlaces WHERE comando='$comando'");
	if($result === false)
	{
		echo "ERROR DELETING MENU ENTRY FROM THE CONFIG DATABASE";
		return;
	}

	// DELETE THE DIRECTORY THAT HOSTS THE OFFLINE SITE
	if(delTree($fullDir) === false)
	{
		// IF THERE WAS AN ERROR DELETING
		// THE DIRECTORY REPORT IT AND RETURN 
		echo "ERROR DELETING THE DIRECTORY $dir";
		return;
	}
	// IF THE DIRECTORY WAS SUCCESSFULLY DELETED LET'S
	// PROCEED WITH THE DELETION OF THE HOST FROM HOSTS FILE
	$originalHostsFile = fopen("/etc/hosts", 'r');
	if($originalHostsFile === false)
	{
		echo "HOSTS FILE COULD NOT BE OPENED";
		return;
	}
	$newHostsFile = fopen("/etc/myhosts", 'w+');
	if($newHostsFile === false)
	{
		echo "THE NEW HOSTS FILE COULD NOT BE CREATED";
		return;
	}

	$line = fgets($originalHostsFile);
	while($line !== false)
	{
		if(stripos($line, $dir) === false)
		{
			fwrite($newHostsFile, $line);
		}
		$line = fgets($originalHostsFile);
	}
	
	fclose($originalHostsFile);
	fclose($newHostsFile);

	if(unlink("/etc/hosts") === false)
	{
		echo "ERROR DELETING OLD HOSTS FILES";
		return;
	}

	if(rename("/etc/myhosts", "/etc/hosts") === false)
	{
		echo "ERROR RENAMING NEW HOST FILE";
		return;	
	}

	if(unlink("/etc/apache2/sites-available/$dir.com.conf") === false)
	{
		echo "ERROR DELETING APACHE2 AVAILABLE-SITE CONFIGURATION";
		return;
	}

	if(unlink("/etc/apache2/sites-enabled/$dir.com.conf") === false)
	{
		echo "ERROR DELETING APACHE2 AVAILABLE-SITE CONFIGURATION";
		return;
	}

	// DELETE THE DIRECTORY THAT HOSTS THE OFFLINE SITE LOGO
	if(delTree("/var/www/html/img/$dir") === false)
	{
		// IF THERE WAS AN ERROR DELETING
		// THE DIRECTORY REPORT IT AND RETURN 
		echo "ERROR DELETING THE DIRECTORY /var/www/html/img/$dir";
		return;
	}

	echo "THE SITE HAS BEEN SUCCESSFULLY DELETED";

	// FUNCTION TO DELETE THE DIRECTORY, FILES AND SUBDIRECTORIES
	function delTree($dir)
	{
		// GET THE FILES IN THE DIRECTORY
		$files = array_diff(scandir($dir), array(".",".."));
		// CHECK EACH FILE TO DETERMINE IF IT IS
		// A DIRECTORY  OR IF IT IS JUST A FILE
		foreach ($files as $file) {
			// IF IT IS A DIRECTORY CALL dellTree RECURSIVILY
			// ELSE, DELETE THE FILE
			(is_dir("$dir/$file")) ? delTree("$dir/$file") : unlink("$dir/$file");
		}

		// FINALLY, DELETE THE DIRECTORY
		return rmdir($dir);
	}	
?>