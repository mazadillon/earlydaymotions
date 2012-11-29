<?php
$title = 'Keyword analysis for &quot;'.htmlspecialchars($data['keyword']).'&quot;';
include 'templates/head.htm.php';
echo '<form action="/analysis/keyword" method="get">';
echo 'Keyword: <input type="text" name="keyword" value="'.htmlspecialchars($data['keyword']).'" /> ';
echo 'Start Date: <input type="text" name="start" value="'.$data['start'].'" /> ';
echo 'End Date: <input type="text" name="end" value="'.$data['end'].'" /> ';
echo '<input type="submit" value="Analyse" /></form>';
if(isset($data['mps'])) {
	echo '<h1>Summary</h1>';
	echo '<ul>';
	echo '<li><a href="#mps">'.count($data['mps']).' MPs</a></li>';
	echo '<li><a href="#parties">'.count($data['parties']).' parties</a></li>';
	echo '<li><a href="#motions">'.count($data['motions']).' motions</a></li>';
	echo '</ul>';
	echo '<a name="mps"><h1>MPs</h1></a>';
	echo '<ol>';
	foreach($data['mps'] as $mp) {
		echo '<li><a href="/mps/'.$mp['id'].'">'.$mp['name'].'</a> ('.$mp['sort'].')</li>';
	}
	echo '</ol>';
	echo '<a name="parties"><h1>Parties</h1></a>';
	echo '<ol>';
	foreach($data['parties'] as $party => $value) {
		echo '<li><a href="/parties/'.$party.'">'.$party.'</a> ('.$value.')</li>';
	}
	echo '</ol>';
	echo '<a name="motions"><h1>Motions</h1></a>';
	foreach($data['motions'] as $motion) {
		echo '<a href="/edms/'.$motion['session'].'/'.$motion['edm'].'.htm">'.$motion['title'].'</a> ('.$motion['signatures'].')<br />';
	}
}
include 'foot.htm.php';
?>
