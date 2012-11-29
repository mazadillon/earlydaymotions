<?php
$title = $data['mp']['name'].' activity during '.$data['session'];
include 'templates/head.htm.php';
echo '<h2><a href="/mps/'.$data['mp']['id'].'/'.$edm->mpNameURL($data['mp']['name']).'">'.$data['mp']['name'].'</a> activity during '.$data['session'].'</h2>';
echo 'EDMs Signed: '.number_format(count($data['edms'])).'<br />';
echo 'Signature Breakdown:';
echo '<ul>';
echo '<li>Proposed: '.number_format($data['breakdown']['1']).'</li>';
echo '<li>Seconded: '.number_format($data['breakdown']['2']).'</li>';
echo '<li>Signed: '.number_format($data['breakdown']['3']).'</li>';
echo '</ul>';
$highlight = 0;
echo '<table><tr><th>Number</th><th>Title</th><th>Signature Date</th><th>Signature Type</th><th>Proposer</th><th>Proposal Date</th></tr>';
foreach($data['edms'] as $current) {
	if($highlight) {
		if($current['type'] == 'Withdrawn') echo '<tr class="withdrawn highlight">';
		else echo '<tr class="highlight">';
		$highlight = 0;
	} else {
		if($current['type'] == 'Withdrawn') echo '<tr class="withdrawn">';
		else echo '<tr>';
		$highlight = 1;
	}
	echo '<td>'.$current['edm'].'</td><td><a href="/'.$current['session'].'/'.$current['edm'].'.htm">'.$current['title'].'</a></td><td>';
	if($current['signed_date'] != '') echo '<a href="/calendar/'.strtolower(date('Y/F/j',strtotime($current['signed_date']))).'">'.date('d/m/Y',strtotime($current['signed_date'])).'</a>';
	else echo 'Unknown';
	echo '</td><td>'.$current['type'].'</td><td><a href="/mps/'.$current['proposer_id'].'/'.$edm->mpNameURL($current['proposer_name']).'">'.$current['proposer_name'].'</a></td><td><a href="/calendar/'.strtolower(date('Y/F/j',strtotime($current['proposed_date']))).'">'.date('d/m/Y',strtotime($current['proposed_date'])).'</a></td></tr>';
}
echo '</table>';
echo '<a href="/mps/'.$data['mp']['id'].'/index.rss"><img src="/images/rss-icon.png" alt="RSS Feed" /> Subscribe to signatures by this MP</a>.<br />';
echo 'Download raw data as <a href="/mps/'.$data['mp']['id'].'/'.$data['session'].'.csv">csv</a> or <a href="/mps/'.$data['mp']['id'].'/'.$data['session'].'.xml">xml</a>.';
//echo 'Data copied from the government early day motion portal <a href="http://edmi.parliament.uk/EDMi/EDMByMember.aspx?MID='.$data['mp']['govid'].'">here</a>.';
include 'templates/foot.htm.php';
?>
