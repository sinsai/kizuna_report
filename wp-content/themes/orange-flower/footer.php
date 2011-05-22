
	</section>

	<footer>
		<nav>
			<a href="<?php bloginfo('url'); ?>">Home</a> |
			<?php
				$list_pages = wp_list_pages('sort_column=menu_order&title_li=&depth=1&echo=0');
				$list_pages = preg_replace('/<\/li>[^>]*<li([^>]*)><a/is', ' | <a$1', $list_pages);
				$list_pages = preg_replace('/<li([^>]*)>/is', '', $list_pages);
				$list_pages = str_replace('</li>', '', $list_pages);
				echo $list_pages;
			?>
		</nav>
		<div id="copyright">
			<span>&copy; 2009 <a href="<?php bloginfo('url'); ?>"><?php bloginfo('name'); ?></a> All rights reserved.</span>
			Powered by WordPress using theme by <a href="http://dimox.net/">Dimox</a>
		</div>
	</footer>

</div><!-- #wrapper -->

<?php wp_footer(); ?>
</body>
</html>
