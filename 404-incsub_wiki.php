<?php
get_header(); ?>

<div class="row">
  <!-- Content -->
  <div class="<?php echo ThemeGeneral::main_column_css_classes(); ?>" id="main">
    <h2 class="entry-title">Wiki Article Not Found</h2>
    <p><?php _e('The wiki page you are looking for does not exist. Feel free to create it yourself.', $wiki->translation_domain); ?></p>
    <?php echo $wiki->get_new_wiki_form(false); ?>
  </div>


  <!-- Sidebar -->
  <div id="main-sidebar" class="<?php echo ThemeGeneral::right_sidebar_css_classes(); ?>">
    <?php dynamic_sidebar('main-sidebar'); ?>
  </div>
</div>

<?php get_footer(); ?>
