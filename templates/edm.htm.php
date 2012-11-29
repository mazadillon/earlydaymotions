<?php
$title = $data['title'].' (EDM'.$data['edm'].')';
$header['keywords'] = $data['title'].', '.$data['proposingmp']['name'].', EDM'.$data['edm'].', '.$data['edm'].', early day motion, signatures, signed';
if($data['topics'] != false) foreach($data['topics'] as $topic) $header['keywords'] .= ', '.$topic['topic'];
$header['description'] = $data['title'].' by '.$data['proposingmp']['name'].': '.$data['text'];
$header['feed'] = '/edms/'.$data['session'].'/'.$data['edm'].'.rss';
include 'templates/head.htm.php';
echo '<div id="parliamentbutton"><a href="http://www.parliament.uk/edm/'.$data['session'].'/'.$data['edm'].'"><img src="/images/parliament_button.png" alt="Parliament Website" title="View this motion at the original source" /></a>';
echo "</div>\n";
echo '<h2>'.$data['title'];
echo " <span class='st_twitter' displayText=''></span>
<span class='st_facebook' displayText=''></span>
<span class='st_email' displayText=''></span>
<span class='st_sharethis' displayText=''></span>";
echo '</h2>';
echo '<p>EDM number '.$data['edm'].' in '.$data['session'];
echo ', proposed by <a href="/mps/'.$data['proposingmp']['id'].'/'.$edm->mpNameURL($data['proposingmp']['name']).'">'.$data['proposingmp']['name'].'</a> on <a href="/calendar/'.strtolower(date('Y/F/j',strtotime($data['date']))).'">'.date('d/m/Y',strtotime($data['date'])).'</a>.';
if($data['topics']) {
	$count = count($data['topics']);
	if($count == 1) {
		echo '<br />Categorised under the topic of <a href="/topics/'.urlencode(strtolower($data['topics'][0]['topic'])).'">'.$data['topics'][0]['topic'].'</a>.';
	} elseif($count > 1) {
		echo '<br />Categorised under the topics of ';
		echo '<a href="/topics/'.urlencode(strtolower($data['topics'][0]['topic'])).'">'.$data['topics'][0]['topic'].'</a>';
		for($i = 1;$i < $count - 1;$i++) {
			echo ', <a href="/topics/'.urlencode(strtolower($data['topics'][$i]['topic'])).'">'.$data['topics'][$i]['topic'].'</a>';
		}
		echo ' and <a href="/topics/'.urlencode(strtolower($data['topics'][$i]['topic'])).'">'.$data['topics'][$i]['topic'].'.</a>';
	}
}
echo '</p>';
echo '<p class="motiontext">'.htmlspecialchars($data['text']).'</p>';
if(($pos = strpos($data['edm'],'A')) !== false) {
	$original = substr($data['edm'],0,$pos);
	echo '<p>This is a ammendment to <a href="/'.$data['session'].'/'.$original.'.htm">'.$original.'</a>.</p>';
} elseif($data['ammendments']) {
	echo '<p>This motion has been ammended, see ';
	for($i = 0;$i < count($data['ammendments']); $i++) {
		if($i > 0 && $i+1 < count($data['ammendments'])) echo ', ';
		if($i > 0 && $i+1 == count($data['ammendments'])) echo ' and ';
		echo '<a href="/'.$data['session'].'/'.$data['ammendments'][$i]['edm'].'.htm">'.$data['ammendments'][$i]['edm'].'</a>';
	}
	echo '.</p>';
}
echo '<p>This motion has been signed by a total of '.number_format(count($data['signatures'])).' MPs';
if($data['withdrawn'] > 0) echo ', '.$data['withdrawn'].' of these signatures have been withdrawn';
echo '.</p>';
echo "<table><tr><th>MP</th><th>Date</th><th>Constituency</th><th>Party</th><th>Type</th></tr>\n";
$flag = false;
foreach($data['signatures'] as $signature) {
	if($flag) {
		if($signature['type'] == 'Withdrawn') echo '<tr class="withdrawn highlight">';
		else echo '<tr class="highlight">';
		$flag = false;
	} else {
		$flag = true;
		if($signature['type'] == 'Withdrawn') echo '<tr class="withdrawn">';
		else echo '<tr>';
	}
	$date = strtotime($signature['date']);
	echo '<td><a href="/mps/'.$signature['mp'].'/'.$edm->mpNameURL($signature['name']).'">'.htmlspecialchars($signature['name']).'</a></td>';
	if($date != 0) echo '<td><a href="/calendar/'.strtolower(date('Y/F/j',$date)).'">'.date('d/m/Y',$date).'</a></td>';
	else echo '<td>Unknown</td>';
	echo '<td>'.htmlspecialchars($signature['constituency']).'</td>';
	echo '<td><a href="/parties/'.urlencode(strtolower($signature['party'])).'">'.htmlspecialchars($signature['party']).'</a></td><td>'.$signature['type'].'</td>';
	echo "</tr>\n";
}
echo '</table>';
echo '<br />';
if($data['current_session']) echo '<a href="/edms/'.$data['session'].'/'.$data['edm'].'.rss"><img src="/images/rss-icon.png" alt="RSS Feed" /></a> <a href="http://www.feedmyinbox.com/?feed=http://www.edms.org.uk/edms/'.$data['session'].'/'.$data['edm'].'.rss"><img src="/images/email.png" alt="Email" /></a> Subscribe to updates for this motion.<br />';
echo 'Download raw data as <a href="/edms/'.$data['session'].'/'.$data['edm'].'.csv">csv</a> or <a href="/edms/'.$data['session'].'/'.$data['edm'].'.xml">xml</a>.';
include 'templates/foot.htm.php';
?>
