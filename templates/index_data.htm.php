<?php
$title = 'Raw Data';
include 'templates/head.htm.php';
?>
<h2>Raw Data</h2>
<p>Download raw data in XML format, the data on this website is covered by the Parliamentary Licence and may be reused under the same license, licenses can be obtained from <a href="http://www.opsi.gov.uk/click-use/"><acronym title="Office of Public Sector Information">OPSI</acronym></a>.</p>
<table>
<tr><th>Session</th><th>First Date</th><th>Last Date</th><th>Motions</th><th>Signatures</th><th>XML</th><th>ZIP</th></tr>
<?php
$edms = 0;
$sigs = 0;
foreach($data['sessions'] as $session) {
	if(isset($session['session'])) {
		if($highlight) {
			echo "<tr class=\"highlight\">";
			$highlight = false;
		} else {
			echo "<tr>";
			$highlight = true;
		}
		echo '<td>'.$session['session'].'</td><td>'.$session['first'].'</td><td>'.$session['last'].'</td><td>'.number_format($session['edms']).'</td><td>'.number_format($session['signatures']).'</td><td><a href="'.$session['session'].'.xml">'.$session['xmlsize'].'</a></td><td><a href="'.$session['session'].'.zip">'.$session['zipsize']."</a></td></tr>\n";
		$edms += $session['edms'];
		$signatures += $session['signatures'];
	}
}
?>
</table>
<h2>Members of Parliament</h2>
<p>Download a list of the MPs active in each session of parliament and the number of EDMs which they signed.</p>
<table><tr><th>Session</th><th>XML</th><th>CSV</th></tr>
<?php
$highlight = false;
foreach($data['mpsessions'] as $session) {
	if($highlight) {
		echo "<tr class=\"highlight\">";
		$highlight = false;
	} else {
		echo "<tr>";
		$highlight = true;
	}
	echo '<td>'.$session['session'].'</td>';
	echo '<td><a href="mps_'.$session['session'].'.xml">'.$session['xml'].'</a></td>';
	echo '<td><a href="mps_'.$session['session'].'.csv">'.$session['csv'].'</a></td>';
	echo "</tr>\n";
}
?>
</table>
<?php
include 'templates/foot.htm.php';
?>
