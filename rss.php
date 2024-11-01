<?php
require_once('../../../wp-load.php'); // TODO: do this the right way
$showreel = new MUShowreelRSS();
$pub_date = date('D, d M Y h:i:s', $showreel->lastCacheTime() == 0 ? time():$showreel->lastCacheTime()) . ' +0000';
header('Content-type: text/xml; charset=utf-8');
echo '<?xml version="1.0" encoding="UTF-8" ?>';
?>
<rss version="2.0">
<channel>
	<title>Wordpress MU - Showreel RSS</title>
	<description></description>
	<link><?=bloginfo('url')?></link>
	<lastBuildDate><?=$pub_date?></lastBuildDate>
	<pubDate><?=$pub_date?></pubDate>

    <? foreach($showreel->feed() as $data): ?>
        <item>
            <title><?=$data['post_title']?></title>
            <description><?=$data['description']?></description>
            <link><?=$data['guid']?></link>
            <guid><?=$data['guid']?></guid>
            <pubDate><?=date('D, d M Y h:i:s', strtotime($data['post_date']))?> +0000</pubDate>
            <? if(isset($data['image_url'])): ?>
                <enclosure url="<?=$data['image_url']?>" length="<?=$data['image_size']?>" type="<?=$data['image_mimetype']?>" />
            <? endif; ?>
        </item>
    <? endforeach; ?>

</channel>
</rss>