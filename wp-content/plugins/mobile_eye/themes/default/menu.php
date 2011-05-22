<?php mobile_header(); ?>
<hr>
<a href="<?php mobile_bloginfo('home'); ?>?view=menu&mode=1#search" accesskey='1'>(1)<?php _e('Search'); ?></a><br>
<a href="<?php mobile_bloginfo('home'); ?>?view=menu&mode=2#last" accesskey='2'>(2)<?php _e('Recent Posts',$_mb); ?></a><br>
<a href="<?php mobile_bloginfo('home'); ?>?view=menu&mode=3#page" accesskey='3'>(3)<?php _e('Pages'); ?></a><br>
<a href="<?php mobile_bloginfo('home'); ?>?view=menu&mode=4#archive" accesskey='4'>(4)<?php _e('Archives'); ?></a><br>
<a href="<?php mobile_bloginfo('home'); ?>?view=menu&mode=5#cat" accesskey='5'>(5)<?php _e('Categories'); ?></a><br>
<?php switch ($_GET['mode']) : case 1:// Search ?>
<hr>
<h2><a name="search"><?php _e('Search'); ?></a></h2>
<form method="GET" action="<?php mobile_bloginfo('home'); ?>">
<input type="text" name="s" value="" />
<input type="submit" value="<?php _e('Search'); ?>" />
</form>

<?php break; case  2: // Recent Posts ?>
<hr>
<h2><a name="last"><?php _e('Recent Posts',$_mb); ?></a></h2>
<ul>
<?php wp_get_archives('type=postbypost&format=custom&before=<li>&after=&limit=10'); ?>
</ul>

<?php break; case  3: // Pages ?>
<hr>
<h2><a name="page"><?php _e('Pages'); ?></a></h2>
<ul>
<?php wp_list_pages('title_li='); ?>
</ul>

<?php break; case  4: // Archives ?>
<hr>
<h2><a name="archive"><?php _e('Archives'); ?></a></h2><ul>
<?php wp_get_archives('type=monthly&show_post_count=true'); ?>
</ul>

<?php break; case  5: // Categories ?>
<hr>
<h2><a name="cat"><?php _e('Categories'); ?></a></h2>
<ul>
<?php wp_list_cats('all=&sort_column=name&optioncount=1'); ?>
</ul>

<?php break; default: // default ?>
<?php break; endswitch; ?>

<?php mobile_footer(); ?>