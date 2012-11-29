<?php
header('Content-type: text/csv');
echo '"topic","session","motion","title"'."\n";
foreach($data as $motion) echo '"'.$motion['topic'].'","'.$motion['session'].'","'.$motion['edm'].'","'.$motion['title']."\"\n";
?>
