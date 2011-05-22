<?php mobile_header(); ?>
<?php if (have_posts()) : do { the_post(); ?>
<hr>
[<a href="<?php mobile_permalink() ?>"><font color="#008800"><?php the_title(); ?></font></a>]
<?php if (!is_mobilehome()) continue; ?>
<br>
<?php the_content(__('(more...)')) ?>
<?php wp_link_pages() ?>

<?php _e('Author:') ?><?php the_author() ?><br>
<?php _e('Categories:') ?><?php the_category(',') ?><br>
<?php the_date('y/n/d'); ?> <?php the_time() ?><br>

<hr>
<?php mobile_comments_link(__('Comments (0)'), __('Comments (1)'), __('Comments (%)')); ?> <?php mobile_trackbacks_link(__('Trackbacks').'(0)', __('Trackbacks').'(1)', __('Trackbacks').'(%)'); ?>

<?php } while (!is_mobilehome() && have_posts()); else: ?>
<hr>
<?php _e('Sorry, no posts matched your criteria.'); ?>
<?php endif; ?>

<?php if (!is_mobilehome()) : ?>
<hr><?php mobile_posts_nav_link('|', '(*)'.__('PREV',$_mb), '(#)'.__('NEXT',$_mb)); ?>
<?php endif; ?>

<?php mobile_footer(); ?>