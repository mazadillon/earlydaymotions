<?php
header("Content-Type: application/xml; charset=UTF-8");
echo '<?xml version="1.0" encoding="UTF-8"?>';
echo "<topics>\n";
foreach($data['topics'] as $topic) {
	echo "\t<topic id=\"".strtolower(urlencode($topic['topic']))."\">\n";
	echo "\t\t<topic>".$topic['topic']."</topic>\n";
	echo "\t\t<motions>".$topic['motions']."</motions>\n";
	echo "\t</topic>\n";
}
echo "</topics>\n";
?>
