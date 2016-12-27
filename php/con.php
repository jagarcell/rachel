<?php
	$command = $_REQUEST["command"];
	$mode = $_REQUEST["mode"];
	
    $commandStrings = explode("/", $command);
    $dir = $commandStrings[count($commandStrings) - 2];
    $dir = str_replace(".", "", $dir);
    $dir = trim($dir, " ");

    $file = '/var/www/html/' . $dir . '.txt';
    
    if ($mode == "d") {
    	# code...
    	if(!unlink($file))
    	{
    		echo "DELETION OF LOG FILE $file FAILED";
    	}
    	return;
    }

    foreach (getlines($file) as $n => $line) {
    	# code...
	    echo "<p style='margin-left: 10px'>$line</p>";
    }

    echo "<p id='last'><p>";

    function getlines($file)
    {
		$f = fopen($file, "r");
		try{
			while($line = fgets($f))
			{
				yield $line;
			}
		}
		finally
		{
			fclose($f);
		}
    }
?>