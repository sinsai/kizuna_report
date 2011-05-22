<?php
if (basename($_SERVER['SCRIPT_FILENAME']) == 'mobile.php') {
	die();
}

// add filter
add_filter('the_content', 'mobile_filter');
//add_filter('comment_text', 'mobile_filter');//なぜか効かない mobile_comment_text()を使う
add_filter('get_comment_author_link', 'mobile_filter');

/********************************************************************************************/

// パターン修飾子"e"用の関数名の短いstripslashes
function _st($data) {
	return stripslashes($data);
}

// URLのファイル名を返す(クエリは含めない)
function filename($url) {
	$purl = parse_url($url);

	if (isset($purl['path'])) {
		$name = basename($purl['path']);

	} elseif (isset($purl['host'])) {
		$name = $purl['host'];

	} else {
		$name = basename($name);
		if ($pos = strpos($name, '?')) {
			$name = substr($name, 0, $pos);
		}
	}

	return $name;
}

// URLをブログ記事か判別して変換
function mobile_entryURL($url) {
	//memo: get_settings('rewrite_rules');

	// URLGATEを使用しない
	if (defined('MB_URLGATE') && !MB_URLGATE)
		return $url;

	// フラグメントはスルー
	if (@$url[0] == '#') return $url;

	$mobile_url = mobile_get_bloginfo('siteurl');
	$host = $_SERVER['SERVER_NAME'];

	if (preg_match("/^[A-Za-z]+(?:-[A-Za-z]+)?(?=:)/", $url, $match)) {
		// スキームの除外
		$wlist = array('mailto', 'device', 'tel', 'tel-av');
		foreach ($wlist as $scheme) {
			if ($scheme == $match[0])
				return $url;
		}

	} else {
		// 相対パスを絶対URLに
		$prtcl = strtolower(trim(array_shift(split('/', $_SERVER['SERVER_PROTOCOL']))));
		if (strpos($prtcl, 'included') !== false) $prtcl = 'http';// 広告のつく鯖だとINCLUDEDになるらしい
		$url = preg_replace("!^\./!", '', $url);
		if ($url[0] == '/') {
			$url = $prtcl.'://'.$host.$url;
		} else {
			$url = $prtcl.'://'.$host.dirname($_SERVER['PHP_SELF']).'/'.$url;
		}
	}

	$purl = parse_url($url);

	// サイト内リンク
	$path = (strlen($mobile_url)>1) ? substr($mobile_url, 0 ,-1) : '/';
	if ($purl['host'] == $host && strpos($purl['path'], $path) === 0) {

		$qurl = preg_quote(get_settings('siteurl').'/', '/');

		// ブログのリンク(通常)
		if (preg_match("/^".$qurl."(?:index.php)?(?:\?|$)/i", $url)) {
			return preg_replace("|^http://".$purl['host']."|", '', $url);
		}

		// ブログのリンク(mod_rewrite)
		if (get_settings('permalink_structure') !== '') {
			$path = preg_replace("/^".$qurl."/", '', $purl['scheme'].'://'.$purl['host'].$purl['path']);
			if (!@filetype($path)) {
				return preg_replace("|^http://".$purl['host']."|", '', $url);
			}
		}
	}

	// ブログ記事以外はURLGATEに通す
	return $mobile_url.PLUGINDIR.'/mobile_eye/urlgate.php?'.$url;
}

// 携帯用に整形
function mobile_filter($text) {
	global $post, $id, $_mb;

	// 認証フォーム post先の編集
	$pass = $post->post_password;
    if (!empty($pass) && $pass != $_COOKIE['wp-postpass_'.COOKIEHASH]) {//パスの存在確認
		if (mobile_noCookie()) {// クッキーに非対応
			if (mobile_noBasicAuth()) {// BASIC認証にも非対応

				$text = __('[ filter.php No.1 ]', $_mb);

			} else {// BASIC認証
				$text = __('[ filter.php No.2 ]', $_mb).
					'<br><a href="'.mobile_get_permalink().'">'.__('Authenticate', $_mb).'</a><br>'.
					__('[ filter.php No.3 ]', $_mb).'<br><br>';
			}
		} else {// クッキーで認証
			$text = preg_replace("/(<form\s[^>]*?action=([\'\"]))[^>]*?\/wp-pass.php\\2/ie",
					'"\\1".mobile_get_permalink()._st("\\2")',
					$text);
		}
		return $text;
	}

	// mobile_filter_a
	$text = preg_replace_callback("/<a\s[^>]*?href=[^>]*?>.*?<\/a>/si", 'mobile_filter_a', $text);

	// 画像をリンクに
	if (MB_NO_IMG) {

		// mobile_filter_img
		$text = preg_replace_callback("/<img\s[^>]*?src=[^>]*?>/si", 'mobile_filter_img', $text);

	}

	return $text;
}

// <a> tag
function mobile_filter_a($text) {

	// 画像のリンク削除
	if (MB_NO_IMGLINK
	 && preg_match("/^(?:<a\s[^>]*?>)(.*<img\s[^>]*?src=[^>]*?>.*)(?:<\/a>)/si", $text[0], $match)) {
		$text[0] = $match[1];

	// 画像をリンクに変換
	} elseif (MB_NO_IMG) {// mobile_filter_a_text
		$text[0] = preg_replace_callback(
			"/^(<a\s[^>]*?href=[^>]*?>)(.*<img\s[^>]*?src=[^>]*?>.*)(?<=<\/a>)/si",
			'mobile_filter_a_text', $text[0]);
	}

	// URLGATE
	$text[0] = preg_replace(
			"/(?<=href=)([\"\'])(.*?)(?=\\1)/ie",
			'_st("\\1").mobile_entryURL("\\2")',
			$text[0]);

	return $text[0];
}

// <a> tag text (<a>***</a>)
function mobile_filter_a_text($text) {

	global $mobile_name, $_mb;

	preg_match("/href=([\'\"])(.*?)\\1/i", $text[1], $match);
	$mobile_name = filename($match[2]);

	if (MB_IMG_NAME) {// ファイル名

		// $text[1]
		$text[1] .= '[';
		if (preg_match("/^(?>.*)(?<=\.(?:jp[eg]|png|gif|bmp)|\.jpeg)/i", $mobile_name)) {
			$text[1] .= __('IMG:',$_mb);
		}
		$text[1] .= $mobile_name.']';

	} else {// ALT

		// $text[1]
		$text[1] .= '[';
		if (preg_match("/^(?>.*)(?<=\.(?:jp[eg]|png|gif|bmp)|\.jpeg)/i", $mobile_name)) {
			$text[1] .= __('IMG:',$_mb);
		}
		if (preg_match("/\stitle=([\'\"])(.*?)\\1/i", $text[1], $match)
		 && $match[2] !== '') {
			$text[1] .= htmlSpChar($match[2]);
		} else {
			$text[1] .= htmlSpChar($mobile_name);
		}
		$text[1] .= ']';

	}

	// <img> tag 2
	$text[2] = preg_replace_callback("/<img\s[^>]*?src=[^>]*?>/si", 'mobile_filter_img2', $text[2]);

	unset($mobile_name);

	return $text[1].$text[2];
}

// <img> tag
function mobile_filter_img($text) {

	global $_mb;

	// スマイリー画像
	if (MB_USE_SMILY && preg_match("/\sclass=([\'\"])wp-smiley\\1/", $text[0])) {
		 return $text[0];
	}

	$pattern = "/^<img\s[^>]*?src=([\'\"])(.*?)\\1[^>]*>$/sie";
	$replacement = '"<a href="._st("\\1").mobile_entryURL("\\2")._st("\\1").">['.__('IMG:',$_mb).'".';
	if (MB_IMG_NAME) {// ファイル名
		$replacement .= '@filename("\\2")';

	} else {// ALT
		$replacement .= 'htmlSpChar(';
		if (preg_match("/\salt=([\'\"])(.*?)\\1/i", $text[0], $match)
		 && $match[2] !== '') {
			$alt = $match[2];
			$replacement .= '$alt)';
		} else {
			$replacement .= 'filename("\\2"))';
		}

	}
	$replacement .= '."]</a>"';

	return preg_replace($pattern, $replacement, $text[0]);
}

// <img>tag 2 (call mobile_filter_a_text)
function mobile_filter_img2($text) {
	global $mobile_name, $_mb;

	// スマイリー画像
	if (MB_USE_SMILY && preg_match("/\sclass=([\'\"])wp-smiley\\1/", $text[0])) {
		 return $text[0];
	}

	$pattern = "/<img\s[^>]*src=([\'\"])(.*?)\\1[^>]*>/sie";
	$replacement = '"</a><a href="._st("\\1")."\\2"._st("\\1").">[".'.
					'(($mobile_name==str_replace(".thumbnail","",@filename("\\2")))'.
					'? "'.__('Thumb',$_mb).'" : "'.__('IMG:',$_mb).':".';
	if (MB_IMG_NAME) {// ファイル名
		$replacement .= '@filename("\\2"))';
	} else {// ALT
		$replacement .= 'htmlSpChar(';
		if (preg_match("/\salt=([\'\"])(.*?)\\1/i", $text[0], $match)
		 && $match[2] !== '') {
			$alt = $match[2];
			$replacement .= '$alt))';
		} else {
			$replacement .= 'filename("\\2")))';
		}
	}
	$replacement .= '."]"';

	return preg_replace($pattern, $replacement, $text[0]);
}

?>
