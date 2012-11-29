#!/usr/local/php5/bin/php
<?php
require '../edm.class.php';

$edm = new edm();

$del = $edm->queryAll("SELECT * FROM signatures WHERE mp=0 GROUP BY edm");
foreach($del as $motion) {
	$edm->parseEDM($motion['edm']);
}
?>