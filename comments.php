<?php
if (post_password_required()) {
  return;
} ?>

<div id='comments'>
  <?php if (have_comments()) { ?>
    <h2 class='mb-3'>
      <?php
      $comments_count = get_comments_number();
      if ($comments_count === '1') { ?>
        One Reply
      <?php } else { ?>
        <?php echo number_format_i18n($comments_count); ?> Replies
      <?php } ?>
      to &ldquo;<?php the_title(); ?>&rdquo;
    </h2>

    <div class='comment-list'>
      <?php wp_list_comments(array(
        'walker' => new Bootstrap_Comment_Walker(),
        'avatar_size' => 32,
        'style' => 'div',
      )); ?>
    </div>

  <?php } ?>

  <?php comment_form(); ?>
</div>
