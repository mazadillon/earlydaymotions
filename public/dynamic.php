<?php
require '../config.php';
require '../edm.class.php';

$edm = new edm();

$session = !isset($_GET['session']) ? false : $_GET['session'];
$motion = !isset($_GET['motion']) ? false : $_GET['motion'];
$member = !isset($_GET['member']) ? false : $_GET['member'];
if(isset($_GET['sort'])) $sort = $_GET['sort'];
$format = (!isset($_GET['format']) OR !in_array($_GET['format'],$edm->config['outputs'])) ? 'htm' : $_GET['format'];
if(strlen($session) == '9') $session = substr($session,0,5).substr($session,7,2);

if($motion && $session) {
	if(!in_array($session,$edm->config['sessions'])) $session = $edm->config['current_year'];
	if($session == $edm->config['current_year']) $timeout = $edm->config['cache']['edm'];
	else $timeout = $edm->config['cache']['edm-old'];
	$path = $edm->config['path'].'json_cache/edms-'.$session.'-'.$motion.'.json';
	$data = $edm->loadCache($path,$timeout);
	if(!$data) {
		$data = $edm->build_edm($motion,$session,'json');
		$data = json_decode(file_get_contents($path),true);
	}
	header($edm->config['mime_types'][$format]);
	include '../templates/edm.'.$format.'.php';
} elseif($member) {
	if($session) {
		if($session == $edm->config['current_year']) $timeout = $edm->config['cache']['mp'];
		else $timeout = $edm->config['cache']['mp-old'];
		
		$path = $edm->config['path'].'json_cache/mps-'.$member.'-'.$session.'.json';
		$data = $edm->loadCache($path,$timeout);
		if(!$data) {
			$edm->mpSession($member,$session,'json');
			$data = json_decode(file_get_contents($path),true);
		}
		header($edm->config['mime_types'][$format]);
		include '../templates/mp_session.'.$format.'.php';
	} else {
		$timeout = $edm->config['cache']['mp'];
		$path = $edm->config['path'].'json_cache/mps-'.$member.'-index.json';
		$data = $edm->loadCache($path,$timeout);
		if(!$data) {
			$edm->build_mp($member,'json');
			$data = @json_decode(file_get_contents($path),true);
		}
		header($edm->config['mime_types'][$format]);
		include '../templates/mp.'.$format.'.php';
	}
}  elseif($session) {
	if(!in_array($session,$edm->config['sessions'])) $session = $edm->config['current_year'];
	if($session == $edm->config['current_year']) $timeout = $edm->config['cache']['edm'];
	else $timeout = $edm->config['cache']['edm-old'];
	if(isset($sort)) {
		if($sort=='sigs') $sort = 'signatures';
		$sort_allowed = array('id','latest','title','signatures');
		if(in_array($sort,$sort_allowed)) {
			$path = $edm->config['path'].'json_cache/edms-'.$session.'-'.$sort.'.json';
			$data = $edm->loadCache($path,$timeout);
			if(!$data) {
				$data['motions'] = $edm->fetchMotionList($session,$sort);
				$data['session'] = $session;
				if($data) file_put_contents($path,json_encode($data));
			}
			include '../templates/list_motions.htm.php';
		}
	} else {
		$path = $edm->config['path'].'json_cache/edms-'.$session.'.json';
		$data = $edm->loadCache($path,$timeout);
		if(!$data) {
			$data = $edm->fetchSession($session);
			if($data) {
				$data['session'] = $session;
				file_put_contents($path,json_encode($data));
			}
		}
		header($edm->config['mime_types'][$format]);
		include '../templates/index_session.'.$format.'.php';
	}
} elseif(isset($_GET['party'])) {
	if($_GET['party'] == 'all') {
		$data = $edm->loadCache($edm->config['path'].'json_cache/parties.json',$edm->config['cache']['parties']);
		if(!$data) {
			$data = $edm->fetchParties();
			$edm->outputJSON($data,'json_cache/parties.json');
		}
		include '../templates/parties.php';
	} else {
		$party = urldecode($_GET['party']);
		$party_path = strtolower($party);
		$data = $edm->loadCache($edm->config['path'].'json_cache/parties-'.$party_path.'.json',$edm->config['cache']['parties']);
		if(!$data) {
			$data = $edm->fetchParty($party);
			if($data['members']) $edm->outputJSON($data,'json_cache/parties-'.$party_path.'.json');
		}
		include '../templates/party.php';
	}
} elseif(isset($_GET['topic'])) {
	if($_GET['topic']=='all') {
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
} elseif(isset($_GET['index_main'])) {
	$timeout = $edm->config['cache']['index_main'];
	$path = $edm->config['path'].'json_cache/index_main.json';
	$data = $edm->loadCache($path,$timeout);
	if($data==false) {
		$edm->index_main();
		$data = @json_decode(file_get_contents($path),true);
	}
	header($edm->config['mime_types']['htm']);
	include '../templates/index_main.htm.php';
} else {
	include '../templates/404.htm.php';
}
?>
