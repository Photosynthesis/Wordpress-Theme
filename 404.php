<?php get_header(); ?>

<div class="row">
  <!-- Content -->
  <div class="<?php echo ThemeGeneral::main_column_css_classes(); ?>" id="main">
    <h2>Page Not Found</h2>

    <p>It appears that the page you are looking for does not exist, or has been
       moved elsewhere.</p>
    <?php
    if (strpos($_SERVER['REQUEST_URI'], '/directory/') === 0) {
      echo "<p>If you are looking for a Community in the Directory which has " .
          "not yet been approved, you may see this message. Contact " .
          "<a href=\"mailto:directory@ic.org\">Directory@ic.org</a> with any " .
          "questions.</p>";
    } ?>
    <p>If you keep ending up here, please head back to our
       <a href="<?php echo get_site_url(); ?>">homepage</a>.</p>
    <div class="google-search-div">
      <gcse:search></gcse:search>
    </div>
  </div>

  <!-- Sidebar -->
  <div id="main-sidebar" class="<?php echo ThemeGeneral::right_sidebar_css_classes(); ?>">
    <?php dynamic_sidebar('main-sidebar'); ?>
  </div>
</div>

<?php get_footer(); ?>
