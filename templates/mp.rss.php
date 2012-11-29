<?php
echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
echo "<rss version=\"2.0\" xmlns:atom=\"http://www.w3.org/2005/Atom\">\n";
echo "<channel>\n";
echo "<atom:link href=\"http://www.edms.org.uk/mps/".$data['id']."/index.rss\" rel=\"self\" type=\"application/rss+xml\" />\n";
echo "\t<title>".$data['name']." - Early Day Motions</title>\n";
echo "\t<link>http://www.edms.org.uk/mps/".$data['id']."</link>\n";
echo "\t<description>Following the early day motions activity for ".$data['name']."</description>\n";
echo "\t<language>en-gb</language>\n";
echo "\t<pubDate>".gmdate('r',strtotime($data['activity'][0]['date']))."</pubDate>\n";
echo "\t<lastBuildDate>".gmdate('r')."</lastBuildDate>\n";
echo "\t<ttl>".count($data['activity'])."</ttl>\n";
foreach($data['activity'] as $action) {
	echo "\t\t<item>\n";
	echo "\t\t\t<title>".$action['title']."</title>\n";
	echo "\t\t\t<link>http://www.edms.org.uk/".$action['session']."/".$action['edm'].".htm</link>\n";
    echo "\t\t\t<description>".$data['name']." has ".strtolower($action['type'])." EDM".$action['edm'].". Motion reads: &quot;".$action['text']."&quot;</description>\n";
	echo "\t\t\t<pubDate>".gmdate('r',strtotime($action['date']))."</pubDate>\n";
    echo "\t\t\t<guid>http://www.edms.org.uk/edms/".$action['session']."/".$action['edm']."#".$action['mp']."</guid>\n";
    echo "\t\t</item>\n";
}
echo "\t</channel>\n";
echo "</rss>";
?>
