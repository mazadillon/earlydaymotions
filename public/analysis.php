<?php
require '../config.php';
require '../edm.class.php';

$edm = new edm();
if(isset($_GET['keyword'])) $data = $edm->analysisKeyword($_GET['keyword'],$_GET['start'],$_GET['end']);
else $data['keyword'] = '';
include 'templates/analysis_keyword.htm.php';
?>
