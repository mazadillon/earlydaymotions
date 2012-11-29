<?php
$title = 'Early Day Motions';
include 'templates/head.htm.php';

echo '<div class="container">';
echo '<div class="left"><h3>Introduction</h3><p>This website aims to provide a simple and open
interface to <a href="/about">Early Day Motion</a> information in order to enable the public to engage with the political
process more easily. The information is obtained through <a href="http://data.parliament.uk/edmi/edmi.svc/help">XML feeds</a>
from the backend of the <a href="http://edmi.parliament.uk/">parliament portal</a> and is available for further reuse.</p></div>';
echo '<div class="right">';
	echo '<h3>Data Stats for Current Session</h3>';
	echo 'MPs Active: '.number_format($data['stats']['mps']).'<br />';
	echo 'EDMs Proposed: '.number_format($data['stats']['edms']).'<br />';
	echo 'Total Signatures: '.number_format($data['stats']['signatures']).'<br />';
	$date = strtotime($data['addition'][0]['date']);
	echo 'Most Recent Addition: <a href="/calendar/'.strtolower(date('Y/F/j',$date)).'">'.date('d/m/Y',$date).'</a>';
echo '</div>';
echo '</div><div class="container">';
echo '<div class="left">';
	echo '<h3>Newest Motions <a href="/recent.rss"><img src="/images/rss-icon.png" alt="RSS" title="RSS Feed" /></a></h3>';
	$highlight = 0;
	if($data['recent'] !== false) {
		echo '<ul>';
		foreach($data['recent'] as $edm) echo  '<li><a href="/'.$edm['session'].'/'.$edm['edm'].'.htm">EDM '.$edm['edm'].'</a> '.$edm['title'].'</li>';
		echo '</ul>';
	}
echo '</div><div class="right">';
	echo '<h3>Current Best Supported Motions</h3>';
	$highlight = 0;
	if($data['popular'] !== false) {
		echo '<ol>';
		foreach($data['popular'] as $edm) echo '<li><a href="/'.$edm['session'].'/'.$edm['edm'].'.htm">EDM '.$edm['edm'].'</a> '.$edm['title'].'</li>';
		echo '</ol>';
	}
echo '</div>';
echo '</div><div class="container">';
echo '<div class="left">';
	echo '<h3>Motions Recently Popular with MPs <a href="/popular.rss"><img src="/images/rss-icon.png" alt="RSS" title="RSS Feed" /></a></h3>';
	$highlight = 0;
	if($data['booming'] !== false) {
		echo '<ol>';
		foreach($data['booming'] as $edm) echo '<li><a href="/'.$edm['session'].'/'.$edm['edm'].'.htm">EDM '.$edm['edm'].'</a> '.$edm['title'].'</li>';
		echo '</ol>';
	} else echo 'Nothing right now, parliament may be in recess.';
echo '</div><div class="right">';
	echo '<h3>Most Prolific MPs</h3>';
	$highlight = 0;
	if($data['prolific'] !== false) {
		echo '<ol>';
		foreach($data['prolific'] as $mp) echo '<li><a href="/mps/'.$mp['id'].'/'.$data['session'].'.htm">'.$mp['name'].'</a></li>';
		echo '</ol>';
	}
	echo '</div></div>';	
include 'foot.htm.php';
?>
