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
  $result = pg_query($conn, "SELECT enlace, description, image, title FROM enlaces");
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
    // STARTS THE OFFLINE SITES MENU TABLE
    echo "        <table style='width: 98%'>";

  	// LOOP THROUGHT THE LINKS ARRAY ROWS 
  	foreach ($links as $key => $row) {
      // SET THE VALUES FOR THE OFFLINE SITE MENU ENTRY
  		$link = $row["enlace"];
      $description = $row["description"];
      $image = "/img/" . $row["image"];
      $title = $row["title"];

      // IF THERE IS NO TITLE ...
      if(strlen($title) == 0)
      {
        // ... LET'S SHOW THE LINK
        $title = $link;
      }

      # ASSEMBLY THE OFFLINE SITE ENTRY AND ECHO IT TO BUILD THE MENU
      # WE ADD THE ROW ID $key TO THE updatesite SO THE MENU'S ENTRY
      # WILL KNOW WHICH IS THE CORRESPONDING ROW CONTAINING THE INFO
	  	echo "          <tr style='width: 100%;'>
            <td href='http://$link' style='width: 14%'>
              <a href='http://$link'>
                <img src='$image' style='width: 60px; height:60px'>
              </a>  
            </td>
            <td style='width: 30%; vertical-align: center;'>
              <a id='link_$key' href='http://$link'>$title</a>
            </td>
            <td style='width: 40%; vertical-align: top;'>
              $description
            </td>
            <td id='update_$key' class='updatetd' style='width: 8%;text-align: center;'>
              <a style='color: rgb(0,0,255);' onclick='updatesite($key)'>Update</a>
            </td>
            <td id='delete_$key' class='updatetd' style='width: 8%; text-align: center;'>
              <a style='color: rgb(0,0,255);' onclick='deletesite($key)'>Delete</a>
            </td>
          </tr>";
  	}

    // END OF OFFLINE SITES MENU TABLE
    echo "        </table>";
  }
?>
