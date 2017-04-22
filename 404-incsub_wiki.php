<?php
get_header(); ?>

<div class="row">
  <!-- Left Sidebar -->
  <div id="left-sidebar" class="<?php echo theme_left_sidebar_css_classes(); ?>">
    <?php dynamic_sidebar('main-left'); ?>
  </div>


  <!-- Content -->
  <div class="<?php echo theme_main_column_css_classes(); ?>" id="main">
    <h2 class="entry-title">Wiki Article Not Found</h2>
    <p><?php _e('The wiki page you are looking for does not exist. Feel free to create it yourself.', $wiki->translation_domain); ?></p>
    <?php echo $wiki->get_new_wiki_form(false); ?>
  </div>


  <!-- Right Sidebar -->
  <div id="right-sidebar" class="<?php echo theme_right_sidebar_css_classes(); ?>">
    <?php dynamic_sidebar('main-right'); ?>
  </div>
</div>

<?php get_footer(); ?>
