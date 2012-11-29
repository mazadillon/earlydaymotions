<?php
if(!isset($data['name'])) {
	$text = 'Sorry but we could not find any information for the MP you are looking for. The Early Day Motion archive only dates back to 1989 so if the MP left parliament before this time they will not be featured on the site.';
	include_once '404.htm.php';
	exit;
}
$title = $data['name'].' - Early Day Motions';
$header['keywords'] = $data['name'];
if(is_array($data['detail'])) foreach($data['detail'] as $session) $header['keywords'] .= ', '.$session['constituency'].', '.$session['party'];
$header['keywords'] .= ', MP, signatures, Early Day Motion';
$header['description'] = 'Early Day Motions proposed, seconded and signed by '.$data['name'].' MP';	
include 'templates/head.htm.php';
echo '<div id="piclinks" style="float:right;">';
if(isset($data['image'])) echo '<img src="'.$data['image'].'"  alt="Photo" title="'.$data['name'].'" /><br />';
if(isset($data['mysocid']) && !empty($data['mysocid'])) echo '<a href="http://www.theyworkforyou.com/mp/?pid='.$data['mysocid'].'"><img src="/images/twfy.png" title="TheyWorkForYou.com" /></a> ';
if(is_array($data['sessions'])) echo '<a href="http://www.parliament.uk/edm/'.$data['sessions'][0]['session'].'/'.$data['govid'].'/'.strtolower(str_replace(' ','-',$data['name'])).'"><img src="/images/gov.png" title="Parliament" /></a><br />';
$count = 0;
foreach($edm->config['media_links'] as $media) {
	if(isset($data[$media]) && !empty($data[$media])) {
		$count++;
		if($media == 'twitter') $data['twitter'] = 'http://www.twitter.com/'.$data['twitter'];
		echo '<a href="'.$data[$media].'"><img src="/images/'.$media.'.png" title="'.$media.'" /></a>';
		if($count == 2) {
			$count = 0;
			echo '<br />';
		} else {
			echo ' ';
		}
	}
}
echo "</div>\n";
echo '<h2>'.$data['name'];
echo " <span class='st_twitter' displayText=''></span>
<span class='st_facebook' displayText=''></span>
<span class='st_email' displayText=''></span>
<span class='st_sharethis' displayText=''></span>";
echo '</h2>';
if($data['breakdown']) {
	echo 'Total EDMs Signed: '.number_format(array_sum($data['breakdown'])).'<br /><br />';
	echo 'Signature Breakdown:<br />';
	echo ' Proposed: '.number_format($data['breakdown']['1']).'<br />';
	echo ' Seconded: '.number_format($data['breakdown']['2']).'<br />';
	echo ' Signed: '.number_format($data['breakdown']['3']).'<br /><br />';
}
if(is_array($data['detail'])) {
	foreach($data['detail'] as $session) {
		if(substr($session['end'],0,4) == '9999') echo 'Has represented ';
		else echo 'Represented ';
		if(empty($session['constituency'])) echo 'an unknown constituency';
		else echo htmlspecialchars($session['constituency']);
		if(!empty($session['party'])) echo ' as a <a href="/parties/'.urlencode(strtolower($session['party'])).'">'.htmlspecialchars($session['party']).'</a> MP';
		if(substr($session['end'],0,4) == '9999') echo ' since '.substr($session['start'],0,4).".<br />\n";
		elseif(substr($session['end'],0,4) == substr($session['start'],0,4)) echo ' during '.substr($session['end'],0,4)."<br />\n";
		else echo ' from '.substr($session['start'],0,4).' until '.substr($session['end'],0,4).".<br />\n";
		$con = $session['constituency'];
		$party = $session['party'];
	}
}
if($data['activity'] !== false) {
	echo "\n<h3>Recent Activity</h3>";
	echo '<ul>';
	foreach($data['activity'] as $action) {
		echo '<li>';
		if($action['date'] != '') echo '<a href="/calendar/'.strtolower(date('Y/F/j',strtotime($action['date']))).'">'.date('d/m/Y',strtotime($action['date'])).'</a> ';
		echo $action['type'].' <a href="/'.$action['session'].'/'.$action['edm'].'.htm">EDM '.$action['edm'].'</a> '.$action['title'].'</li>';
	}
	echo '</ul>';
}	

if($data['sessions']) {
	$highlight = 0;
	echo "\n<div class=\"left\">";
	echo '<h3>Historic Activity</h3>';
	echo '<table><tr><th>Session</th><th>Proposed</th><th>Seconded</th><th>Signed</th><th>Total</th></tr>';
	foreach($data['sessions'] as $session) {
		if($highlight) {
			echo '<tr class="highlight">';
			$highlight = 0;
		} else {
			echo '<tr>';
			$highlight = 1;
		}
		echo '<td><a href="/mps/'.$data['id'].'/'.$session['session'].'.htm">'.$session['session'].'</a></td><td>'.$session['breakdown']['1'].'</td><td>'.$session['breakdown']['2'].'</td><td>'.$session['breakdown']['3'].'</td><td>'.array_sum($session['breakdown']).'</td></tr>';
	}
	echo '</table>';
	echo "</div>\n";
} else echo '<h2>No Data</h2><p>This MP has never signed an early day motion.</p>';
if($data['topics']) {
	echo '<div class="right">';
	echo '<h3>Favourite Topics</h3>';
	echo '<p>These are the topics which motions proposed by '.$data['name'].' have been classified into.</p>';
	echo '<table><tr><th>Topic</th><th>Motions</th></tr>';
	$highlight = 0;
	foreach($data['topics'] as $topic) {
		if($highlight) {
			echo '<tr class="highlight">';
			$highlight = 0;
		} else {
			echo '<tr>';
			$highlight = 1;
		}
		echo '<td><a href="/topics/'.urlencode(strtolower($topic['topic'])).'">'.$topic['topic'].'</a></td><td>'.$topic['motions'].'</td></tr>';
	}
	echo '</table>';
	echo "</div>\n";
}
echo '<br clear="all" /><a href="/mps/'.$data['id'].'/index.rss"><img src="/images/rss-icon.png" alt="RSS Feed" /></a> <a href="http://www.feedmyinbox.com/?feed=http://www.edms.org.uk/mps/'.$data['id'].'/index.rss"><img src="/images/email.png" alt="Email" /></a> Receive updates on this MP&apos;s activity.<br />';
echo 'Download raw data as <a href="/mps/'.$data['id'].'/index.csv">csv</a> or <a href="/mps/'.$data['id'].'/index.xml">xml</a>.<br />';
include 'foot.htm.php';
?>
