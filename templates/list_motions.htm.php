<?php
$title = 'All Motions in Session '.$data['session'];
include 'templates/head.htm.php';
echo '<h3>'.$title.'</h3>';
echo '<table>';
echo "<tr><th><a href=\"list_id.htm\">Number</a></th><th><a href=\"list_title.htm\">Title</a></th><th>Proposer</th><th>Date</th><th><a href=\"list_sigs.htm\">Signatures</a></th><th><a href=\"list_latest.htm\">Latest Signature</a></th></tr>\n";
$highlight = false;
foreach($data['motions'] as $motion) {
	if($highlight) {
		echo "<tr class=\"highlight\">";
		$highlight = false;
	} else {
		echo "<tr>";
		$highlight = true;
	}
	echo '<td>'.$motion['edm'].'</td>';
	echo '<td><a href="/'.$motion['session'].'/'.$motion['edm'].'.htm">'.$motion['title'].'</a></td>';
	echo '<td><a href="/mps/'.$motion['proposer'].'">'.$motion['name'].'</a></td><td><a href="/calendar/'.strtolower(date('Y/F/j',strtotime($motion['date']))).'">'.date('d/m/Y',strtotime($motion['date'])).'</a></td>';
	echo '<td>'.number_format($motion['signatures']).'</td><td><a href="/calendar/'.strtolower(date('Y/F/j',strtotime($motion['latest']))).'">'.date('d/m/Y',strtotime($motion['latest']))."</a></td></tr>\n";
}
echo '</table>';
include 'foot.htm.php';
?>
