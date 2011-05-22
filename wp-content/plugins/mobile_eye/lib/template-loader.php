<?php

/**
 *
 * テンプレートローダ
 *
 */

// wp_head()
mobile_get_echo('wp_head');

// load template
mobile_load_data('functions');
if (is_menu()) {
	mobile_load_data('menu');
} elseif (is_single() || is_page()) {
	mobile_load_data('entry');
} else {
	mobile_load_data();
}

/********************************************************************************************/

function mobile_header() {
	mobile_load_data('header');
}

function mobile_footer() {
	mobile_load_data('footer');
}

// from comments-template.php [comments_template()]
function mobile_comments( $file = '/comments' ) {
	global $wp_query, $withcomments, $post, $wpdb, $id, $comment, $user_login, $user_ID, $user_identity, $_mb;

	if ( ! (is_single() || is_page() || $withcomments) )
		return;

	$req = get_option('require_name_email');
	$commenter = wp_get_current_commenter();
	extract($commenter);

	if( is_comments() || is_trackbacks() ) {
		// TODO: Use API instead of SELECTs.
		$q = "SELECT * FROM $wpdb->comments WHERE comment_post_ID = '$post->ID' ";
		if (is_comments()) {
			$q .= "AND comment_type = '' ";
		} elseif (is_trackbacks()) {
			$q .= "AND comment_type != '' ";
		}
		if ( empty($comment_author) ) {
			$comments = $wpdb->get_results($q."AND comment_approved = '1' ORDER BY comment_date");
		} else {
			$author_db = $wpdb->escape($comment_author);
			$email_db  = $wpdb->escape($comment_author_email);
			$comments = $wpdb->get_results($q."AND ( comment_approved = '1' OR ( comment_author = '$author_db' AND comment_author_email = '$email_db' AND comment_approved = '0' ) ) ORDER BY comment_date");
		}

		$comments = apply_filters('comments_array', $comments, $post->ID );
	}


	$file = basename($file).'.php';
	$dir = MB_DIR_PATH.'/themes/';

	if ( is_dir($dir.MB_THEME.'/') ) {
		$themeDir = $dir.MB_THEME.'/';
	} else {
		$themeDir = $dir.'default/';
	}

	if ( is_file($themeDir.$file) ) {
		@include_once $themeDir.$file;
	} else {
		if (!@include_once $dir.'default/comments.php') {
			die('Cannot load Theme.');
		}
	}
}

function mobile_load_data($str = 'index') {
	global $posts, $post, $wp_did_header, $wp_did_template_redirect, $wp_query,
		$wp_rewrite, $wpdb, $wp_version, $wp, $id, $comment, $_mb;

	$file = basename($str).'.php';
	$dir = MB_DIR_PATH.'/themes/';

	if ( is_dir($dir.MB_THEME.'/') ) {
		$themeDir = $dir.MB_THEME.'/';
	} else {
		$themeDir = $dir.'default/';
	}

	if ( is_file($themeDir.$file) ) {
		include_once $themeDir.$file;
	} elseif ($str != 'functions') {
		if (!@include_once $dir.'default/'.$file) {
			die('Cannot load Theme.');
		}
	}
}


?>