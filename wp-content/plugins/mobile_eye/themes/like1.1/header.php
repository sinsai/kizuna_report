<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=Shift_JIS">
<title><?php mobile_title(); ?></title>
<do type="accept"><noop /></do>
</head>
<body>
<?php if (is_mobilehome()) : ?>
<h1><?php bloginfo('name'); ?></h1>
<hr>
<a href="#last"><?php _e('Recent Posts',$_mb); ?></a> 
<?php endif; ?>

<?php if (!is_menu()) : ?>
<a href="<?php mobile_bloginfo('home'); ?>?view=menu&mode=4#archive"><?php _e('Archives'); ?></a> 
<a href="<?php mobile_bloginfo('home'); ?>?view=menu&mode=1#search"><?php _e('Search'); ?></a> 
<a href="<?php mobile_bloginfo('home'); ?>?view=menu"><?php _e('MENU',$_mb); ?></a>
<?php endif; ?>
