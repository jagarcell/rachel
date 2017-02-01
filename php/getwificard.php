<?php
    // WE GET HE INFO FROM THE INTERFACE BY
    // MEAN OF THE ip addr show COMMAND
    $interfaces = file("/etc/hostapd/hostapd.conf");
    foreach ($interfaces as $key => $value) {
        # code...
        if(stripos($value, "interface")>=0)
        {
            $valueSplit = split("=", $value);
		$wificard = $valueSplit[1];
            echo $wificard;
            return;
        }
    }?>