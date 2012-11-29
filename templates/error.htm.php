<?php
$title = 'Error';
include '../templates/head.htm.php';
echo '<div style="text-align:center;width:100%">';
if(isset($message)) echo $message;
else echo 'An unknown error occured';
echo '</div>';
include '../templates/foot.htm.php';
?>
