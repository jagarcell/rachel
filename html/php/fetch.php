<?php
  $conn = pg_connect("host= localhost dbname=configdb user=jagarcell password=Requeson1");
  if(!$conn)
  {
    echo "ERROR CONNECTING TO THE DATABASE";
    return;
  }
  $comandoDeDescarga = $_REQUEST["comandoDeDescarga"];

  str_replace(" ./", "", $comandoDeDescarga);

  $result = pg_query($conn, "SELECT enlace, comando FROM enlaces WHERE comando='$comandoDeDescarga'");

  if(!$result)
  {
    echo "ERROR FETCHING THE TABLE";
    return;
  }
  $row = pg_fetch_row($result);
  if(!$row)
  {
    echo "INEXISTENTE";
    return;
  }
  else
  {
    echo "EXISTENTE"; 
  }

//  echo json_encode($row);
  return;
  ?>