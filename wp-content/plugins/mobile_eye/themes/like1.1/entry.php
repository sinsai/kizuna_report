<?php mobile_header() ?>

<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
<?php if (isset($_GET['view'])) : ?>
<hr>
[<a href="<?php mobile_permalink() ?>"><font color="#008800"><?php the_title(); ?></font></a>]<br>
<?php mobile_comments() ?>
<?php break; endif; ?>

<hr>
[<font color="#008800"><?php the_title() ?></font>]<br>
<?php the_content(__('(more...)')) ?>
<?php wp_link_pages() ?>

<?php _e('Author:') ?><?php the_author() ?><br>
<?php _e('Categories:') ?><?php the_category(',') ?><br>
<?php the_date('y/n/d'); ?> <?php the_time() ?><br>

<hr>
<?php mobile_comments() ?>

<?php if (is_single()) : ?>
<hr>
<?php mobile_previous_post('%<br>', '(*)'.__('PREV',$_mb).':') ?>
<?php mobile_next_post('%', '(#)'.__('NEXT',$_mb).':') ?>
<?php endif; ?>

<?php endwhile; else: ?>
<p><?php _e('Sorry, no posts matched your criteria.') ?></p>
<?php endif; ?>

<?php mobile_footer() ?>