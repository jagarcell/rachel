  <?php
  	$url = $_REQUEST["url"];
//    $url = "http://dev.worldpossible.org/cgi/rachelmods.pl";
    $html_content = file_get_contents($url);
    $inicio = strpos($html_content, "<ul class=");
    $longitud = strpos($html_content, "ul>", $inicio) - $inicio + 3;
    $result = $html_content;
//    $result = str_replace("/cgi", "browse.php/cgi", $result);
    $result = substr($html_content, $inicio, $longitud);

    $result = str_replace("viewmod", "http://dev.worldpossible.org/cgi/viewmod", $result);
    $result = str_replace("../mods", "http://dev.worldpossible.org/mods", $result);
    $result = str_replace("www.rachel.com", "http://dev.worldpossible.org", $result);
    $result = "<!DOCTYPE html>
				<html>
				<head>
					<title></title>
				  <link rel='stylesheet' href='http://dev.worldpossible.org/style.css'>
				</head>
				<body>
					<div>
    				" . $result . "		
					</div>
				</body>
				</html>
				";
    echo $result;
  ?>
