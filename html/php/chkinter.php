<?php
	$response = null;
	system("ping -c 1 google.com", $response);

	if ($response == 0) {
		# code...
	} else {
		# code...
	}
 ?>