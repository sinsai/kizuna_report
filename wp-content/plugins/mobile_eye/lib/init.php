<?php

// 設定の読み込み
if (!@include_once(MB_DIR_PATH.'/settings.php')) {
	header('Content-Type: text/plain; charset=Shift_JIS');
	die('Cannot read "settings.php".');
}

mb_substitute_character('none');

// URL
$tmp = parse_url(get_settings('siteurl'));
mobile_set_bloginfo('siteurl', @$tmp['path'].'/');
$tmp = parse_url(get_settings('home'));
mobile_set_bloginfo('home', @$tmp['path'].'/');

// WP Version
mobile_set_bloginfo('version', (float)preg_replace('/[^\d.]/', '', get_bloginfo('version')));

// plugin Version
mobile_set_bloginfo('plg_version', MB_EYE_P_VER);


/********************************************************************************************/

// output settting
function mobile_bloginfo($str) {
	echo mobile_get_bloginfo($str);
}

// get settting
function mobile_get_bloginfo($str) {
	global $_mb_bloginfo;

	return @$_mb_bloginfo[$str];
}

// set settting
function mobile_set_bloginfo($str, $data) {
	global $_mb_bloginfo;

	$_mb_bloginfo[$str] = $data;
}


/********************************************************************************************/

function is_mobilehome() {
	return ($_SERVER['REQUEST_URI'] == mobile_get_bloginfo('home'));
}

function is_comments() {
	return ($_GET['view'] == 'com');
}

function is_trackbacks() {
	return ($_GET['view'] == 'tra');
}

function is_write() {
	return ($_GET['view'] == 'wrt');
}

function is_menu() {
	return ($_GET['view'] == 'menu');
}


/********************************************************************************************/

// Cookieが使えないか
function mobile_noCookie() {
	return preg_match("/^(?:DoCoMo|J-PHONE\/[^5]\.)/", $_SERVER['HTTP_USER_AGENT']);
	//DoCoMoとJ-PHONEのTypeWより以前
}

// BASIC認証が使えないか
function mobile_noBasicAuth() {
	return preg_match("/^J-PHONE\/[32]\./", $_SERVER['HTTP_USER_AGENT']);
	// J-PHONEのTypeC
}

// 認証
function mobile_Auth() {
	global $single, $post;

	$pass = $post->post_password;

	// スルー（BASIC認証,Cookie非対応)
	if (!empty($pass) && mobile_noBasicAuth()) {


	// BASIC認証(Cookie非対応)
	} elseif (!empty($pass) && mobile_noCookie()) {
		if ($single && $_SERVER['PHP_AUTH_PW'] != $pass) {
			header('HTTP/1.0 401 Unauthorized');
			header('WWW-Authenticate: Basic realm="Protected Posts"');
		} else {
			$_COOKIE['wp-postpass_'.COOKIEHASH] = $_SERVER['PHP_AUTH_PW'];
		}

	// 認証
	} elseif (isset($_POST['post_password'])) {// from wp-pass.php
		// wp-settings.phpでadd_magic_quotesされてるから無条件で実行(get_magic_quotes_gpc()はしない)
		$_POST['post_password'] = stripslashes($_POST['post_password']);
		setcookie('wp-postpass_'.COOKIEHASH, $_POST['post_password'], time() + 864000, COOKIEPATH);// 10 days

		// 無理やり使う
		$_COOKIE['wp-postpass_'.COOKIEHASH] = $_POST['post_password'];
	}
}

?>