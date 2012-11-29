<?php
header("Content-Type: application/xml; charset=UTF-8");
echo '<?xml version="1.0" encoding="UTF-8"?>';
echo "\n<rss version=\"2.0\">\n";
echo "<channel>\n";
echo "\t<title>".$data[0]['topic']." - Early Day Motions</title>\n";
echo "\t<link>http://www.edms.org.uk/topics/".strtolower(urlencode($data[0]['topic']))."</link>\n";
echo "\t<description>Monitor early day motions listed under the topic of ".$data[0]['topic']."</description>\n";
echo "\t<language>en-gb</language>\n";
echo "\t<pubDate>".gmdate('r',strtotime($data[0]['date']))."</pubDate>\n";
echo "\t<lastBuildDate>".gmdate('r')."</lastBuildDate>\n";
echo "\t<ttl>".count($data)."</ttl>\n";
foreach($data as $motion) {
	echo "\t\t<item>\n";
	echo "\t\t\t<title>EDM".$motion['edm']." ".$motion['title']."</title>\n";
	echo "\t\t\t<link>http://www.edms.org.uk/".$motion['session']."/".$motion['edm'].".htm</link>\n";
	echo "\t\t\t<description>".$motion['text']."</description>\n";
	echo "\t\t\t<pubDate>".gmdate('r',strtotime($motion['date']))."</pubDate>\n";
	echo "\t\t\t<guid>http://www.edms.org.uk/edms/".$motion['session']."/".$motion['edm']."</guid>\n";
	echo "\t\t</item>\n";
}
echo "\t</channel>\n";
echo "</rss>";
?>
