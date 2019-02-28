<?
/**
 * PickupKeywords 를 기반으로 패키지명으로 setUrl이 동작 되도록 되어있음.
 */
class GetAppInfoByPackagename extends PickupKeywords{
	public $search_tags = 'title';
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
	public function setPackagename($packagename)
	{
		$this->setUrl('https://play.google.com/store/apps/details?id='.urlencode($packagename));
	}
	public function getHTML($url){
		return str_replace('Apps on Google Play', '', parent::getHTML($url)); //불필요 단어 삭제
	}
}