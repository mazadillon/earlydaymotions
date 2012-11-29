#!/usr/local/php5/bin/php
<?php
require '/home/mazadillon/edm/edm.class.php';
$scraper = new edm();
//$scraper->parseEDM('1334','2010-11');
//$scraper->parseEDMList();
$scraper->parseSessions();
//foreach($scraper->config['sessions'] as $session) $scraper->parseTopicsList($session);
?>
