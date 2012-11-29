<?php
echo '"mp","date","type","constituency","party"'."\n";
foreach($data['signatures'] as $signature) echo '"'.$signature['name'].'","'.$signature['date'].'","'.$signature['type'].'","'.$signature['constituency'].'","'.$signature['party']."\"\n";
?>
