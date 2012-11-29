<?php
class edm {
	function edm() {
		include 'config.php';
		require 'edmi_xml.class.php';
		$this->edmixml = new edmiXML;
		$this->config = $config;
		$this->mysqlConnect();
		$this->scrapeStatus = array('edms' => 0,'mps' => 0, 'signatures' => 0);
		$this->config['current_year'] = $this->fetchCurrentSession();
		$this->loadSessions();
	}
	
	function mysqlConnect() {
		$this->db = mysql_connect($this->config['mysql']['host'],$this->config['mysql']['user'],$this->config['mysql']['pass']);
		if(mysql_select_db($this->config['mysql']['db'])) {
			mysql_query("SET NAMES 'utf8'");
			mysql_query("SET CHARACTER SET 'utf8'");
			mysql_query("SET collation_connection = 'utf8_unicode_ci'");
			return true;
		} else return false;
	}
	
	function queryRow($query) {
		$result = mysql_query($query);
		if(!$result) die(mysql_error());
		if(mysql_num_rows($result) > 0) return mysql_fetch_assoc($result);
		else return false;
	}
	
	function queryOne($query) {
		$result = mysql_query($query);
		if(!$result) die(mysql_error());
		if(mysql_num_rows($result) > 0)	return mysql_result($result,0);
		else return false;
	}
	
	function queryAll($query) {
		$result = mysql_query($query);
		if(!$result) die(mysql_error());
		if(mysql_num_rows($result) < 1) return false;
		else {
			while($row = mysql_fetch_assoc($result)) $return[] = $row;
			return $return;
		}
	}
	
	function fetchMPByName($name) {
		$result = mysql_query("SELECT id FROM mps WHERE name='".mysql_real_escape_string($name)."'");
		if(mysql_num_rows($result) == 1) return mysql_result($result,0);
		else return false;
	}
	
	function fetchMPByMysocid($id) {
		$result = mysql_query("SELECT * FROM mps WHERE mysocid='".mysql_real_escape_string($id)."'");
		if(mysql_num_rows($result) == 1) return mysql_fetch_assoc($result);
		else return false;
	}
	
	function loadSessions() {
		$data = $this->queryAll("SELECT * FROM sessions ORDER BY id DESC");
		foreach($data as $session) {
			$this->config['sessions'][$session['id']] = $session['name'];
		}
	}

	function formatFileSize($path) {
		$bytes = filesize($path);
		$units = array('B', 'KB', 'MB', 'GB', 'TB'); 
	   	$bytes = max($bytes, 0); 
	    	$pow = floor(($bytes ? log($bytes) : 0) / log(1024)); 
	    	$pow = min($pow, count($units) - 1); 
	   
	    	$bytes /= pow(1024, $pow); 
	   
	    	return round($bytes, 0) . ' ' . $units[$pow];
	}

	function search($query,$session=false) {
		if(!$session) $session=mysql_result(mysql_query("SELECT max(session) FROM edms limit 1"),0);
		$data['mps'] = false;
		$data['edms'] = false;
		$data['query'] = htmlspecialchars(strip_tags($_GET['q']));
		//Lookup MP by postcode
		$postcode_mp = $this->postcode_lookup_mp($query);
		if($postcode_mp) $data['mps'][$post_code_mp] = $postcode_mp;
		//Lookup MP by name
		$result = mysql_query("SELECT * FROM mps WHERE name LIKE '%".mysql_real_escape_string($_GET['q'])."%'");
		if(mysql_num_rows($result) > 0) {
			while($row = mysql_fetch_assoc($result)) {
				$data['mps'][$row['id']] = $row;
			}
		}
		$result = mysql_query("SELECT min(mpsessions.start) as start,max(mpsessions.end) as end,mpsessions.party,mpsessions.constituency,mpsessions.name,mps.id FROM mpsessions,mps WHERE mpsessions.mp=mps.id and mpsessions.constituency LIKE '%".mysql_real_escape_string($_GET['q'])."%' group by mpsessions.mp");
		if(mysql_num_rows($result) > 0) {
			while($row = mysql_fetch_assoc($result)) {
				$data['mps'][$row['id']] = $row;
			}
		}
		if(eregi('(EDM)?[ ]?([0-9]{1,4}[A-Z]?[0-9]?)',$data['query'],$regs)) {
			$_GET['q'] = $regs[2];
			if($session=='all') $result = mysql_query("SELECT * FROM edms WHERE edm = '".mysql_real_escape_string($_GET['q'])."' ORDER BY session DESC");
			else $result = mysql_query("SELECT * FROM edms WHERE session='".mysql_real_escape_string($session)."' AND edm = '".mysql_real_escape_string($_GET['q'])."'");
			if(mysql_num_rows($result) > 0) {
				while($row = mysql_fetch_assoc($result)) {
					$data['edms'][$row['id']] = $row;
				}
			}
		}
		if($session=='all') $result = mysql_query("SELECT * FROM edms WHERE match (title,text) AGAINST ('".mysql_real_escape_string($_GET['q'])."')");
		else $result = mysql_query("SELECT * FROM edms WHERE session='".mysql_real_escape_string($session)."' AND match (title,text) AGAINST ('".mysql_real_escape_string($_GET['q'])."')");
		if(mysql_num_rows($result) > 0) {
			while($row = mysql_fetch_assoc($result)) {
				$data['edms'][$row['id']] = $row;
			}
		}
		if($session=='all') $result = mysql_query("SELECT * FROM edms WHERE edm LIKE '%".mysql_real_escape_string($_GET['q'])."%'");
		else $result = mysql_query("SELECT * FROM edms WHERE session='".mysql_real_escape_string($session)."' AND edm LIKE '%".mysql_real_escape_string($_GET['q'])."%'");
		if(mysql_num_rows($result) > 0) {
			while($row = mysql_fetch_assoc($result)) {
				$data['edms'][$row['id']] = $row;
			}
		}
		//@krsort($data['edms']);
		return $data;
	}	

	// Still needs tweaking to handle cases where it's e.g. BBC's or BP's or UK's
	
	function titleCaseWord($word,$bigwords) {
		if(in_array($word,$bigwords) OR in_array($word,$this->config['uppercase'])) {
			return $word;
		} else {
			$word = ucwords(mb_strtolower($word,'latin1'));
			if(in_array($word[0],array('(','`','"',"'"))) $word[1] = strtoupper($word[1]);
			elseif(($pos = strpos($word,'-'))!==false) $word[$pos+1] = strtoupper($word[$pos+1]);
			return $word;
		}
	}
	
	function convertTitleCase($text,$motion='') {
		$text = mb_convert_encoding($text,'Latin1','UTF-8');
		$words = explode(' ',$text);
		$motion_words = explode(' ',$motion);
		$output = '';
		foreach($words as $word) {
			if(substr($word,-2)=="'S") $output .= $this->titleCaseWord(substr($word,0,-2),$motion_words)."'s ";
			elseif(substr($word,1)=='(' AND substr($word,-1) == ')') $output .= '('.$this->titleCaseWord(substr($word,0,-2),$motion_words).") ";
			else $output .= $this->titleCaseWord($word,$motion_words).' ';			
		}
		trim($output);
		return $output;
	}
		
	
	function fixDate($date) {
		$date = explode('.',$date);
		return $date[2].'-'.$date[1].'-'.$date[0];
	}
	
	function fetchAmmendments($edm,$session) {
		$result = mysql_query("SELECT * FROM edms WHERE edm LIKE '".$edm."A%' AND session='".$session."'");
		if(mysql_num_rows($result) > 0) {
			while($row = mysql_fetch_assoc($result)) {
				$return[] = $this->fetchEDM($row['id']);
			}
			return $return;
		} else return false;
	}
	
	function fetchCurrentSession() {
		if(!isset($this->currentSession)) $this->currentSession = $this->queryOne("SELECT name FROM sessions ORDER BY id DESC limit 1");
		return $this->currentSession;
	}
	
	function fetchRecentActivity($limit,$mp=false) {
		if($limit == null) $limit = 10;
		if($mp == false) $result = mysql_query("SELECT signatures.*,edms.title,edms.session,edms.edm,edms.text FROM signatures,edms WHERE signatures.edm=edms.id and edms.edm NOT LIKE '%A%' ORDER BY date DESC,signatures.edm DESC,signatures.type DESC LIMIT ".$limit);
		else $result = mysql_query("SELECT signatures.*,edms.title,edms.session,edms.edm,edms.text FROM signatures,edms WHERE mp = '".$mp."' and signatures.edm=edms.id ORDER BY date DESC,signatures.edm desc LIMIT ".$limit);
		if(mysql_num_rows($result) > 0) {
			while($row = mysql_fetch_assoc($result)) {
				$row['type'] = $this->config['sig_type'][$row['type']];
				$return[] = $row;
			}
			return $return;
		} else return false;
	}
	
	function mpNameURL($name) {
		$name = strtolower($name);
		$name = str_replace(' ','-',$name);
		$name = preg_replace('/[^a-z-]*/','', $name);
		return $name;
	}

	function fetchMPSummary() {
		$result = mysql_query("SELECT count(*) FROM mps");
		return mysql_result($result,0);
	}
	
	function fetchRecentlyActiveMPs($limit) {
		$result = mysql_query("SELECT * FROM signatures JOIN mps ON signatures.mp=mps.id group by signatures.mp order by date desc,edm desc limit ".$limit) or die(mysql_error());
		if(mysql_num_rows($result) > 0) {
			while($row = mysql_fetch_assoc($result)) {
				$row['date'] = $this->displayDate($row['date']);
				$row['type'] = $this->config['sig_type'][$row['type']];
				$return[] = $row;
			}
			return $return;
		} else return false;		
	}
	
	function fetchBoomingMotions($limit,$cutoff=14) {
		$date = strtotime('-'.$cutoff.' days');
		$result = mysql_query("SELECT * , count( * ) AS count FROM `signatures` JOIN edms ON signatures.edm=edms.id WHERE edms.session='".$this->config['current_year']."' AND signatures.date >= '".date('Y-m-d',$date)."' GROUP BY signatures.edm ORDER BY count DESC LIMIT ".$limit) or die(mysql_error());
		if(mysql_num_rows($result) < 1) return false;
		else {
			while($row = mysql_fetch_assoc($result)) {
				$return[] = $row;
			}
			return $return;
		}
	}

	function fetchParty($party) {
		$data['party'] = $party;
		$data['members'] = $this->queryAll("SELECT mpsessions.* FROM mpsessions JOIN mps ON mpsessions.mp=mps.id WHERE mpsessions.party='".mysql_real_escape_string($party)."' group by mpsessions.mp ORDER BY mps.sname ASC") or die(mysql_error());
		$data['topics'] = $this->queryAll("select topics.topic,count(*) as motions from motiontopics JOIN edms ON motiontopics.edm=edms.id join mpsessions on edms.proposer=mpsessions.mp join topics on motiontopics.topic=topics.id where mpsessions.party='".mysql_real_escape_string($party)."' and edms.date > mpsessions.start and edms.date < mpsessions.end group by topics.id order by motions desc LIMIT 10");
		return $data;
	}

	function fetchParties() {
		return $this->queryAll("SELECT *,count(distinct(mp)) as count FROM mpsessions WHERE party != '' GROUP BY party ORDER by PARTY ASC");
	}
	
	function fetchMPList($sort) {
		if($sort=='signatures') $dir = 'desc';
		elseif($sort=='latest') $dir='desc';
		else {
			$sort='sname';
			$dir = 'asc';
		}
		$result = mysql_query("select mps.*,count(*) as signatures,max(signatures.date) as latest from mps,signatures where signatures.mp=mps.id group by signatures.mp order by ".$sort." ".$dir);
		while($row = mysql_fetch_assoc($result)) {
			$row['date'] = $this->displayDate($row['date']);
			$return[] = $row;
		}
		return $return;
	}

	function fetchMotionList($session,$sort) {
		if($sort=='signatures') $dir = 'desc';
		elseif($sort=='latest') $dir='desc';
		elseif($sort=='title') $dir='asc';
		else {
			$sort='id';
			$dir = 'asc';
		}
		$result = mysql_query("select edms.session,edms.edm,edms.id as id,edms.title,edms.proposer,edms.date,count(*) as signatures,max(signatures.date) as latest,mps.name from edms,signatures,mps where edms.id=signatures.edm and edms.proposer=mps.id and session='".$session."' group by signatures.edm order by ".$sort." ".$dir) or die(mysql_error());
		while($row = mysql_fetch_assoc($result)) {
			$return[] = $row;
		}
		return $return;
	}
	
	function fetchMPDetail($id) {
		$result = mysql_query("SELECT * FROM mpsessions WHERE mp='".mysql_real_escape_string($id)."' ORDER BY end DESC");
		$i = 0;
		if(mysql_num_rows($result) == 0) return false;
		else while($row = mysql_fetch_assoc($result)) {
			$temp[] = $row;
		}
		$return[] = $temp[0];
		for($i = 0;$i < count($temp);$i++) {
			$j = $i - 1;
			$temp[$i]['end_year'] = substr($temp[$i]['end'],0,4);
			$temp[$i]['start_year'] = substr($temp[$i]['start'],0,4);
			if($i != 0) {
				if($temp[$i]['party'] !== $temp[$j]['party'] OR $temp[$i]['constituency'] !== $temp[$j]['constituency'] OR $temp[$i]['end_year'] !== $temp[$j]['start_year']) $return[] = $temp[$i];
				else $return[count($return)-1]['start'] = $temp[$i]['start'];
			}
		}
		return $return;
	}
	
	function fetchSignatureTimeline($id,$session=false) {
		if($session==false) $result = mysql_query("SELECT date,count(*) as signatures FROM signatures WHERE edm='".$id."' and date is not null GROUP BY date ORDER BY date ASC");
		else $result = mysql_query("SELECT signatures.date,count(*) as signatures FROM signatures,edms WHERE signatures.edm=edms.id and edms.session='".$session."' and mp='".$id."' and signatures.date is not null GROUP BY date ORDER BY signatures.date ASC");
		$count = 0;
		$signatures = 0;
		while($row=mysql_fetch_assoc($result)) {
			$date = explode("-",$row['date']);
			$return[$count]['date']['year'] = $date[0];
			$return[$count]['date']['month'] = $date[1] - 1;
			$return[$count]['date']['day'] = $date[2];
			
			if($type=='edm') {
				$signatures += $row['signatures'];
				$return[$count]['signatures'] = $signatures;
			} else $return[$count]['signatures'] = $row['signatures'];
			$count++;
		}
		return $return;
	}
	
	function postcode_lookup_mp($postcode) {
		if(preg_match('/^[a-zA-Z]{1,2}[0-9][0-9A-Za-z]? [0-9][a-zA-Z]{2}$/',$postcode)) {
			$data = @file_get_contents('http://www.theyworkforyou.com/api/getMP?key='.$this->config['twfy_api_key'].'&output=php&postcode='.urlencode($postcode));
			$data = unserialize($data);
			$mp = $this->fetchMPByMysocid($data['person_id']);
			if($mp) return $mp;
			else return false;
		} else return false;
	}
	
	function fetchMPURLS($id) {
		$data = @file_get_contents('http://www.theyworkforyou.com/api/getMPInfo?key='.$this->config['twfy_api_key'].'&output=php&id='.$id.'&fields=wikipedia_url,bbc_profile_url,mp_website,guardian_mp_summary,expenses_url,twitter_username');
		$data = unserialize($data);
		$query = 'UPDATE mps SET ';
		$comma = false;
		foreach($this->config['media_links'] as $twfy => $db) {
			//if(!isset($data[$twfy])) $data[$twfy] = '';
			if($comma) $query .= ',';
			else $comma = true;
			$query .= $db."='".$data[$twfy]."'";
		}
		$query .= ' WHERE id='.$id;
		mysql_query($query);
	}
	
	function fetchMPsMedia() {
		$data = $this->queryAll("SELECT * FROM mps");
		foreach($data as $mp) {
			echo 'Fetching data for '.$mp['name'].' ('.$mp['id'].")\n";
			$this->fetchMPURLS($mp['id']);
			sleep(3);
		}
	}

	function displayDate($date) {
		$date = explode('-',$date);
		return $date[2].'/'.$date[1].'/'.$date[0];
	}
	
	function fetchMP($id) {
		$mp = $this->queryRow("SELECT * FROM mps WHERE id='".mysql_real_escape_string($id)."'");
		if($mp) {
			$mp['detail'] = $this->fetchMPdetail($id);
			return $mp;
		} else return false;
	}
	
	function convertSession($session) {
		if(array_key_exists($session, $this->config['sessions'])) return $this->config['sessions'][$session];
		else return false;
	}
	
	function revertSession($session) {
		return array_search($session,$this->config['sessions']);
	}
		
	function invertName($name) {
		$name = explode(',',trim($name));
		return trim($name[1]).' '.trim($name[0]);
	}
	
	function revertName($name) {
		$name = explode(' ',trim($name));
		$return = $name[count($name) -1].',';
		for($i = 0;$i < count($name)-1;$i++) {
			$return.= ' '.$name[$i];
		}
		return $return;
	}

	function convertGovToMysoc($id) {
		$result = mysql_query("SELECT id FROM mps WHERE govid='".mysql_real_escape_string($id)."'");
		if(mysql_num_rows($result) == 1) return mysql_result($result,0);
		else return $id + 9000000; 
	}

	function convertMysocToGov($id) {
		$result = mysql_query("SELECT govid FROM mps WHERE id='".mysql_real_escape_string($id)."'");
		if(mysql_num_rows($result) == 1) return mysql_result($result,0);
		else {
			if($id > 9000000) return $id - 9000000; 
			else return false; 
		}
	}
	
	function fetchEDM($id) {
		$return = $this->queryRow("SELECT edms.*,mps.name,count(signatures.mp) as signatures FROM edms JOIN signatures ON edms.id=signatures.edm JOIN mps ON edms.proposer=mps.id WHERE edms.id='".mysql_real_escape_string($id)."' group by signatures.edm");
		if($return['id'] != $id) return false;
		else {
			$return['topics'] = $this->fetchEdmTopics($return['id']);
			return $return;
		}
	}
	
	function fetchEdmTopics($id) {
		return $this->queryAll("SELECT topics.topic FROM motiontopics JOIN topics ON motiontopics.topic=topics.id WHERE motiontopics.edm='".mysql_real_escape_string($id)."'");
	}
	
	function fetchMPActiveSessions($mp) {
		$result = mysql_query("SELECT edms.session FROM signatures JOIN edms ON signatures.edm=edms.id WHERE signatures.mp = '".$mp."' GROUP by edms.session ORDER BY session DESC");
		if(mysql_num_rows($result) > 0) {
			while($row = mysql_fetch_assoc($result)) $return[] = $row['session'];
			return $return;
		} else return false;
	}
	
	function fetchMPTopics($mp) {
		return $this->queryAll("SELECT topics . * , count( * ) AS motions FROM motiontopics JOIN edms ON edms.id = motiontopics.edm JOIN topics ON motiontopics.topic = topics.id WHERE edms.proposer = '".mysql_real_escape_string($mp)."' GROUP BY motiontopics.topic HAVING motions > 2 ORDER BY motions DESC LIMIT 10");
	}
	
	function fetchMPSigningBreakdown($id,$session='all') {
		if($session=='all') $result = mysql_query("SELECT type,count(*) as count from signatures WHERE mp='".$id."' group by type");
		else $result = mysql_query("SELECT type,count(*) as count from signatures JOIN edms on signatures.edm = edms.id WHERE mp='".$id."' and session='".$session."' group by type");
		if(mysql_num_rows($result) > 0) {
			while($row = mysql_fetch_assoc($result)) $return[$row['type']] = $row['count'];
			return $return;
		} else return false;
	}
	
	function fetchPopularMotions($limit,$session='all') {
		if(!is_numeric($limit)) $limit = 10;
		if($session == 'all') $result = mysql_query("SELECT edms.*,count(*) as supporters,mps.name FROM signatures,edms,mps WHERE signatures.edm=edms.id and edms.proposer=mps.id group by signatures.edm order by supporters desc limit ".$limit) or die(mysql_error());
		else $result = mysql_query("SELECT edms.*,count(*) as supporters,mps.name FROM signatures,edms,mps WHERE signatures.edm=edms.id and edms.proposer=mps.id and edms.session='".$session."' group by signatures.edm order by supporters desc limit ".$limit);
		if(mysql_num_rows($result) > 0) {
			while($row = mysql_fetch_assoc($result)) {
				$return[] = $row;
			}
			return $return;
		} else return false;
	}
	
	function fetchNewestMotions($limit,$session='all') {
		if($limit=='feed') {
			$result = mysql_query("SELECT * FROM edms WHERE `edm` REGEXP '^[0-9]{1,5}$' AND date >= '".date("Y-m-d",strtotime("yesterday"))."' and date is NOT null order by date desc,id desc") or die(mysql_error());
			if(mysql_num_rows($result) == 0) return $this->fetchNewestMotions(10);
		} else {
			if(!is_numeric($limit)) $limit = 10;
			if($session == 'all') $result = mysql_query("SELECT * FROM edms WHERE `edm` REGEXP '^[0-9]{1,5}$' order by date desc,id desc limit ".$limit);
			else $result = mysql_query("SELECT * FROM edms WHERE `edm` REGEXP '^[0-9]{1,5}$' AND session='".$session."' order by date desc,id desc limit ".$limit);
		}
		if(mysql_num_rows($result) > 0) {
			while($row = mysql_fetch_assoc($result)) {
				$return[] = $row;
			}
			return $return;
		} else return false;
	}
	
	function fetchPartyActivity($session='all') {
		if(!is_numeric($limit)) $limit = 10;
		if($session == 'all') $result = mysql_query("SELECT distinct(signatures.mp),count(*) as count,party FROM signatures JOIN edms on signatures.edm=edms.id JOIN mps on signatures.mp=mps.id JOIN mpsessions ON mps.id=mpsessions.mp WHERE mpsessions.start<=signatures.date and mpsessions.end >= signatures.date group by party order by count desc") or die(mysql_error());
		else $result = mysql_query("SELECT distinct(signatures.mp),count(*) as count,party FROM signatures JOIN edms on signatures.edm=edms.id JOIN mps on signatures.mp=mps.id JOIN mpsessions ON mps.id=mpsessions.mp WHERE mpsessions.start<=signatures.date and mpsessions.end >= signatures.date and edms.session='".$session."' group by party order by count desc") or die(mysql_error());
		if(mysql_num_rows($result) > 0) {
			while($row = mysql_fetch_assoc($result)) {
				$return[] = $row;
			}
			return $return;
		} else return false;
	}
	
	function fetchProlificMPs($limit,$session='all',$type='all') {
		if(!is_numeric($limit)) $limit = 10;
		$query = "SELECT mps.*,count(*) as edms FROM signatures JOIN edms ON signatures.edm=edms.id JOIN mps ON signatures.mp=mps.id WHERE signatures.date IS NOT NULL ";
		if($session != 'all') $query .= "and edms.session='".$session."' ";
		if($type != 'all') $query .= "and signatures.type='".$type."' ";
		$query .= "group by mp order by edms desc limit ".$limit;
		$result = mysql_query($query) or die(mysql_error());
		if(mysql_num_rows($result) > 0) {
			while($row = mysql_fetch_assoc($result)) {
				$return[] = $row;
			}
			return $return;
		} else return false;
	}

	function fetchSignatures($edm,$displaydate=false,$recent=false) {
		if($recent) $result = mysql_query("SELECT * FROM signatures LEFT JOIN mpsessions ON signatures.mp=mpsessions.mp WHERE signatures.edm='".$edm."' ORDER BY date DESC LIMIT 10");
		else $result = mysql_query("SELECT *,date IS NULL AS isnull FROM `signatures` WHERE signatures.edm='".$edm."' ORDER BY isnull ASC,date ASC,type ASC");
		if(mysql_num_rows($result) == 0) return false;
		else {
			while($row = mysql_fetch_assoc($result)) {
				if($row['isnull'] == 0) {
					$mpsession = $this->queryRow("SELECT * FROM mpsessions WHERE mpsessions.start <= '".$row['date']."' AND mpsessions.end >= '".$row['date']."' and mp = '".$row['mp']."'");
					$row['name'] = $mpsession['name'];
					$row['party'] = $mpsession['party'];
					$row['constituency'] = $mpsession['constituency'];
				}
				if(!isset($row['name']) OR $row['isnull'] == 1) {
					$mp = $this->queryRow("SELECT * FROM mps WHERE id='".$row['mp']."'");
					$row['party'] = $mp['party'];
					$row['constituency'] = $mp['constituency'];
					$row['name'] = $mp['name'];
				}
				$row['type'] = $this->config['sig_type'][$row['type']];
				$return[] = $row;
			}
			return $return;
		}
	}
	
	function fetchEDMS($mp,$session='all') {
		if($session=='all') $result = mysql_query("SELECT edms.title,edms.id,edms.proposer as proposer_id,edms.edm,mps.name as proposer_name,signatures.date as signed_date,edms.date as proposed_date,edms.session,signatures.type as type,edms.status FROM signatures JOIN edms signatures.edm=edms.id JOIN mps ON mps.id=edms.proposer WHERE signatures.mp='".$mp."' ORDER by edms.session DESC,signatures.date DESC,edms.date DESC,edms.id DESC");
		else $result = mysql_query("SELECT edms.title,edms.id,edms.proposer as proposer_id,edms.edm,mps.name as proposer_name,signatures.date as signed_date,edms.date as proposed_date,edms.session,signatures.type as type,edms.status FROM signatures JOIN edms ON signatures.edm=edms.id JOIN mps on edms.proposer=mps.id WHERE signatures.mp='".$mp."' and edms.session='".$session."' ORDER by edms.session DESC,signatures.date DESC,edms.date DESC,edms.id DESC") or die(mysql_error());
		if(mysql_num_rows($result) == 0) return false;
		else {
			while($row = mysql_fetch_assoc($result)) {
				$row['type'] = $this->config['sig_type'][$row['type']];
				$return[] = $row;
			}
			return $return;
		}
	}
	
	function fetchSignature($edm,$mp) {
		//echo "Searching for $mp in $edm \n";
		$result = mysql_query("SELECT * FROM signatures WHERE mp='".mysql_real_escape_string($mp)."' AND edm='".mysql_real_escape_string($edm)."'");
		if(mysql_num_rows($result) == 1) return mysql_fetch_assoc($result);
		else return false;
	}
	
	function createMP($name,$sname,$id) {
		if(!empty($name) && !empty($id)) {
			$result = mysql_query("INSERT IGNORE into mps (name,sname,id) values('".mysql_real_escape_string($name)."','".mysql_real_escape_string($sname)."','".mysql_real_escape_string($id)."')");
			$this->counters['new_mps']++;
			//echo 'MP inserted';
			return mysql_insert_id();
		}
	}
	
	function fetchSession($session) {
		$result = mysql_query("SELECT count(*) as signatures FROM signatures JOIN edms ON signatures.edm=edms.id WHERE edms.session='".$session."'");
		$data['signatures'] = mysql_result($result,0);
		$result = mysql_query("SELECT count(distinct(signatures.edm)) as edms FROM signatures JOIN edms ON signatures.edm=edms.id WHERE edms.session='".$session."'");
		$data['edms'] = mysql_result($result,0);
		$result = mysql_query("SELECT count(distinct(signatures.mp)) as mps FROM signatures JOIN edms ON signatures.edm=edms.id WHERE edms.session='".$session."'");
		$data['mps'] = mysql_result($result,0);
		$data['prolific'] = $this->fetchProlificMPs(10,$session);
		$data['supported'] = $this->fetchPopularMotions(10,$session);
		return $data;
	}
	
	function missingFile($path) {
		$path = explode('/',$path);
		if($path[1] == 'mps') {
			if($this->fetchMP($path[2])) {
				$this->queueBuild('mp',$path[2]);
				if(isset($path[3])) {
					if(in_array($path[3],$this->config['sessions'])) {
						$session = substr($path[3],0,4);
						$this->queueBuild('mpsession',$path[2].$session);
					}
				}
			}
		}
		if($path[1] == 'edms') {
			$edm = explode('.',$path[3]);
			$result = mysql_query("SELECT id FROM edms WHERE session='".mysql_real_escape_string($path[2])."' AND edm='".mysql_real_escape_string($edm[0])."'");
			if(mysql_num_rows($result) == 1) $this->queueBuild('edm',mysql_result($result,0));
		}
		return $path[1];
	}

	function fetchSessionList() {
		$result = mysql_query("SELECT *,count(*) as signatures,min(edms.date) as first,max(edms.date) as last FROM edms JOIN signatures on edms.id=signatures.edm group by edms.session order by edms.session desc");
		$result2 = mysql_query("select session,count(*) as edms from edms group by session order by session desc");
		$count = 0;
		while($row = mysql_fetch_assoc($result)) {
			$row['first'] = $this->displayDate($row['first']);
			$row['last'] = $this->displayDate($row['last']);
			$return[$count] = $row;
			$return[$count]['edms'] = mysql_result($result2,$count,'edms');
			$count++;
		}
		return $return;
	}
			
	function createEDM($id,$number,$session,$title,$proposer,$name,$text,$date) {
		$mp = $this->fetchMP($proposer);
		if(!$mp) $mp = $this->createMP($name,$this->revertName($name),$proposer);
		else $mp = $mp['id'];
		$title = trim($this->convertTitleCase($title,$text));
		mysql_query("INSERT IGNORE INTO edms (id,session,edm,title,proposer,text,date) VALUES ('".mysql_real_escape_string($id)."','".$session."','".mysql_real_escape_string($number)."','".mysql_real_escape_string($title)."','".$mp."','".mysql_real_escape_string($text)."','".mysql_real_escape_string($date)."')");
	}
	
	function createSignature($edm,$mp_name,$sname,$mp_id,$type,$date=false) {
		$mp = $this->fetchMP($mp_id);
		$mp = $mp['id'];
		if(!$mp) $mp = $this->createMP($mp_name,$sname,$mp_id);
		//else echo "MP already exists ($mp)\n";
		$already = $this->fetchSignature($edm,$session,$mp);
		if($already === false) {
			if($date == false) $date = 'NULL';
			else $date = "'".$date."'";
			mysql_query("INSERT IGNORE INTO signatures (edm,mp,type,date) VALUES ('".mysql_real_escape_string($edm)."','".$mp."','".mysql_real_escape_string($type)."',".$date.")") or die(mysql_error());
			$this->scrapeStatus['signatures']++;
			return true;
			echo "Inserted signature by ".$mp_name." of EDM ID ".$edm."\n";
		} elseif($already['type'] != $type) {
			mysql_query("UPDATE signatures SET type='".$type."' WHERE mp='".$mp_id."' AND edm='".$edm."'");
		} else return false;
	}
	
	function addEDMDetail($edm,$date,$text) {
		if($this->fetchEDM($edm)) {
			$result = mysql_query("UPDATE edms SET date='".$date."',text='".mysql_real_escape_string($text)."' WHERE id='".$edm."' LIMIT 1");
			return true;
		} else return false;
	}
	
	function addTopic($topic,$id) {
		if(mysql_query("INSERT IGNORE INTO `topics` (topic,id) VALUES ('".mysql_real_escape_string($topic)."','".mysql_real_escape_string($id)."')")) return true;
		else die(mysql_error());
	}
	
	// EDM id (gov) and MP id (mysoc)
	function withdrawSignature($edm,$mp) {
		echo 'Withdrawn signature for MP '.$mp.' in EDM '.$edm."\n";
		if(mysql_query("UPDATE signatures SET type='0' WHERE edm='".mysql_real_escape_string($edm)."' AND mp='".mysql_real_escape_string($mp)."' LIMIT 1")) return true;
		else return false;
	}
	
	function addSignatureDate($edm,$mp,$date) {
		if($this->fetchEDM($edm) && $this->fetchMP($mp)) {
			$result = mysql_query("UPDATE signatures SET date='".$date."' WHERE edm='".$edm."' AND mp='".$mp."' LIMIT 1");
			return true;
		} else return false;
	}
	
	function addMemberMysocid($mp,$mysocid) {
		if($this->fetchMP($mp)) {
			$result = mysql_query("UPDATE mps SET mysocid='".mysql_real_escape_string($mysocid)."' WHERE id='".$mp."' LIMIT 1");
			
			return true;
		} else return false;
	}		
	
	function queueBuild($type,$id) {
		if(ctype_digit($id)) {
			$result = mysql_query("insert ignore into builder(type,id) VALUES ('".mysql_real_escape_string($type)."','".mysql_real_escape_string($id)."')") or die(mysql_error());
			//echo 'Adding '.$type.' '.$id." to the build queue\n";
		} else print_r($matches);
		return true;
	}
	
	function check_utf8() {
		$result = mysql_query("SELECT * FROM edms ORDER BY ID ASC");
		while($row = mysql_fetch_assoc($result)) {
			$encoding = mb_detect_encoding($row['title']);
			$title = html_entity_decode($row['title'], ENT_QUOTES, 'UTF-8');
			if($title !== $row['title']) echo $title.' vs '.$row['title']."\n";
		}
	}
	
	function utf8_status() {
		$return['titles'] = array();
		$return['texts'] = array();
		$return['names'] = array();
		$return['parties'] = array();
		$return['constituencies'] = array();
		$result = mysql_query("SELECT title,text FROM edms");
		while($row = mysql_fetch_assoc($result)) {
			$encoding = mb_detect_encoding($row['title']);
			if(isset($return['titles'][$encoding])) $return['titles'][$encoding]++;
			else $return['titles'][$encoding] = 1;
			$encoding = mb_detect_encoding($row['text']);
			if(isset($return['texts'][$encoding])) $return['texts'][$encoding]++;
			else $return['texts'][$encoding] = 1;
		}
		$result = mysql_query("SELECT name FROM mps");
		while($row = mysql_fetch_assoc($result)) {
			$encoding = mb_detect_encoding($row['name']);
			if(isset($return['names'][$encoding])) $return['names'][$encoding]++;
			else $return['names'][$encoding] = 1;
		}
		$result = mysql_query("SELECT party,constituency FROM mpsessions");
		while($row = mysql_fetch_assoc($result)) {
			$encoding = mb_detect_encoding($row['party']);
			if(isset($return['parties'][$encoding])) $return['parties'][$encoding]++;
			else $return['parties'][$encoding] = 1;
			$encoding = mb_detect_encoding($row['constituency']);
			if(isset($return['constituencies'][$encoding])) $return['constituencies'][$encoding]++;
			else $return['constituencies'][$encoding] = 1;
		}
		print_r($return);
	}	
	
	function keywords() {
		$result = mysql_query("SELECT text FROM edms limit 10000");
		$return = array();
		while($row=mysql_fetch_assoc($result)) {
			$text = explode(' ',$row['text']);
			foreach($text as $word) {
				$word = strtolower($word);
				if(!in_array($word,$this->config['common_words'])) {
					if(in_array($word,$return)) $return[$word]++;
					else $return[$word] = 1;
				}
			}
		}
		//print_r($return);
		asort($return);
		print_r($return);
	}
	
	function calendar($year,$month=false,$day=false) {
		if(!$month) $month='january';
		if($day) {
			$data['date'] = strtotime($month.' '.$day.' '.$year);
			if($date !== false) {
				$data['signatures'] = mysql_result(mysql_query("SELECT count(*) as count FROM signatures WHERE date = '".date('Y-m-d',$data['date'])."'"),0,'count');
				$data['mps'] = $this->queryAll("SELECT mps.id,mps.name,count(*) as count FROM `signatures`,`mps` where signatures.mp=mps.id and date = '".date('Y-m-d',$data['date'])."' group by mp order by count desc");
				$data['proposed'] = $this->queryAll("SELECT edms.edm,edms.id,edms.session,edms.title,mps.name,mps.id as mp FROM edms,mps WHERE date = '".date('Y-m-d',$data['date'])."' and edms.proposer=mps.id ORDER BY edms.id ASC");
				$data['popular'] = $this->queryAll("SELECT edms.edm,edms.session,edms.title,count(*) as count  FROM `signatures` JOIN `edms` on signatures.edm=edms.id WHERE signatures.`date` = '".date('Y-m-d',$data['date'])."' group by signatures.edm order by count desc limit 10");
				$data['next'] = strtotime($this->queryOne("SELECT date FROM signatures WHERE date > '".date('Y-m-d',$data['date'])."' ORDER BY date ASC limit 1"));
				$data['prev'] = strtotime($this->queryOne("SELECT date FROM signatures WHERE date < '".date('Y-m-d',$data['date'])."' ORDER BY date DESC limit 1"));;
			}
		} else {
			$data['start'] = strtotime($month.' 1st '.$year);
			$data['end'] = strtotime('+1 month',$data['start']);
			$data['signatures'] = $this->queryAll("SELECT date,count(*) as count FROM signatures WHERE date >= '".date('Y-m-d',$data['start'])."' AND date < '".date('Y-m-d',$data['end'])."' group by date order by date asc");
			$data['edms'] = $this->queryAll("SELECT date,count(*) as count FROM edms WHERE date >= '".date('Y-m-d',$data['start'])."' AND date < '".date('Y-m-d',$data['end'])."' group by date order by date ASC");
			$data['next'] = strtotime($this->queryOne("SELECT date FROM signatures WHERE date >= '".date('Y-m-d',$data['end'])."' ORDER BY date ASC limit 1"));
			$data['prev'] = strtotime($this->queryOne("SELECT date FROM signatures WHERE date < '".date('Y-m-d',$data['start'])."' ORDER BY date DESC limit 1"));
		}
		return $data;
	}
	
	function topicsList() {
		$return['topics'] = $this->queryAll('SELECT topics.topic,count(*) as motions FROM topics JOIN motiontopics ON topics.id=motiontopics.topic GROUP BY topics.id ORDER BY topics.topic ASC');
		$return['count'] = count($return['topics']);
		$return['categorised'] = 0;
		foreach($return['topics'] as $topic) {
			$return['summary'][strtolower($topic['topic'][0])]++;
			$return['categorised'] += $topic['motions'];
		}
		return $return;
	}
	
	function topic($topic) {
		return $this->queryAll("SELECT topics.topic,edms.* FROM topics JOIN motiontopics ON topics.id=motiontopics.topic JOIN edms ON motiontopics.edm=edms.id WHERE topics.topic='".mysql_real_escape_string($topic)."' ORDER BY edms.date DESC");
	}

	function logEvent($log, $path) {
		if($log == 'scraper') {
			if(!isset($this->scraperlog)) $this->scraperlog = fopen('/home/earlydaymotion/edm/logs/'.gmdate('Y-m-d').'-scraper.log','a');
			fwrite($this->scraperlog,gmdate('Y-m-d H:i:s').' '.$path."\n");
		} elseif($log == 'builder') {
			if(!isset($this->builderlog)) $this->builderlog = fopen('/home/earlydaymotion/edm/logs/'.gmdate('Y-m-d').'-builder.log','a');
			fwrite($this->builderlog,gmdate('Y-m-d H:i:s').' '.$path."\n");
		}
		return true;
	}
	
	function analysisKeyword($keyword,$start=false,$end=false) {
		if($start==false OR !preg_match("/[0-9]{4}-[0-9]{2}-[0-9]/",$start)) $start = '1989-11-01';
		if($end==false OR !preg_match("/[0-9]{4}-[0-9]{2}-[0-9]/",$end)) $end = date('Y-m-d');
		$data['keyword'] = $keyword;
		$data['start'] = $start;
		$data['end'] = $end;
		$data['motions'] = $this->queryAll("Select *,count(DISTINCT signatures.mp,signatures.edm) as signatures FROM signatures JOIN edms ON edms.id=signatures.edm WHERE match(`title`,`text`) AGAINST ('".mysql_real_escape_string($keyword)."' IN BOOLEAN MODE) AND edms.date > '".$start."' AND edms.date < '".$end."' GROUP BY signatures.edm ORDER BY signatures DESC");
		$data['mps'] = $this->queryAll("SELECT *,sum( 1 / `type` ) AS sort FROM signatures JOIN edms ON signatures.edm = edms.id JOIN mps ON signatures.mp = mps.id WHERE match(`title`,`text`) AGAINST ('".mysql_real_escape_string($keyword)."' IN BOOLEAN MODE) AND edms.date > '".$start."' AND edms.date < '".$end."' GROUP BY signatures.mp ORDER BY sort DESC");
		foreach($data['mps'] as $mp) {
			$info = $this->fetchMPDetail($mp['id']);
			$party = $info[0]['party'];
			if(isset($data['parties'][$party])) $data['parties'][$party] += $mp['sort'];
			else $data['parties'][$party] = $mp['sort'];
		}
		arsort($data['parties']);
		return $data;
	}
	
	function logProgress($field) {
		if(!$this->queryOne("SELECT * FROM log WHERE date='".gmdate('Y-m-d')."'")) mysql_query("INSERT INTO log (date) VALUES ('".gmdate('Y-m-d')."')");
		mysql_query("UPDATE log SET ".$field."=1 WHERE date='".gmdate('Y-m-d')."'");
		return true;
	}

	function resetCookie() {
		if(file_exists($this->config['cookie'])) unlink($this->config['cookie']);
	}
	
	function returnPage($url,$post) {
		echo 'Fetching '.$url."\n";
		sleep(5);
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_COOKIEFILE, $this->config['cookie']); 
		curl_setopt($ch, CURLOPT_COOKIEJAR, $this->config['cookie']);
        $return = curl_exec($ch);
		curl_close($ch);
		$this->logEvent('scraper',$url);
		return $return;
	}
	
	// Get the list of current MPs from TWFY
	function twfyMPs() {
		$data = unserialize(@file_get_contents('http://www.theyworkforyou.com/api/getMPs?key='.$this->config['twfy_api_key'].'&output=php'));
		foreach($data as $mp) {
			if($this->fetchMPByMysocid($mp['person_id']) == false) {
				$this->createMP($mp['name'],$this->revertName($mp['name']),$mp['person_id']);
				$this->addMemberMysocid($mp['person_id'],$mp['person_id']);
				$this->fetchMPURLS($mp['person_id']);
				$this->importMPSessions($mp['person_id']);
			}
		}
	}
	
	// Parse MP List from EDMi website, doesn't include MPs which have never signed an EDM.
	function parseMPList() {
		$mps = $this->edmixml->GetMemberPickList();
		$existing = $this->queryAll('SELECT govid,name FROM mps ORDER BY sname ASC');
		foreach($existing as $mp) {
			unset($mps[$mp['govid']]);
		}
		echo count($mps).' don\'t exist';
		foreach($mps as $id => $mp) {
			$id = $this->convertGovToMysoc($id);
			$name = explode(' ',$mp);
			$i = count($name) - 1;
			$sname = $name[$i].', ';
			unset($name[$i]);
			foreach($name as $n) $sname .= $n.' ';
			$sname = trim($n);
			$this->createMP($mp,$sname,$id);
		}
	}
	
	function parseSessions() {
		$sessions = $this->edmixml->GetAllSessions();
		$current = $this->queryAll("SELECT * FROM sessions ORDER BY id ASC");
		foreach($sessions as $session) {
			$matched = false;
			foreach($current as $cur) {
				if($session['id'] == $cur['id']) {
					$matched = true;
					if($cur['end'] != $session['end']) mysql_query("UPDATE sessions SET end='".mysql_real_escape_string($session['end'])."' WHERE id='".$session['id']."'") or die(mysql_error());
				}
			}
			if(!$matched) {
				mysql_query("INSERT INTO sessions (id,name,start,end) VALUES ('".$session['id']."','".$session['name']."','".$session['start']."','".$session['end']."')") or die(mysql_error());
			}
		}
	}
	
	function parseEDMList() {
		$edms = $this->edmixml->GetEdmsBySessionId($this->config['current_session']);
		$existing = $this->queryAll("SELECT edms.id as id,count(*) as sigs FROM edms JOIN signatures ON edms.id=signatures.edm WHERE edms.session='".$this->config['current_year']."' GROUP BY signatures.edm ORDER BY id DESC");
		echo count($edms).' edms returned, '.count($existing)." existing found\n";
		foreach($edms as $edm) {
			echo 'EDM id: '.$edm['id']."\n";
			$matched = false;
			foreach($existing as $i => $exist) {
				echo '- Checking against '.$exist['id']."\n";
				if($exist['id'] == $edm['id']) {
					$matched = true;
					echo "- Matched with existing entry\n";
					if($exist['sigs'] < $edm['signatures']) {
						echo '- Signature mismatch '.$exist['sigs'] .' != '.$edm['signatures']."\n";
						$this->scrapeQueue('edm',$edm['id']);
					} else echo "- Signatures OK\n";
					unset($existing[$i]);
					break;
				}
			}
			if(!$matched) {
				echo $edm['id']." must not exist in DB\n";
				$this->scrapeQueue('edm',$edm['id']);
			}
		}
	}
	
	function parseEDM($id) {
		$exists = $this->queryOne("SELECT id FROM edms WHERE id='".mysql_real_escape_string($id)."'");
		if(!$exists) {
			$edm = $this->edmixml->GetEdmDetailsWithId($id);
			$this->createEDM($edm['id'],$edm['number'],$this->config['current_year'],$edm['title'],$this->convertGovToMysoc($edm['proposer']),$edm['proposer_name'],mb_convert_encoding($edm['text'],'latin1','UTF-8'),$edm['date']);
		} else echo $exists." exists\n";
		$signatures = $this->edmixml->GetSignatoriesForEdmId($id);
		foreach($signatures as $sig) {
			$sig['type']++;
			if($sig['withdrawn'] == '1') $sig['type'] = 0;
			$this->createSignature($id,$this->invertName($sig['name']),$sig['name'],$this->convertGovToMysoc($sig['id']),$sig['type'],$sig['date']);
		}
	}
	
	function scrapeQueue($type,$number) {
		mysql_query("INSERT IGNORE INTO scrape_queue (type,id) VALUES ('".$type."','".$number."')");
		return true;
	}
	
	function checkWithdrawn() {
		$withdrawn = $this->queryAll("SELECT *,count(*) as count FROM `signatures` WHERE `type` =0 GROUP BY edm ORDER BY count DESC");
		$total = count($withdrawn);
		$count = 0;
		foreach($withdrawn as $edm) {
			$count++;
			echo '('.$count.'/'.$total.') Checking withdrawn signatures for edm '.$edm['edm']."\n";
			$this->parseEDM($edm['edm']);
		}
		echo 'Done';
	}
	
	function executeScrapeQueue() {
		$queue = $this->queryAll('SELECT * FROM scrape_queue ORDER BY cast(id AS unsigned integer) desc');
		$count = 0;
		$total = count($queue);
		if($queue) {
			foreach($queue as $item) {
				if($item['type'] == 'edm') {
					$count++;
					echo '('.$count.'/'.$total.') Parsing '.$item['type'].' '.$item['id']."\n";
					$this->parseEDM($item['id']);
					mysql_query("DELETE FROM scrape_queue WHERE type='".$item['type']."' AND id='".$item['id']."'");
				}
			}
		}
		echo $count.' items scraped';
		return true;
	}
	
	// $session is in the EDMi form

	
	function parseMember($mp,$session='') {
		if($mp > 9999) {
			$twfy_id = $mp;
			$mp = $this->convertMysocToGov($mp);
		} else $twfy_id = $this->convertGovToMysoc($mp);
		$this->queueBuild('mp',$twfy_id);
		$this->scrapeStatus['mps']++;
		$page = $this->returnPage('http://edmi.parliament.uk/EDMi/EDMByMember.aspx?MID='.$mp.'&SESSION='.$session,false);
		list($head,$page) = explode('<span class="ItalicsTitle">(',$page);
		list($party,$page) = explode(')</span>',$page,2);
		$party = trim(strip_tags($party));
		list($constituency,$page) = explode('</span>',$page,2);
		list($junk,$constituency) = explode('<span>',$constituency);
		$constituency = trim(strip_tags($constituency));
		list($junk,$edms) = explode('Date Signed</TH>',$page);
		$edms = explode('</tr>',$edms);
		foreach($edms as $edm) {
			$edm = explode('</td>',$edm);
			if(count($edm) != 1) {
				list($junk,$id) = explode('EDMDetails.aspx?EDMID=',$edm[1]);
				list($id,$junk) = explode('&SESSION=',$id);
				$id = trim($id);
				$exists = $this->queryOne("SELECT edm FROM signatures WHERE mp='".$twfy_id."' AND edm='".$id."'");
				if($exists != $id) {
					echo 'EDM id '.$id." does not exist, looking up\n";
					$this->scrapeQueue('edm',$id);
				}
				$date = $this->fixDate(trim(strip_tags($edm[4])));
				$this->addSignatureDate($id,$this->convertGovToMysoc($mp),$date);
				//echo 'Adding date '.$date.' for mp '.$mp.' ('.$this->convertGovToMysoc($mp).') to EDM '.$id."\n";
			}
		}
	}
	
	function parseThrough($type,$id,$variable=false) {
		if($type=='edm') {
			if($variable != false) $post = '__VIEWSTATE='.urlencode($this->viewstate).'&SignatureStatus=1';
			else $post = '';
			$page = $this->returnPage('http://edmi.parliament.uk/EDMi/EDMDetails.aspx?EDMID='.$id,$post);
		} elseif($type=='mp') {
			if($variable == false OR !in_array($variable,$this->config['sessions'])) $session = $this->config['current_year'];
			else $session = $this->config['edmi_sessions'][$variable];
			$page = $this->returnPage('http://edmi.parliament.uk/EDMi/EDMByMember.aspx?MID='.$id.'&SESSION='.$session,false);
		}
		print($page);
	}
	
	function parseEDMs() {
		for($i=10600;$i<42427;$i++) {
			echo '('.$i.'/42427) ';
			$this->parseEDM($i);
		}
	}

	function parseMissingSignatureDates() {
		$result = mysql_query("select signatures.mp,count(*),edms.session,mps.govid from signatures,edms,mps where signatures.edm=edms.id and signatures.mp=mps.id and signatures.type != 0 and signatures.date is null group by signatures.mp,edms.session ORDER BY count(*) DESC") or die(mysql_error());
		$total = mysql_num_rows($result);
		$count = 1;
		while($row = mysql_fetch_assoc($result)) {
			echo '('.$count.'/'.$total.') Looking up MP with govid '.$row['govid'].' in session '.$row['session']."\n";
			$this->parseMember($row['govid'],$row['session']);
			$this->resetCookie();
			$count++;
		}
	}
	
	function parseTopicsList($session) {
		echo 'Parsing topics for session '.$session."\n";
		$data = $this->edmixml->GetAllTopicsBySessionId($this->config['edm_sessions'][$session]);
		$current = $this->queryAll("SELECT motiontopics.*,edms.session,count(*) as motions FROM motiontopics JOIN topics ON motiontopics.topic=topics.id JOIN edms ON motiontopics.edm=edms.id WHERE edms.session='".$session."' GROUP BY motiontopics.topic ORDER BY topics.topic ASC");
		foreach($data as $topic) {
			$matched = false;
			foreach($current as $i => $cur) {
				if($cur['topic'] == $topic['id']) {
					$matched = true;
					if($cur['motions'] < $topic['motions']) {
						$this->parseTopic($topic['id'],$session);
					}
					unset($current[$i]);
					break;
				}
			}
			if(!$matched) {
				$exists = $this->queryOne("SELECT topic FROM topics WHERE id='".mysql_real_escape_string($topic['id'])."'");
				if(!$exists) mysql_query("INSERT IGNORE INTO topics (id,topic) VALUES ('".mysql_real_escape_string($topic['id'])."','".mysql_real_escape_string($topic['topic'])."')");
				$this->parseTopic($topic['id'],$session);
			}
		}
	}
	
	function parseTopic($topic,$session) {
		echo 'Parsing '.$topic." in ".$session."\n";
		$data = $this->edmixml->GetEdmsByTopicId($topic,$this->config['edm_sessions'][$session]);
		foreach($data as $edm) mysql_query("INSERT IGNORE INTO motiontopics (topic,edm) VALUES ('".mysql_real_escape_string($topic)."','".$edm."')");
	}
	
	function fetch_images() {
		$index = file_get_contents('http://www.theyworkforyou.com/images/mps/');
		preg_match_all('<a href="([0-9]{3,7})\.([a-zA-Z]{3,4})">', $index, $matches);
		foreach($matches[1] as $id => $mp) {
			$pictures[$mp]['format'] = $matches[2][$id];
			$pictures[$mp]['source'] = 'mps';
		}
		$index = file_get_contents('http://www.theyworkforyou.com/images/mpsL/');
		preg_match_all('<a href="([0-9]{3,7})\.([a-zA-Z]{3,4})">', $index, $matches);
		foreach($matches[1] as $id => $mp) {
			$pictures[$mp]['format'] = $matches[2][$id];
			$pictures[$mp]['source'] = 'mpsL';
		}
		$result = mysql_query("SELECT id FROM mps ORDER BY id ASC");
		$mps = array();
		while($row = mysql_fetch_assoc($result)) $mps[] = $row['id'];
		foreach($pictures as $mp => $data) {
			if(in_array($mp,$mps)) {
				$destination = $this->config['path'].'public/images/mps/'.$mp.'.jpg';
				//echo 'Found image '.$mp.'.'.$data['format'].' - looking for '.$destination."\n";
				$formats = array('JPG','JPEG','PNG','GIF');
				if(in_array(strtoupper($data['format']),$formats)) {
					//echo "Valid Format\n";
					if(!file_exists($destination)) {
						sleep(1);
						//echo "Copying image \n";
						$image = file_get_contents('http://www.theyworkforyou.com/images/'.$data['source'].'/'.$mp.'.'.$data['format']);
						file_put_contents($destination,$image);
						exec('/usr/bin/mogrify -format jpg '.$destination);
						echo 'Created '.$mp.".jpg \n";
					}
				}
			} else {
				$destination = $this->config['path'].'public/images/mps/'.$mp.'.jpg';
				if(file_exists($destination)) {
					unlink($destination);
					echo 'Deleted '.$mp.".jpg\n";
					$count++;
				}
			}
		}
		//echo $count." images deleted\n";
	}
	
	// Search the XML file for an MP by name
	function matchPersonName($name) {
		$data = simplexml_load_file('parlparse/people.xml');
		$return = array();
		foreach($data->person as $person) {
			ereg('/person/([0-9]{1,9})',$person['id'],$matches);
			if($person['latestname'] == $name) $return[] = $matches[1];
		}
		return $return;
	}
	
	// Return detail about an MP from TWFY
	// ID is the mysoc ID
	function personDetail($id) {
		return unserialize(@file_get_contents('http://www.theyworkforyou.com/api/getMP?key='.$this->config['twfy_api_key'].'&output=php&id='.$id));
	}
	
	function personSearch($name) {
		sleep(1);
		return unserialize(@file_get_contents('http://www.theyworkforyou.com/api/getMPs?key='.$this->config['twfy_api_key'].'&output=php&search='.urlencode($name)));
	}
	
	function importMPSessions($id) {
		sleep(1);
		$count = 0;
		echo "Looking up ID ".$id."\n";
		$data = unserialize(@file_get_contents('http://www.theyworkforyou.com/api/getMP?key='.$this->config['twfy_api_key'].'&output=php&id='.$id));
		foreach($data as $session) {
			$exists = $this->queryAll("SELECT * FROM mpsessions WHERE start='".mysql_real_escape_string($session['entered_house'])."' AND mp='".$id."'");
			if($exists && $exists[0]['end'] != $session['left_house']) mysql_query("UPDATE mpsessions SET end='".mysql_real_escape_string($session['left_house'])."' WHERE mp='".mysql_real_escape_string($session['person_id'])."' AND start='".mysql_real_escape_string($session['entered_house'])."'");
			else mysql_query("INSERT IGNORE into mpsessions (mp,start,end,party,constituency,name) VALUES ('".mysql_real_escape_string($session['person_id'])."','".mysql_real_escape_string($session['entered_house'])."','".mysql_real_escape_string($session['left_house'])."','".mysql_real_escape_string($session['party'])."','".mysql_real_escape_string($session['constituency'])."','".mysql_real_escape_string($session['first_name'])." ".mysql_real_escape_string($session['last_name'])."')") or die(mysql_error());
			$count++;
		}
		//echo $count." mp sessions imported.";
	}
	
	function fixMPid($old,$new) {
		if($old > 9000000) $govid = $old - 9000000;
		else $govid = $old;
		$rows = 0;
		mysql_query("UPDATE mps SET id='".mysql_real_escape_string($new)."', mysocid='".mysql_real_escape_string($new)."', govid='".mysql_real_escape_string($govid)."' where id='".mysql_real_escape_string($old)."'");
		$rows += mysql_affected_rows();
		mysql_query("UPDATE signatures SET mp='".mysql_real_escape_string($new)."' where mp='".mysql_real_escape_string($old)."'");
		$rows += mysql_affected_rows();
		mysql_query("UPDATE edms SET proposer='".mysql_real_escape_string($new)."' where proposer='".mysql_real_escape_string($old)."'");
		$rows += mysql_affected_rows();
		//echo $rows." rows changed\n";
		$this->importMPSessions($new);
	}
	
	function missingMPSessions() {
		$data = $this->queryAll("SELECT * FROM mps LEFT JOIN mpsessions ON mps.id=mpsessions.mp WHERE mpsessions.constituency IS NULL");
		foreach($data as $mp) {
			$this->importMPSessions($mp['id']);
		}
	}

	function dailyScrape() {
		$this->cronTab();
		/*
		$this->parseEDMList();
		$this->lookupPersonIDs();
		$this->parseMissingSignatureDates();
		$this->resetCookie();
		$this->parseTopicsList($this->config['current_year']);
		$this->fetch_images();
		$this->missingMPSessions();
		echo "==== Scrape Results ====\n";
		echo " EDMs Scraped: ".$this->scrapeStatus['edms']."\n";
		echo " MPs Scraped: ".$this->scrapeStatus['mps']."\n";
		echo " Signatures Added: ".$this->scrapeStatus['signatures']."\n";
		mysql_query("INSERT INTO scraper (date,edms,mps,signatures) VALUES ('".gmdate('Y-m-d')."','".$this->scrapeStatus['edms']."','".$this->scrapeStatus['mps']."','".$this->scrapeStatus['signatures']."')");
		*/
	}
	
	function resetAncientMPs() {
		$result = mysql_query("SELECT * FROM `mpsessions` WHERE START < '1960-00-00' GROUP BY mp");
		$count = 0;
		while($row = mysql_fetch_assoc($result)) {
			mysql_query("UPDATE mps SET mysocid = NULL where id='".$row['mp']."'");
			$count++;
		}
		echo $count." mps reset";
	}

	// Search all-members.xml and use member ID to search people.xml
	function searchAllMember($name) {
		$info = explode(" ",$name);
		$first = $info[0];
		$last = $info[count($info)-1];
		$data = simplexml_load_file('parlparse/all-members.xml');
		$members = array();
		foreach($data as $member) {
			if($member['firstname'] == $first && $member['lastname'] == $last) {
				$members[] = substr($member['id'],25);
			}
		}

		$return = array();
		$data = simplexml_load_file('parlparse/people.xml');
	}

	// Use this function to match new MPs
	function lookupPersonIDs() {
		$result = mysql_query("SELECT * FROM mps WHERE mysocid IS NULL");
		while($row = mysql_fetch_assoc($result)) {
			$search = $this->personSearch($row['name']);
			if($search[0]['name'] == $row['name']) {
				echo 'Matched '.$row['name'].' to ID '.$search[0]['person_id']."\n";
				$this->fixMPid($row['id'],$search[0]['person_id']);
				$this->queueBuild('mp',$search[0]['person_id']);
			}
		}
	}
	
	function detailedLookupPersonIDs() {
		$result = mysql_query("SELECT * FROM mps WHERE mysocid IS NULL");
		while($row = mysql_fetch_assoc($result)) {
			echo "Looking up ".$row['name']."\n";
			$match = false;
			$name_ids = $this->matchPersonName($row['name']);
			$aliases = $this->lookupNameAliases($row['name']);
			$ids = array_merge($aliases,$name_ids);
			foreach($ids as $id) {
				echo "Found id ".$id."\n";
				if($match == false) {
					$data = $this->personDetail($id);
					foreach($data as $session) {
						if($data['constituency'] == $row['constituency']) {
							$this->addMemberMysocid($row['id'],$data['person_id']);
							echo "Matched using constituency\n";
							$match = true;
							break;
						} elseif(substr($session['entered_house'],0,4) > 1989) {
							$this->addMemberMysocid($row['id'],$data['person_id']);
							echo "Suspect that ".$row['id']." is ".$id."\n";
							break;
						}
					}
				}
			}
		}
	}
	
	function fix_person_ids() {
		$result = mysql_query("SELECT mpsessions.mp, mps.name FROM `mpsessions` , mps WHERE mpsessions.mp = mps.id AND `start` < '1989-01-01' GROUP BY mp");
		while($row = mysql_fetch_assoc($result)) {
			$persons = $this->lookup_personid($row['name']);
			$matched = false;
			foreach($persons as $person) {
				if($person != $row['mp'] && $matched == false) {
					sleep(1);
					$data = unserialize(@file_get_contents('http://www.theyworkforyou.com/api/getMP?key='.$this->config['twfy_api_key'].'&output=php&id='.$person));
					foreach($data as $session) {
						if(substr($session['entered_house'],0,4) > 1989) {
							echo "Suspect that ".$row['mp']." is ".$person."\n";
							$this->fixMPid($row['mp'],$person);
							$matched = true;
							break;
						}
					}
				}
			}
		}
	}
	
	function import_person_aliases() {
		$result = mysql_query("SELECT * FROM mps WHERE mysocid IS NULL");
		while($row = mysql_fetch_assoc($result)) {
			$members[$row['name']]['id'] = $row['id'];
		}
		
		$data = simplexml_load_file('parlparse/member-aliases.xml');
		foreach($data->alias as $alias) {
			$fullname = strval($alias['fullname']);
			$alternate = strval($alias['alternate']);
			if(array_key_exists($fullname,$members)) $members[$fullname]['aliases'][] = $alternate;
			if(array_key_exists($alternate,$members)) $members[$alternate]['aliases'][] = $fullname;
		}
		
		foreach($members as $name => $member) {
			foreach($member['aliases'] as $alias) {
				$mysocid = $this->matchPersonName($alias);
				if($mysocid !== false) {
					$this->addMemberMysocid($member['id'],$mysocid);
					echo 'Added id '.$mysocid.' to '.$name.' by using alias '.$alias."\n";
				}
			}
		}
		
	}

	// Looks up all person IDs for all aliases for a name
	function lookupNameAliases($name) {
		$data = simplexml_load_file('parlparse/member-aliases.xml');
		$matches = array();
		foreach($data->alias as $alias) {
			$fullname = strval($alias['fullname']);
			$alternate = strval($alias['alternate']);
			if($fullname == $name) $matches[] = $alternate;
			if($alternate == $name) $matches[] = $fullname;
		}
		$return = array();
		if(!empty($matches)) {
			foreach($matches as $name) {
				echo "Looking up alias ".$name."\n";
				$ids = $this->matchPersonName($name);
				array_merge($return,$ids);
			}
		}
		return $return;
	}
	

	function fixAllMPids() {
		$result = mysql_query("SELECT * FROM mps WHERE id != mysocid AND mysocid IS NOT NULL");
		while($row = mysql_fetch_assoc($result)) {
			$this->fixMPid($row['id'],$row['mysocid']);
		}
	}

	function fetchAllMPs() {
		$result = mysql_query("SELECT * FROM mps WHERE mysocid IS NOT NULL");
		while($row = mysql_fetch_assoc($result)) {
			echo 'Fetching MP '.$row['name']."\n";
			$this->importMPSessions($row['id']);
		}
	}

	function parsetemplate($data,$template,$output) {
		ob_start();
		$template = $this->config['path'].$template;
		$output = $this->config['path'].$output;
		if(file_exists($template)) {
			include $template;
			$content = ob_get_contents();
			ob_end_clean();
			file_put_contents($output,$content);
			//echo 'Outputting data to '.$output."\n";
			$this->logEvent('builder',$output);
		} else echo 'Template "'.$template.'"'." does not exist, skipping\n";
		return true;
	}

	function outputJSON($data,$path) {
		$path = $this->config['path'].$path;
		file_put_contents($path,json_encode($data));
		$this->logevent('builder',$path);
	}

	function loadCache($path,$timeout) {
		if(!file_exists($path)) return false;
		else {
			$date = time() - filemtime($path);
			if($date < $timeout) {
				$data = json_decode(file_get_contents($path),true);
				$data['cache_last_modified'] = gmdate('Y-m-d H:i:s',$date);
				return $data;
			} else return false;
		}
	}
	
	function withdrawnSignatures($edm) {
		return $this->queryOne("SELECT count(*) as count FROM signatures WHERE edm='".$edm."' AND type='0'");
	}

	//A specified EDM
	function build_edm($motion,$session,$output='htm') {
		if($session == 'id') $id = $motion;
		else $id = $this->queryOne("SELECT id FROM edms WHERE edm='".$motion."' AND session='".$session."'");
		$data = $this->fetchEDM($id);
		$data['proposingmp']['id'] = $data['proposer'];
		$data['proposingmp']['name'] = $data['name'];
		$data['ammendments'] = $this->fetchAmmendments($id,$data['session']);
		if($data['session'] == $this->config['current_year']) $data['current_session'] = true;
		if($data) {
			if($output=='rss') $data['signatures'] = $this->fetchSignatures($id,$session,false,true);
			else {
				$data['signatures'] = $this->fetchSignatures($id,true);
				$data['withdrawn'] = $this->withdrawnSignatures($id);
				//$data['timeline'] = $this->fetchSignatureTimeline($id);
				//$data['parties'] = $this->fetchSignaturePartyBreakdown($id);
			}			
		} else die('EDM Has No Signatures');
		$this->outputJSON($data,'json_cache/edms-'.$data['session'].'-'.$data['edm'].'.json');
	}

	//All EDMs in a given session
	function build_edms($session,$output='htm') {
		echo 'Building EDMs from '.$session."\n";
		$result = mysql_query("SELECT * FROM edms WHERE session='".$session."' ORDER BY id ASC") or die(mysql_error());
		while($row = mysql_fetch_assoc($result)) {
			echo 'Building '.$row['title']."\n";
			$this->build_edm($row['id'],'id',$output);
		}
	}
	
	//MP Index
	function build_mp($mp,$output='htm') {
		$data = $this->fetchMP($mp);
		if($data) {
			$data['activity'] = $this->fetchRecentActivity(10,$mp);
			$data['breakdown'] = $this->fetchMPSigningBreakdown($mp);
			$data['topics'] = $this->fetchMPTopics($mp);
			$session_list = $this->fetchMPActiveSessions($mp);
			if(file_exists($this->config['path'].'public/images/mps/'.$data['mysocid'].'.jpg')) $data['image'] = '/images/mps/'.$data['mysocid'].'.jpg';
			if($session_list !== false) {
				$i = 0;
				foreach($session_list as $session){
					$data['sessions'][$i]['session'] = $session;
					$data['sessions'][$i]['breakdown'] = $this->fetchMPSigningBreakdown($mp,$session);
					if($session == $this->fetchCurrentSession()) $this->mpSession($mp,$session,$output);
					$i++;
				}
			} //else echo 'No Sessions';
			$this->outputJSON($data,'json_cache/mps-'.$mp.'-index.json');
		} //else echo "Error fetching MP ".$mp."\n";
	}
	
	//MPs Activity in a Session
	function mpSession($mp,$session,$output='htm') {
		//echo ' Building session '.$session." for MP ID ".$mp."\n";
		$data['edms'] = $this->fetchEDMS($mp,$session);
		$data['session'] = $session;
		if(isset($this->mp) && $this->mp['id'] == $mp) $data['mp'] = $this->mp;
		else $data['mp'] = $this->fetchMP($mp);
		$data['breakdown'] = $this->fetchMPSigningBreakdown($mp,$session);
		//$data['timeline'] = $this->fetchSignatureTimeline($mp,$session);
		$this->outputJSON($data,'json_cache/mps-'.$mp.'-'.$session.'.json');
	}
	
	//All mps listed by name/signatures/latest signature
	function list_mps($output='htm') {
		$data = $this->fetchMPList('sname');
		if(in_array($output,$this->config['outputs'])) $this->parsetemplate($data,'templates/list_mps.'.$output.'.php','public/mps/list_name.'.$output);
		else die('Unknown output format');
		$data = $this->fetchMPList('signatures');
		if(in_array($output,$this->config['outputs'])) $this->parsetemplate($data,'templates/list_mps.'.$output.'.php','public/mps/list_sigs.'.$output);
		else die('Unknown output format');
		$data = $this->fetchMPList('latest');
		if(in_array($output,$this->config['outputs'])) $this->parsetemplate($data,'templates/list_mps.'.$output.'.php','public/mps/list_latest.'.$output);
		else die('Unknown output format');
	}

	function list_motions($session,$output='htm') {
		$data['session'] = $session;
		if($output=='htm') {
			$data['motions'] = $this->fetchMotionList($session,'id');
			if(in_array($output,$this->config['outputs'])) $this->parsetemplate($data,'templates/list_motions.'.$output.'.php','public/edms/'.$session.'/list_id.'.$output);
			else die('Unknown output format');
			$data['motions'] = $this->fetchMotionList($session,'title');
			if(in_array($output,$this->config['outputs'])) $this->parsetemplate($data,'templates/list_motions.'.$output.'.php','public/edms/'.$session.'/list_title.'.$output);
			else die('Unknown output format');
			$data['motions'] = $this->fetchMotionList($session,'latest');
			if(in_array($output,$this->config['outputs'])) $this->parsetemplate($data,'templates/list_motions.'.$output.'.php','public/edms/'.$session.'/list_latest.'.$output);
			else die('Unknown output format');
			$data['motions'] = $this->fetchMotionList($session,'signatures');
			if(in_array($output,$this->config['outputs'])) $this->parsetemplate($data,'templates/list_motions.'.$output.'.php','public/edms/'.$session.'/list_sigs.'.$output);
			else die('Unknown output format');
		} else {
			$data['motions'] = $this->fetchMotionList($session,'id');
			if(in_array($output,$this->config['outputs'])) $this->parsetemplate($data,'templates/list_motions.'.$output.'.php','public/edms/'.$session.'/motions.'.$output);
			else die('Unknown output format');
		}
		
	}
	
	//All MPs
	function mps($start=false,$output='htm') {
		echo "Building MPs\n";
		if($start) $result = mysql_query("SELECT * FROM mps WHERE id >= ".mysql_real_escape_string($start)." ORDER BY id ASC") or die(mysql_error());
		else $result = mysql_query("SELECT * FROM mps ORDER BY id ASC") or die(mysql_error());
		while($row = mysql_fetch_assoc($result)) {
			echo 'Building '.$row['name']." (".$row['id'].")\n";
			$dir = 'public/mps/'.$row['id'];
			if(!is_dir($dir)) mkdir($dir);
			$this->mp = $row;
			$this->mp($row['id'],$output);
			$sessions = $this->fetchMPActiveSessions($row['id']);
			if($sessions !== false) {
				foreach($sessions as $session) {
					$this->mpSession($row['id'],$session);
				}
			}
		}
	}
	
	// The MPs homepage
	function index_mps($output='htm') {
		$session = $this->config['current_year'];
		$data['mps'] = $this->fetchMPSummary();
		$data['active'] = $this->fetchRecentlyActiveMPs(10);
		$data['parties'] = $this->fetchPartyActivity($session);
		$data['proposers'] = $this->fetchProlificMPs(10,$session,1);
		$data['seconders'] = $this->fetchProlificMPs(10,$session,2);
		$data['signers'] = $this->fetchProlificMPs(10,$session,3);
		$this->parsetemplate($data,'templates/index_mps.'.$output.'.php','public/static/mps_index.'.$output);
	}
	
	//The site homepage
	function index_main() {
		$data['session'] = $this->config['current_year'];
		$data['recent'] = $this->fetchNewestMotions(10);
		$data['popular'] = $this->fetchPopularMotions(10,$data['session']);
		$data['booming'] = $this->fetchBoomingMotions(10);
		$data['prolific'] = $this->fetchProlificMPs(10,$data['session']);
		$data['addition'] = $this->fetchRecentActivity(1);
		$data['stats']['mps'] = mysql_num_rows(mysql_query("SELECT signatures.edm FROM `signatures` JOIN edms ON signatures.edm=edms.id WHERE edms.session ='".$data['session']."' group by signatures.mp"));
		$data['stats']['edms'] = mysql_result(mysql_query("SELECT count(*) as count FROM edms where session='".$data['session']."'"),0);
		$data['stats']['signatures'] = $this->queryOne("SELECT count(*) FROM `signatures` JOIN edms ON signatures.edm=edms.id WHERE edms.session ='".$data['session']."'");
		$this->outputJSON($data,'json_cache/index_main.json');
		//$this->parsetemplate($data,'templates/index_main.'.$output.'.php','public/index.'.$output);
	}

	//The session homepage
	function index_edms($output='htm') {
		$data = $this->fetchSessionList();
		if(in_array($output,$this->config['outputs'])) $this->parsetemplate($data,'templates/index_edms.'.$output.'.php','public/static/edms_index.'.$output);
		else die('Unknown output format');
	}

	function index_data($output='htm') {
		$data['sessions'] = $this->fetchSessionList();
		foreach($data['sessions'] as $id => $session) {
				$data['sessions'][$id]['xmlsize'] = $this->formatFileSize($this->config['path'].'public/data/'.$session['session'].'.xml');
				$data['sessions'][$id]['zipsize'] = $this->formatFileSize($this->config['path'].'public/data/'.$session['session'].'.zip');
		}
		$i = 0;
		foreach($this->config['sessions'] as $session) {
			$data['mpsessions'][$i]['session'] = $session;
			$data['mpsessions'][$i]['csv'] = $this->formatFileSize($this->config['path'].'public/data/mps_'.$session.'.csv');
			$data['mpsessions'][$i]['xml'] = $this->formatFileSize($this->config['path'].'public/data/mps_'.$session.'.xml');
			$i++;
		}
		if(in_array($output,$this->config['outputs'])) $this->parsetemplate($data,'templates/index_data.'.$output.'.php','public/data/index.'.$output);
		else die('Unknown output format');
    }
	
	function feeds() {
		$data['recent'] = $this->fetchNewestMotions('feed');
		$this->parsetemplate($data['recent'],'templates/feed_recent.rss.php','public/recent.rss');
		$data['popular'] = $this->fetchBoomingMotions(20);
		$this->parsetemplate($data['popular'],'templates/feed_popular.rss.php','public/popular.rss');
	}
	
	function mps_session($session,$output='htm') {
		$result = mysql_query("SELECT mps.*,signatures.type,count(*) as count from signatures,edms,mps where signatures.mp=mps.id and signatures.edm=edms.id and edms.session='".$session."' group by signatures.mp order by count desc");
		while($row=mysql_fetch_assoc($result)) {
			$data['mps'][] = $row;
		}
		$data['session'] = $session;
		$data['session_summary'] = $this->fetchSession($session);
		$this->parsetemplate($data,'templates/mps_session.'.$output.'.php','public/mps/'.$session.'.'.$output);		
	}
	
	function sessionIndex($session,$output='htm') {
		echo 'Building session index for '.$session."\n";
		$data = $this->fetchSession($session);
		$data['session'] = $session;
		$this->parsetemplate($data,'templates/index_session.'.$output.'.php','public/edms/'.$session.'/index.'.$output);
	}

	function allSessionIndexes($output='htm') {
		foreach($this->config['sessions'] as $session) {
			echo 'Building index for session '.$session."\n";
			$this->sessionIndex($session,$output);
			$this->list_motions($session,$output);
		}
	}

	function buildAllTypes($function,$variable=false) {
		foreach($this->config['outputs'] as $output) {
			if($variable) $this->$function($variable,$output);
			else $this->$function($output);
		}
		return true;
	}
	
	function buildMissingMPs() {
		$result = mysql_query("SELECT * FROM mps ORDER BY id ASC");
		while($row = mysql_fetch_assoc($result)) {
			if(!file_exists('public/mps/'.$row['id'].'/index.rss')) {
				echo 'MP '.$row['id']." does not exist, building\n";
				$this->buildAllTypes('mp',$row['id']);
			}
		}
		return true;
	}

	function dump_data($session) {
		if(!$session OR !in_array($session,$this->config['sessions'])) $session = $this->config['current_year'];
		$session_xml = fopen($this->config['path'].'public/data/'.$session.'.xml.tmp','w');
		fwrite($session_xml,'<?xml version="1.0" encoding="UTF-8"?>'."\n");
		fwrite($session_xml,"\t<session period=\"".$session."\">\n");
		$result = mysql_query("select edms.*,count(*) as signatures,mps.name,mps.sname from signatures join edms on signatures.edm=edms.id join mps on edms.proposer=mps.id group by edms.id having edms.session='".$session."' order by edms.id asc");
		while($row = mysql_fetch_assoc($result)) {
			$edm_head = "\t\t<motion>\n";
			$edm_head .= "\t\t\t<id>".$row['id']."</id>\n";
			$edm_head .= "\t\t\t<session>".$row['session']."</session>\n";
			$edm_head .= "\t\t\t<number>".$row['edm']."</number>\n";
			$edm_head .= "\t\t\t<title>".htmlspecialchars($row['title'])."</title>\n";
			$edm_head .= "\t\t\t<text>".htmlspecialchars($row['text'])."</text>\n";
			$edm_head .= "\t\t\t<proposer id=\"".$row['proposer']."\">".htmlspecialchars($row['sname'])."</proposer>\n";
			$edm_head .= "\t\t\t<signature_count>".$row['signatures']."</signature_count>\n";
			$edm_head .= "\t\t\t<signatures>\n";
			fwrite($session_xml,$edm_head);
			$signatures = mysql_query("SELECT * FROM signatures JOIN mps ON signatures.mp=mps.id WHERE signatures.edm='".$row['id']."' ORDER BY date asc,type ASC");
			while($sig = mysql_fetch_assoc($signatures)) {
				$sig_xml = "\t\t\t\t<signature>\n";
				$sig_xml .= "\t\t\t\t\t<mp id=\"".$sig['mp']."\">".htmlspecialchars($sig['sname'])."</mp>\n";
				$sig_xml .= "\t\t\t\t\t<date>".$sig['date']."</date>\n";
				$sig_xml .= "\t\t\t\t\t<type>".$this->config['sig_type'][$sig['type']]."</type>\n";
				$sig_xml .= "\t\t\t\t</signature>\n";
				fwrite($session_xml,$sig_xml);
			}
			$edm_tail = "\t\t\t</signatures>\n";
			$edm_tail .= "\t\t</motion>\n";
			fwrite($session_xml,$edm_tail);
			$session = $row['session'];
		}
		fwrite($session_xml,"\t</session>");
		fclose($session_xml);
		unlink($this->config['path'].'public/data/'.$session.'.xml');
		rename($this->config['path'].'public/data/'.$session.'.xml.tmp',$this->config['path'].'public/data/'.$session.'.xml');
		exec('/usr/bin/zip -j '.$this->config['path'].'public/data/'.$session.'.zip '.$this->config['path'].'public/data/'.$session.'.xml');
	}

	function dump_mps($session) {
		if(!in_array($session,$this->config['sessions'])) die('Unknown Session');
		$result = mysql_query("SELECT mps.name,mps.id,mps.govid,count(*) as signatures FROM `edms` JOIN `signatures` ON signatures.edm=edms.id JOIN mps ON signatures.mp=mps.id WHERE edms.session='".$session."' group by signatures.mp order by edms.session asc,mps.name asc");
		$csv = fopen($this->config['path'].'/public/data/mps_'.$session.'.csv','w');
		fwrite($csv,"Name,No. of Signatures,PersonID,EDMI ID\n");
		$xml = fopen($this->config['path'].'/public/data/mps_'.$session.'.xml','w');
		fwrite($xml,'<?xml version="1.0" encoding="UTF-8"?>'."\n");
		fwrite($xml,'<members session="'.$session.'">'."\n");
		while($row=mysql_fetch_assoc($result)) {
			fwrite($csv,'"'.$row['name'].'",'.$row['signatures'].','.$row['id'].','.$row['govid']."\n");
			fwrite($xml,"\t<member>\n");
			fwrite($xml,"\t\t<name>".$row['name']."</name>\n");
			fwrite($xml,"\t\t<signatures>".$row['signatures']."</signatures>\n");
			fwrite($xml,"\t\t<personID>".$row['id']."</personID>\n");
			fwrite($xml,"\t\t<edmiID>".$row['govid']."</edmiID>\n");
			fwrite($xml,"\t</member>\n");
		}
		fwrite($xml,"</members>");
		fclose($xml);
		fclose($csv);
	}

	function processBuildQueue() {
		$result = mysql_query("SELECT * FROM builder");
		if(mysql_num_rows($result) > 0) {
			while($row = mysql_fetch_assoc($result)) {
				echo 'Building '.$row['type'].' with ID '.$row['id']."\n";
				if($row['type'] == 'mp') $this->build_mp($row['id'],'json');
				elseif($row['type'] == 'edm') $this->build_edm($row['id'],'id','json');
				elseif($row['type'] == 'mpsession') {
					$mp = substr($row['id'],0,-4);
					$sess = substr($row['id'],-4);
					$next = $sess + 1;
					$session = $sess.'-'.$next;
					echo 'Building session '.$session." for MP ".$mp."\n";
					$this->mpSession($mp,$session,'json');
				}
				mysql_query("DELETE FROM builder WHERE type='".$row['type']."' and id='".$row['id']."'");
			}
		}
	}

	function dailyBuild() {
		$this->cronTab();
		/*
		$session = $this->fetchCurrentSession();
		$this->feeds();
		echo "Built Feeds\n";
		$this->index_main();
		$this->buildAllTypes('index_mps');
		$this->buildAllTypes('index_edms');
		$this->buildAllTypes('sessionIndex',$session);
		echo "Built Indexes\n";
		$this->buildAllTypes('list_motions',$session);
		$this->buildAllTypes('list_mps');
		echo "Processed building motion and MP lists\n";
		$this->dump_mps($session);
		$this->dump_data();
		echo "Built data dump\n";
		$this->index_data();
		mysql_query("UPDATE scraper SET built='1' WHERE date='".gmdate('Y-m-d')."'");
		*/
	}
	
	function cronTab() {
		if(gmdate('Gi') < 730) die(gmdate('Gi').' Waiting until after 8am');
		else echo "== Early Day Motion CronTab ==\n";
		$session = $this->config['current_year'];
		$log = $this->queryRow("SELECT * FROM log WHERE date='".gmdate('Y-m-d')."'");
		// Scrape
		if(!$log OR $log['list'] != '1') {
			mysql_query("INSERT IGNORE INTO log (date) VALUE ('".gmdate('Y-m-d')."')") or die(mysql_error());
			echo "Scraping EDM List\n";
			$this->parseSessions();
			$this->parseEDMList();
			$this->logProgress('list');
			$log = $this->queryRow("SELECT * FROM log WHERE date='".gmdate('Y-m-d')."'");
		}
		if($log['edms'] != '1') {
			echo "Looking up EDMs\n";
			$this->executeScrapeQueue();
			$this->logProgress('edms');
		}
		if($log['mps'] != '1') {
			echo "Looking up MPs\n";
			$this->parseMPList();
			$this->twfyMPs();
			$this->lookupPersonIDs();
			//$this->parseMissingSignatureDates();
			$this->fetch_images();
			//$this->missingMPSessions();
			$this->logProgress('mps');
		}
		if($log['topics'] != '1') {
			echo "Scraping Topics\n";
			$this->resetCookie();
			$this->parseTopicsList($this->config['current_year']);
			$this->logProgress('topics');
		}
		// Now Build
		if($log['feeds'] != '1') {
			echo "Building Feeds\n";
			$this->feeds();
			$this->logProgress('feeds');
		}
		if($log['indexes'] != '1') {
			echo "Building Indexes\n";
			$this->index_main();
			echo "-Main Index\n";
			$this->buildAllTypes('index_mps');
			echo "-MPs\n";
			$this->buildAllTypes('index_edms');
			echo "-EDMs\n";
			$this->buildAllTypes('sessionIndex',$session);
			echo "-Session Index\n";
			$this->buildAllTypes('list_mps');
			echo "-MP Lists\n";
			$this->logProgress('indexes');
		}
		if($log['data'] != '1') {
			echo "Building data dumps\n";
			$this->dump_mps($session);
			$this->dump_data($session);
			$this->logProgress('data');
		}
		if($log['data_index'] != '1') {
			echo "Building data index\n";
			$this->index_data();
			$this->logProgress('data_index');
		}
		echo 'Days work complete';
	}	
}
?>
