<?php
echo '<?xml version="1.0" encoding="UTF-8"?>';
echo "<rss version=\"2.0\">\n";
echo "<channel>\n";
echo "\t<title>Most Recent Early Day Motions</title>\n";
echo "\t<link>http://www.edms.org.uk/</link>\n";
echo "\t<description>Monitor the most recently proposed Early Day Motions</description>\n";
echo "\t<language>en-gb</language>\n";
echo "\t<pubDate>".gmdate('r',strtotime($data[0]['date']))."</pubDate>\n";
echo "\t<lastBuildDate>".gmdate('r')."</lastBuildDate>\n";
echo "\t<ttl>".count($data)."</ttl>\n";
if(!empty($data)) {
	foreach($data as $edm) {
		echo "\t\t<item>\n";
		echo "\t\t\t<title>".$edm['title']."</title>\n";
		echo "\t\t\t<link>http://www.edms.org.uk/".$edm['session']."/".$edm['edm'].".htm</link>\n";
		echo "\t\t\t<description>".$edm['text']."</description>\n";
		echo "\t\t\t<pubDate>".gmdate('r',strtotime($edm['date']))."</pubDate>\n";
		echo "\t\t\t<guid>http://www.edms.org.uk/edms/".$edm['session']."/".$edm['edm'].".htm</guid>\n";
		echo "\t\t</item>\n";
	}
}
echo "\t</channel>\n";
echo "</rss>";
?>
