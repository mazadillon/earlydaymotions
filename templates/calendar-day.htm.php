<?php
$title = 'Activity for '.date('F jS Y',$data['date']);
$header['keywords'] = 'Early day motion, signatures, proposed, signed, seconded, '.date('F jS Y',$data['date']);
$header['description'] = 'See which motions were proposed, seconded and signed on '.date('F jS Y',$data['date']);
include '../templates/head.htm.php';
echo '<table id="calendar">';
echo '<tr>';
if($data['prev'] > 0) echo '<th><a href="/calendar/'.strtolower(date('Y/F/j',$data['prev'])).'">&lt;&lt; '.date('F jS',$data['prev']).'</a></th>';
else echo '<th>No More Data</th>';
echo '<th colspan="3"><a href="/calendar/'.date('Y',$data['date']).'/'.strtolower(date('F',$data['date'])).'">'.date('F ',$data['date']).'</a> '.date('jS Y',$data['date']).'</th>';
if($data['next'] > 0) echo '<th><a href="/calendar/'.date('Y',$data['next']).'/'.strtolower(date('F',$data['next'])).'/'.date('j',$data['next']).'">'.date('F jS',$data['next']).' &gt;&gt;</a></th>';
else echo '<th>No More Data</th>';
echo '</tr>';
echo '</table>';
if($data['signatures'] > 0) {
	echo '<p>A total of '.count($data['mps']).' mps proposed '.count($data['proposed']).' motions and submitted a total of '.$data['signatures'].' signatures.</p>';
	echo '<div class="container">';
	echo '<div class="left">';
	echo '<h2>Motions Proposed Today</h2>';
	if(!empty($data['proposed'])) {
		foreach($data['proposed'] as $motion) {
			echo '<a href="/'.$motion['session'].'/'.$motion['edm'].'.htm">EDM'.$motion['edm'].'</a> '.$motion['title'].' proposed by <a href="/mps/'.$motion['mp'].'/'.$motion['session'].'.htm">'.$motion['name']."</a><br />\n";
		}
	} else echo '<p>None proposed</p>';
	echo '</div>';

	echo '<div class="right">';
	echo '<h2>Today\'s Most Popular Motions</h2>';
	echo '<ol>';
	foreach($data['popular'] as $motion) {
		echo '<li><a href="/'.$motion['session'].'/'.$motion['edm'].'.htm">EDM'.$motion['edm'].'</a> '.$motion['title'].' ('.$motion['count']." signatures)</li>\n";
	}
	echo '</ol>';

	echo '<h2>Today\'s Most Prolific MPs</h2>';
	echo '<ol>';
	$count = 0;
	foreach($data['mps'] as $mp) {
		$count++;
		echo '<li><a href="/mps/'.$mp['id'].'/'.$motion['session'].'.htm">'.$mp['name'].'</a> ('.$mp['count']." signatures)</li>\n";
		if($count > 10) break;
	}
	echo '</ol>';
	echo '</div>';
	echo '</div>';
} else {
	if($data['date'] >= strtotime(date('Y-m-d'))) echo '<p>Data is not yet available for this date.</p>';
	else echo '<p>There were no motions proposed or signed on this date.</p>';
}
echo "<br clear=\"all\">";
echo '<a href="/calendar/'.strtolower(date('Y/F/j',$data['date'])).'/xml"><img src="/images/xml.png" alt="xml" title="Download as XML" /></a> ';
echo '<a href="/calendar/'.strtolower(date('Y/F/j',$data['date'])).'/csv"><img src="/images/csv.png" alt="xml" title="Download as CSV" /></a> ';
echo 'Download raw data.';

include '../templates/foot.htm.php';
?>
