<?php
require '../config.php';
require '../edm.class.php';

$edm = new edm();
if(!isset($_GET['q']) OR empty($_GET['q'])) {
	$data['query'] = false;
} else {
	$data['query'] = $_GET['q'];
	$data['session'] = $_GET['session'];
	$data = $edm->search($data['query'],$data['session']);
}
include '../templates/search.php';
?>
