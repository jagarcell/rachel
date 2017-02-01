<?php
	// WE GET THIS TO KNOW IF THE USER NEEDS
	// TO GET OR SET THE CONFIGURATION PARAMETERS
	$mode = $_REQUEST["mode"];

	// PARAMETERS ARRAY
	$parameters = array(
		"ssid" => "",
		"pwd" => "",
		"wificard" => "",
		"ip" => "",
		"sip" => "",
		"eip" => "",
		"clients" => "",
		"maxclients" => "",
		"concession" => "",
		"mask" => "",
		"homepage" => "",
		"adminpassword" => 0,
		"error" => false,
		"message" => ""
	);

	$release = true;

	if($release)
	{
		// RELEASE ENVIROMENT
		$interfacesFile = "/etc/network/interfaces";
		$hostapdconfFile = "/etc/hostapd/hostapd.conf";
		$rachelcomconfFile = "/etc/apache2/sites-available/rachel.com.conf";
		$dnsmasqconfFile = "/etc/dnsmasq.conf";
		$hostsFile = "/etc/hosts";
	}
	else
	{
		// DEVELOPTMENT ENVIROMENT
		$interfacesFile = "/var/www/interfaces";
		$hostapdconfFile = "/var/www/hostapd.conf";
		$rachelcomconfFile = "/var/www/rachel.com.conf";
		$dnsmasqconfFile = "/var/www/dnsmasq.conf";
		$hostsFile = "/var/www/hosts";
	}

	if($mode == "get")
	{
		// IF GET IS SET LET'S READ THE CONFIGURATION
		// THE ssid COMES FROM THE hostapd.conf FILE 
		$hostApd = file("$hostapdconfFile");

		if(!$hostApd)
		{
			$parameters["error"] = true;
			$parameters["message"] = "HOSTAPD FILE NOT FOUND";
			echo json_encode($parameters);
			return;
		}
		foreach ($hostApd as $value) {
			# code...
			// WE LOOK FOR A ssid STRING
			if(strpos($value, "ssid") !== false)
			{
				$lineArray = split("=", $value);
				$parameters["ssid"] = $lineArray[1];
			}

			// WE LOOK FOR A wpa_passphrase STRING
			if(strpos($value, "wpa_passphrase") !== false)
			{
				$lineArray = split("=", $value);
				$parameters["pwd"] = $lineArray[1];
			}

			// TO GET THE WIFI CARD 
	        if(stripos($value, "interface") !== false)
	        {
	            $valueSplit = split("=", $value);
				$wificard = $valueSplit[1];
	            $parameters["wificard"] = $wificard;
	        }
		}

		$interfaces = file("$interfacesFile");

		$cardfound = false;
		foreach ($interfaces as $value) {
			# code...
			if(stripos($value, $wificard) !== false)
			{
				$cardfound = true;
				continue;
			}

			if($cardfound === true && stripos($value, "address") !== false)
			{
				$address = trim($value);
				$addressArray = split(" ", $address);
				$parameters["ip"] = $addressArray[1];
				continue;			}

			if($cardfound === true && stripos($value, "netmask") !== false)
			{
				$netmask = trim($value);
				$netmaskArray = split(" ", $netmask);
				$parameters["mask"] = $netmaskArray[1];
				break;
			}
		}

		$rachel = file("$rachelcomconfFile");
		foreach ($rachel as $key => $value) {
			# code...
			if (stripos($value, "ServerAlias") !== false) {
				# code...
				$value = trim($value);
				$valueSplit = split(" ", $value);
				$parameters["homepage"] = $valueSplit[1];
				break;
			}
		}

		$dnsmasqconf = file("$dnsmasqconfFile");

		foreach ($dnsmasqconf as $value) {
			# code...

			// TO GET THE WIFI CARD 
	        if(stripos($value, "dhcp-range") !== false)
	        {
	            $valueSplit = split("=", $value);
				$dhcp = $valueSplit[1];
				$dhcpSplit = split(",", $dhcp);
				$sip = $dhcpSplit[0];
				$eip = $dhcpSplit[1];
				$dhcpmask = $dhcpSplit[2];
				$dhcpmask = trim($dhcpmask);

				$parameters["clients"] = clients($sip, $eip);
				$parameters["maxclients"] = maxclients($dhcpmask);

				$concession = trim($dhcpSplit[3]);
				$concession = rtrim($concession, "hH");
	            $parameters["sip"] = trim($sip);
	            $parameters["eip"] = trim($eip);
	            $parameters["concession"] = $concession;
	            
	        }
		}


	    // CONNECT TO THE DATABASE
		$conn = pg_connect("host= localhost dbname=configdb user=postgres password=postgres");


	    // CHECK IF WE GOT CONNECTION
		if($conn === false)
		{
	    // THERE WAS AN ERROR CONNECTING
		  $parameters["error"] = true;
		  $parameters["message"] = "ERROR CONNECTING TO THE CONFIG DATABASE";
		  return;
		}

	    // CONNECTION OK, QUERY THE DB TO CHECK
	    // IF THE SITE IS ALREADY PRESENT
	    $result = pg_query($conn, "SELECT cfgpwd FROM serverconf");
	    if($result === false)
	    {
	    	$parameters["adminpassword"] = 0;
  			// INSERT THE NEW CONFIGURATION
	    	$result = pg_query($conn, "INSERT INTO serverconf(cfgpwd) VALUES (0);");
	    	if($result === false)
	    	{
				$parameters["error"] = true;
				$parameters["message"] = "ERROR INITIALIZING THE CONFIGURATION ";
				echo json_encode($parameters);
        		return;	    		
	    	}    	
	    }
	    else
	    {
	    	// UPDATE THE CONFIGURATION
		  	$rows = pg_fetch_all($result);

		  	if($rows !== false)
		  	{
			  	$parameters["adminpassword"] = $rows[0]["cfgpwd"];
		  	}
		  	else
		  	{
		  		$parameters["adminpassword"] = 0;
	    	}
	    }
		echo json_encode($parameters);
	}

	else
	{
		// SAVE THE CONFIGURATION VALUES
		$ssid = $_REQUEST["ssid"];
		$pwd = $_REQUEST["pwd"];
		$wificard = $_REQUEST["wificard"];
		
		$ip1 = $_REQUEST["ip1"];	
		$ip2 = $_REQUEST["ip2"];
		$ip3 = $_REQUEST["ip3"];
		$ip4 = $_REQUEST["ip4"];
		
		$mask1 = $_REQUEST["mask1"];
		$mask2 = $_REQUEST["mask2"];
		$mask3 = $_REQUEST["mask3"];
		$mask4 = $_REQUEST["mask4"];
		
		$sIp1 = $_REQUEST["sIp1"];
		$sIp2 = $_REQUEST["sIp2"];
		$sIp3 = $_REQUEST["sIp3"];
		$sIp4 = $_REQUEST["sIp4"];

		$eIp1 = $_REQUEST["eIp1"];
		$eIp2 = $_REQUEST["eIp2"];
		$eIp3 = $_REQUEST["eIp3"];
		$eIp4 = $_REQUEST["eIp4"];
		
		$concession = $_REQUEST["concession"];
		$homepage = $_REQUEST["homepage"];

		$adminpassword = $_REQUEST["adminpassword"];

		$interfaces = file("$interfacesFile");
		if($interfaces === false)
		{
			$parameters["error"] = true;
			$parameters["message"] =  "SAVE OPERATION FAILED. FILE $interfacesFile COULD NOT BE READ";
			echo json_encode($parameters);
			return;
		}

		$dnsmasqconf = file("$dnsmasqconfFile");
		if($dnsmasqconf === false)
		{
			$parameters["error"] = true;
			$parameters["message"] = "SAVE OPERATION FAILED. FILE $dnsmasqconfFile COULD NOT BE READ";
			echo json_encode($parameters);
			return;
		}

		$hostapd = file("$hostapdconfFile");
		if($hostapdconf === false)
		{
			$parameters["error"] = true;
			$parameters["message"] = "SAVE OPERATION FAILED. FILE $hostapdconfFile COULD NOT BE READ";
			echo json_encode($parameters);
			return;
		}

		$rachel= file($rachelcomconfFile);
		if($rachel === false)
		{
			$parameters["error"] = true;
			$parameters["message"] = "SAVE OPERATION FAILED. FILE $rachelcomconfFile COULD NOT BE READ";
			echo json_encode($parameters);
			return;
		}

		$hosts = file("$hostsFile");
		if($hosts === false)
		{
			$parameters["error"] = true;
			$parameters["message"] = "SAVE OPERATION FAILED. FILE $hostsFile COULD NOT BE READ";
			echo json_encode($parameters);
			return;
		}

		$cardfound = false;
		$oldIp = "$ip1.$ip2.$ip3.$ip4";

		foreach ($interfaces as $key => $value) {
			# code...
			if(stripos($value, $wificard) !== false)
			{
				$cardfound = true;
				continue;
			}

			if($cardfound === true && stripos($value, "address") !== false)
			{
				$addressArray = split(" ", $value);
				$oldIp = trim($addressArray[1]);
				$interfaces[$key] = "$addressArray[0] $ip1.$ip2.$ip3.$ip4\n";
			}

			if($cardfound === true && stripos($value, "netmask") !== false)
			{
				$netmaskArray = split(" ", $value);
				$interfaces[$key] = "$netmaskArray[0] $mask1.$mask2.$mask3.$mask4\n";
			}

			if($cardfound === true && stripos($value, "network") !== false){
				$valueArray = split(" ", $value);
				$network1 = intval($ip1) & intval($mask1);
				$network2 = intval($ip2) & intval($mask2);
				$network3 = intval($ip3) & intval($mask3);
				$network4 = intval($ip4) & intval($mask4);
				$valueArray[1] = "$network1.$network2.$network3.$network4";
				$interfaces[$key] = "$valueArray[0] $valueArray[1]\n";
			}

			if($cardfound === true && stripos($value, "broadcast") !== false){
				$valueArray = split(" ", $value);
				$broadcast1 = intval($ip1) | (255 - intval($mask1));
				$broadcast2 = intval($ip2) | (255 - intval($mask2));
				$broadcast3 = intval($ip3) | (255 - intval($mask3));
				$broadcast4 = intval($ip4) | (255 - intval($mask4));
				$valueArray[1] = "$broadcast1.$broadcast2.$broadcast3.$broadcast4";
				$interfaces[$key] = "$valueArray[0] $valueArray[1]\n";
			}
		}

		if(file_put_contents("$interfacesFile", $interfaces) === false)
		{
			$parameters["error"] = true;
			$parameters["message"] = "SAVE OPERATION FAILED. FILE $interfacesFile COULD NOT BE WRITTEN";
			echo json_encode($parameters);
			return;
		}

		foreach ($dnsmasqconf as $key => $value) {
			# code...
			if(stripos($value, "dhcp-range") !== false)
			{
				$dnsmasqconf[$key] = "dhcp-range= $sIp1.$sIp2.$sIp3.$sIp4, $eIp1.$eIp2.$eIp3.$eIp4, $mask1.$mask2.$mask3.$mask4, $concession"."h";
			}
		}

		if(file_put_contents("$dnsmasqconfFile", $dnsmasqconf) === false)
		{
			$parameters["error"] = true;
			$parameters["message"] = "SAVE OPERATION FAILED. FILE $dnsmasqconfFile COULD NOT BE WRITTEN";
			echo json_encode($parameters);
			return;
		}

		foreach ($hostapd as $key => $value) {
			# code...
			if(stripos($value, "wpa_passphrase") !== false)
			{
				$hostapd[$key] = "wpa_passphrase=$pwd\n";
			}
			if(stripos($value, "ssid") !== false)
			{
				$hostapd[$key] = "ssid=$ssid\n";
			}
		}

		if(file_put_contents("$hostapdconfFile", $hostapd) === false)
		{
			$parameters["error"] = true;
			$parameters["message"] = "SAVE OPERATION FAILED. FILE $hostapdconfFile COULD NOT BE WRITTEN";
			echo json_encode($parameters);
			return;
		}

		foreach ($rachel as $key => $value) {
			# code...
			if(stripos($value, "ServerName") !== false)
			{
				$rachel[$key] = "ServerName $homepage\n";
			}
			if(stripos($value, "ServerAlias") !== false)
			{
				$valueArray = split(" ", $value);
				$oldHomePage = $valueArray[count($valueArray) - 1];
				$rachel[$key] = "ServerAlias $homepage\n";
			}
		}
		
		exec("service apache2 stop");

		if(file_put_contents("$rachelcomconfFile", $rachel) === false)
		{
			$parameters["error"] = true;
			$parameters["message"] = "SAVE OPERATION FAILED. FILE $rachelcomconfFile COULD NOT BE WRITTEN";
			echo json_encode($parameters);
			return;
		}

		foreach ($hosts as $key => $value) {
			# code...
 			if(stripos($value, $oldHomePage) !== false)
 			{
 				$hosts[$key] = "$ip1.$ip2.$ip3.$ip4 $homepage\n";
 				continue;
 			}
			if(stripos($value, $oldIp) !== false)
			{
				$value = trim($value);
				$valueArray = split(" ", $value);
				$hostIndex = count($valueArray) - 1;
				$hosts[$key] = "$ip1.$ip2.$ip3.$ip4 $valueArray[$hostIndex]\n";
 			}
		}

		if(file_put_contents("$hostsFile", $hosts) === false)
		{
			$parameters["error"] = true;
			$parameters["message"] = "SAVE OPERATION FAILED. FILE $hostsFile COULD NOT BE WRITTEN";
			echo json_encode($parameters);
			return;
		}

		// ADMIN PASSWORD SAVE OPERATION

	    // CONNECT TO THE DATABASE
		$conn = pg_connect("host= localhost dbname=configdb user=postgres password=postgres");


	    // CHECK IF WE GOT CONNECTION
		if($conn === false)
		{
	    // THERE WAS AN ERROR CONNECTING
			$parameters["error"] = true;
			$parameters["message"] = "ERROR CONNECTING TO THE CONFIG DATABASE";
			echo json_encode($parameters);
		  return;
		}

	    // CONNECTION OK, QUERY THE DB TO CHECK
	    // IF THE SITE IS ALREADY PRESENT
	    $result = pg_query($conn, "SELECT cfgpwd FROM serverconf");
	    if($result === false)
	    {
			$parameters["error"] = true;
			$parameters["message"] = "FAILED TO FETCH THE SERVER CONFIGURATION";
			echo json_encode($parameters);
  			return;	    	
	    }
	    else
	    {
	    	// UPDATE THE CONFIGURATION
		  	$rows = pg_fetch_all($result);

		  	if($rows === false)
		  	{
	  			// INSERT THE NEW CONFIGURATION
		    	$result = pg_query($conn, "INSERT INTO serverconf( cfgpwd) VALUES ('$adminpassword');");
		    	if($result === false)
		    	{
					$parameters["error"] = true;
					$parameters["message"] = "ERROR SAVING THE CONFIGURATION ";
					echo json_encode($parameters);
	        		return;	    		
		    	}
		  	}
		  	else
		  	{

			  	$orgcfgpwd = $rows[0]["cfgpwd"];

		    	$result = pg_query($conn, "UPDATE serverconf
					SET cfgpwd='$adminpassword' WHERE cfgpwd='$orgcfgpwd'");
		    	if($result === false)
		    	{
					$parameters["error"] = true;
					$parameters["message"] = "ERROR UPDATING THE CONFIGURATION ";
					echo json_encode($parameters);
	        		return;	    		
		    	}
	    	}
	    }

		$parameters["message"] = "SAVE OPERATION WAS SUCCESSFUL";
			echo json_encode($parameters);
		return;
	}

	function maxclients($mask)
	{
		$mask = trim($mask);
		$maskSplit = split("\.", $mask);
		$oct1 = floatval($maskSplit[0]);
		$oct2 = floatval($maskSplit[1]);
		$oct3 = floatval($maskSplit[2]);
		$oct4 = floatval($maskSplit[3]);

		$result = (255 - $oct1 + 1)*(255 - $oct2 + 1)*
					(255 - $oct3 + 1)*(255 - $oct4) - 3;

		return $result;
	}

	function clients($startIp, $endIp)
	{
		$startIpSplit = split("\.", $startIp);
		$sIp1 = $startIpSplit[0];
		$sIp2 = $startIpSplit[1];
		$sIp3 = $startIpSplit[2];
		$sIp4 = $startIpSplit[3];

		$endIpSplit = split("\.", $endIp);
		$eIp1 = $endIpSplit[0];
		$eIp2 = $endIpSplit[1];
		$eIp3 = $endIpSplit[2];
		$eIp4 = $endIpSplit[3];

		$result = ($eIp1 - $sIp1 + 1)*($eIp2 - $sIp2 + 1)*
						($eIp3 - $sIp3 + 1)*($eIp4 - $sIp4);
		return $result;
	}
?>