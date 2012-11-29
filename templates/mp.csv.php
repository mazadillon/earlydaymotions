<?php
echo '"session","proposed","seconded","signed"'."\n";
foreach($data['sessions'] as $session) echo '"'.$session['session'].'","'.$session['breakdown']['1'].'","'.$session['breakdown']['2'].'","'.$session['breakdown']['3']."\"\n";
?>
