			</div><!-- #content-->
		</div><!-- #container-->

    <aside id="left">

<?php	if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar('Sidebar 1') ) : ?>

			<div class="section">
				<h3><div>Categories</div></h3>
				<div class="inner">
					<ul>
						<?php wp_list_categories('show_count=1&title_li='); ?>
					</ul>
				</div>
			</div><!-- .section -->

			<div class="section">
				<h3><div>Tags</div></h3>
      	<div class="inner" id="tagCloud">
					<?php wp_tag_cloud('smallest=8&largest=18&number=100&orderby=name&order=ASC'); ?>
      	</div>
			</div><!-- .section -->

			<div class="section">
				<h3><div>Meta</div></h3>
				<div class="inner">
					<ul>
	          <?php wp_register(); ?>
						<li><?php wp_loginout(); ?></li>
						<?php wp_meta(); ?>
					</ul>
      	</div>
			</div><!-- .section -->

<?php endif; ?>

		</aside><!-- #left -->

		<aside id="right">

<?php	if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar('Sidebar 2') ) : ?>

			<div class="section">
				<h3><div>RSS</div></h3>
				<div class="inner">
					<ul>
						<li><a href="<?php bloginfo('rss2_url'); ?>">RSS Blog</a></li>
						<li><a href="<?php bloginfo('comments_rss2_url'); ?>">RSS Comments</a></li>
					</ul>
      	</div>
			</div><!-- .section -->

			<div class="section">
				<h3><div>Recent Posts</div></h3>
				<div class="inner">
					<ul>
						<?php wp_get_archives('type=postbypost&limit=7'); ?>
					</ul>
      	</div>
			</div><!-- .section -->

			<div class="section">
				<h3><div>Archive</div></h3>
				<div class="inner">
					<ul>
						<?php wp_get_archives('type=monthly'); ?>
					</ul>
      	</div>
			</div><!-- .section -->

			<div class="section">
				<h3><div>Blogroll</div></h3>
				<div class="inner">
					<ul>
						<?php wp_list_bookmarks('categorize=0&title_li='); ?>
					</ul>
      	</div>
			</div><!-- .section -->

<?php endif; ?>

		</aside><!-- #right -->