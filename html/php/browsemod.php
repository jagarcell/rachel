<?php
  	$url = $_REQUEST["url"];
//    $url = "http://dev.worldpossible.org/cgi/rachelmods.pl";
    $html_content = file_get_contents($url);
    $result = $html_content;
	$result = str_replace("/normalize", "http://dev.worldpossible.org/normalize", $result);
  $result = str_replace("/newlook", "http://dev.worldpossible.org/newlook", $result);
  $result = str_replace("/style", "http://dev.worldpossible.org/style", $result);
    $result = str_replace("/jquery", "http://dev.worldpossible.org/jquery", $result);
    $result = str_replace("../mods/", "http://dev.worldpossible.org/mods/", $result);
    $result = str_replace("../sample", "http://dev.worldpossible.org/sample", $result);
	$result = str_replace("/img", "http://dev.worldpossible.org/img", $result);
	$result = str_replace("/cgi/sample", "http://dev.worldpossible.org/cgi/sample", $result);
//	$result = str_replace("hidden", "none", $result);
    $result = str_replace("www.rachel.com", "http://dev.worldpossible.org", $result);
    echo $result;
  ?>
