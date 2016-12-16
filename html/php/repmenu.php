<?php
	// CONNECT TO CONFIGUATION DATABASE
	$conn = pg_connect("host=localhost dbname=configdb user=jagarcell password=Requeson1");
  if(!$conn)
  {
  	// IF NO CONNECTION RETURN AN ERROR
    echo "ERROR CONNECTING TO THE DATABASE";
    return;
  }
  // AS WE CONNECTED TO THE DATABASE LET'S QUERY
  // THE LINK'S TABLE FOR ALL AVAILABLE OFFLINE SITES
  $result = pg_query($conn, "SELECT enlace, description FROM enlaces");
  if(!$result)
  {
  	// IF THERE IS NOT RESULT RETURN AN ERROR
  	echo ("NO RESULTS AFTER FETCHING THE SITE'S MENU");
  	return;
  }
  // WE'VE GOT RESULTS SO LET'S FETCH THE ROWS
  $links = pg_fetch_all($result);
  if($links)
  {
    echo "        <table style='width: 98%'>";

  	// LOOP THROUGHT THE LINKS ARRAY ROWS 
  	foreach ($links as $key => $row) {
  		# GET THE LINK AND ECHO IT
  		$link = $row["enlace"];
      $description = $row["description"];
	  	echo "          <tr style='width: 100%'>
            <td style='width: 30%; vertical-align: top;'>
              <a href='http://$link'>$link</a>
            </td>
            <td width: 70%; vertical-align: top;'>
              $description
            </td>
          </tr>";
  	}
    echo "        </table>";
  }
?>
