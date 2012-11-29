<?php
echo '<?xml version="1.0" encoding="UTF-8"?>';
echo "<edm>\n";
echo '<id>'.$data['id']."</id>\n";
echo '<session>'.$data['session']."</session>\n";
echo '<number>'.$data['edm']."</number>\n";
echo '<title>'.$data['title']."</title>\n";
echo '<proposer id="'.$data['proposingmp']['id'].'">'.$data['proposingmp']['name']."</proposer>\n";
echo '<date>'.$data['date']."</date>\n";
echo '<text>'.$data['text']."</text>\n";
echo "<topics>\n";
if(!empty($data['topics'])) {
	foreach($data['topics'] as $topic) {
		echo "\t<topic>".$topic['topic']."</topic>\n";
	}
}
echo "</topics>\n";
echo "<signatures>\n";
foreach($data['signatures'] as $signature) {
	echo "\t<signature>\n";
	echo "\t\t<mp id=\"".$signature['mp'].'">'.$signature['name']."</mp>\n";
	echo "\t\t<date>".$signature['date']."</date>\n";
	echo "\t\t<type>".$signature['type']."</type>\n";
	echo "\t\t<constituency>".htmlentities($signature['constituency'], ENT_COMPAT, 'UTF-8')."</constituency>\n";
	echo "\t\t<party>".$signature['party']."</party>\n";
	echo "\t</signature>\n";
}
echo "</signatures>\n";
echo '</edm>';
?>
