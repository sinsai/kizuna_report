<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=Shift_JIS">
<title><?php mobile_title(); ?></title>
<do type="accept"><noop /></do>
</head>
<body>
<?php if ($_SERVER['REQUEST_URI'] == mobile_get_bloginfo('home')) { ?>
<h1><?php bloginfo('name'); ?></h1>
<hr>
<?php } ?>

<a href="<?php mobile_bloginfo('home'); ?>?view=menu" accesskey="8">(8)<?php _e('MENU',$_mb); ?></a>|<a href="#_btm" name="_top" accesskey="9">(9)<?php _e('BOTTOM',$_mb); ?></a>
