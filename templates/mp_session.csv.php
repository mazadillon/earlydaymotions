<?php
echo '"session","number","title","date","type"'."\n";
foreach($data['edms'] as $edm) echo '"'.$data['session'].'","'.$edm['edm'].'","'.$edm['title'].'","'.$edm['signed_date'].'","'.$edm['type']."\"\n";
?>
