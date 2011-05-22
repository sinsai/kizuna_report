<?php mobile_header(); ?>
<hr>
<a href="#search" accseskey='1'>(1)<?php _e('Search'); ?></a><br>
<a href="<?php mobile_bloginfo('home'); ?>#last" accesskey='2'>(2)<?php _e('Recent Posts',$_mb); ?></a><br>
<a href="#page" accseskey='3'>(3)<?php _e('Pages'); ?></a><br>
<a href="#archive" accseskey='4'>(4)<?php _e('Archives'); ?></a><br>
<a href="#cat" accseskey='5'>(5)<?php _e('Categories'); ?></a><br>

<hr>
<h2><a name="search"><?php _e('Search'); ?></a></h2>
<form method="GET" action="<?php mobile_bloginfo('home'); ?>">
<input type="text" name="s" value="" />
<input type="submit" value="<?php _e('Search'); ?>" />
</form>

<hr>
<h2><a name="page"><?php _e('Pages'); ?></a></h2><ul>
<?php wp_list_pages('title_li='); ?>
</ul>

<hr>
<h2><a name="archive"><?php _e('Archives'); ?></a></h2><ul>
<?php wp_get_archives('type=monthly&show_post_count=true'); ?>
</ul>

<hr>
<h2><a name="cat"><?php _e('Categories'); ?></a></h2><ul>
<?php wp_list_cats('all=&sort_column=name&optioncount=1'); ?>
</ul>

<?php mobile_footer(); ?>