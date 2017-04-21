<?php

class Bootstrap_Comment_Walker extends Walker_Comment {
  protected function html5_comment($comment, $depth, $args) {
    $tag = ($args['style'] === 'div') ? 'div' : 'li'; ?>
    <<?php echo $tag; ?> id='comment-<?php comment_ID(); ?>' <?php comment_class($this->has_children ? 'parent media' : 'media'); ?>>
      <?php if ($args['avatar_size'] != 0) { ?>
        <a class='d-flex mr-2' href='<?php comment_author_url() ?>'>
          <?php echo get_avatar($comment, $args['avatar_size']); ?>
        </a>
      <?php } ?>
      <div class='media-body' id='div-comment-<?php comment_ID(); ?>'>
        <h5 class='mt-0 mb-1'><strong><?php comment_author_link(); ?></strong></h5>
        <div><small>
          <a href='<?php comment_link(comment_ID(), $args); ?>' class='text-muted'>
            <time datetime='<?php comment_time('c'); ?>'>
              <?php comment_date(); ?> at <?php comment_time(); ?>
            </time>
          </a>
        </small></div>
        <div class="comment-content">
          <?php comment_text(); ?>
        </div>

        <ul class='list-inline' id='comment-links-<?php comment_ID(); ?>'>
          <?php
          edit_comment_link('Edit', '<li class="edit-link list-inline-item">', '</li>');
          comment_reply_link(array_merge($args, array(
            'add_below' => 'comment-links',
            'depth' => $depth,
            'max_depth' => $args['max_depth'],
            'before' => '<li class="reply-link list-inline-item">',
            'after' => '</li>',
          ))); ?>
        </ul>
      <?php // .media & .media-body tags are closed in end_el() ?>
  <?php }

  public function end_el(&$output, $comment, $depth = 0, $args = array()) {
    $output .= "</div>";
    parent::end_el($output, $comment, $depth, $args);
  }
}
?>
