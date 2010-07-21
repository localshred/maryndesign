<?php
header('Content-Type: text/html');

include_once('dbClass.php');
$db = new dbClass();

$arrFeedInfo = array(
	'blog' => array(
		'title' 		=> 'MarynDesign.com Weblog',
		'description' 	=> 'Code Detail, Graphic Design, Portfolio, and Weblog for BJ Neilsen',
		'link'			=> 'http://www.maryndesign.com/',
		'sql'			=> "
			SELECT
				b.EntryID,
				DATE_FORMAT(b.PostDate,'%a, %d %b %Y %T -0700') as PublishDate,
				b.Title,
				b.ShortDesc,
				u.UserID,
				u.Name
			FROM
				BlogEntries b
			LEFT JOIN AdminUsers u
				ON b.UserID = u.UserID
			ORDER BY b.PostDate DESC
				LIMIT 15"
	),
	'comments' => array(
		'title' 		=> 'MarynDesign.com Weblog Comments',
		'description' 	=> 'Blog Comments from MarynDesign.com',
		'link'			=> 'http://www.maryndesign.com/',
		'sql'			=> "
			SELECT
				bc.EntryID,
				bc.CommentID,
				bc.Name,
				bc.Comments,
				DATE_FORMAT(bc.CommentTime,'%a, %d %b %Y %T -0700') as PublishDate
			FROM
				BlogComments bc
			ORDER BY bc.CommentTime DESC
				LIMIT 15"
	),
);


$dir = 'feeds/';
foreach($arrFeedInfo as $feed => $arrFeed) {
	$file = $feed . '.xml';
	$fullpath = $dir . $file;
	if (!$handle = fopen($fullpath,'w+')) {
		mail_error('The file ' . $file . ' isn\'t writeable','RSS open/create failure');
		exit;
	}
	else {
		if (is_writeable($fullpath)) {
			unset($xml);
			$xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
<rss xmlns:dc=\"http://purl.org/dc/elements/1.1/\" version=\"2.0\">

<channel>
	<title>" . stripslashes($arrFeed['title']) . "</title>
	<description>" . stripslashes($arrFeed['description']) . "</description>
	<link>" . $arrFeed['link'] . "</link>
	<copyright>All content, Site Design, and Portfolio Work Copyright 2006" . (date('Y') > 2006 ? "-" . date('Y') : '') . " MarynDesign.com</copyright>";

			$db->runQuery($arrFeed['sql']);
			if ($db->numRows()) {
				while ($objPost = $db->nextObject()) {
					switch($feed) {
						case "blog":
						$xml .= "
	<item>
		<title>" . htmlentities(strip_tags(stripslashes($objPost->Title))) . "</title>
		<guid>" . $arrFeed['link'] . "?id=" . $objPost->EntryID . "&amp;showEntry=1</guid>
		<author>" . $objPost->Name . "</author>
		<description>" . htmlentities(strip_tags(stripslashes($objPost->ShortDesc),'ENT_QUOTES')) . "</description>
		<link>" . $arrFeed['link'] . "?id=" . $objPost->EntryID . "&amp;showEntry=1</link>
		<pubDate>" . $objPost->PublishDate . "</pubDate>
	</item>";
							break;
						case "comments":
						$xml .= "
	<item>
		<title>" . htmlentities(strip_tags(stripslashes($objPost->Name))) . " - \"" . substr(htmlentities(strip_tags(stripslashes($objPost->Comments),'ENT_QUOTES')),0,47) . (strlen($objPost->Comments) > 47 ? '...' : '') . "\"</title>
		<guid>" . $arrFeed['link'] . "?id=" . $objPost->EntryID . "&amp;showEntry=1#comment" . $objPost->CommentID . "</guid>
		<author>" . $objPost->Name . "</author>
		<description>" . htmlentities(strip_tags(stripslashes($objPost->Comments),'ENT_QUOTES')) . "</description>
		<link>" . $arrFeed['link'] . "?id=" . $objPost->EntryID . "&amp;showEntry=1#comment" . $objPost->CommentID . "</link>
		<pubDate>" . $objPost->PublishDate . "</pubDate>
	</item>";
						break;
					}
				}
			}
			$xml .= "
</channel>

</rss>";

			if (fwrite($handle, $xml) === FALSE) {
				mail_error('The file ' . $file . ' isn\'t writeable','RSS write failure');
				exit;
			}
			fclose($handle);
		}
		else {
			mail_error('The file ' . $file . ' isn\'t writeable','RSS write failure');
			exit;
		}
		print "\n\t-> Feed " . $file . " created Successfully!\n\n";
	}
}//end foreach

function mail_error($bodytext,$subjtext='') {
	$to = 'bj.neilsen@gmail.com';
	$subject = $subjtext == '' ? 'RSS failure' : $subjtext;
	$body = $bodytext . "\n\n" . date('c');
	mail($to,$subject,$body,"From: rss.errors@maryndesign.com\r\n");
}

?>
