<?php get_header(); ?>

<div class="row">
  <!-- Left Sidebar -->
  <div id="left-sidebar" class="<?php echo theme_left_sidebar_css_classes(); ?>">
    <?php dynamic_sidebar('main-left'); ?>
  </div>


  <!-- Content -->
  <div class="<?php echo theme_main_column_css_classes(); ?>">
  <?php
  if (have_posts()) {
    while (have_posts()) {
      the_post(); ?>
      <div class="post">
        <div id="main" class="entry">
          <?php the_content(); ?>
        </div>
      </div>
  <?php }
  } ?>
  </div>

  <!-- Right Sidebar -->
  <div id="right-sidebar" class="<?php echo theme_right_sidebar_css_classes(); ?>">
    <?php dynamic_sidebar('main-right'); ?>
  </div>
</div>

<?php get_footer(); ?>
