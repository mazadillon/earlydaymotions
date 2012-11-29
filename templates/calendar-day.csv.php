<?php
echo "date,id,session,number,title,proposer_id,proposer_name\n";
if(count($data['proposed']) > 0 && $data['signatures'] > 0) {
	foreach($data['proposed'] as $motion) {
		echo date('Y-m-d',$data['date']).",";
		echo $motion['id'].",";
		echo $motion['session'].",";
		echo $motion['edm'].",";
		echo "\"".$motion['title']."\",";
		echo $motion['mp'].",";
		echo "\"".$motion['name']."\"\n";
	}
}
?>
