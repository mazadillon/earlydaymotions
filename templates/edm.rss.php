<?php
$sigs = count($data['signatures']);
if($sigs < 20) $count = $sigs;
else $count = 20;
echo '<?xml version="1.0" encoding="UTF-8"?>';
echo "<rss version=\"2.0\">\n";
echo "<channel>\n";
echo "\t<title>EDM".$data['edm']." - ".$data['title']."</title>\n";
echo "\t<link>http://www.edms.org.uk/".$data['session']."/".$data['edm']."</link>\n";
echo "\t<description>Monitor signatures to EDM".$data['edm']."</description>\n";
echo "\t<language>en-gb</language>\n";
echo "\t<pubDate>".gmdate('r',strtotime($data['signatures'][0]['date']))."</pubDate>\n";
echo "\t<lastBuildDate>".gmdate('r')."</lastBuildDate>\n";
echo "\t<ttl>".count($data['signatures'])."</ttl>\n";
foreach($data['signatures'] as $signature) {
	echo "\t\t<item>\n";
	echo "\t\t\t<title>".$signature['type']." by ".$signature['name']."</title>\n";
	echo "\t\t\t<link>http://www.edms.org.uk/".$data['session']."/".$data['edm'].".htm</link>\n";
	echo "\t\t\t<description>".$signature['name']." has ".strtolower($signature['type'])." early day motion ".$data['edm']."</description>\n";
	echo "\t\t\t<pubDate>".gmdate('r',strtotime($signature['date']))."</pubDate>\n";
	echo "\t\t\t<guid>http://www.edms.org.uk/edms/".$data['session']."/".$data['edm']."#".$signature['mp']."</guid>\n";
	echo "\t\t</item>\n";
}
echo "\t</channel>\n";
echo "</rss>";
?>
