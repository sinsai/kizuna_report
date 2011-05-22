<?php if (is_mobilehome()) { ?>
<hr>
<h2><a name="last"><?php _e('Recent Posts',$_mb); ?></a></h2>
<ul>
<?php mobile_get_recentPosts(10, '<li>', ''); ?>
</ul>
<?php } ?>

<hr>
<a href="<?php mobile_bloginfo('home'); ?>" accesskey="0">(0)HOME</a> 
<hr><?php // credit ?>
<a href="http://xn--65q67bs6i.jp/">Mobile Eye</a><a href="http://hrlk.com/">+ v<?php mobile_bloginfo('plg_version'); ?>(hrlk.com)</a>
</body>
</html>