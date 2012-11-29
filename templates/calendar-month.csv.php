<?php
echo "date,proposed,signed\n";
if(count($data['edms']) > 0) {
	foreach($data['edms'] as $day) {
		echo $day['date'].",";
		foreach($data['signatures'] as $signatures) {
			if($signatures['date'] == $day['date']) echo $signatures['count'].",";
		}
		echo $day['count']."\n";
	}
}
?>
