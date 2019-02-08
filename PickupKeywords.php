<?
/**
 * HTML의 키워드 추출 프로그램
 */
class PickupKeywords{
	private $html = '';
	public $search_tags = 'h1,h2,h3,h4,h5,title,span,div,li,a';
	public $search_metas = 'meta[name="description"],meta[name="keywords"],meta[property="og:title"],meta[property="og:description"]';
	public $conf_scores = array(
		'h1'=>50,
		'h2'=>40,
		'h3'=>30,
		'h4'=>20,
		'h5'=>10,
		'h6'=>10,
		'title'=>100,
		'span'=>10,
		'a'=>20,
		'li'=>10,
		'meta-description'=>90,
		'meta-keywords'=>90,
		'meta-og:title'=>100,
		'meta-og:description'=>90,
	);
	public $min_length = 2;
	public $max_length = 100;
	public function setUrl($url)
	{
		$html = @file_get_contents($url);
		$this->setHTML($html);
	}
	public function setHTML($html){
		$doc = new DOMDocument('1.0','UTF-8');
		libxml_use_internal_errors(true); //에러 감추기
		$doc->loadHTML('<?xml encoding="UTF-8">' .$html);
		// dirty fix
		foreach ($doc->childNodes as $item)
		    if ($item->nodeType == XML_PI_NODE)
		        $doc->removeChild($item); // remove hack
		$doc->encoding = 'UTF-8'; // insert proper
		$this->html = $doc;
	}
	public function split_tags_string($string){
		$matched = array();
		// preg_match_all('/([^#\t\s\n\x00-\x2C\x2E-\x2F\x3A-\x40\x5B-\x5E\x60\x7B-\x7F]{1,30})/u',strtolower($string),$matched);
		preg_match_all('/([^#\t\s\n\x00-\x2C\x2E-\x2F\x3A-\x40\x5B-\x5E\x60\x7B-\x7F]{'.$this->min_length.','.$this->max_length.'})/u',strtolower($string),$matched);
		return isset($matched[1])?array_unique($matched[1]):array();
	}
	public function getMetas(){
		$res = array();
		$nodes = select_elements($this->search_metas, $this->html);
		foreach($nodes as $n){	
			// print_r($n);
			$attributes_name = isset($n['attributes']['name'])?$n['attributes']['name']:'';
			$attributes_property = isset($n['attributes']['property'])?$n['attributes']['property']:'';
			$attributes_content = isset($n['attributes']['content'])?trim($n['attributes']['content']):'';
			
			$tag = 'meta-'.(isset($attributes_name[0])?$attributes_name:$attributes_property);
			if(!isset($tag[0])){continue;}
			if(!isset($attributes_content[0])){continue;}
			$score = isset($this->conf_scores[$tag])?$this->conf_scores[$tag]:1;
			$res[]=array('tag'=>$tag,'text'=>$attributes_content,'score'=>$score);
		}
		return $res;
	}
	public function getTags(){
		$res = array();
		$nodes = select_elements($this->search_tags, $this->html);
		$htags = array('h1','h2','h3','h4','h5');// 의미가 있는것으로 본다.
		foreach($nodes as $n){	
			
			$tag = $n['name'];
			if(!in_array($tag,$htags) && count($n['children'])>0){continue;}
			$text = trim($n['text']);
			if(strlen($text)==0){continue;}
			$score = isset($this->conf_scores[$tag])?$this->conf_scores[$tag]:1;
			$res[]=array('tag'=>$tag,'text'=>$text,'score'=>$score);
		}
		return $res;
	}
	public function getTexts(){
		return array_merge($this->getMetas(),$this->getTags());
	}
	public function getWords($texts){
		$words = array();
		foreach($texts as & $r){
			$score = $r['score'];
			$split_text = $this->split_tags_string($r['text']);
			foreach ($split_text as $k) {
				$k = trim($k);
				if(!isset($words[$k])){
					$words[$k] = array(0,0,$k);
				}
				$words[$k][0]++;
				$words[$k][1]+=$score;
			}
		}
		usort($words,"PickupKeywords_my_sort");
		return $words;
	}
}


// 점수/평균/갯수 로 소팅
function PickupKeywords_my_sort($a,$b){
	$r = $b[1]-$a[1];
	if($r==0){
		$r = $b[1]/$b[0]-$a[1]/$a[0];
		if($r==0){
			$r = $b[0]-$a[0];
		}
	}
	return  $r;
}