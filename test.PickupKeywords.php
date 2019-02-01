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
// meta 추출
$nodes = select_elements('meta', $html);
foreach($nodes as $n){	
	// print_r($n);
	$attributes_name = isset($n['attributes']['name'])?$n['attributes']['name']:'';
	$attributes_property = isset($n['attributes']['property'])?$n['attributes']['property']:'';
	$attributes_content = isset($n['attributes']['content'])?$n['attributes']['content']:'';
	$tag = isset($attributes_name[0])?$attributes_name:$attributes_property;
	
	if(!isset($tag[0])){
		continue;
	}
	if(!preg_match('/(description|title)/',$tag)){
		continue;
	}
	$text = $attributes_content;
	$res[]=array('tag'=>$tag,'text'=>$text);
}
// tag 추출
$nodes = select_elements('h1,h2,h3,h4,h5,title,span,div,li,a', $html);
foreach($nodes as $n){	
	if(count($n['children'])>0){continue;}
	$res[]=array('tag'=>$n['name'],'text'=>trim($n['text']));
}




// 점수 측정
$conf_scores = array(
	'h1'=>50,
	'h2'=>40,
	'h3'=>30,
	'h4'=>20,
	'h5'=>10,
	'h6'=>10,
	'title'=>100,
	'description'=>90,
	'span'=>10,
	'a'=>20,
	'li'=>10,
	// 'og:title'=>100,
	// 'og:description'=>90,
);
foreach($res as & $r){
	$r['score'] = isset($conf_scores[$r['tag']])?$conf_scores[$r['tag']]:1;
}
print_r($res);
// 문장 나누기
$words = array();
foreach($res as & $r){
	$score = $r['score'];
	$split_text = split_tags_string($r['text']);
	foreach ($split_text as $k) {
		if(!isset($words[$k])){
			$words[$k] = array(0,0,$k);
		}
		$words[$k][0]++;
		$words[$k][1]+=$score;
	}
}
// 점수/평균/갯수 로 소팅
function my_sort($a,$b){
	$r = $a[1]-$b[1];
	if($r==0){
		$r = $a[1]/$a[0]-$b[1]/$b[0];
		if($r==0){
			$r = $a[0]-$b[0];
		}
	}
	return  $r*-1;
}
usort($words,"my_sort");
print_r($words);
exit('END');