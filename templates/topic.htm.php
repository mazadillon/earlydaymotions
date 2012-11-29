<?php
$title = $data[0]['topic'];
$header['keywords'] = $data[0]['topic'].', early day motions, parliament, government, topic, EDM';
$header['description'] = 'Early day motions categorised under topic '.$data[0]['topic'];
$header['feed'] = '/topics/'.strtolower(urlencode($data[0]['topic'])).'/rss';
include '../templates/head.htm.php';
if(!isset($data[0]['topic'])) {
	echo '<p>No EDMs have been categorised with this topic.</p>';
} else {
echo '<div id="parliamentbutton"><a href="http://edmi.parliament.uk/EDMi/EDMByTopic.aspx?TOPIC_ID='.$data[0]['topic'].'"><img src="/images/parliament_button.png" alt="Parliament Website" title="View this topic at the original source" /></a></div>';
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
echo '<a href="/topics/'.strtolower(urlencode($data[0]['topic'])).'/xml"><img src="/images/xml.png" alt="xml" title="Download as XML" /></a> ';
echo '<a href="/topics/'.strtolower(urlencode($data[0]['topic'])).'/csv"><img src="/images/csv.png" alt="xml" title="Download as CSV" /></a> ';
echo '<a href="'.strtolower(urlencode($data[0]['topic'])).'/json"><img src="/images/json.png" alt="json" title="Download as JSON" /></a> ';
echo 'Download raw data.';
include '../templates/foot.htm.php';
?>
