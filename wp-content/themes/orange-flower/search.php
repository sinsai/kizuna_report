<?php get_header(); ?>

	<?php if (have_posts()) : ?>

		<div class="pagetitle">Search Results for &#8216;<span><?php the_search_query(); ?></span>&#8216;</div>

		<?php while (have_posts()) : the_post(); ?>

    <article class="post">
			<h2><a href="<?php the_permalink() ?>" rel="bookmark"><?php the_title(); ?></a></h2>
			<div class="pmeta"><span class="pdate"><?php the_time('F jS, Y') ?></span> <span class="pcat">Posted in <?php the_category(', ') ?></span><?php the_tags(' <span class="ptags">Tags: ', ', ', '</span>'); ?></div>
			<div class="entry">
				<?php the_content('Ream more &raquo;'); ?>
			</div>
			<div class="comments"><?php comments_popup_link('No Comments', '1 Comment', '% Comments'); ?></div>
		</article>

		<?php endwhile; ?>

  	<?php navi(); ?>

	<?php else : ?>

		<h2 class="center">Not Found. Try a different search.</h2>
		<?php include (TEMPLATEPATH . '/searchform.php'); ?>

	<?php endif; ?>

<?php get_sidebar(); ?>
<?php get_footer(); ?>