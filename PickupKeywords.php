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
		'span'=>5,
		'a'=>1,
		'li'=>5,
		'meta-description'=>50,
		'meta-keywords'=>50,
		'meta-og:title'=>100,
		'meta-og:description'=>25,
	);
	public $min_length = 2; //최소 길이
	public $max_length = 100; //최대 길이
	public $numeric_multiple = 1;//숫자에 대한 배수(0이면 숫자는 우선순위가 0이 됨)
	public function getHTML($url){
		if(function_exists('curl_init')){
			$conn = curl_init($url);
			curl_setopt($conn, CURLOPT_FAILONERROR, 1);
			//curl_setopt( $conn, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
			//curl_setopt( $conn, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
			curl_setopt($conn, CURLOPT_HEADER, true); //응답헤더 OFF. ON 할경우 받는 파일에 헤더가 붙음.
			curl_setopt($conn, CURLOPT_RETURNTRANSFER , true); //응답 내용 가져올지 여부. TRUE면 내용을 리턴. FALSE면 직접 브라우저에 출력
			//curl_setopt($conn, CURLOPT_USERAGENT,"Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/37.0.2062.102 Safari/537.36"); //User Agent 설정
			curl_setopt($conn, CURLOPT_CONNECTTIMEOUT,10); //서버 접속시 timeout 설정
			curl_setopt($conn, CURLOPT_TIMEOUT, 10); //서버 접속시 timeout 설정
			//curl_setopt($conn, CURLOPT_TIMEOUT, $timeout); // curl exec 실행시간 timeout 설정
			$data = curl_exec($conn);
			//echo ($data);		exit;
			$split_result = explode("\r\n\r\n", $data, 2);
			return  isset($split_result[1])?$split_result[1]:'';
		}else{
			return file_get_contents($url);
		}
			
	}
	public function setUrl($url)
	{
		$html = $this->getHTML($url);
		$charset = $this->getCharset($html);
		if($charset != 'utf-8'){
			$html = str_ireplace($charset, 'utf-8', $html);
			$html = iconv('euc-kr', 'utf-8//IGNORE', $html);	
		}
		
		$this->setHTML($html);
	}
	public function setHTML($html){
		$doc = new DOMDocument('1.0','UTF-8');
		libxml_use_internal_errors(true); //에러 감추기
		$doc->loadHTML($html);
		$doc->loadHTML('<?xml encoding="UTF-8">' .$html);
		// dirty fix
		foreach ($doc->childNodes as $item)
		    if ($item->nodeType == XML_PI_NODE)
		        $doc->removeChild($item); // remove hack
		$doc->encoding = 'UTF-8'; // insert proper
		$this->html = $doc;
	}
	private function getCharset($html){
		$matched = array();
		preg_match('/(?:(?:charset=["\']?)([^"\',\s\t\n]+)(?:["\',\s\t\n]?))/i',$html,$matched);
		$charset = isset($matched[1])?$matched[1]:'utf-8';
		return strtolower($charset);
	}
	public function split_tags_string($string){
		$matched = array();
		// preg_match_all('/([^#\t\s\n\x00-\x2C\x2E-\x2F\x3A-\x40\x5B-\x5E\x60\x7B-\x7F]{1,30})/u',strtolower($string),$matched);
		preg_match_all('/([^#\t\s\n\x00-\x2C\x2E-\x2F\x3A-\x40\x5B-\x5E\x60\x7B-\x7F]{'.$this->min_length.','.$this->max_length.'})/u',strtolower($string),$matched);
		return isset($matched[1])?array_unique($matched[1]):array();
	}
	public function getMetas(){
		$res = array();
		// print_r($this->html->saveHTML());
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
				if(preg_match('/^\d+$/',$k)){ //숫자로만 이루어져있을 경우
					$words[$k][1]+=($score*$this->numeric_multiple);	
				}else{
					$words[$k][1]+=$score;
				}
				
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