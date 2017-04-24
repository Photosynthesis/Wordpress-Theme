<?php

/** TODO: Organize into classes/files **/


/** General Site Layout **/
/* Disable jQuery */
add_action('wp_enqueue_scripts', 'theme_disable_jquery');
function theme_disable_jquery() {
  wp_deregister_script('jquery');
}

/* Enable Sidebars */
add_action('widgets_init', 'theme_register_sidebars');
function theme_register_sidebars() {
  $sidebars = array(
    array('name' => 'Main Left', 'id' => 'main-left'),
    array('name' => 'Main Right', 'id' => 'main-right'),
    array('name' => 'WooCommerce Left', 'id' => 'wc-left'),
    array('name' => 'WooCommerce Right', 'id' => 'wc-right'),
  );
  foreach ($sidebars as $sidebar) {
    register_sidebar(array(
      'name' => $sidebar['name'],
      'id' => $sidebar['id'],
      'before_widget' => '<div id="%1$s" class="widget %2$s">',
      'after_widget' => '</div>',
      'before_title' => '<h4 class="widget-title text-center">',
      'after_title' => '</h4>'
    ));

  }
}

/* Bootstrap Menu */
require_once('includes/bootstrap_menu_walker.php');
register_nav_menus(array(
  'primary' => __( 'Primary Menu', 'FIC Theme'  ),
));

/* Return the CSS classes for left Sidebars */
function theme_left_sidebar_css_classes() {
  return "hidden-sm-down col-md-4 col-lg-5 col-xl-4 sidebar";
}
/* Return the CSS classes for right Sidebars */
function theme_right_sidebar_css_classes() {
  return "col-24 col-md-5 col-xl-4 sidebar";
}
/* Return the CSS classes for the main Content */
function theme_main_column_css_classes() {
  return "col-24 col-md-14 col-xl-16 center-column";
}

/* Print Out the Header, Left Sidebar, and Opening Center Tag */
function theme_main_top() {
  $sidebar_class = theme_left_sidebar_css_classes();
  $center_class = theme_main_column_css_classes();
  get_header();
  echo "\n<div class='row'>";
  echo "\n<div id='left-sidebar' class='{$sidebar_class}'>\n";
  dynamic_sidebar('main-left');
  echo "\n</div>\n";
  echo "<div id='main' class='{$center_class}'>";
}

/* Print Out the Closing Center Tag, Right Sidebar, and Footer */
function theme_main_bottom() {
  $sidebar_class = theme_right_sidebar_css_classes();
  echo "</div>";
  echo "<div id='right-sidebar' class='{$sidebar_class}'>\n";
  dynamic_sidebar('main-right');
  echo "\n</div></div>\n";
  get_footer();
}


/** Login Page **/
/* Replace the Logo */
function theme_customize_login_logo() {
  $logo_path = get_stylesheet_directory_uri() . "/img/logo-login-fic.png";
  echo <<<CSS
    <style type="text/css">
      #login h1 a, .login h1 a {
        background-image: url("{$logo_path}");
        background-size: auto;
        width: 300px;
        padding-bottom: 30px;
      }
    </style>
CSS;
}
add_action('login_enqueue_scripts', 'theme_customize_login_logo');

/* Link to Logo to the Home Page */
function theme_customize_login_logo_url() {
  return home_url();
}
add_action('login_headerurl', 'theme_customize_login_logo_url');

/* Set the Login Logo's Link Title to the Name of the Site */
function theme_customize_login_logo_title(){
  return 'Fellowship of Intentional Community';
}
add_action('login_headertitle', 'theme_customize_login_logo_title');



/** Comments **/
require_once('includes/bootstrap_comment_walker.php');
add_theme_support('html5', array('comment-list'));



/** Posts **/
/* Support Post Thumbnails */
add_theme_support('post-thumbnails');
set_post_thumbnail_size(175, 175);

/* Replace the Read More Text for Excerpts with a Link to the Post */
function theme_excerpt_more($more) {
  return "&hellip; <a class='btn btn-sm btn-link' href='" . get_permalink(get_the_ID()) . "'>" .
    "Read More</a>";
}
add_filter('excerpt_more', 'theme_excerpt_more');



/** WooCommerce **/
/* Woocommerce Hooks */
remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10);
remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10);

add_action('woocommerce_before_main_content', 'my_theme_wrapper_start', 10);
add_action('woocommerce_after_main_content', 'my_theme_wrapper_end', 10);

function my_theme_wrapper_start() {
  echo '<section id="main">';
}

function my_theme_wrapper_end() {
  echo '</section>';
}
add_action('after_setup_theme', 'woocommerce_support');
function woocommerce_support() {
  add_theme_support('woocommerce');
}
?>
