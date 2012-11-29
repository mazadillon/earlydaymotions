<?php
echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
echo "<mp>\n";
echo "\t<id>".$data['mp']['id']."</id>\n";
echo "\t<name>".htmlspecialchars($data['mp']['name'])."</name>\n";
echo "\t<session>".$data['session']."</session>\n";
echo "\t<edms>\n";
foreach($data['edms'] as $edm) {
	echo "\t\t<edm>\n";
	echo "\t\t\t<number>".$edm['edm']."</number>\n";
	echo "\t\t\t<title>".htmlspecialchars($edm['title'])."</title>\n";
	echo "\t\t\t<date>".$edm['signed_date']."</date>\n";
	echo "\t\t\t<type>".$edm['type']."</type>\n";
	echo "\t\t</edm>\n";
}
echo "\t</edms>\n";
echo "</mp>";
?>
