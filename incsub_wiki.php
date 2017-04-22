<?php
get_header(); ?>

<div class="row">
  <!-- Left Sidebar -->
  <div id="left-sidebar" class="<?php echo theme_left_sidebar_css_classes(); ?>">
    <?php dynamic_sidebar('main-left'); ?>
  </div>


  <!-- Content -->
  <div class="<?php echo theme_main_column_css_classes(); ?>" id="main">
    <h1 class="entry-title"><?php the_title(); ?></h1>
    <?php if (!post_password_required()) { ?>
      <div class="incsub_wiki incsub_wiki_single">
        <div class="incsub_wiki_tabs incsub_wiki_tabs_top"><?php echo $wiki->tabs(); ?><div class="incsub_wiki_clear"></div></div>
      </div>
      <?php
      $revision_id = isset($_REQUEST['revision'])?absint($_REQUEST['revision']):0;
      $left        = isset($_REQUEST['left'])?absint($_REQUEST['left']):0;
      $right       = isset($_REQUEST['right'])?absint($_REQUEST['right']):0;
      $action      = isset($_REQUEST['action'])?$_REQUEST['action']:'view';

      if ($action == 'discussion') {
        comments_template( '', true );
      } else {
        echo $wiki->decider(apply_filters('the_content', $post->post_content), $action, $revision_id, $left, $right, false);
      } ?>
    <?php } ?>
  </div>


  <!-- Right Sidebar -->
  <div id="right-sidebar" class="<?php echo theme_right_sidebar_css_classes(); ?>">
    <?php dynamic_sidebar('main-right'); ?>
  </div>
</div>

<?php get_footer(); ?>
