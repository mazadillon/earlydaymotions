<?php
echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
echo "<day>\n";
echo "\t<date>".date('Y-m-d',$data['date'])."</date>\n";
echo "\t<signed>".$data['signatures']."</signed>\n";
if(count($data['proposed']) > 0 && $data['signatures'] > 0) {
	echo "\t<proposed>".count($data['proposed'])."</proposed>\n";
	echo "\t<mps>".count($data['mps'])."</mps>\n";
	echo "\t<session>".$data['proposed'][0]['session']."</session>\n";
	echo "\t<proposed>\n";
	foreach($data['proposed'] as $motion) {
		echo "\t\t<motion id=\"".$motion['id']."\">";
		echo "\t\t\t<number>".$motion['edm']."</number>\n";
		echo "\t\t\t<title>".htmlspecialchars($motion['title'])."</title>\n";
		echo "\t\t\t<proposer id=\"".$motion['mp']."\">".htmlspecialchars($motion['name'])."</proposer>\n";
		echo "\t\t</motion>\n";
	}
	echo "\t</proposed>\n";
}
echo "</day>";
?>
