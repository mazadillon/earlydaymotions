#!/usr/local/php5/bin/php
<?php
require '../edm.class.php';

$edm = new edm();
$type = $edm->missingFile($_SERVER['REQUEST_URI']);
if($type=='mps') {
	$title='MP Not Found';
	$text='Sorry but we could not find any information for the MP you are looking for. The Early Day Motion archive only dates
	back to 1989 so if the MP left parliament before this time they will not be featured on the site.';
} elseif($type=='edms') {
	$title='EDM Not Found';
	$text='Sorry, the Early Day Motion you are looking for could not be found, try using the search box above to find the Early Day
	Motion in question.';
} else {
	$title='Page Not Found';
	$text='Sorry, the page you are looking for could not be found.';
}
include '../templates/404.htm.php';
?>
