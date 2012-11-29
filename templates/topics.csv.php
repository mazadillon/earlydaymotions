<?php
header('Content-type: text/csv');
echo '"topic","no. of motions"'."\n";
foreach($data['topics'] as $topic) echo '"'.$topic['topic'].'","'.$topic['motions']."\"\n";
?>
