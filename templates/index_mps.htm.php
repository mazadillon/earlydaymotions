<?php
$title = 'MPs EDM Activity';
include 'templates/head.htm.php';

echo '<h3>MPs</h3>';
echo '<p>A total of <b>'.number_format($data['mps']).'</b> MPs have signed early day motions. ';
echo 'List MPs by <a href="/mps/list_name.htm">name</a>, <a href="/mps/list_sigs.htm">number of signatures</a> or <a href="/mps/list_latest.htm">latest signature</a>.</p>';

echo '<div class="container">';
echo '<div class="left">';echo '<h3>Recently Active MPs</h3>';
if($data['active']) {
	echo '<ul>';
	foreach($data['active'] as $mp) {
		echo '<li><a href="/mps/'.$mp['id'].'/'.$this->mpNameURL($mp['name']).'">'.$mp['name']."</a></li>\n";
	}
	echo '</ul>';
} else echo 'No MPs have been active recently.';
echo '</div>';

echo '<div class="right">';
echo '<h3>MPs Proposing Most Motions</h3>';
echo '<ol>';
foreach($data['proposers'] as $mp) {
	echo '<li><a href="/mps/'.$mp['id'].'/'.$this->mpNameURL($mp['name']).'">'.$mp['name']."</a></li>\n";
}
echo '</ol>';
echo '</div>';

echo '<div class="container">';
echo '<div class="left"><h3>MPs Seconding Most Motions</h3>';
echo '<ol>';
foreach($data['seconders'] as $mp) {
	echo '<li><a href="/mps/'.$mp['id'].'/'.$this->mpNameURL($mp['name']).'">'.$mp['name']."</a></li>\n";
}
echo '</ol>';
echo '</div>';
echo '<div class="right"><h3>MPs Signing Most Motions</h3>';
echo '<ol>';
foreach($data['signers'] as $mp) {
	echo '<li><a href="/mps/'.$mp['id'].'/'.$this->mpNameURL($mp['name']).'">'.$mp['name']."</a></li>\n";
}
echo '</ol>';
echo '</div></div>';
echo '<p>Visit the <a href="/data">raw data</a> area of the site to download lists of MPs</a></p>';
include 'foot.htm.php';
?>
