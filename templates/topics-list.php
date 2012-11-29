<?php
$title = 'Listing of Early Day Motion Topics';
include '../templates/head.htm.php';
echo '<h1>'.$title.'</h1>';
echo '<p>Early day motions have been categorised into <b>'.number_format($data['count']).'</b> topics, some motions have been categorised into multiple topics and in total <b>'.number_format($data['categorised']).'</b> categorisations have been made. Motions are categorised into topics in the parliament offices, the complete archive has not been categorised - only back as far as the 2007-2008 session.</p>';
$letter = false;
foreach($data['topics'] as $topic) {
	$l = strtolower($topic['topic'][0]);
	if($letter != $l) {
		if($letter != false) echo '</ul>';
		$letter = $l;
		echo '<h2>'.strtoupper($l).'</h2>';
		echo '<ul>';
	}
	echo '<li><a href="/topics/'.urlencode(strtolower($topic['topic'])).'">'.$topic['topic'].'</a> ('.$topic['motions'].')</li>';
}
echo '</ul>';
include '../templates/foot.htm.php';
?>
