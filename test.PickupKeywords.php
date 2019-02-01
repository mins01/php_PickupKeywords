<?
require('PickupKeywords.php');
require('php_selector/selector.inc');
//
function split_tags_string($bt_tags_string){
	$matched = array();
	preg_match_all('/([^#\t\s\n\x00-\x2C\x2E-\x2F\x3A-\x40\x5B-\x5E\x60\x7B~\x7F]{1,30})/u',strtolower($bt_tags_string),$matched);
	return isset($matched[1])?array_unique($matched[1]):array();
}
// HTML 
$html = file_get_contents('test.html');
$res = array();

$pkk = new PickupKeywords();
$pkk->setHtml($html);
$texts = $pkk->getTexts();//메타와 태그 기준으로 문장(texts) 추출
$words = $pkk->getWords($texts); //texts 에서 단어 추출(점수 포함)
// print_r($texts); 
print_r(array_splice($words,0,10));
// print_r($words);
exit;
exit('END');