<?php
  $rsync = $_REQUEST["rsync"];
  $link = $_REQUEST["link"]; 
  $description = $_REQUEST["description"];
  $imgsrc = $_REQUEST["imgsrc"];
  $title = $_REQUEST["title"];
  $iface = $_REQUEST["iface"];

  // EXPLODE THE src STRING TO GET THE IMAGE NAME
  $imgarray = explode("/", $imgsrc);
  $imgname = $imgarray[count($imgarray) - 1];

  // NORMALIZE THE rsync COMMAND REMOVING THE TARGER DIRECTORY
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
//  $rsync = str_replace("./", "/var/www/", $rsync);

  // VARIABLE TO HOLD THE rsync RESULT
  $retval = 0;

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
	  $conn = pg_connect("host= localhost dbname=configdb user=postgres password=postgres");

    // CHECK IF WE GOT CONNECTION
	  if($conn === false)
	  {
      // THERE WAS AN ERROR CONNECTING

	    echo "ERROR CONNECTING TO THE CONFIG DATABASE";
	    return;
	  }

    // CONNECTION OK, QUERY THE DB TO CHECK
    // IF THE SITE IS ALREADY PRESENT
    $result = pg_query($conn, "SELECT enlace, comando FROM enlaces WHERE comando='$command'");

    // IF WE DON'T GET A RESULT FROM THE DB REPORT IT
    if($result === false)
    {
      echo "ERROR FETCHING THE TABLE";
      return;
    }
    
    // AS WE GOT A RESULT FROM THE DB WE CHECK
    // IF THE  SITE IS ALREADY REGISTERED
    $row = pg_fetch_row($result);

    if($row === false)
    {
      // IF THE SITE IS NOT YET REGISTERED
      // WE WILL TRY TO REGISTER IT

      // WE REPLACE ALL THE ' CHARACTERS BY '' TO
      // AVOID ERROR WHEN UPDATING THE POSTGRESQL DB
      $enlace = str_replace("'", "''", $enlace);
      $command = str_replace("'", "''", $command);
      $description = str_replace("'", "''", $description);
      $title = str_replace("'", "''", $title);

      // LET'S INSERT THE DATA IN THE CONFIG DB
      $result = pg_query($conn, "INSERT INTO enlaces(
              enlace, comando, description, image, title)
          VALUES ('$enlace', '$command', '$description', '$dir/$imgname', '$title');");

      // IF THERE WAS AN ERROR DURING THE
      // REGISTRATION PROCESS WE WILL REPORT IT
      if($result === false)
      {
        echo "ERROR DURING THE TABLE WRITING";
        return;
      }
      else
      {
        // AS THERE WAS NO PROBLEM REGISTERING THE
        // SITE LET'S CONFIGURE THE ACCESS TO IT

        // LET'S SAVE THE
        $cwd = getcwd();

        // CHANGE WORKING DIRECTORY  
        chdir("/var/www/html/img");
        $newwd = getcwd();
        // MAKE A DIRECTORY UNDER img TO HOST
        // THE IMAGE FOR THE DOWNLOADING SITE
        mkdir($dir);

        // WE GO BACK TO THE PREVIOUS WORKING DIRECTORY
        chdir($cwd);

        // ... AND THE WE COPY THE IMAGE FROM THE REPOSITORY
        // TO THE PATH CREATED
        copy($imgsrc, "$newwd/$dir/$imgname");

        $ipAddr = getipaddr($iface);

        // REGISTER THE HOST IN THE HOSTS FIE
        $hostscount = file_put_contents("/etc/hosts", "\n$ipAddr  " . "$enlace", FILE_APPEND);

        // REGISTER THE HOST IN APACHE2
        $fileconf = "/etc/apache2/sites-available/" . "$dir" . ".com.conf";
        // WRITE THE CONFIGURATION TO THE
        // conf FILE ON sites-available
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

        // NOW WE WRITE THE CONFIGURATION TO THE
        // conf FILE ON THE sites-enbled DIRECTORY
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

        // RELOAD THE APACHE2 SERVICE TO MAKE AVILABLE THE CHANGES
        system("sudo service apache2 reload", $result);
        rename("/var/www/$dir/rachel-index.php", "/var/www/$dir/index.php");
        echo "NEW SITE DOWNLOADED";
      }
    }
    else
    {
        // LET'S SAVE THE
        $cwd = getcwd();

        // CHANGE WORKING DIRECTORY  
        chdir("/var/www/html/img");
        $newwd = getcwd();
        // MAKE A DIRECTORY UNDER img TO HOST
        // THE IMAGE FOR THE DOWNLOADING SITE
        mkdir($dir);

        // WE GO BACK TO THE PREVIOUS WORKING DIRECTORY
        chdir($cwd);

        // ... AND THE WE COPY THE IMAGE FROM THE REPOSITORY
        // TO THE PATH CREATED
        copy($imgsrc, "$newwd/$dir/$imgname");
        echo "SITE UPDATED";
    }
  }

  function getipaddr($iface)
  {
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
          return $ipArray[1];
        }
      }
      // THE REQUESTED INTERFACE IS NOT PRESENT
      // SO WE ECHO THE ERROR MESSAGE
      return "ADAPTER NOT FOUND";
  }
?>
