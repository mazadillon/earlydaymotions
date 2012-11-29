<?php
header("Content-Type: application/xml; charset=UTF-8");
echo '<?xml version="1.0" encoding="UTF-8"?>';
echo "<topic id=\"".urlencode($data[0]['topic'])."\">\n";
echo "<motions>\n";
foreach($data as $topic) {
	echo "\t<motion id=\"".$topic['id']."\">\n";
	echo "\t\t<session>".$topic['session']."</session>\n";
	echo "\t\t<edm>".$topic['edm']."</edm>\n";
	echo "\t\t<title>".$topic['title']."</title>\n";
	echo "\t</motion>\n";
}
echo "</motions>\n";
echo "</topic>";
?>
