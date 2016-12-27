<?php
  $conn = pg_connect("host= localhost dbname=configdb user=postgres password=postgres");
  if(!$conn)
  {
    echo "ERROR AL ABRIR LA CONECCION";
    return;
  }
  $comandoDeDescarga = $_REQUEST["comandoDeDescarga"];
  $enlace = $_REQUEST["enlace"];
  $result = pg_query($conn, "INSERT INTO enlaces(
            enlace, comando, idunico)
            VALUES ($enlace, $comandoDeDescarga, 1)");
  if(!$result)
  {
    echo "ERROR AL BUSCAR EN LA TABLA";
    return;
  }

//  echo json_encode($row);
  return;
  ?>