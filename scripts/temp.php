#!/usr/local/php5/bin/php
<?php
require '../edm.class.php';

$edm = new edm();
$edm->fetchAllMPs();
/*
$mps = $edm->queryAll("SELECT id,name,sname FROM mps order by id ASC");
$count = 0;
foreach($mps as $mp) {
	$sname = $edm->revertName(trim($mp['name']));
	if($sname != $mp['sname']) {
		echo $sname.' (new) vs '.$mp['sname']." (db)\n";
		mysql_query("UPDATE mps SET sname='".$sname."' WHERE id='".$mp['id']."'");
		$count++;
	}
}
echo $count.'/'.count($mps);

foreach($edm->config['sessions'] as $session) {
	$path = '/home/mazadillon/edm/public/edms/'.$session;
	$old_path = '/home/mazadillon/edm/public/edms/'.$edm->config['edm_sessions'][$session];
	if(!is_dir($path)) mkdir($path);
	echo 'cp '.$old_path.'/* '.$path.'/';
	echo "\n";
	exec('cp '.$old_path.'/* '.$path.'/');
	//echo "Building ".$session."\n";
	//$edm->dump_data($session);
}
*/
?>
