<?php
$title = 'Activity for '.date('F Y',$data['start']);
$header['keywords'] = 'Early day motion, signatures, proposed, signed, calendar, activity, seconded, '.date('F Y',$data['start']);
$header['description'] = 'See how many motions were proposed and signed each day during '.date('F Y',$data['start']);
include '../templates/head.htm.php';
echo '<table id="calendar">';
echo '<tr><th>';
if($data['prev'] > 0) echo '<a href="/calendar/'.date('Y',$data['prev']).'/'.strtolower(date('F',$data['prev'])).'">&lt;&lt; '.date('F Y',$data['prev']).'</a>';
else echo 'No More Data';
echo '</th>';
echo '<th colspan="3">'.date('F Y',$data['start']).'</th><th>';
if($data['next'] > 0) echo '<a href="/calendar/'.date('Y',$data['next']).'/'.strtolower(date('F',$data['next'])).'">'.date('F Y',$data['next']).' &gt;&gt;</a>';
else echo 'No More Data';
echo '</th></tr><tr><th>Monday</th><th>Tuesday</th><th>Wednesday</th><th>Thursday</th><th>Friday</th></tr>';
$signatures = $edms = 0;
//Loop through calendar days
if(date('w',$data['start']) > 1) {
	$start = 1;
	echo '<tr>';
	while($start < date('w',$data['start'])) {
		echo '<td>&nbsp;</td>';
		$start++;
	}
}
$highlight = true;
$date = $data['start'];
while($date < $data['end']) {
	$weekday = date('w',$date);
	if($weekday > 0 AND $weekday < 6) {
		if($weekday == 1 && $highlight) {
			echo '<tr class="highlight">';
			$highlight = false;
		} elseif($weekday == 1) {
			echo '<tr>';
			$highlight = true;
		}		
		echo '<td>';
		echo '<a href="/calendar/'.date('Y',$date).'/'.strtolower(date('F',$date)).'/'.date('j',$date).'"><h2>'.date('j',$date).'</h2></a>';
		if($data['edms'][$edms]['date'] == date('Y-m-d',$date)) {
			echo $data['edms'][$edms]['count'].' Motions<br />';
			$edms++;
		} //else echo 'No Motions Proposed<br />';
		if($data['signatures'][$signatures]['date'] == date('Y-m-d',$date)) {
			echo $data['signatures'][$signatures]['count'].' Signatures';
			$signatures++;
		} //else echo 'No Motions Signed';
		echo '</td>';
		if($weekday == 5) echo "</tr>\n";
		$start = false;
	}
	$date += 86400;
}
echo '</table>';
echo '<a href="/calendar/'.strtolower(date('Y/F',$data['start'])).'/xml"><img src="/images/xml.png" alt="xml" title="Download as XML" /></a> ';
echo '<a href="/calendar/'.strtolower(date('Y/F',$data['start'])).'/csv"><img src="/images/csv.png" alt="xml" title="Download as CSV" /></a> ';
echo 'Download raw data.';
include '../templates/foot.htm.php';
?>
