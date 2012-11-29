<?php
$title = 'Parties';
include '../templates/head.htm.php';

echo '<h1>Parties</h1>';
echo '<table><tr><th>Party</th><th>MPs</th></tr>';
foreach($data as $party) {
	echo '<tr><td><a href="/parties/'.urlencode(strtolower($party['party'])).'">'.$party['party'].'</a></td>';
	echo '<td>'.$party['count'].'</td></tr>';
}
echo '</table>';
include '../templates/foot.htm.php';
?>
