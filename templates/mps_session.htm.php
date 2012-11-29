<?php
$title = 'MPs Active in '.$data['session'];
include 'templates/head.htm.php';

echo '<h2>'.$title.'</h2>';
echo 'Party: '.$data['party'].'<br />';
echo 'Constituency: '.$data['constituency'].'<br />';
echo 'Total EDMs Signed: '.count($data['edms']).'<br />';
echo 'Signature Breakdown:<br />';
echo 'Proposed: '.$data['breakdown']['1'].'<br />';
echo 'Seconded: '.$data['breakdown']['2'].'<br />';
echo 'Signed: '.$data['breakdown']['3'].'<br />';

echo '<h3>Recent Activity</h3>';
if($data['activity'] !== false) {
	echo '<ul>';
	foreach($data['activity'] as $action) echo '<li><a href="/calendar/'.strtolower(date('Y/F/j',strtotime($action['date']))).'">'.date('d/m/Y',strtotime($action['date'])).'</a> '.$action['type'].' <a href="/'.$action['session'].'/'.$action['edm'].'.htm">EDM '.$action['edm'].'</a> &quot;'.$action['title'].'&quot;</li>';
	echo '</ul>';
}	

echo '<h3>Historic Activity</h3>';
$highlight = 0;
if($data['sessions'] !== false) {
	echo '<table><tr><th>Session</th><th>Proposed</th><th>Seconded</th><th>Signed</th><th>Total</th></tr>';
	foreach($data['sessions'] as $session) {
		if($highlight) {
			echo '<tr class="highlight">';
			$highlight = 0;
		} else {
			echo '<tr>';
			$highlight = 1;
		}
		echo '<td><a href="/mps/'.$data['id'].'/'.$session['session'].'.htm">'.$session['session'].'</a></td><td>'.$session['breakdown']['1'].'</td><td>'.$session['breakdown']['2'].'</td><td>'.$session['breakdown']['3'].'</td><td>'.array_sum($session['breakdown']).'</tr>';
	}
	echo '</table>';
} else echo 'No data for MP';

echo '<a href="/mps/'.$data['id'].'/index.rss"><img src="/images/rss-icon.png" alt="RSS Feed" />Subscribe to signatures by this MP</a>.<br />';
echo 'Download raw data as <a href="/mps/'.$data['id'].'/index.csv">csv</a> or <a href="/mps/'.$data['id'].'/index.xml">xml</a>.';
echo 'Data copied from the government early day motion portal <a href="http://edmi.parliament.uk/EDMi/EDMByMember.aspx?MID='.$data['id'].'">here</a>.';
include 'foot.htm.php';
?>
