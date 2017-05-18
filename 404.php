<?php get_header(); ?>

<div class="row">
  <!-- Left Sidebar -->
  <div id="left-sidebar" class="<?php echo ThemeGeneral::left_sidebar_css_classes(); ?>">
    <?php dynamic_sidebar('main-left'); ?>
  </div>


  <!-- Content -->
  <div class="<?php echo ThemeGeneral::main_column_css_classes(); ?>" id="main">
    <h2>Page Not Found</h2>
    <p>It appears that the page you are looking for does not exist, or has been
       moved elsewhere.</p>
    <p>If you keep ending up here, please head back to our
       <a href="<?php echo get_site_url(); ?>">homepage</a>.</p>
    <div class="google-search-div">
      <gcse:search></gcse:search>
    </div>
  </div>


  <!-- Right Sidebar -->
  <div id="right-sidebar" class="<?php echo ThemeGeneral::right_sidebar_css_classes(); ?>">
    <?php dynamic_sidebar('main-right'); ?>
  </div>
</div>

<?php get_footer(); ?>
