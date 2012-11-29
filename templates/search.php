<?php
if($data['query']) $title = 'Search Results for &quot;'.$data['query'].'&quot;';
else $title = 'Search';
include '../templates/head.htm.php';
echo '<h2>'.$title.'</h2>';
echo '<form action="/search.php" method="get">';
echo '<fieldset><legend>Search</legend><input type="text" name="q"';
if(isset($data['query'])) echo ' value="'.htmlspecialchars($data['query']).'"';
echo ' accesskey="s" /> ';
echo '<select name="session"><option value="all">All Sessions</option>';
$result = mysql_query("SELECT session FROM edms GROUP BY session ORDER BY session desc");
if(isset($_GET['session'])) $session = $_GET['session'];
else $session = mysql_result($result,0);
while($row=mysql_fetch_assoc($result)) {
	if($row['session'] == $session) echo '<option value="'.$row['session'].'" selected="selected">'.$row['session']."</option>\n";
	else echo '<option value="'.$row['session'].'">'.$row['session']."</option>\n";
}
echo '</select>';
echo ' <input type="submit" value="Search" /></fieldset></form><hr />';
if($data['query']) {
	if(!$data['mps']) echo 'No MPs matched your query<br />';
	else {
		echo '<h3>Found these MPs</h3>';
		echo '<ul>';
		foreach($data['mps'] as $mp) {
			echo '<li><a href="/mps/'.$mp['id'].'">'.$mp['name'].'</a> ';
			if(isset($mp['start'])) {
				if(!empty($mp['party'])) echo $mp['party'];
				if(!empty($mp['constituency']))	echo ' MP for '.$mp['constituency'];
				else echo 'MP';
				echo ' from '.substr($mp['start'],0,4).' to ';
				if(substr($mp['end'],0,4) == '9999') echo 'now.';
				else echo substr($mp['end'],0,4).'.';
			}
			echo '</li>';
		}
		echo '</ul>';
	}
	if(!$data['edms']) echo 'No motions matched your query<br />';
	else {
		echo '<h3>Found these Early Day Motions</h3>';
		echo '<ul>';
		foreach($data['edms'] as $edm) {
			echo '<li><a href="/'.$edm['session'].'/'.$edm['edm'].'.htm">EDM'.$edm['edm'].' '.$edm['title'].'</a> proposed during the session '.$edm['session'].'</li>';
		}
		echo '</ul>';
	}
}
include '../templates/foot.htm.php';
?>
