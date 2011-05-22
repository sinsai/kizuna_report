<?php
require_once MB_DIR_PATH.'/lib/init.php';
require_once MB_DIR_PATH.'/lib/filter.php';

/********************************************************************************************/

// start Mobile Eye Plus
function mobile_eye() {

	mobile_Auth();
	header('Content-Type: text/html; charset=Shift_JIS');
	//ob_implicit_flush(0);
	ob_start('mobile_output_handler');
	require_once MB_DIR_PATH.'/lib/template-loader.php';
	ob_end_flush();
	die();

}

function mobile_output_handler($output) {

	// 単純なタグ整形
	$output = str_replace(
		array('<br />',  '</p>',   '<strong>','</strong>', '<code>', '</code>','</li>','<p>'),
		array('<br>',   '<br><br>',  '<u>',     '</u>',      '',        '',      '',     ''),
		$output);

	// 省パケ
	if (MB_CUT_PACKE) {
		// 全角英数字カナを半角に
		if (function_exists('mb_convert_kana')) {
			$output = mb_convert_kana($output, 'askV', get_settings('blog_charset'));
		}
		// 改行コード/インデント/連続する空白,線,改行/コメントアウトの削除
		$output = str_replace(array("\r","\n","\t"), '', $output);
		$output = preg_replace(
					array("/(\s|<hr>)\\1+/i", "/<br><br>(?:<br>)+/i", "/<!--.*?-->/i",),
					array(      '\\1',            '<br><br>',                ''),
					$output
				);
	}

	return mobile_encode($output);
}

// 他の関数の出力返す
function mobile_get_echo() {
	if (!($nargs = func_num_args())) {
		return 'not found arguments';
	}
	$larg = func_get_args();
	if (!function_exists($larg[0])) {
		return '"'.htmlSpChar($larg[0]).'" is not defined';
	}
	$args = '';
	if ($nargs > 1) {
		$i = 1;
		while ($i < $nargs) {
			$args .= '$larg['.$i.']';
			if(++$i != $nargs) $args .= ', ';
		}
	}
	ob_start();
	eval($larg[0].'('.$args.');');
	$contents = ob_get_contents();
	ob_end_clean();
	return $contents;
}

/********************************************************************************************/

// Cache
$_mb_title = '';//mobile_title()


function mobile_title() {
	global $_mb_title, $year, $monthnum, $wp_locale, $month, $m, $_mb;

	if ($_mb_title) {
		echo $_mb_title;
		return ;
	}

	if (is_home()) {
		switch ($_GET['view']) {
		case 'menu' : $title = __('MENU',$_mb); break;
		default     : $title = get_settings('blogname');
		}
	} elseif (is_single() || is_page()) {
		$title = the_title('', '', 0);
		if ($_GET['view']) $title .= ' - ';
		switch ($_GET['view']) {
		case 'com' : $title .= __('Comments');        break;
		case 'tra' : $title .= __('Trackbacks');      break;
		case 'wrt' : $title .= __('Leave a comment'); break;
		}
	} elseif (is_day()) {
		$title  = get_the_time(get_settings('date_format'));
	} elseif (is_month()) {
		if (mobile_get_bloginfo('version') > 2) {
			$title  = $year.' ';
			$title .= $wp_locale->get_month($monthnum);
		} else {
			$title  = substr($m, 0, 4).' ';
			$title .= $month[substr($m, 4, 2)];
		}
	} elseif (is_year()) {
		$title  = $year ? $year : $m;
	} elseif (is_search()) {
		$title  = __('Search');
		$title .= '&quot;'.htmlSpChar(stripslashes($_GET['s'])).'&quot;';
	} elseif(is_category()) {
		$title  = __('Categories');
		$title .= '&quot;'.single_cat_title('', 0).'&quot;';
	} else {
		$title = get_settings('blogname');
	}
	echo $_mb_title = $title;
}

function mobile_get_permalink($uid = 0) {
	global $id, $post, $tableposts, $wpdb;

	if (MB_PERMALINK) {
		$purl = parse_url(get_settings('home').'/');
		return preg_replace("|^http://".$purl['host']."|", '', get_permalink($uid));
	}

	$_id = 0;
	$uid = (int)$uid;

	if ($uid > 0) {
		if ($post->ID == $uid) {
			$_id  = $uid;
			$type = $post;
		} else {
			$_id   = $uid;
			$query = "SELECT post_type, post_status FROM ".$tableposts." "
					."WHERE post_date < '".date('Y-m-d H:i:s')."' "
					."AND ID = ".$uid;
			$type  = $wpdb->get_results($query);
		}
	} elseif ($post->ID == $id) {
		$_id  = $id;
		$type = $post;
	} elseif (is_numeric($post->ID)) {
		$_id  = $post->ID;
		$type = $post;
	} elseif (is_numeric($id)) {
		$_id   = $id;
		$query = "SELECT post_type, post_status FROM ".$tableposts." "
				."WHERE post_date < '".date('Y-m-d H:i:s')."' "
				."AND ID = ".$id;
		$type  = $wpdb->get_results($query);
	}

	if ($_id) {
		return mobile_get_bloginfo('home').'?'.
			((mobile_get_bloginfo('version') > 2) ?
			(($type->post_type   == 'page'  ) ? 'page_id' : 'p')
			:
			(($type->post_status == 'static') ? 'page_id' : 'p')
			).'='.$_id;
	}

	return mobile_get_bloginfo('home');
}

function mobile_permalink($uid = 0) {
	echo mobile_get_permalink($uid);
}


function mobile_permalinkAddQuery($q = '') {
	$url  = mobile_get_permalink();
	$purl = parse_url($url);
	if (isset($purl['query'])) {
		return $url.'&'.$q;
	} else {
		return $url.'?'.$q;
	}
}


function mobile_comment_text() {
	echo mobile_filter(mobile_get_echo('comment_text'));
}

/********************************************************************************************/

// Cache
$_mb_ctp = array();//mobile_get_ctp_number()
$_mb_ctp_err = array();// mobile_comments_popup_link(), mobile_trackbacks_popup_link()


function mobile_get_ctp_number($post_id, $mode = '')
{
	global $wpdb, $postdata, $tablecomments, $_mb_ctp;
	if (!isset($_mb_ctp[$post_id])) {
		$post_id = (int)$post_id;
		$query = 'SELECT comment_type FROM '.$tablecomments.' '
				.'WHERE comment_post_ID = '.$post_id.' '
				."AND comment_approved = '1'";
		$result = $wpdb->get_results($query);
		$ctp_number = array(0,0,0,0);
		foreach ($result as $row) {
			switch (@$row->comment_type[0]) {
			case 't': ++$ctp_number[2]; break;
			case 'p': ++$ctp_number[3]; break;
			default : ++$ctp_number[1]; break;
			}
			++$ctp_number[0];
		}
		$_mb_ctp[$post_id] = $ctp_number;
	} else {
		$ctp_number = $_mb_ctp[$post_id];
	}
	switch ($mode) {
	case 'all'       : return $ctp_number[0];
	case 'comments'  : return $ctp_number[1];
	case 'trackbacks': return $ctp_number[2];
	case 'pingbacks' : return $ctp_number[3];
	default          : return $ctp_number;
	}
}

function mobile_ctp_number( $zero = false, $one = false, $more = false, $number = 0 ) {

	if ( $number > 1 )
		$output = str_replace('%', $number, ( false === $more ) ? __('% Comments') : $more);
	elseif ( $number == 0 )
		$output = ( false === $zero ) ? __('No Comments') : $zero;
	else // must be one
		$output = ( false === $one ) ? __('1 Comment') : $one;

	echo apply_filters('comments_number', $output, $number);
}

function mobile_comments_link($zero='No Comments', $one='1 Comment', $more='% Comments', $none='Comments Off')
{
	global $id, $wpcommentspopupfile, $post, $wpdb, $_mb_ctp_err;


	if ($post->comment_count > 0) {
		$number = mobile_get_ctp_number($id, 'comments');
	} else {
		$number = 0;
	}

	if ( 0 == $number && 'closed' == $post->comment_status) {
		echo $none;
		return;
	}

	if ( !empty($post->post_password) ) { // if there's a password
		if ($_COOKIE['wp-postpass_'.COOKIEHASH] != $post->post_password) {  // and it doesn't match the cookie
			if (!@$_mb_ctp_err[$id]) echo(__('Enter your password to view comments'));
			$_mb_ctp_err[$id] = 1;
			return;
		}
	}

	echo '<a href="'.mobile_permalinkAddQuery('view=com').'">';
	mobile_ctp_number($zero, $one, $more, $number);
	echo '</a>';

}

function mobile_trackbacks_link($zero='No Trackbacks', $one='1 Trackback', $more='% Trackbacks', $none='Trackbacks Off')
{
	global $id, $wpcommentspopupfile, $post, $wpdb, $_mb_ctp_err;

	if ($post->comment_count > 0) {
		$ctp    = mobile_get_ctp_number($id);
		$number = $ctp[0]-$ctp[1];
	} else {
		$number = 0;
	}

	if ( 0 == $number && 'closed' == $post->ping_status ) {
		echo $none;
		return;
	}

	if ( !empty($post->post_password) ) { // if there's a password
		if ($_COOKIE['wp-postpass_'.COOKIEHASH] != $post->post_password) {  // and it doesn't match the cookie
			if (!@$_mb_ctp_err[$id]) echo(__('Enter your password to view comments'));
			$_mb_ctp_err[$id] = 1;
			return;
		}
	}

	echo '<a href="'.mobile_permalinkAddQuery('view=tra').'">';
	mobile_ctp_number($zero, $one, $more, $number);
	echo '</a>';
}


/********************************************************************************************/


function mobile_addAccesskey($str, $key='#') {
	// <a> tag
	$str = preg_replace_callback("/<a\s[^>]*?href=[^>]*?>.*?<\/a>/si", 'mobile_filter_a', $str);

	if (is_array($key)) {
		$i = 0;
		$n = count($key);
		return preg_replace("/(?><)a\s/ei", '\'$0accesskey="\'.$key[(($i<$n)?$i++:$n)].\'" \'', $str);
	}
	return preg_replace("/(?><)a\s/i", '$0accesskey="'.$key.'" ', $str);
}

function mobile_previous_post($format='%', $previous='previous post: ', $title='yes', $in_same_cat='no', $limitprev=1, $excluded_categories='') {
	echo mobile_addAccesskey(
			mobile_get_echo('previous_post', $format, $previous, $title, $in_same_cat, $limitprev, $excluded_categories),
			'*'
		);
}

function mobile_next_post($format='%', $next='next post: ', $title='yes', $in_same_cat='no', $limitnext=1, $excluded_categories='') {
	echo mobile_addAccesskey(
		mobile_get_echo('next_post', $format, $next, $title, $in_same_cat, $limitnext, $excluded_categories),
		'#'
	);
}

function mobile_posts_nav_link($sep=' &#8212; ', $prelabel='&laquo; Previous Page', $nxtlabel='Next Page &raquo;') {
	global $paged;
	echo mobile_addAccesskey(
			mobile_get_echo('posts_nav_link', $sep, $prelabel, $nxtlabel),
			($paged > 1) ? array('*', '#') : '#'
		);
}

?>