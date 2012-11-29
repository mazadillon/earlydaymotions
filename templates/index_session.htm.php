<?php
$title = 'Session '.$data['session'];
include 'templates/head.htm.php';
echo '<h2>'.$title.'</h2>';
echo 'Motions proposed during session: '.number_format($data['edms']).'<br />';
echo 'Total signatures during session: '.number_format($data['signatures']).'<br />';
echo 'MPs who signed at least one motion: '.number_format($data['mps']).'<br /><br />';

echo '<p>View motions from '.$data['session'].' by <a href="/'.$data['session'].'/list_id.htm">number</a>, <a href="/'.$data['session'].'/list_title.htm">title</a>, ';
echo '<a href="/'.$data['session'].'/list_sigs.htm">number of signatures</a> or <a href="/'.$data['session'].'/list_latest.htm">latest signature date</a>.</p>';

echo '<h3>Best Supported Motions</h3>';
echo '<table><tr><th>#</th><th>Title</th><th>Proposer</th><th>Signatures</th></tr>';
foreach($data['supported'] as $rank => $motion) {
	$rank++;
	if(is_int($rank/2)) echo '<tr class="highlight">';
	else echo '<tr>';
	echo '<td>'.$rank.'</td><td><a href="/'.$data['session'].'/'.$motion['edm'].'.htm">'.$motion['title'].'</a><td><a href="/mps/'.$motion['proposer'].'/'.$edm->mpNameURL($motion['name']).'">'.$motion['name'].'</a></td><td>'.number_format($motion['supporters']).'</td></tr>';
}
echo '</table>';

echo '<h3>Most Prolific MPs</h3>';
echo '<table><tr><th>#</th><th>MP</th><th>Signatures</th></tr>';
foreach($data['prolific'] as $rank => $mp) {
	$rank++;
	if(is_int($rank/2)) echo '<tr class="highlight">';
	else echo '<tr>';
	echo '<td>'.$rank.'</td><td><a href="/mps/'.$mp['id'].'/'.$edm->mpNameURL($mp['name']).'">'.$mp['name'].'</a></td><td>'.number_format($mp['edms']).'</td></tr>';
}
echo '</table>';
echo '<p>Download this data as <a href="/data/'.$data['session'].'.xml">XML</a>.</p>';
include 'templates/foot.htm.php';
?>
