<?php if (is_trackbacks()) : // trackback ?>
<?php mobile_comments_link(__('Comments (0)'), __('Comments (1)'), __('Comments (%)')); ?> <a href="<?php echo mobile_permalinkAddQuery('view=wrt'); ?>"><?php _e('Leave a comment'); ?></a>

<?php elseif (is_comments()) : // comment ?>
<a href="<?php echo mobile_permalinkAddQuery('view=wrt'); ?>"><?php _e('Leave a comment'); ?></a> <?php mobile_trackbacks_link(__('Trackbacks').'(0)', __('Trackbacks').'(1)', __('Trackbacks').'(%)'); ?>

<?php else : // form ?>
<?php mobile_comments_link(__('Comments (0)'), __('Comments (1)'), __('Comments (%)')); ?> <?php mobile_trackbacks_link(__('Trackbacks').'(0)', __('Trackbacks').'(1)', __('Trackbacks').'(%)'); ?>

<?php endif; ?>

<?php if ( is_comments() || is_trackbacks() ) : // comment or trackback ?>
<hr>
<?php if ( $comments ) : ?>
<?php foreach ($comments as $comment) : ?>
<li>
<?php comment_author_link() ?>@<?php comment_date('y/n/d') ?> <?php comment_time('H:i') ?><br>
<?php mobile_comment_text() ?>
<?php endforeach; /* end for each comment */ ?>

<?php else : ?>
<?php _e('No comments yet.'); ?>
<?php endif; ?>

<?php elseif (is_write()) : // form ?>
<hr>
<?php if ( comments_open() ) : ?>
<h3><?php _e('Leave a comment'); ?></h3>

<form action="<?php mobile_bloginfo('siteurl'); ?>mobile-comments-post.php" method="POST">
<?php _e('Name'); ?><?php if ($req) _e('(required)'); ?>:<br>
<input type="text" name="author" value="<?php echo $comment_author; ?>" size="14" /><br>
<?php _e('E-mail'); ?><?php if ($req) _e('(required)'); ?>:<br>
<input type="text" name="email" value="<?php echo $comment_author_email; ?>" size="14" /><br>
URL:<br>
<input type="text" name="url" value="<?php echo $comment_author_url; ?>" size="14" /><br>
<?php _e('Your Comment'); ?>:<br>
<textarea cols="14" rows="4" name="comment"></textarea><br>
<input type="submit" name="submit" value="<?php _e('Say It!'); ?>" />
<input type="hidden" name="comment_post_ID" value="<?php echo $id; ?>" />
<input type="hidden" name="redirect_to" value="<?php echo mobile_permalinkAddQuery('view=com'); ?>" />
<?php do_action('comment_form', $post->ID); ?>
</form>

<?php else : // Comments are closed ?>
<?php _e('Sorry, the comment form is closed at this time.'); ?>
<?php endif; ?>

<?php endif; ?>