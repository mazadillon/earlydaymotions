<?php
$title = 'About';
include_once '../../templates/head.htm.php';
?>
<div style="float:right">
<script src="http://widgets.twimg.com/j/2/widget.js"></script>
<script>
new TWTR.Widget({
  version: 2,
  type: 'search',
  search: 'from:earlydaymotion',
  interval: 6000,
  title: 'Early Day Motions',
  subject: 'News and updates to edms.org.uk',
  width: 250,
  height: 300,
  theme: {
    shell: {
      background: '#e5eecc',
      color: '#000000'
    },
    tweets: {
      background: '#f1f1ed',
      color: '#444444',
      links: '#1985b5'
    }
  },
  features: {
    scrollbar: false,
    loop: true,
    live: true,
    hashtags: true,
    timestamp: true,
    avatars: true,
    toptweets: true,
    behavior: 'default'
  }
}).render().start();
</script>
</div>
<h2>FAQ</h2>
<ul>
<li><b>What is an Early Day Motion?</b><br />
(From <a href="http://edmi.parliament.uk">EDMI</a>) Early day motions (EDMs) are formal motions submitted for debate in the House of Commons.
However, very few EDMs are actually debated. Instead, they are used for reasons such as publicising the views of individual MPs, drawing
attention to specific events or campaigns, and demonstrating the extent of parliamentary support for a particular cause or point of view.</li>
<li><b>Why waste your time with EDMs?</b><br />
Contributing to EDMs may not be the most useful thing that an MP does, but they provide constituents with an insight into what their representative
is spending their time on and what issues they take an interest in. By adding EDMs to the picture of what an MP is doing a constituent is more able to hold
their representative to account.</li>
<li><b>Why duplicate the parliament portal?</b><br />
In my opinion the parliament portal is not easy to use, it does not provide easy access to the full extent of it's information. There is also
no facility to download raw data or follow motions using RSS feeds. MP ids on the parliament portal have also been linked to those used by the
<a href="http://ukparse.kforge.net/parlparse/">parlparse</a> project.</li>
</ul>
<h2>Contact</h2>
<ul>
<li>To contact your MP about a motion please use the website <a href="http://www.writetothem.com/">WriteToThem.com</a>.</li>
<li>If you wish to report a technical problem with the website or suggest an additional feature then you may email <i>matt@mattford.net</i></li>
</ul>
<h2>Data Update Status</h2>
<p>Monitor when data was last scraped from the parliament portal and see the process of the current update</p>
<?php
include_once '../../edm.class.php';
$edm = new edm;
$update = $edm->queryRow('SELECT * FROM log ORDER BY date DESC LIMIT 1');
echo '<table><tr>';
foreach(array_keys($update) as $header) {
	echo '<th>'.$header.'</th>';
}
echo '</td></tr><tr>';
foreach($update as $key => $value) {
	echo '<td>';
	if($key == 'date') echo $value;
	elseif($value==1) echo '<img src="/images/tick.png" alt="OK" title="Complete" />';
	else echo '<img src="/images/hourglass.png" alt="X" title="Waiting" />';
	echo '</td>';
}
echo '</tr></table>';
include_once '../../templates/foot.htm.php';
?>
