<?
class edmiXML {
	function edmiXML() {
		$this->config['sleep'] = 5;
	}
	
	function returnPage($url) {
		echo 'Fetching '.$url."\n";
		sleep($this->config['sleep']);
		$ch = curl_init($url);
		if(!isset($this->scraperlog)) $this->scraperlog = fopen('/home/mazadillon/edm/logs/'.gmdate('Y-m-d').'-edmixml.log','a');
		fwrite($this->scraperlog,gmdate('Y-m-d H:i:s').' '.$url."\n");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $return = curl_exec($ch);
		curl_close($ch);
		return $return;
	}
	
	function fixDate($date) {
		return date('Y-m-d',strtotime($date));
	}
	
	// Incomplete
	function GetAllSessions() {
		$data =  $this->returnPage('http://data.parliament.uk/EDMi/EDMi.svc/Session/List');
		$xml = simplexml_load_string($data);
		$sessions = $xml->children('es',TRUE);
		$this->sessions = array();
		foreach($sessions->children('es',TRUE) as $session) {
			$attr = $session->attributes();
			$return['id'] = (string)$attr['id'];
			$return['name'] = (string)$session->SessionValue;
			$return['start'] = $this->fixDate((string)$session->SessionStartDate);
			$return['end'] = (string)$session->SessionEndDate;
			if($return['end'] != '') $return['end'] = $this->fixDate($return['end']);
			$this->sessions[] = $return;
		}
		return $this->sessions;
	}
	
	// Requires session ID as set by gov (905 = 2010-11)
	function GetEdmsBySessionId($session) {
		$data = $this->returnPage('http://data.parliament.uk/EDMi/EDMi.svc/Session/'.$session.'/List?allEdms=all&sortcolumn=number&sortorder=desc');
		$xml = simplexml_load_string($data);
		$edms = $xml->children('ee',TRUE);
		foreach($edms->children('ee',TRUE) as $edm) {
			$attr = $edm->attributes();
			$return['id'] = (string)$attr['id'];
			$return['number'] = (string)$attr['Number'];
			$return['signatures'] = (string)$attr['NumberOfSignatures'];
			$return['title'] = (string)$edm->Title;
			$return['status'] = (string)$edm->Status;
			$return['proposer_name'] = (string)$edm->PrimarySponsor;
			$sponsor = $edm->PrimarySponsor->attributes();
			$return['proposer'] = (string)$sponsor['id'];
			$motions[] = $return;
		}
		if(count($motions) > 0)	return $motions;
		else return false;
	}
	
	function GetEdmDetailsWithId($id) {
		$data = $this->returnPage('http://data.parliament.uk/EDMi/EDMi.svc/'.$id);
		if(strlen(trim($data)) > 0) {
			$edm = simplexml_load_string($data);
			$attr = $edm->attributes();
			if(isset($attr['AmendmentNumber'])) $return['number'] = (string)$attr['AmendmentNumber'];
			else $return['number'] = (string)$attr['Number'];
			$return['id'] = (string)$attr['id'];
			$return['signatures'] = (string)$attr['NumberOfSignatures'];
			$return['title'] = (string)$edm->Title;
			$return['text'] = strip_tags((string)$edm->MotionText);
			$return['status'] = (string)$edm->Status;
			$return['date'] = $this->fixDate((string)$edm->DateTabled);
			$return['proposer_name'] = (string)$edm->TablingMember;
			$sponsor = $edm->TablingMember->attributes();
			$return['proposer'] = (string)$sponsor['id'];
			return $return;
		} else return false;
	}
	
	function GetSignatoriesForEdmId($edm) {
		$data = $this->returnPage('http://data.parliament.uk/EDMi/EDMi.svc/'.$edm.'/Signature/List?allsignatures=all&sortcolumn=Datesigned');
		if(strpos($data,'es:Signature') !== false) {
			$xml = simplexml_load_string($data);
			$signatories = $xml->children('es',TRUE);
			foreach($signatories->children('es',TRUE) as $signer) {
				$attr = $signer->attributes();
				$return['signature_id'] = (string)$attr['id']; //Signature ID (not relevant to much)
				$return['withdrawn'] = (string)$attr['Withdrawn']; //Boolean
				$return['hasinterest'] = (string)$attr['HasInterest'];
				$return['type'] = (int)$attr['IsSponsor']; //Signature type
				$return['order'] = (string)$attr['Order']; // Unsure what this is for
				$return['name'] = (string)$signer->SignedMember;
				$attr = $signer->SignedMember->attributes();
				$return['id'] = (string)$attr['id']; // MP ID
				$return['party'] = (string)$signer->Party;
				$return['constituency'] = (string)$signer->Constituency;
				$return['date'] = $this->fixDate((string)$signer->DateSigned);
				$signatures[] = $return;
			}
			return $signatures;
		} else return false;
	}
	
	function GetMemberPickList() {
		$data = $this->returnPage('http://data.parliament.uk/EDMi/EDMi.svc/Member/List');
		$xml = simplexml_load_string($data);
		foreach($xml->PickListItems->children() as $mp) {
			$attr = $mp->attributes();
			$mps[(string)$attr['id']] = (string)$mp;
		}
		return $mps;
	}
	
	function GetAllTopics() {
		$data = $this->returnPage('http://data.parliament.uk/EDMi/EDMi.svc/Topic/List');
		$xml = simplexml_load_string($data);
		foreach($xml->SessionTopics->children() as $sessiontopic) {
			$attr = $sessiontopic->Session->attributes();
			$return['session'] = (string)$attr['id'];
			$return['topic'] = (string)$sessiontopic->Topic;
			$attr = $sessiontopic->attributes();
			$return['motions'] = (string)$attr['NumberOfMotions'];
			$attr = $sessiontopic->Topic->attributes();
			$return['id'] = (string)$attr['id'];
			$topics[] = $return;
		}
		if(is_array($topics)) return $topics;
		else return false;
	}
	
	function GetAllTopicsBySessionId($session) {
		$data = $this->returnPage('http://data.parliament.uk/EDMi/EDMi.svc/Session/'.$session.'/Topic/List');
		$xml = simplexml_load_string($data);
		foreach($xml->SessionTopics->children() as $sessiontopic) {
			$attr = $sessiontopic->Session->attributes();
			$return['session'] = (string)$attr['id'];
			$return['topic'] = (string)$sessiontopic->Topic;
			$attr = $sessiontopic->attributes();
			$return['motions'] = (string)$attr['NumberOfMotions'];
			$attr = $sessiontopic->Topic->attributes();
			$return['id'] = (string)$attr['id'];
			$topics[] = $return;
		}
		if(is_array($topics)) return $topics;
		else return false;
	}
	
	// Returns an array of EDM ids for the requested topic ID and session ID
	function GetEdmsByTopicId($topic,$session) {
		$data = $this->returnPage('http://data.parliament.uk/EDMi/EDMi.svc/Topic/'.$topic.'/'.$session.'/List');
		$xml = simplexml_load_string($data);
		$edms = $xml->children('ee',TRUE);
		foreach($edms->children('ee',TRUE) as $edm) {
			$attr = $edm->attributes();
			$return[] = (string)$attr['id'];
		}
		if(is_array($return)) return $return;
		else return false;
	}
}
?>