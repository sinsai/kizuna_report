<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>
<head profile="http://gmpg.org/xfn/11">
	<meta charset="<?php bloginfo('charset'); ?>" />
	<title><?php if (function_exists('seo_title_tag')) { seo_title_tag(); } else { bloginfo('name'); wp_title();} ?><?php if ( $cpage < 1 ) {} else { echo (' - comment page '); echo ($cpage);} ?></title>
	<link rel="stylesheet" href="<?php bloginfo('stylesheet_url'); ?>" type="text/css" media="screen" />
	<!--[if IE]><script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script><![endif]-->
	<!--[if IE 6]><link rel="stylesheet" href="<?php bloginfo('template_url'); ?>/ie6.css" type="text/css" media="screen" /><![endif]-->
	<link rel="alternate" type="application/rss+xml" title="<?php bloginfo('name'); ?> RSS Feed" href="<?php bloginfo('rss2_url'); ?>" />
	<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />
	<?php if (is_singular() AND comments_open() AND (get_option('thread_comments') == 1)) wp_enqueue_script( 'comment-reply' ); ?>
	<?php wp_head(); ?>
</head>

<body<?php global $config; echo $config['layout']; ?>>

<div id="wrapper">

	<header>
		<h1 id="sitename"><a href="<?php bloginfo('url'); ?>"><?php bloginfo('name'); ?></a></h1>
		<div id="description"><?php bloginfo('description'); ?></div>
		<div id="top">
			<nav>
				<ul>
					<li><a href="<?php bloginfo('url'); ?>/">Home</a></li>
					<?php wp_list_pages('title_li=&depth=1'); ?>
				</ul>
			</nav>
			<ul id="cats">
			  <?php wp_list_categories('show_count=0&title_li=&depth=2'); ?>
			</ul>
		</div>
		<form method="get" action="<?php bloginfo('url'); ?>/" id="search">
			<input type="text" name="s" value="<?php the_search_query(); ?>" class="search" />
			<input type="submit" value="" class="go" />
		</form>
	</header>

	<section<?php echo $config['sidebars']; ?>>

		<div id="container">
			<div id="content">

<?php if (!is_home() || !is_front_page() || is_paged()) { ?>
				<div id="crumbs"><?php if (function_exists('dimox_breadcrumbs')) dimox_breadcrumbs(); ?></div>
<?php }?>