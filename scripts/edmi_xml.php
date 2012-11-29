<?php
require '../edmi_xml.class.php';

$class = new edmiXML;
//print_r($class->GetEdmsBySessionId(905));
//$class->GetSignatoriesForEdmId(42800)
//print_r($class->GetMemberPickList());
print_r($class->getAllSessions());
//print_r($class->GetAllTopics());
//print_r($class->GetEdmsByTopicId('1016',891));
//print_r($class->GetAllTopicsBySessionId(891));
?>