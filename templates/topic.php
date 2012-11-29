<?php
$title = $data[0]['topic'];
include '../templates/head.htm.php';
if(!isset($data[0]['topic'])) {
	echo '<p>No EDMs have been categorised with this topic.</p>';
} else {
	echo '<h1>'.$title.'</h1>';
	echo '<p>A total of <b>'.count($data).'</b> early day motions have been categorised under the <a href="/topics">topic</a> of <b>'.$title.'</b>.</p>';
	$session = false;
	foreach($data as $motion) {
		if($motion['session'] != $session) {
			if($session != false) echo '</ul>';
			echo '<h2>'.$motion['session'].'</h2>';
			echo '<ul>';
		}
		$date = strtotime($motion['date']);
		echo '<li><a href="/'.$motion['session'].'/'.$motion['edm'].'.htm">EDM'.$motion['edm'].' '.$motion['title'].'</a> proposed on <a href="/calendar/'.strtolower(date('Y/F/j',$date)).'">'.date('d/m/Y',$date).'</a></li>';
		$session = $motion['session'];		
	}
	echo '</ul>';
}
include '../templates/foot.htm.php';
?>
