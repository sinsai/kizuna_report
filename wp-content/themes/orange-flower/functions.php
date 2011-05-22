<?php
remove_action('wp_head', 'wp_generator');
remove_action('wp_head', 'wlwmanifest_link');
remove_action('wp_head', 'rsd_link');


add_filter('comments_template', 'legacy_comments');
function legacy_comments($file) {
	if(!function_exists('wp_list_comments')) : // WP 2.7-only check
		$file = TEMPLATEPATH . '/comments-old.php';
	endif;
	return $file;
}


function dimox_breadcrumbs() {

	$delimiter = '<span>&raquo;</span>';
	$name = 'Home';

	if ( !is_home() || !is_front_page() || is_paged() ) {
		global $post;
		$home = get_bloginfo('url');
		echo '<a href="' . $home . '" class="home">' . $name . '</a> ' . $delimiter . ' ';

		if ( is_category() ) {
			global $wp_query;
			$cat_obj = $wp_query->get_queried_object();
		  $thisCat = $cat_obj->term_id;
			$thisCat = get_category($thisCat);
			$parentCat = get_category($thisCat->parent);
			if ($thisCat->parent != 0) echo(get_category_parents($parentCat, TRUE, ' ' . $delimiter . ' '));
			echo 'Archive by category &#39;';
			single_cat_title();
			echo '&#39;';

		} elseif ( is_day() ) {
    	echo '<a href="' . get_year_link(get_the_time('Y')) . '">' . get_the_time('Y') . '</a> ' . $delimiter . ' ';
    	echo '<a href="' . get_month_link(get_the_time('Y'),get_the_time('m')) . '">' . get_the_time('F') . '</a> ' . $delimiter . ' ';
    	echo get_the_time('d');

		} elseif ( is_month() ) {
    	echo '<a href="' . get_year_link(get_the_time('Y')) . '">' . get_the_time('Y') . '</a> ' . $delimiter . ' ';
    	echo get_the_time('F');

		} elseif ( is_year() ) {
    	echo get_the_time('Y');

		} elseif ( is_single() ) {
			$cat = get_the_category(); $cat = $cat[0];
			echo get_category_parents($cat, TRUE, ' ' . $delimiter . ' ');
			the_title();

		} elseif ( is_page() && !$post->post_parent ) {
			the_title();

		} elseif ( is_page() && $post->post_parent ) {
			$parent_id  = $post->post_parent;
			$breadcrumbs = array();
			while ($parent_id) {
				$page = get_page($parent_id);
				$breadcrumbs[] = '<a href="' . get_permalink($page->ID) . '">' . get_the_title($page->ID) . '</a>';
				$parent_id  = $page->post_parent;
			}
			$breadcrumbs = array_reverse($breadcrumbs);
			foreach ($breadcrumbs as $crumb) echo $crumb . ' ' . $delimiter . ' ';
			the_title();

		} elseif ( is_search() ) {
			echo 'Search results for &#39;' . get_search_query() . '&#39;';

		} elseif ( is_tag() ) {
			echo 'Posts tagged &#39;';
			single_tag_title();
			echo '&#39;';

		} elseif ( is_author() ) {
	 		global $author;
			$userdata = get_userdata($author);
			echo 'Articles posted by ' . $userdata->display_name;
		}

		if ( get_query_var('paged') ) {
			if ( is_category() || is_day() || is_month() || is_year() || is_search() || is_tag() || is_author() ) echo ' (';
			echo __('Page') . ' ' . get_query_var('paged');
			if ( is_category() || is_day() || is_month() || is_year() || is_search() || is_tag() || is_author() ) echo ')';
		}

	}
}


if ( function_exists('register_sidebar') )
    register_sidebar(array(
        'name' => 'Sidebar 1',
        'before_widget' => '<div class="section %2$s">',
        'after_widget' => '</div></div><!-- .section -->

				',
        'before_title' => '
				<h3><div>',
        'after_title' => '</div></h3><div class="inner">',
    ));

if ( function_exists('register_sidebar') )
    register_sidebar(array(
        'name' => 'Sidebar 2',
        'before_widget' => '<div class="section %2$s">',
        'after_widget' => '</div></div><!-- .section -->

				',
        'before_title' => '
				<h3><div>',
        'after_title' => '</div></h3><div class="inner">',
    ));


function navi() {

	global $wp_query;
	$max_page = $wp_query->max_num_pages;
	$nump=6;

	if($max_page > 1) echo '<div class="navigation">';

	if ($max_page!=1) {
		$paged = intval(get_query_var('paged'));
		if(empty($paged) || $paged == 0) $paged = 1;

		if($paged!=1)echo '<a href="'.get_pagenum_link($paged-1).'" class="prev">Pre</a>';

		if($paged!=1) echo '<a href="'.get_pagenum_link(1).'">1</a>';
		else echo '<span class="current">1</span>';

		if($paged-$nump>1) $start=$paged-$nump; else $start=2;
		if($paged+$nump<$max_page) $end=$paged+$nump; else $end=$max_page-1;

		if($start>2) echo " ... ";

		for ($i=$start;$i<=$end;$i++) {
			$zero = '';
			if($paged!=$i) echo '<a href="'.get_pagenum_link($i).'">'.$i.'</a>';
			else echo '<span class="current">'.$i.'</span>';
		}

		if($end<$max_page-1) echo " ... ";

		if($paged!=$max_page) echo '<a href="'.get_pagenum_link($paged+1).'" class="next">Next</a>';

		if($paged!=$max_page) echo '<a href="'.get_pagenum_link($max_page).'" class="last">Last</a>';
			else echo '<span class="current">'.$max_page.'</span>';
	}

	if($max_page > 1) echo '</div>';

}


function mytheme_comment($comment, $args, $depth) {
   $GLOBALS['comment'] = $comment; ?>
<li <?php comment_class(); ?> id="comment-<?php comment_ID() ?>">
	<div id="div-comment-<?php comment_ID(); ?>" class="commentdiv">
		<div class="comment-author vcard">
			<?php echo get_avatar($comment, $size='32'); ?>
			<?php printf(__('<cite class="fn">%s</cite> <span class="says">says:</span>'), get_comment_author_link()) ?>
			<div class="commentmetadata"><a href="<?php echo htmlspecialchars( get_comment_link( $comment->comment_ID ) ) ?>"><?php printf(__('%1$s at %2$s'), get_comment_date(),  get_comment_time()) ?></a><?php edit_comment_link(__('(Edit)'),'&nbsp;&nbsp;','') ?></div>
		</div>
		<div class="ctext">
			<?php if ($comment->comment_approved == '0') : ?>
			<em><?php _e('Your comment is awaiting moderation.') ?></em>
			<?php endif; ?>
			<?php comment_text() ?>
		</div>
		<div class="reply">
			<?php comment_reply_link(array_merge( $args, array('add_below' => 'div-comment', 'depth' => $depth, 'max_depth' => $args['max_depth']))) ?>
		</div>
	</div>
<?php
}



/* ===== THEME OPTIONS ===== */

$config['layout'] = ' id="' . get_option('layoutPosition') . '"';
if (get_option('layoutPosition') == 'lpLeft') $config['layout'] = '';

$config['sidebars'] = ' id="' . get_option('sidebarsPosition') . '"';
if (get_option('sidebarsPosition') == 'lcr') $config['sidebars'] = '';


load_theme_textdomain('orange-flower');

add_action('admin_menu', 'ofl_settings');

function ofl_options() {
	if(isset($_POST['submitted']) && $_POST['submitted'] == "yes") {

		$layoutPosition = $_POST['layoutPosition'];
		$sidebarsPosition = $_POST['sidebarsPosition'];

		update_option("layoutPosition", $layoutPosition);
		update_option("sidebarsPosition", $sidebarsPosition);

		echo "<div id=\"message\" class=\"updated fade\"><p><strong>Options saved!</strong></p></div>";
	}

?>

<div class="wrap">

	<form method="post" name="ofl_options" target="_self">
		<h2>Orange Flower Theme Options</h2>
		<table class="form-table">
			<tr valign="top">
				<th scope="row" style="width: 220px">Main layout position:</th>
				<td>
					<select name="layoutPosition">
						<option value="lpLeft"<?php if(get_option('layoutPosition') == "lpLeft") { echo ' selected="selected"'; } ?>>Left</option>
						<option value="lpCenter"<?php if(get_option('layoutPosition') == "lpCenter") { echo ' selected="selected"'; } ?>>Center</option>
						<option value="lpRight"<?php if(get_option('layoutPosition') == "lpRight") { echo ' selected="selected"'; } ?>>Right</option>
					</select>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">Sidebars position:</th>
				<td>
					<select name="sidebarsPosition">
						<option value="lcr"<?php if(get_option('sidebarsPosition') == "lcr") { echo ' selected="selected"'; } ?>>Sidebar1 - Content - Sidebar2</option>
						<option value="lrc"<?php if(get_option('sidebarsPosition') == "lrc") { echo ' selected="selected"'; } ?>>Sidebar1 - Sidebar2 - Content</option>
						<option value="clr"<?php if(get_option('sidebarsPosition') == "clr") { echo ' selected="selected"'; } ?>>Content - Sidebar1 - Sidebar2</option>
					</select>
				</td>
			</tr>
		</table>

		<p class="submit">
			<input name="submitted" type="hidden" value="yes" />
			<input type="submit" name="Submit" value="Update" />
		</p>
	</form>

	<div style="text-align:center;">
		<p>&copy; <?php echo date('Y'); ?> <a href="http://dimox.net" target="_blank">Dimox</a> | <a href="http://wordpress.org/extend/themes/orange-flower" target="_blank">Orange Flower Theme</a></p>
	</div>

</div>

<?php
}

function ofl_settings() {
	add_submenu_page('themes.php', 'Orange Flower Theme Options', 'Orange Flower Theme Options', 'edit_themes', __FILE__, 'ofl_options');
}


?>