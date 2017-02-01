<?php
// THIS IS THE INTERFACE'S NAME WHICH IP IS REQUIRED
	$iface = $_REQUEST["iface"];
    // WE GET HE INFO FROM THE INTERFACE BY
    // MEAN OF THE ip addr show COMMAND
    $localIP = exec('ip addr show', $output);
    // LOOP THROUGH THE localIP INFO ARRAY UNTIL
    // WE SEE THE REQUESTED INTERFACE NAME
    foreach ($output as $key => $value) {
      // IS THIS THE INTERFACE WE ARE LOOKING FOR?
      if(strstr($value, $iface))
      {
        // WE GOT A MATCH SO LET'S GET THE IP SECTION
        $ifInfoArray = split("/", $output[$key+2]);
        $ipArray = split("inet", $ifInfoArray[0]);
        // HERE IS THE IP, LET'S ECHO IT
        echo $ipArray[1];
        return;
      }
    }
    // THE REQUESTED INTERFACE IS NOT PRESENT
    // SO WE ECHO THE ERROR MESSAGE
    echo "ADAPTER NOT FOUND";
?>