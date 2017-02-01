<?php
	$interfaces = file("/etc/network/interfaces");
	foreach ($interfaces as $key => $value) {
		# code...
		if(stripos($value, "wlan"))
		{
			echo $value;
			return;
		}
	}
?>