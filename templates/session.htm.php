<?php
$title = 'Session '.$data['session'];
include 'templates/head.htm.php';
echo '<h2>'.$title.'</h2>';
echo 'Motions proposed during session: '.$data['edms'].'<br />';
echo 'Total signatures during session: '.$data['signatures'].'<br />';
echo 'MPs who signed at least one motion: '.$data['mps'].'<br />';

echo '<h3>Best Supported Motions</h3>';
echo '<table><tr><th>#</th><th>Title</th><th>Proposer</th><th>Signatures</th></tr>';
foreach($data['supported'] as $rank => $edm) {
	$rank++;
	if(is_int($rank/2)) echo '<tr class="highlight">';
	else echo '<tr>';
	echo '<td>'.$rank.'</td><td><a href="/'.$data['session'].'/'.$edm['edm'].'.htm">'.$edm['title'].'</a><td><a href="/mps/'.$edm['proposer'].'.htm">'.$edm['name'].'</a></td><td>'.$edm['supporters'].'</td></tr>';
}
echo '</table>';

echo '<h3>Most Prolific MPs</h3>';
echo '<table><tr><th>#</th><th>MP</th><th>Party</th><th>Constituency</th><th>Signatures</th></tr>';
foreach($data['prolific'] as $rank => $mp) {
	$rank++;
	if(is_int($rank/2)) echo '<tr class="highlight">';
	else echo '<tr>';
	echo '<td>'.$rank.'</td><td><a href="/mps/'.$mp['id'].'.htm">'.$mp['name'].'</a></td><td>'.$mp['party'].'</td><td>'.$mp['constituency'].'</td><td>'.$mp['edms'].'</td></tr>';
}
echo '</table>';
?>