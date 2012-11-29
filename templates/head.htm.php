<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
      "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">

<head>
<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
<meta http-equiv="content-language" content="en" />
<script type="text/javascript">var switchTo5x=true;</script>
<script type="text/javascript" src="http://w.sharethis.com/button/buttons.js"></script>
<script type="text/javascript">stLight.options({publisher: "97cc93cd-887f-4ac4-9e90-381e0b79bb1e",onhover:false}); </script>
<?php
if(!isset($header['keywords'])) $header['keywords'] = 'Early Day Motions, EDMs, Parliament, Government, Members of Parliament, early day motion, MPs, vote, data, xml, open, petition, rss, feed';
echo '<meta name="keywords" content="'.$header['keywords']."\" />\n";
if(!isset($header['description'])) $header['description'] = 'Providing open access to Early Day Motion data and signatures and allowing users to subscribe to RSS feeds for motions or members of parliament';
echo '<meta name="description" content="'.$header['description']."\" />\n";
if(isset($header['feed'])) echo "<link rel=\"alternate\" type=\"application/rss+xml\" title=\"RSS Feed\" href=\"".$header['feed']."\" />\n";
echo '<title>'.$title."</title>\n";
echo '<!-- Data built '.$data['cache_last_modified']." -->\n";
?>
<link rel="stylesheet" type="text/css" href="/style.css" />
<!-- Google Analytics -->
<script type="text/javascript">

  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-24420161-1']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();

</script>
</head>

<body>
<div id="header">
<a href="/"><img src="/images/logo.png" alt="Early Day Motions" /></a>
<div class="nav">
<ul>
<li><a href="/mps">MPs</a></li>
<li><a href="/edms">Motions</a></li>
<li><a href="/topics">Topics</a></li>
<li><a href="/calendar">Calendar</a></li>
<li><a href="/data">Raw Data</a></li>
<li><a href="/about">About</a></li>
</ul>
</div>
<form action="/search.php" method="get"><div id="form"><input type="text" name="q" id="q" accesskey="s" /> <input type="submit" value="Search" /></div></form>
</div>
<div id="main">
