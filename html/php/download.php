<?php
  $rsync = $_REQUEST["rsync"];
  $link = $_REQUEST["link"]; 
  $description = $_REQUEST["description"];
  $retval = 0;

  $command = $rsync;
  str_replace(" ./", "", $command);

  // LET'S GET THE REPOSITORY DIRECORY FROM DE RSYNC LINE
  $commandStrings = explode("/", $rsync);
  $dir = $commandStrings[count($commandStrings) - 2];
  $dir = str_replace(".", "", $dir);
  $dir = trim($dir);  
  
  // WE BUILD HERE THE LINK TO THE SITE TO BE HOSTED IN APACHE2
  $enlace = "www." . $dir . ".com";

  // THIS IS THE RSYNC COMMAND TO BE RUN FOR THE SITE DOWNLOADING
  // THE COMMAND INSTRUCTS TO DOWNLOAD THE SITE TO THE SUBDIRECTORY
  // WITH THE ORIGIN NAME UNDER /var/www AND OUTPUT THE DOWNLOAD PROGRESS
  // TO FILE CALLED WITH THE SAME SUBDIRECTORY NAME AND THE .txt EXTENSION
  $rsync = str_replace("./", "/var/www/ > /var/www/html/" . $dir . ".txt", $rsync);

  // EXECUTE THE RSYNC COMMAND	  
  system($rsync, $retval);

  // CHECK IF THE DOWNLOAD WAS OK
	if ($retval != 0) {
  	# ERROR, DON'T ADD THE LINK TO THE CONFIGDB
		echo("ERROR " . $retval . " DOWNLOADING THE SITE, TRY AGAIN LATER = ");
		echo $rsync;
		return;
  } 
  else {
  	# DOWNLOAD OK, ADD THE LINK TO
  	# THE CONFIGDB IF IT DOESN'T EXIST

    // CONNCT TO THE DATABASE
	  $conn = pg_connect("host= localhost dbname=configdb user=jagarcell password=Requeson1");

    // CHECK IF WE GOT CONNECTION
	  if(!$conn)
	  {
      // THERE WAS AN ERROR CONNECTING

	    echo "ERROR AL ABRIR LA CONECCION";
	    return;
	  }

    // CONNECTION OK, QUERY THE DB TO CHECK
    // IF THE SITE IS ALREADY PRESENT
    $result = pg_query($conn, "SELECT enlace, comando FROM enlaces WHERE comando='$command'");

    // IF WE DON'T GET A RESULT FROM THE DB REPORT IT
    if(!$result)
    {
      echo "ERROR AL BUSCAR EN LA TABLA";
      return;
    }

    // AS WE GOT A RESULT FROM THE DB WE CHECK
    // IF THE  SITE IS ALREADY REGISTERED
    $row = pg_fetch_row($result);

    if(!$row)
    {
      // IF THE SITE IS NOT YET REGISTERED
      // WE WILL TRY TO REGISTER IT
      $enlace = str_replace("'", "''", $enlace);
      $command = str_replace("'", "''", $command);
      $description = str_replace("'", "''", $description);

      $result = pg_query($conn, "INSERT INTO enlaces(
              enlace, comando, description)
          VALUES ('$enlace', '$command', '$description');");

      // IF THERE WAS AN ERROR DURING THE
      // REGISTRATION PROCESS WE WILL REPORT IT
      if(!$result)
      {
        echo "<br>$command<br>";
        echo "ERROR AL REGISTRAR EN LA TABLA";
        return;
      }
      else
      {
        // AS THERE WAS NO PROBLEM REGISTERING THE
        // SITE LET'S CONFIGURE THE ACCESS TO IT

        // REGISTER THE HOST IN THE HOSTS FIE
        $hostscount = file_put_contents("/etc/hosts", "\n127.0.0.1  " . "$enlace", FILE_APPEND);

        // REGISTER THE HOST IN APACHE2
        $fileconf = "/etc/apache2/sites-available/" . "$dir" . ".com.conf";
        $apache2confcount = file_put_contents($fileconf, 
              "<VirtualHost *:80>
          # The ServerName directive sets the request scheme, hostname and port that
          # the server uses to identify itself. This is used when creating
          # redirection URLs. In the context of virtual hosts, the ServerName
          # specifies what hostname must appear in the request's Host: header to
          # match this virtual host. For the default virtual host (this file) this
          # value is not decisive as it is used as a last resort host regardless.
          # However, you must set it for any further virtual host explicitly.
          #ServerName www.example.com

          ServerAdmin webmaster@localhost
          DocumentRoot /var/www/" . "$dir" .
          "\n         ServerName  " . "$enlace" .
          "\n         ServerAlias " . "$dir" . ".com" .
          "\n
          # Available loglevels: trace8, ..., trace1, debug, info, notice, warn,
          # error, crit, alert, emerg.
          # It is also possible to configure the loglevel for particular
          # modules, e.g.
          #LogLevel info ssl:warn

          ErrorLog ${APACHE_LOG_DIR}/error.log
          CustomLog ${APACHE_LOG_DIR}/access.log combined

          # For most configuration files from conf-available/, which are
          # enabled or disabled at a global level, it is possible to
          # include a line for only one particular virtual host. For example the
          # following line enables the CGI configuration for this host only
          # after it has been globally disabled with 'a2disconf'.
          #Include conf-available/serve-cgi-bin.conf
        </VirtualHost>

        # vim: syntax=apache ts=4 sw=4 sts=4 sr noet");

        $fileconf = "/etc/apache2/sites-enabled/" . "$dir" . ".com.conf";
        $apache2confcount = file_put_contents($fileconf, 
              "<VirtualHost *:80>
          # The ServerName directive sets the request scheme, hostname and port that
          # the server uses to identify itself. This is used when creating
          # redirection URLs. In the context of virtual hosts, the ServerName
          # specifies what hostname must appear in the request's Host: header to
          # match this virtual host. For the default virtual host (this file) this
          # value is not decisive as it is used as a last resort host regardless.
          # However, you must set it for any further virtual host explicitly.
          #ServerName www.example.com

          ServerAdmin webmaster@localhost
          DocumentRoot /var/www/" . "$dir" .
          "\n         ServerName  " . "$enlace" .
          "\n         ServerAlias " . "$dir" . ".com" .
          "\n
          # Available loglevels: trace8, ..., trace1, debug, info, notice, warn,
          # error, crit, alert, emerg.
          # It is also possible to configure the loglevel for particular
          # modules, e.g.
          #LogLevel info ssl:warn

          ErrorLog ${APACHE_LOG_DIR}/error.log
          CustomLog ${APACHE_LOG_DIR}/access.log combined

          # For most configuration files from conf-available/, which are
          # enabled or disabled at a global level, it is possible to
          # include a line for only one particular virtual host. For example the
          # following line enables the CGI configuration for this host only
          # after it has been globally disabled with 'a2disconf'.
          #Include conf-available/serve-cgi-bin.conf
        </VirtualHost>

        # vim: syntax=apache ts=4 sw=4 sts=4 sr noet");

        system("sudo service apache2 reload", $result);
      }
    }
  }

//  echo $retval . " " . $rsync . " hosts = " . $hostscount;
?>
