<?php header('Content-Type: text/html; charset=Shift_JIS'); ?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=Shift_JIS" />
<title>URLGATE</title>
<do type="accept"><noop /></do>
</head>
<body>
<?php
// 初期化
$n = 1;
$url = htmlentities($_SERVER['QUERY_STRING'], ENT_QUOTES, 'Shift_JIS');

// URLをリンクに変換
// url2link(GATE, タイトル, 変換方式)
function url2link($gate, $title, $i=1) {
	global $n, $url;

	if ($gate == '') $n = '#';

	if ($n>0 && $n<=9 || $n == '#') echo $n.'.';
	else echo '･';

	echo '<a href="'.$gate;
	if (preg_match("/^(?>.*)(?<=\=)/", $gate) && $i == 1)
		echo urlencode($url);
	elseif ($i == 2) echo substr($url, strpos($url, '://')+3);
	else echo $url;

	if ($n>0 && $n<=9 || $n == '#')
		echo '" accesskey="'.$n;

	echo '">'.$title.'</a><br>';

	if ($n == '#') unset($n);
	elseif ($n>0 && $n<=9) ++$n;
}


// 処理開始
if (preg_match("@^(?:https?|ftp)://@", $url)) {

	// 画像
	if (preg_match("@^(?>.*)(?<=\.(?:jp[eg]|png|gif|bmp)|\.jpeg)@i", $url)) {

		echo '[画像変換]<br>';
		url2link('http://s'.mt_rand(1,3).'.srea.jp/r.php?url=', '画像ﾘｻｲｽﾞ');
		url2link('http://fileseek.net/getimg.html?', 'ﾌｧｲﾙｼｰｸ');
		url2link('http://pic.to/', 'pic.to', 2);
		echo '<br>';

	// 動画
	} elseif (preg_match(
		"@^(?>.*)(?<=".
			"\.r[am]|".
			"\.(?:3g[2p]|as[fx]|avi|flv|mov|mp[g3]|ram|smi|wa[vx]|wm[av]|wvx)|".
			"\.(?:mpeg|smil)".
		")@i",
		$url)) {

		echo '[動画/音声変換]<br>';
		url2link('http://fileseek.net/getmovie.html?', 'ﾌｧｲﾙｼｰｸ');
		echo '<br>';

	} else {

		// 動画(youtube)
		if (preg_match("@^http://(?:www.)?youtube\.com/watch\?v\=@", $url)) {
			echo '[動画/音声変換]<br>';
			url2link('http://fileseek.net/getmovie.html?', 'ﾌｧｲﾙｼｰｸ');
			echo '<br>';
		}

		// PCページ
		echo '[ﾍﾟｰｼﾞ変換]<br>';
		url2link('http://www.google.com/gwt/n?_gwt_noimg=1&u=', 'Google');
		url2link('http://s'.mt_rand(1,2).'.srea.jp/pc/p.php?', '画像URLGET');
		url2link('http://fileseek.net/proxy.html?', 'ﾌｧｲﾙｼｰｸ');
		url2link('http://www.sjk.co.jp/c/w.exe?y=', '通勤ﾌﾞﾗｳｻﾞ');
		url2link('http://mobazilla.ax-m.jp/?', 'mobazilla');
		echo '<br>';

	}

	echo '[直接]<br>';
	if (@$_SERVER['HTTP_X_UP_DEVCAP_MULTIMEDIA'][9] > 1)
		url2link('device:pcsiteviewer?url=', 'PCｻｲﾄﾋﾞｭｰｱで見る', 0);
	url2link('', 'そのまま');
	echo '<br>';

} elseif (preg_match("@^(?:mms|rtsp)://@", $url)) {
	echo '[動画/音声変換]<br>';
	url2link('http://fileseek.net/getmovie.html?', 'ﾌｧｲﾙｼｰｸ');
	echo '<br>';
}

if (!empty($url)) {

	echo '[ｺﾋﾟｰ](*)<br>';
	echo '<input type="text" value="'.$url.'" accesskey="*">';

} else {
	echo 'BAD REQUEST';
}

?>
<hr><a href="../../../" accesskey="0">0.HOME</a>
<hr><a href="http://hrlk.com/">URLGATE v1.0.4</a>
</body>
</html>