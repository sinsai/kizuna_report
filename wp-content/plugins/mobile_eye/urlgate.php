<?php header('Content-Type: text/html; charset=Shift_JIS'); ?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=Shift_JIS" />
<title>URLGATE</title>
<do type="accept"><noop /></do>
</head>
<body>
<?php
// ������
$n = 1;
$url = htmlentities($_SERVER['QUERY_STRING'], ENT_QUOTES, 'Shift_JIS');

// URL�������N�ɕϊ�
// url2link(GATE, �^�C�g��, �ϊ�����)
function url2link($gate, $title, $i=1) {
	global $n, $url;

	if ($gate == '') $n = '#';

	if ($n>0 && $n<=9 || $n == '#') echo $n.'.';
	else echo '�';

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


// �����J�n
if (preg_match("@^(?:https?|ftp)://@", $url)) {

	// �摜
	if (preg_match("@^(?>.*)(?<=\.(?:jp[eg]|png|gif|bmp)|\.jpeg)@i", $url)) {

		echo '[�摜�ϊ�]<br>';
		url2link('http://s'.mt_rand(1,3).'.srea.jp/r.php?url=', '�摜ػ���');
		url2link('http://fileseek.net/getimg.html?', '̧�ټ��');
		url2link('http://pic.to/', 'pic.to', 2);
		echo '<br>';

	// ����
	} elseif (preg_match(
		"@^(?>.*)(?<=".
			"\.r[am]|".
			"\.(?:3g[2p]|as[fx]|avi|flv|mov|mp[g3]|ram|smi|wa[vx]|wm[av]|wvx)|".
			"\.(?:mpeg|smil)".
		")@i",
		$url)) {

		echo '[����/�����ϊ�]<br>';
		url2link('http://fileseek.net/getmovie.html?', '̧�ټ��');
		echo '<br>';

	} else {

		// ����(youtube)
		if (preg_match("@^http://(?:www.)?youtube\.com/watch\?v\=@", $url)) {
			echo '[����/�����ϊ�]<br>';
			url2link('http://fileseek.net/getmovie.html?', '̧�ټ��');
			echo '<br>';
		}

		// PC�y�[�W
		echo '[�߰�ޕϊ�]<br>';
		url2link('http://www.google.com/gwt/n?_gwt_noimg=1&u=', 'Google');
		url2link('http://s'.mt_rand(1,2).'.srea.jp/pc/p.php?', '�摜URLGET');
		url2link('http://fileseek.net/proxy.html?', '̧�ټ��');
		url2link('http://www.sjk.co.jp/c/w.exe?y=', '�ʋ���׳��');
		url2link('http://mobazilla.ax-m.jp/?', 'mobazilla');
		echo '<br>';

	}

	echo '[����]<br>';
	if (@$_SERVER['HTTP_X_UP_DEVCAP_MULTIMEDIA'][9] > 1)
		url2link('device:pcsiteviewer?url=', 'PC����ޭ���Ō���', 0);
	url2link('', '���̂܂�');
	echo '<br>';

} elseif (preg_match("@^(?:mms|rtsp)://@", $url)) {
	echo '[����/�����ϊ�]<br>';
	url2link('http://fileseek.net/getmovie.html?', '̧�ټ��');
	echo '<br>';
}

if (!empty($url)) {

	echo '[��߰](*)<br>';
	echo '<input type="text" value="'.$url.'" accesskey="*">';

} else {
	echo 'BAD REQUEST';
}

?>
<hr><a href="../../../" accesskey="0">0.HOME</a>
<hr><a href="http://hrlk.com/">URLGATE v1.0.4</a>
</body>
</html>