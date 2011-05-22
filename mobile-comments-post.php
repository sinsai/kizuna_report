<?php
require( dirname(__FILE__) . '/wp-config.php' );

// error
function mobile_err($mes) {
	header('Content-type: text/plain; charset=Shift_JIS');
	//ob_implicit_flush(0);
	ob_start('mobile_encode');
	die($mes);
}

nocache_headers();

$comment_post_ID = (int) $_POST['comment_post_ID'];

$status = $wpdb->get_row("SELECT post_status, comment_status FROM $wpdb->posts WHERE ID = '$comment_post_ID'");

if ( empty($status->comment_status) ) {
	do_action('comment_id_not_found', $comment_post_ID);
	exit;
} elseif ( 'closed' ==  $status->comment_status ) {
	do_action('comment_closed', $comment_post_ID);
	mobile_err( __('Sorry, comments are closed for this item.') );
} elseif ( 'draft' == $status->post_status ) {
	do_action('comment_on_draft', $comment_post_ID);
	exit;
}


//$mb_flag = (!ini_get('mbstring.encoding_translation'));
//$comment_author       = trim($mb_flag ? mobile_decode($_POST['author']) : $_POST['author']);
//$comment_author_email = trim($_POST['email']);
//$comment_author_url   = trim($_POST['url']);
//$comment_content      = trim($mb_flag ? mobile_decode($_POST['comment']) : $_POST['comment']);

$comment_author       = trim(mobile_decode($_POST['author']));
$comment_author_email = trim($_POST['email']);
$comment_author_url   = trim($_POST['url']);
$comment_content      = trim(mobile_decode($_POST['comment']));

// If the user is logged in
$user = wp_get_current_user();
if ( $user->ID ) :
	$comment_author       = $wpdb->escape($user->display_name);
	$comment_author_email = $wpdb->escape($user->user_email);
	$comment_author_url   = $wpdb->escape($user->user_url);
else :
	if ( get_option('comment_registration') )
		mobile_err( __('Sorry, you must be logged in to post a comment.') );
endif;

$comment_type = '';

if ( get_settings('require_name_email') && !$user->ID ) {
	if ( 6 > strlen($comment_author_email) || '' == $comment_author )
		mobile_err( __('Error: please fill the required fields (name, email).') );
	elseif ( !is_email($comment_author_email))
		mobile_err( __('Error: please enter a valid email address.') );
}

if ( '' == $comment_content )
	mobile_err( __('Error: please type a comment.') );

$commentdata = compact('comment_post_ID', 'comment_author', 'comment_author_email', 'comment_author_url', 'comment_content', 'comment_type', 'user_ID');

$comment_id = wp_new_comment( $commentdata );

if ( !$user->ID ) :
	$comment = get_comment($comment_id);
	setcookie('comment_author_' . COOKIEHASH, $comment->comment_author, time() + 30000000, COOKIEPATH, COOKIE_DOMAIN);
	setcookie('comment_author_email_' . COOKIEHASH, $comment->comment_author_email, time() + 30000000, COOKIEPATH, COOKIE_DOMAIN);
	setcookie('comment_author_url_' . COOKIEHASH, clean_url($comment->comment_author_url), time() + 30000000, COOKIEPATH, COOKIE_DOMAIN);
endif;

$location = ( empty($_POST['redirect_to']) ? get_permalink($comment_post_ID) : $_POST['redirect_to'] ) . '#comment-' . $comment_id;
$location = apply_filters('comment_post_redirect', $location, $comment);

wp_redirect( $location );

?>
