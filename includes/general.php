<?php
/** General Site & Layout Functions **/
class ThemeGeneral
{
  /** General Theme Functions **/

  /* Enable Support for Various Theme Features */
  public static function enable_support() {
    add_theme_support('html5', array('comment-list'));
    add_theme_support('post-thumbnails');
    add_theme_support('title-tag');
  }

  /* Set Sizes for Thumbnails Used By Theme Templates */
  public static function set_thumbnail_sizes() {
    set_post_thumbnail_size(200);
    add_image_size('product-thumbnail', 250, 325, array('center', 'top'));
    add_image_size('cart-thumbnail', 75, 0);
  }

  /* Register & Enqueue Compiled Scripts & Styles */
  public static function enqueue_assets() {
    foreach (scandir(__DIR__ . "/../dist") as $dist_file) {
      $extension = pathinfo($dist_file, PATHINFO_EXTENSION);
      if ($extension === 'js') {
        wp_enqueue_script($dist_file, get_stylesheet_directory_uri() . "/dist/{$dist_file}", array(), null);
      } else if ($extension === 'css') {
        wp_enqueue_style($dist_file, get_stylesheet_directory_uri() . "/dist/{$dist_file}", array(), null);
      }
    }
  }

  /* Add Main & WooCommerce Left/Right Sidebars */
  public static function register_sidebars() {
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

  /* Add Main Nav Menu */
  public static function register_menu() {
    register_nav_menus(array(
      'primary' => __( 'Primary Menu', 'FIC Theme'  ),
    ));
  }

  /* Add Favicon to Login & Admin Pages */
  public static function add_favicon() {
    $favicon_url = get_stylesheet_directory_uri() . '/img/favicon/favicon.ico';
    echo "<link rel='shortcut icon' href='{$favicon_url}' />";
  }

  /* Customize the Login Page CSS - Replace Logo, Change Background Color */
  public static function customize_login_css() {
    $logo_path = get_stylesheet_directory_uri() . "/img/logo-login-fic.png";
    echo <<<CSS
      <style type="text/css">
        body {
          background: #666698 !important;
        }
        #login {
          background: white;
          margin: 0 auto !important;
          padding: 1em 1.5em !important;
          border-bottom-left-radius: 3px !important;
          border-bottom-right-radius: 3px !important;
        }
        #login > form {
          margin-top: 0 !important;
          padding: 1em 0 0 !important;
          box-shadow: none;
        }
        #login h1 a, .login h1 a {
          background-image: url("{$logo_path}");
          background-size: auto;
          width: 300px;
          padding-bottom: 30px;
        }
      </style>
CSS;
  }

  /* Link the Login Page's Logo to the Home Page */
  public static function customize_login_logo_url() {
    return home_url();
  }

  /* Set the Login Logo's Title Attribute to the Name of the Site */
  public static function customize_login_logo_title() {
    return 'Fellowship of Intentional Community';
  }

  /* Replace Posts "Read More" Excerpt Text with a Link to the Post */
  public static function post_excerpt_link() {
    return "&hellip; <a class='btn btn-sm btn-link' href='" . get_permalink(get_the_ID()) . "'>" .
      "Read More</a>";
  }

  /* Show Comments In Reverse Order, From Recent to Oldest */
  public static function reverse_comments($comments) {
    return array_reverse($comments);
  }


  /** Layout Functions **/

  /* Return CSS Classes for Left Sidebars */
  public static function left_sidebar_css_classes() {
    return "hidden-sm-down col-md-4 col-lg-5 col-xl-4 sidebar";
  }

  /* Return CSS Classes for Right Sidebars */
  public static function right_sidebar_css_classes() {
    return "col-24 col-md-5 col-xl-4 sidebar";
  }

  /* Return CSS Classes for Center Column */
  public static function main_column_css_classes() {
    return "col-24 col-md-14 col-xl-16 center-column";
  }

  /* Echo the Header, Left Sidebar, & Opening Center Column Tag */
  public static function top($sidebar='main') {
    $sidebar_class = ThemeGeneral::left_sidebar_css_classes();
    $center_class = ThemeGeneral::main_column_css_classes();
    get_header();
    echo "\n<div class='row'>";
    echo "\n<div id='left-sidebar' class='{$sidebar_class}'>\n";
    dynamic_sidebar("{$sidebar}-left");
    echo "\n</div>\n";
    echo "<div id='main' class='{$center_class}'>";
  }

  /* Echo the Closing Center Column Tag, Right Sidebar, & Footer */
  public static function bottom($sidebar='main') {
    $sidebar_class = ThemeGeneral::right_sidebar_css_classes();
    echo "</div>";
    echo "<div id='right-sidebar' class='{$sidebar_class}'>\n";
    dynamic_sidebar("{$sidebar}-right");
    echo "\n</div></div>\n";
    get_footer();
  }
}

ThemeGeneral::enable_support();
ThemeGeneral::set_thumbnail_sizes();
ThemeGeneral::register_menu();
add_action('wp_enqueue_scripts', array('ThemeGeneral', 'enqueue_assets'));
add_action('widgets_init', array('ThemeGeneral', 'register_sidebars'));
add_action('login_head', array('ThemeGeneral', 'add_favicon'));
add_action('admin_head', array('ThemeGeneral', 'add_favicon'));
add_action('login_enqueue_scripts', array('ThemeGeneral', 'customize_login_css'));
add_action('login_headerurl', array('ThemeGeneral', 'customize_login_logo_url'));
add_action('login_headertitle', array('ThemeGeneral', 'customize_login_logo_title'));
add_filter('excerpt_more', array('ThemeGeneral', 'post_excerpt_link'));
add_filter('comments_array', array('ThemeGeneral', 'reverse_comments'));

?>
