<?php
require '../config.php';
require '../edm.class.php';

$edm = new edm();

$format = isset($_GET['format']) ? $_GET['format'] : 'htm';
if(!in_array($format,$edm->config['outputs'])) $format = 'htm';

if(!isset($_GET['topic'])) {
	$path = 'json_cache/topics-index.json';
	$data = $edm->loadCache($path,$edm->config['cache']['topic-list']);
	if(!$data) {
		$data = $edm->topicsList();
		$edm->outputJSON($data,$path);
	}
	include '../templates/topics.'.$format.'.php';	
} else {
	$_GET['topic'] = strtolower($_GET['topic']);
	$path = 'json_cache/topics-'.$_GET['topic'].'.json';
	$data = $edm->loadCache($path,$edm->config['cache']['topic']);
	if(!$data) {
		$data = $edm->topic(urldecode($_GET['topic']));
		if(!$data) {
			$text = 'Unknown topic';
			include '../templates/404.htm.php';
			exit;
		} else $edm->outputJSON($data,$path);
	}
	include '../templates/topic.'.$format.'.php';
}
?>
