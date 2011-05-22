<?php
/* ==================================================
 *   Search WP root and load WordPress
   ================================================== */

// Place the path to the WordPress root directory
$wp_root = dirname(dirname(dirname(dirname(__FILE__)))) . '/';
// if WP root is /home/foo/public_html/wordpress and wp-content is moved to /home/foo/public_html/wp-content, $wp_root is below:
//$wp_root = dirname(dirname(dirname(dirname(__FILE__)))) . '/wordpress/';

if (file_exists($wp_root . 'wp-load.php')) {
	require $wp_root . 'wp-load.php';
} elseif (file_exists($wp_root . 'wp-config.php')) {
	require $wp_root . 'wp-config.php';
} else {
	$wpload_error = isset($wpload_error) ? $wpload_error : 'Could not find wp-load.php/wp-config.php because custom WP_PLUGIN_DIR is set.';
	if (isset($wpload_status) && is_int($wpload_status)) {
		echo $wpload_error;
		exit($wpload_status);
	} else {
		exit($wpload_error);
	}
}
?>