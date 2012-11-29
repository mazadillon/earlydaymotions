<?php
$title = 'All MPs';
include 'templates/head.htm.php';
echo '<table>';
echo "<tr><th>Name</th><th>Total Signatures</th><th>Latest Signature</th></tr>\n";
$highlight = false;
foreach($data as $mp) {
	if($highlight) {
		echo "<tr class=\"highlight\">";
		$highlight = false;
	} else {
		echo "<tr>";
		$highlight = true;
	}
	echo '<td><a href="/mps/'.$mp['id'].'/'.$this->mpNameURL($mp['name']).'">'.$mp['sname'].'</td><td>'.number_format($mp['signatures']).'</td><td>'.date('d/m/Y',strtotime($mp['latest']))."</td></tr>\n";
}
echo '</table>';
include 'foot.htm.php';
?>
