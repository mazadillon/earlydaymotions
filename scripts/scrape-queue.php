#!/usr/local/php5/bin/php
<?php
require '/home/mazadillon/edm/edm.class.php';
$scraper = new edm();
$scraper->executeScrapeQueue();
?>
