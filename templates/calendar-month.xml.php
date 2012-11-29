<?php
echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
echo "<month>\n";
echo "\t<year>".date('Y',$data['start'])."</year>\n";
echo "\t<month>".date('F',$data['start'])."</month>\n";
echo "\t<activity>\n";
if(count($data['edms']) > 0) {
	foreach($data['edms'] as $day) {
		echo "\t\t<day>\n";
		echo "\t\t\t<date>".$day['date']."</date>\n";
		echo "\t\t\t<signatures>";
		foreach($data['signatures'] as $signatures) {
			if($signatures['date'] == $day['date']) echo $signatures['count'];
		}
		echo "</signatures>\n";
		echo "\t\t\t<proposed>".$day['count']."</proposed>\n";
		echo "\t\t</day>\n";
	}
}
echo "\t</activity>\n";
echo "</month>";
?>
