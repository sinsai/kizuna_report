<?php
/*
Plugin Name: Mobile Eye+
Plugin URI: http://hrlk.com/script/mobile-eye-plus/
Description: This is WordPress template for mobile.Based on <a href="http://www.alexking.org/index.php?content=software/wordpress/content.php#wp_120">WordPress Mobile Edition</a> and <a href="http://phpbb.xwd.jp/viewtopic.php?t=341">Mobile Eye</a>
Author: Maou
Version: 1.2.3
Author URI: http://hrlk.com/
*/
define('MB_EYE_P_VER', '1.2.3');			// Mobile Eye+ Version
define('MB_DIR_PATH', dirname(__FILE__));	// DirPath
$_mb = 'mb_eye_plus';// Language Domain
load_plugin_textdomain($_mb, 'wp-content/plugins/'.basename(MB_DIR_PATH).'/languages');

/********************************************************************************************/

// 携帯端末ならtrue
function is_mobile() {
	$pattern = // 1段目:先頭から含む, 2段目:途中に含む
		"/^(?:DoCoMo|KDDI|SoftBank|Vodafone|J-PHONE|UP\.Browser|MOT-|L-mode|Nokia|PDXGW)|".
		"SHARP\/WS.*?Opera|WILLCOM|DDIPOCKET|Opera Mini/";

	return preg_match($pattern, $_SERVER['HTTP_USER_AGENT']);
}

// 文字コードのエンコードが必要ならtrue
function mobile_ndEncode() {
	return (!preg_match('/S(?:hift_)?JIS/i', get_settings('blog_charset')));
}

// 文字コードをブログデフォルトからShift_JIS変換
function mobile_encode($output) {
	if (mobile_ndEncode() && function_exists('mb_convert_encoding')) {
		return mb_convert_encoding($output, 'SJIS-win', get_settings('blog_charset'));
	} else {
		return $output;
	}
}

// 文字コードをブログデフォルトに変換(オプション画面とコメント投稿用)
function mobile_decode($output) {
	if (mobile_ndEncode() && function_exists('mb_convert_encoding')) {
		return mb_convert_encoding($output, get_settings('blog_charset'), 'auto');
		//return mb_convert_encoding($output, get_settings('blog_charset'), 'SJIS-win');
		//return mb_convert_encoding($output, get_settings('blog_charset'), 'ASCII, JIS, SJIS-win, eucJP-win, UTF-8');
	} else {
		return $output;
	}
}

// 特殊文字を HTML エンティティに変換
function htmlSpChar($str) {
	return htmlspecialchars($str, ENT_QUOTES, get_settings('blog_charset'));
}

/********************************************************************************************/

// 携帯向け表示
if (is_mobile()) {

	// 検索ワードの文字コードをブログデフォルトに変換する
	if (isset($_GET['s']) && mobile_ndEncode() /*&& !ini_get('mbstring.encoding_translation')*/) {
			$_GET['s'] = mobile_decode($_GET['s']);
	}

	require_once MB_DIR_PATH.'/lib/functions.php' ;
	add_action('template_redirect', 'mobile_eye');


// オプション
} elseif (basename($_SERVER['SCRIPT_FILENAME']) != 'index.php') {

	// Add Options
	function mobile_admin_menu() {
		if (function_exists('add_options_page')) {
			require_once MB_DIR_PATH.'/lib/option.php';
			add_options_page('Mobile Eye+ Option', 'MobileEye+', 9, MB_DIR_PATH, 'mobile_options_page');
		}
	}

	// Option
	add_action('admin_menu', 'mobile_admin_menu');

}

?>
