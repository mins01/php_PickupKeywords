<?
require('PickupKeywords.php');
require('GetAppInfoByPackagename.php');
require('php_selector/selector.inc');
//
function split_tags_string($bt_tags_string){
	$matched = array();
	preg_match_all('/([^#\t\s\n\x00-\x2C\x2E-\x2F\x3A-\x40\x5B-\x5E\x60\x7B~\x7F]{1,30})/u',strtolower($bt_tags_string),$matched);
	return isset($matched[1])?array_unique($matched[1]):array();
}

echo "PickupKeywords\n";
$url = 'https://play.google.com/store/apps/details?id=com.mins01.othello001';
$pkk = new PickupKeywords();
$pkk->setUrl($url);
// $pkk->setHTML($html);
$texts = $pkk->getTexts();
$words = $pkk->getWords($texts);
print_r($words[0]);
print_r($words[1]);
print_r($words[2]);

echo "GetAppInfoByPackagename\n";
$pkname = 'com.mins01.othello001';
$gabp = new GetAppInfoByPackagename();
$texts = $gabp->setPackagename($pkname);
// $pkk->setHTML($html);
$texts = $gabp->getTexts();
print_r($texts);
$words = $gabp->getWords($texts);
print_r($words[0]);
print_r($words[1]);
print_r($words[2]);

exit('END');