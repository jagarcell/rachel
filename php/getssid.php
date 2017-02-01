<?php
	$hostApd = file("/etc/hostapd/hostapd.conf");
	foreach ($hostApd as $key => $value) {
		# code...
		if(strpos($value, "sid", 0))
		{
			$lineArray = split("=", $value);
			echo $lineArray[1];
			return;	
		}
	}
?>