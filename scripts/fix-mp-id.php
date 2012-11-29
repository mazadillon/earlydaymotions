#!/usr/local/php5/bin/php
<?php
require '../edm.class.php';

if(!isset($_SERVER['argv'][1]) OR !isset($_SERVER['argv'][2])) die("Please specify an old id followed by a new id");

$scraper = new edm();
$scraper->fixMPid($_SERVER['argv'][1],$_SERVER['argv'][2]);
$scraper->build_mp($_SEVER['argv'][2]);

?>
