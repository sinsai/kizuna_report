<?php get_header(); ?>

	<?php if (have_posts()) : while (have_posts()) : the_post(); ?>

		<article class="post">
			<h2><?php the_title(); ?></h2>
			<div class="entry">
				<?php the_content(); ?>
			</div>
			<?php wp_link_pages(array('before' => '<p><strong>Pages:</strong> ', 'after' => '</p>', 'next_or_number' => 'number')); ?>
			<?php edit_post_link('Edit', '<p>', '</p>'); ?>
		</article>

		<?php comments_template(); ?>

	<?php endwhile; endif; ?>

<?php get_sidebar(); ?>
<?php get_footer(); ?>