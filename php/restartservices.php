<?php
	system('sudo service dnsmasq restart', $result1);
	system('sudo service apache2 reload', $result2);
	system('sudo systemctl daemon-reload', $result3);
	system('sudo service hostapd restart', $result4);
	if($result1 == 0 && $result2 == 0 && $result3 == 0 && $result4 == 0)
	{
		echo "OK";
	}
	else
	{
		echo "ERROR";
	}
?>