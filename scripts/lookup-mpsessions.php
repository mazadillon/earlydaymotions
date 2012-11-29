#!/usr/local/php5/bin/php
<?php
require '../edm.class.php';

if(!isset($_SERVER['argv'][1])) die("Please specify a mysoc ID");

$scraper = new edm();
$scraper->importMPSessions($_SERVER['argv'][1]);

$scraper->build_mp($_SERVER['argv'][1],'json');

?>
