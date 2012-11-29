#!/usr/local/php5/bin/php
<?php
require '../edm.class.php';

$edm = new edm();

$del = $edm->queryAll("SELECT signatures.edm as id,edms.edm FROM `signatures` LEFT JOIN edms ON signatures.edm = edms.id WHERE edms.edm IS NULL GROUP BY signatures.edm");
foreach($del as $edm) {
	echo 'Deleting EDM ID '.$edm['id']."\n";
	mysql_query("DELETE FROM signatures WHERE edm='".$edm['id']."'");
	echo mysql_affected_rows()." signatures deleted.\n";
}
?>