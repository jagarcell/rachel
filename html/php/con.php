<?php
	$nline = $_REQUEST["nline"];
	$command = $_REQUEST["command"];
	
    $commandStrings = explode("/", $command);
    $dir = $commandStrings[count($commandStrings) - 2];
    $dir = str_replace(".", "", $dir);
    $dir = trim($dir, " ");

    $file = '/var/www/html/' . $dir . '.txt';

    foreach (getlines($file) as $n => $line) {
    	# code...
//    	if($n > 1) break;
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