<?php
$title = 'Sessions Overview';
include 'templates/head.htm.php';
echo '<h3>Motions</h3>';
echo '<p>Motions are divided into parliamentary sessions, some historic sessions are currently missing signature dates. ';
echo 'All motions have proposal dates which can be used as a guide when there are no signature dates</p>';

echo '<table><tr><th>Session</th><th>Motions</th><th>Signatures</th><th>First Motion Date</th><th>Last Motion Date</th></tr>';
foreach($data as $session) {
	if($highlight) {
		echo "<tr class=\"highlight\">";
		$highlight = false;
	} else {
		echo "<tr>";
		$highlight = true;
	}
	echo '<td><a href="/'.$session['session'].'">'.$session['session'].'</td><td>'.number_format($session['edms']).'</td><td>'.number_format($session['signatures']).'</td><td>'.$session['first'].'</td><td>'.$session['last']."</td></tr>\n";
}
echo '</table>';
include 'templates/foot.htm.php';
?>
