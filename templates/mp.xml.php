<?php
echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
echo "<mp>\n";
echo "\t<id>".$data['id']."</id>\n";
echo "\t<name>".htmlspecialchars($data['name'])."</name>\n";
echo "\t<sessions>\n";
foreach($data['sessions'] as $session) {
	echo "\t\t<session>\n";
	echo "\t\t\t<period>".$session['session']."</period>\n";
	echo "\t\t\t<proposed>".$session['breakdown']['1']."</proposed>\n";
	echo "\t\t\t<seconded>".$session['breakdown']['2']."</seconded>\n";
	echo "\t\t\t<signed>".$session['breakdown']['3']."</signed>\n";
	echo "\t\t</session>\n";
}
echo "\t</sessions>\n";
echo "</mp>";
?>
