<?
require('PickupKeywords.php');
require('GetAppInfoByPackagename.php');
require('php_selector/selector.inc');

$url = isset($_GET['url'])?$_GET['url']:'http://domeggook.com/main/';
$pkname = isset($_GET['pkname'])?$_GET['pkname']:'com.mins01.othello001';
$mode = isset($_GET['mode'])?$_GET['mode']:'';
$texts = array();
$words = array();
$sh = '';
if($mode =='url' && isset($url[0])){
	// $url = 'https://play.google.com/store/apps/details?id=com.mins01.othello001';
	$pkk = new PickupKeywords();
	$pkk->numeric_multiple = 0.1; //숫자의 가중치 낮춤
	$pkk->setUrl($url);
	$texts = $pkk->getTexts();
	$words = $pkk->getWords($texts);
	$sh = $url;
}else if($mode =='pkname' && isset($pkname[0])){
	// $pkname = 'com.mins01.othello001';
	$gabp = new GetAppInfoByPackagename();
	$gabp->numeric_multiple = 0.1; //숫자의 가중치 낮춤
	$gabp->setPackagename($pkname);
	$texts = $gabp->getTexts();
	$words = $gabp->getWords($texts);
	$sh = $pkname;
}

?>
<!doctype html>
<html lang="ko" >
<head>
	<title>PickupKeywords</title>
	<meta charset="utf-8">
	<meta http-equiv="Content-Script-Type" content="text/javascript">
	<meta http-equiv="Content-Style-Type" content="text/css">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	
	<!-- jquery 관련 -->
	<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" crossorigin="anonymous"></script>  
	
	
	<!-- 부트스트랩 4 : IE8지원안됨! -->
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" crossorigin="anonymous"> 
	<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" crossorigin="anonymous"></script> 
	<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" crossorigin="anonymous"></script>
	<!-- vue.js -->
	<script src="https://cdn.jsdelivr.net/npm/vue"></script>
	
	<!-- meta og -->
	
	<meta property="og:title" content="PickupKeywords">
	<meta property="og:description" content="PickupKeywords">
	<meta name="og:image" content="http://www.mins01.com/img/logo.gif">
	<meta property="og:image:width" content="190">
	<meta property="og:image:height" content="70" />
	<meta property="og:site_name" content="PickupKeywords" />
	<meta property="og:type" content="website">
	
	<!-- //meta og -->
	
	
</head>
<body>
	<div class="container">
		<h1>PickupKeywords</h1>
		<div class="container">
			<ul class="list-group">
				<li class="list-group-item active">조건</li>
				<li class="list-group-item">
					<form action="" method="get" >
						<input type="hidden" name="mode" value="url" />
						<div class="input-group mb-3">
							<div class="input-group-prepend">
								<span class="input-group-text" id="basic-addon1">url</span>
							</div>
							<input type="url" name="url" class="form-control" placeholder="http(s?)://.*" value="<?=htmlspecialchars($url)?>">
							<div class="input-group-append">
								<button class="btn btn-info">get info</button>
							</div>
						</div>
					</form>
				</li>
				<li class="list-group-item">
					<form action="" method="get" >
						<input type="hidden" name="mode" value="pkname" />
						<div class="input-group mb-3">
							<div class="input-group-prepend">
								<span class="input-group-text" id="basic-addon1">Package name</span>
							</div>
							<input type="text" name="pkname" class="form-control" placeholder="com.aaa.app" value="<?=htmlspecialchars($pkname)?>">
							<div class="input-group-append">
								<button class="btn btn-info">get info</button>
							</div>
						</div>
					</form>
				</li>
			</ul>
			<hr />
			<ul class="list-group">
				<li class="list-group-item active">결과 : <?=htmlspecialchars($sh)?></li>
				<li class="list-group-item">
					<h3>words (단어 추출+가중치 부과,TOP10)</h3>
					<table class="table">
						<tr><th>rank</th><th>count</th><th>score</th><th>word</th></tr>
						<?
						 	$i = 1;
							foreach(array_slice($words,0,10) as $r): ?>
							<tr><td><?=$i++?></td><td><?=$r[0]?></td><td><?=$r[1]?></td><td><?=htmlspecialchars($r[2])?></td></tr>
						<? endforeach; ?>
					</table>
					<h3>texts (추출 내용). count : <?=count($texts); ?></h3>
					<table class="table">
						<tr><th>tag</th><th>text</th><th>score</th></tr>
						<?
						 	$i = 1;
							foreach($texts as $r): ?>
							<tr><td><?=htmlspecialchars($r['tag'])?></td><td><?=htmlspecialchars($r['text'])?></td><td><?=htmlspecialchars($r['score'])?></td></tr>
						<? endforeach; ?>
					</table>					
				</li>
			</ul>
		</div>
	</div>
</body>
</html>