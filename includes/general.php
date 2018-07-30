<?php
/** General Site & Layout Functions **/
class ThemeGeneral
{
  /** Special Page IDs **/
  public static $home_page_id = 14986;
  public static $development_page_id = 255787;

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
    add_image_size('product-image', 300, 0);
    add_image_size('cart-thumbnail', 75, 0);
  }

  /* Register & Enqueue Compiled Scripts & Styles */
  public static function enqueue_assets() {
    global $post;

    $directory_js_filename = "";
    $wholesale_js_filename = "";

    foreach (scandir(__DIR__ . "/../dist") as $dist_file) {
      $extension = pathinfo($dist_file, PATHINFO_EXTENSION);
      if ($extension === 'js') {
        if (strpos($dist_file, "directory") !== false) {
          $directory_js_filename = $dist_file;
          continue;
        } else if (strpos($dist_file, "wholesale") !== false && $wholesale_js_filename === "") {
          $wholesale_js_filename = $dist_file;
          continue;
        }
        wp_enqueue_script($dist_file, get_stylesheet_directory_uri() . "/dist/{$dist_file}", array(), null);
      } else if ($extension === 'css') {
        wp_enqueue_style($dist_file, get_stylesheet_directory_uri() . "/dist/{$dist_file}", array(), null);
      }
    }
    if ($directory_js_filename !== "") {
      wp_enqueue_script(
        $directory_js_filename,
        get_stylesheet_directory_uri() . "/dist/{$directory_js_filename}",
        array(), null);
    }

    if ($wholesale_js_filename !== "" && $post->post_name === "wholesale") {
      wp_enqueue_script(
        $wholesale_js_filename,
        get_stylesheet_directory_uri() . "/dist/{$wholesale_js_filename}",
        array(), null);
    }

    wp_enqueue_script('stripe-checkout', 'https://checkout.stripe.com/checkout.js', array());
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

  /* Remove the `Customize` Link From the Admin Bar */
  public static function remove_customize_menu() {
      global $wp_admin_bar;
      $wp_admin_bar->remove_menu('customize');
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
    return "col-24 col-sm-12 col-md-7 pull-md-17 col-lg-5 pull-lg-19 col-xl-4 pull-xl-16 sidebar";
  }

  /* Return CSS Classes for Right Sidebars */
  public static function right_sidebar_css_classes() {
    return "col-24 col-sm-12 col-md-24 col-xl-4 sidebar";
  }

  /* Return CSS Classes for Center Column */
  public static function main_column_css_classes() {
    return "col-24 col-md-17 push-md-7 col-lg-19 push-lg-5 col-xl-16 push-xl-4 center-column";
  }

  /* Echo the Header, Left Sidebar, & Opening Center Column Tag */
  public static function top($sidebar='main') {
    $sidebar_class = ThemeGeneral::left_sidebar_css_classes();
    $center_class = ThemeGeneral::main_column_css_classes();
    get_header();
    echo "\n<div class='row'>";
    echo "<div id='main' class='{$center_class}'>";
  }

  /* Echo the Closing Center Column Tag, Right Sidebar, & Footer */
  public static function bottom($sidebar='main') {
    $left_sidebar_class = ThemeGeneral::left_sidebar_css_classes();
    $right_sidebar_class = ThemeGeneral::right_sidebar_css_classes();
    echo "</div>";
    echo "\n<div id='left-sidebar' class='{$left_sidebar_class}'>\n";
    dynamic_sidebar("{$sidebar}-left");
    echo "\n</div>\n";
    echo "<div id='right-sidebar' class='{$right_sidebar_class}'>\n";
    dynamic_sidebar("{$sidebar}-right");
    echo "\n</div></div>\n";
    get_footer();
  }

  /* Disable Automatic Paragraph Breaks on Specific Posts & Post Types. */
  public static function auto_paragraphs($post_content) {
    global $post;
    $excluded =
      (get_post_type() == 'directory')
      || ($post->post_name === 'directory')
      || ($post->ID === ThemeGeneral::$home_page_id)
      || ($post->ID === ThemeGeneral::$development_page_id);
    if ($excluded) {
      return $post_content;
    }
    return wpautop($post_content);
  }

  /* Disable the Visual Editor on Specific Pages */
  public static function remove_richtext_editor($can_use) {
    global $post;

    if ($post->ID === ThemeGeneral::$home_page_id ||
        $post->ID === ThemeGeneral::$development_page_id ||
        $post->post_name === 'directory') {
      return false;
    }
    return $can_use;
  }

  /* Generate the Recent Posts Section of the Home Page */
  public static function recent_posts() {
    $pinned_posts = wp_get_recent_posts(array(
      'numberposts' => 8,
      'post_type' => 'post',
      'post_status' => 'publish',
      'meta_key' => ThemeBlogPosts::$pinned_meta_field,
      'meta_value' => 1,
      'meta_compare' => '=',
    ), OBJECT);
    $recent_posts = wp_get_recent_posts(array(
      'numberposts' => 8 + sizeof($pinned_posts),
      'post_type' => 'post',
      'post_status' => 'publish',
    ), OBJECT);

    $posts = is_array($pinned_posts) ?  array_merge($pinned_posts, $recent_posts) : $recent_posts;
    $rendered_ids = array();
    $output = "";
    foreach ($posts as $recent_post) {
      if (count($rendered_ids) === 8) { break; }
      if (in_array($recent_post->ID, $rendered_ids)) {
        continue;
      } else {
        $rendered_ids[] = $recent_post->ID;
      }

      $thumbnail_element = get_the_post_thumbnail(
        $recent_post, array(0, 250), array('class' => 'img-fluid mb-2')
      );
      $post_title = $recent_post->post_title;
      $post_name = $recent_post->post_name;
      $post_author = get_the_author_meta('display_name', $recent_post->post_author);
      $author_url = get_author_posts_url($recent_post->post_author);

      $output .= "<div class='col-sm-12 col-lg-6 d-flex flex-column text-center mb-3'>\n";
      $output .= "<a href='/{$post_name}' class='my-auto'>" . $thumbnail_element . "</a>\n";
      $output .= "<h4 class='mt-auto'><a href='/{$post_name}'>{$post_title}</a></h4>\n";
      $output .= "<div class='text-muted'>By <a href='{$author_url}'>{$post_author}</a>.</div>";
      $output .= "</div>\n";
    }
    return $output;
  }

  /* Allow eBook Uploads */
  public static function allow_ebook_mimes($mimes) {
    return array_merge($mimes, array(
      'epub' => 'application/epub+zip',
      'mobi' => 'application/x-mobipocket-ebook',
    ));
  }
}

ThemeGeneral::enable_support();
ThemeGeneral::set_thumbnail_sizes();
ThemeGeneral::register_menu();
add_action('wp_enqueue_scripts', array('ThemeGeneral', 'enqueue_assets'));
add_action('widgets_init', array('ThemeGeneral', 'register_sidebars'));
add_action('login_head', array('ThemeGeneral', 'add_favicon'));
add_action('admin_head', array('ThemeGeneral', 'add_favicon'));
add_action('wp_before_admin_bar_render', array('ThemeGeneral', 'remove_customize_menu'));
add_action('login_enqueue_scripts', array('ThemeGeneral', 'customize_login_css'));
add_action('login_headerurl', array('ThemeGeneral', 'customize_login_logo_url'));
add_action('login_headertitle', array('ThemeGeneral', 'customize_login_logo_title'));
add_filter('excerpt_more', array('ThemeGeneral', 'post_excerpt_link'));
add_filter('comments_array', array('ThemeGeneral', 'reverse_comments'));
remove_filter('the_content', 'wpautop');
add_filter('the_content', array('ThemeGeneral', 'auto_paragraphs'));
add_filter('user_can_richedit', array('ThemeGeneral', 'remove_richtext_editor'));
add_shortcode('homepage_recent_posts_widget', array('ThemeGeneral', 'recent_posts'));
add_filter('category_description', 'do_shortcode');
add_filter('upload_mimes', array('ThemeGeneral', 'allow_ebook_mimes'));

?>
