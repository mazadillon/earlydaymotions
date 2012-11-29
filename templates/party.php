<?php
$title = $data['members'][0]['party'];
include '../templates/head.htm.php';

echo '<a href="/parties">[Parties List]</a>';
echo '<h1>'.$data['members'][0]['party'].'</h1>';
echo '<div class="left">';
echo '<p>'.count($data['members']).' MPs have signed EDM\'s on behalf of '.$data['members'][0]['party'].'.</p>';
echo '<table>';
echo '<tr><th>Name</th><th>Constituency</th></tr>';
foreach($data['members'] as $member) {
	echo '<tr><td><a href="/mps/'.$member['mp'].'/'.$edm->mpNameURL($member['name']).'">'.$member['name'].'</a></td>';
	echo '<td>'.$member['constituency'].'</td></tr>';
}
echo '</table>';
echo '</div>';
if($data['topics']) {
	echo '<div class="right">';
	echo '<table><tr><th>Topic</th><th>Motions Proposed</th></tr>';
	foreach($data['topics'] as $topic) {
		echo '<tr><td><a href="'.urlencode(strtolower($topic['topic'])).'">'.$topic['topic'].'</a></td>';
		echo '<td>'.$topic['motions'].'</td></tr>';
	}
	echo '</table>';
	echo '</div>';
}
include '../templates/foot.htm.php';
?>
