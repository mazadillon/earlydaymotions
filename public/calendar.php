<?php
require '../config.php';
require '../edm.class.php';

$edm = new edm();
if(!isset($_GET['year']) OR !ctype_digit($_GET['year'])) $_GET['year'] = date('Y');
if(!isset($_GET['month'])) $_GET['month'] = date('F');
if(!isset($_GET['day']) OR !ctype_digit($_GET['day'])) $day = date('j');
$format = (isset($_GET['format']) && in_array($_GET['format'],$edm->config['outputs'])) ? $_GET['format'] : 'htm';

if($_GET['year'] < 1989 OR strtotime($_GET['month'].' 1st '.$_GET['year']) > date('U')) {
	$message = 'Could not load information for the requested date.<br />Unfortunately we do not have data for before 1989 or into the future.';
	include '../templates/error.htm.php';
	exit;
}

$data = $edm->calendar($_GET['year'],$_GET['month'],$_GET['day']);
header($edm->config['mime_types'][$format]);
if(isset($data['start'])) include '../templates/calendar-month.'.$format.'.php';
elseif(isset($data['date'])) include '../templates/calendar-day.'.$format.'.php';
else include '../templates/calendar-day.htm.php';
?>
