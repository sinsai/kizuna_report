<?php mobile_header(); ?>
<?php if (have_posts()) : $i=1; while (have_posts()) : the_post(); ?>

<?php the_date('', '<hr><u>', '</u><br>') ?>
<a href="<?php mobile_permalink() ?>"<?php if($i<7) echo ' accesskey="'.$i.'"'?>><?php if($i<7) echo '('.$i++.')' ?><?php the_title(); ?></a><br>
<?php endwhile; else: ?>
<hr>
<?php _e('Sorry, no posts matched your criteria.'); ?>
<?php endif; ?>

<hr><?php mobile_posts_nav_link('|', '(*)'.__('PREV',$_mb), '(#)'.__('NEXT',$_mb)); ?>

<?php mobile_footer(); ?>