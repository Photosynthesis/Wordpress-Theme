<?php
/** General Site & Layout Functions **/
class ThemeGeneral
{
  /** Special Page IDs **/
  public static $home_page_slug = 'home';
  public static $development_page_slug = 'support';
  public static $bookstore_page_slug = 'communities-bookstore';

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
    add_image_size('card-image', 0, 300);
    add_image_size('wide-thumbnail', 420, 285, TRUE);
    add_image_size('large-wide-thumbnail', 800, 550, TRUE);
  }

  /* Register & Enqueue Compiled Scripts & Styles.
   *
   * Ignores any scripts starting with `admin_` - those are loaded by the
   * `enqueue_admin_assets` method.
   */
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
        } else if (strpos($dist_file, "admin_") === 0) {
          // skip admin scripts
          continue;
        }
        wp_enqueue_script($dist_file, get_stylesheet_directory_uri() . "/dist/{$dist_file}", array(), null);
      } else if ($extension === 'css') {
        if (strpos($dist_file, "editor_") === 0) {
          // skip editor styles
          continue;
        }
        wp_enqueue_style($dist_file, get_stylesheet_directory_uri() . "/dist/{$dist_file}", array(), null);
      }
    }
    if ($directory_js_filename !== "") {
      wp_enqueue_script(
        $directory_js_filename,
        get_stylesheet_directory_uri() . "/dist/{$directory_js_filename}",
        array(), null);
    }

    if ($wholesale_js_filename !== "") {
      wp_enqueue_script(
        $wholesale_js_filename,
        get_stylesheet_directory_uri() . "/dist/{$wholesale_js_filename}",
        array(), null);
    }

    wp_enqueue_script('stripe-checkout', 'https://checkout.stripe.com/checkout.js', array());

    $rest_config = array(
      'nonce' => wp_create_nonce('wp_rest'),
    );
    wp_localize_script('jquery', 'themeRestConfig', $rest_config);
  }

  /* Register & Enqueue Compiled Admin Scripts
   *
   * All admin script filenames should begin with `admin_`. A global
   * `themeAdminConfig` object is available with the AJAX url & nonce as well
   * as a REST nonce.
   */
  public static function enqueue_admin_assets() {
    $admin_ajax_config = array(
      'ajaxUrl' => admin_url('admin-ajax.php', 'https'),
      'ajaxNonce' => wp_create_nonce('theme-admin-ajax'),
      'restNonce' => wp_create_nonce('wp_rest'),
    );
    wp_localize_script('jquery', 'themeAdminConfig', $admin_ajax_config);
    foreach (scandir(__DIR__ . "/../dist") as $dist_file) {
      if (strpos($dist_file, "admin_") === 0) {
        wp_enqueue_script($dist_file, get_stylesheet_directory_uri() . "/dist/{$dist_file}", array(), null);
      }
    }
  }

  public static function enqueue_editor_assets() {
    foreach (scandir(__DIR__ . "/../dist") as $dist_file) {
      if (strpos($dist_file, "editor_") === 0) {
        wp_enqueue_style($dist_file, get_stylesheet_directory_uri() . "/dist/{$dist_file}", array(), null);
      }
    }
  }

  /* Add Main & WooCommerce Left/Right Sidebars */
  public static function register_sidebars() {
    $sidebars = array(
      array('name' => 'Main', 'id' => 'main-sidebar'),
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
      'directory' => __( 'Directory Sub-Menu', 'FIC Theme'),
      'directory-types' => __( 'Directory Types Sub-Menu', 'FIC Theme'),
      'about' => __( 'About Sub-Menu', 'FIC Theme' ),
      'cmag' => __( 'CMag Sub-Menu', 'FIC Theme' ),
      'cmag-topics' => __( 'CMag Topics Sub-Menu', 'FIC Theme' ),
      'offers' => __( 'Offers Sub-Menu', 'FIC Theme' ),
      'bookstore' => __( 'Bookstore Sub-Menu', 'FIC Theme' ),
      'bookstore-topics' => __( 'Bookstore Topics Sub-Menu', 'FIC Theme' ),
      'categories' => __( 'Categories Sub-Menu', 'FIC Theme' ),
    ));
  }

  /* Add Favicon to Login & Admin Pages */
  public static function add_favicon() {
    $favicon_url = get_stylesheet_directory_uri() . '/img/favicon/favicon.ico?v=M4mloMGjlj';
    echo "<link rel='shortcut icon' href='{$favicon_url}' />";
  }

  /* Remove the `Customize` Link From the Admin Bar */
  public static function remove_customize_menu() {
      global $wp_admin_bar;
      $wp_admin_bar->remove_menu('customize');
  }

  /* Customize the Login Page CSS - Replace Logo, Change Background Color */
  public static function customize_login_css() {
    $logo_path = get_stylesheet_directory_uri() . "/img/logo-header-full-color.png?v=1";
    echo " <link href='//fonts.googleapis.com/css?family=Source+Sans+Pro:400,700' rel='stylesheet' type='text/css'>";
    echo <<<CSS
      <style type="text/css">
        body {
          background: white !important;
          font-family: "Source Sans Pro", Roboto, "Helvetica Neue", Arial, sans-serif !important;
          display: flex;
        }
        * {
          transition: all 0.3s ease-in-out;
        }
        #login {
          background: #F6F5F3;
          margin: auto !important;
          padding: 1em 5em !important;
          border-radius: 0.5rem !important;
        }
        #login > form {
          background: #F6F5F3;
          margin-top: 0 !important;
          padding: 1em 0 0 !important;
          box-shadow: none;
        }
        #login > form > p.submit > input[type="submit"] {
          background: #579C87;
          border-color: #579C87;
          border-radius: 0.5rem;
          color: white;
          font-weight: bold;
          box-shadow: none;
          text-shadow: none;
        }
        #login > form > p.submit > input[type="submit"]:hover {
          color: #579C87;
          background: white;
        }
        #login h1 {
          margin-top: 16px;
        }
        #login h1 a, .login h1 a {
          background-image: url("{$logo_path}");
          background-size: contain;
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
    return 'Foundation for Intentional Community';
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

  /* Return CSS Classes for Right Sidebars */
  public static function right_sidebar_css_classes($sidebar='main') {
    return 'col-24 col-sm-7 col-lg-6 col-xl-5 sidebar';
  }

  /* Return CSS Classes for Center Column */
  public static function main_column_css_classes($sidebar='main') {
    return "col-24 col-sm-17 col-lg-18 col-xl-19 center-column";
  }

  /* Echo the Header & Opening Center Column Tag */
  public static function top($sidebar='main') {
    $center_class = ThemeGeneral::main_column_css_classes($sidebar);
    get_header();
    echo "\n<div class='row'>";
    echo "<div id='main' class='{$center_class}'>";
  }

  /* Echo the Closing Center Column Tag, Right Sidebar, & Footer. For the wc
   * type, show a left & right sidebar.
   */
  public static function bottom($sidebar='main') {
    echo "</div>";
    $right_sidebar_class = ThemeGeneral::right_sidebar_css_classes($sidebar);
    if ($sidebar == 'wc') {
      echo "<div id='main-sidebar' class='{$right_sidebar_class}'>\n";
      dynamic_sidebar("{$sidebar}-right");
      echo "\n</div></div>\n";
    } else {
      echo "<div id='main-sidebar' class='{$right_sidebar_class}'>\n";
      dynamic_sidebar("{$sidebar}-sidebar");
      echo "\n</div></div>\n";
    }
    get_footer();
  }

  /* Disable Automatic Paragraph Breaks on Specific Posts & Post Types. */
  public static function auto_paragraphs($post_content) {
    global $post;
    $excluded =
      (get_post_type() == 'directory')
      || ($post->post_name === 'directory')
      || ($post->post_name === ThemeGeneral::$development_page_slug)
      || is_shop()
      || ($post->post_name === ThemeGeneral::$bookstore_page_slug);
    if ($excluded) {
      return $post_content;
    }
    return wpautop($post_content);
  }

  /* Disable the Visual Editor on Specific Pages */
  public static function remove_richtext_editor($can_use) {
    global $post;

    if ($post->post_name === ThemeGeneral::$development_page_slug ||
        is_shop() ||
        $post->post_name === ThemeGeneral::$bookstore_page_slug) {
      return false;
    }
    return $can_use;
  }

  /* Ignore stickiness of posts in the main blog loop. */
  public static function unstick_posts($query) {
    if (!$query->is_main_query()) {
      return;
    }
    if (is_home()) {
      $query->set('ignore_sticky_posts', 1);
    }
  }

  /* Generate the Recent Posts Section of the Home Page */
  public static function recent_posts() {
    $pinned_posts = wp_get_recent_posts(array(
      'numberposts' => 8,
      'post_type' => 'post',
      'post_status' => 'publish',
      'post__in' => get_option('sticky_posts'),
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
        $recent_post, 'card-image', array('class' => 'img-fluid mb-2')
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

  /* Fetch the Board & Staff Data.
   *
   * This is structured like:
   *
   * - board: array of
   *    - name
   *    - bio
   *    - image
   * - staff: array of
   *    - name
   *    - bio
   *    - image
   *
   */
  const board_staff_option_name = "fic_board_staff_data";
  public static function get_board_and_staff_data() {
    $default = array(
      'board' => array(),
      'staff' => array(),
    );
    return get_option(self::board_staff_option_name, $default);
  }

  /* Save the Board & Staff Data to the DB.
   *
   * Note that you need to validate the format & types yourself, no validation
   * is done in this function.
   */
  public static function set_board_and_staff_data($options) {
    update_option(self::board_staff_option_name, $options, false);
  }

  /* Render the div for Board & Staff's Elm code to hook into, as well as the
   * Board & Staff json as the `boardStaffData` global JS variable.
   */
  public static function render_board_and_staff() {
    $data = self::get_board_and_staff_data();
    return '<div id="elm-board-staff"></div>' .
      '<script type="text/javascript">' .
        'var boardStaffData = ' .  json_encode($data) .  ';' .
      '</script>';
  }

  public static function render_partners_block() {
    $sisters = array(
      array(
        'name' => 'Cohousing',
        'tracking' => 'coho',
        'image' => 'cohousing.png',
        'link' => 'http://www.cohousing.org',
        'height' => 40,
      ),
      array(
        'name' => 'The Federation of Egalitarian Communities',
        'tracking' => 'fec',
        'image' => 'fec.png',
        'link' => 'http://thefec.org',
        'height' => 40,
      ),
      array(
        'name' => 'North American Students of Cooperation',
        'tracking' => 'NASCO',
        'image' => 'nasco.jpg',
        'link' => 'http://www.nasco.coop/',
        'height' => 40,
      ),
      array(
        'name' => 'Global Ecovillage Network',
        'tracking' => 'GEN',
        'image' => 'gen.png',
        'link' => 'http://gen.ecovillage.org',
        'height' => 40,
      ),
      array(
        'name' => 'New Economy Coalition',
        'tracking' => 'NEC',
        'image' => 'nec.png',
        'link' => 'https://neweconomy.net/',
        'height' => 40,
      ),
    );
    $sponsors = array(
      array(
        'name' => 'NuMundo',
        'tracking' => 'NM',
        'image' => 'numundo.png',
        'link' => 'http://www.numundo.org/',
        'height' => 40,
      ),
      array(
        'name' => 'California Cohousing',
        'tracking' => 'calcoho',
        'image' => 'calcoho.jpg',
        'link' => 'http://www.calcoho.org/',
        'height' => 40,
      ),
      array(
        'name' => 'International Communes Desk',
        'tracking' => 'ICD',
        'image' => 'icd.jpg',
        'link' => 'http://www.communa.org.il',
        'height' => 40,
      ),
      array(
        'name' => 'ICSA',
        'tracking' => 'ICSA',
        'image' => 'icsa.jpg',
        'link' => 'http://www.communa.org.il/icsa',
        'height' => 40,
      ),
      array(
        'name' => 'Communal Studies Association',
        'tracking' => 'CSA',
        'image' => 'csa.gif',
        'link' => 'http://www.communalstudies.org/',
        'height' => 50,
      ),
      array(
        'name' => 'Transition US',
        'tracking' => 'TransitionUS',
        'image' => 'transition-us.png',
        'link' => 'http://www.transitionus.org/',
        'height' => 40,
      ),
      array(
        'name' => 'NW Intentional Communities Association',
        'tracking' => 'NICA',
        'image' => 'nica.jpg',
        'link' => 'https://nwcommunities.org/',
        'height' => 40,
      ),
    );

    $output = "<div class='partners row'><div class='col'>\n";
    $output .= "<h3>PARTNERS</h3>";

    $output .= "<div>";
    foreach ($sisters as $partner) {
      $output .= self::partner_logo($partner, 'sister_org');
    }
    $output .= "</div><div>";
    foreach ($sponsors as $partner) {
      $output .= self::partner_logo($partner, 'sponsor');
    }
    $output .= "</div>";

    $output .= "</div></div>\n";
    return $output;
  }
  private static function partner_logo($partner, $tracking_type) {
    $footer_logo_path = get_stylesheet_directory_uri() . "/img/footer-logos/";
    $tracking = self::fic_footer_track_click($tracking_type . "_" . $partner['tracking']);
    $output = "<a href='{$partner['link']}' target='_blank' title='{$partner['name']}' onclick='{$tracking}'>\n";
    $output .= "<img src='{$footer_logo_path}{$partner['image']}' alt='{$partner['name']}' title='{$partner['name']}' height='{$partner['height']}' hspace='4' border='0' />\n";
    $output .= "</a>\n";
    return $output;
  }
  private static function fic_footer_track_click($event_label) {
    return "ga('send', 'event', 'Theme.Footer', 'click', '{$event_label}');";
  }

  public static function render_new_arrivals() {
    $products = wc_get_products(array(
      'limit' => 4,
      'orderby' => 'date',
      'order' => 'DESC',
      'category' => array('books'),
      'status' => 'publish',
    ));
    $output =
      "<div id='home-new-arrivals' class='row justify-content-md-center'>" .
        "<div class='col-24'>" .
          "<h5 class='clearfix'>" .
            "<a href='/community-bookstore/category/recently-added/'>NEW BOOKS</a>" .
            "<a href='/community-bookstore/category/books/' class='btn btn-sm btn-primary'>" .
              "BROWSE BOOKS" .
            "</a>" .
          "</h5>" .
        "</div>";
    foreach ($products as $product) {
      $name = $product->get_title();
      $image = $product->get_image(
        'wide-thumbnail', array('class' => 'img-fluid rounded-lg')
      );
      $link = $product->get_permalink();
      $output .= "<div class='col-sm-12 col-md-8 col-lg-6 item-block pop-image'><a href='{$link}'>";
      $output .= $image . "<div>{$name}</div>";
      $output .= "</a></div>";
    }
    $output .= "</div>";
    return $output;
  }

  public static function render_upcoming_events() {
    $events = tribe_get_events(array('posts_per_page' => 4));
    $output =
      "<div id='home-upcoming-events' class='row justify-content-md-center'>" .
        "<div class='col-24'>" .
          "<h5 class='clearfix'>" .
            "<a href='/events/'>UPCOMING EVENTS</a>" .
            "<a href='/events/' class='btn btn-sm btn-primary'>" .
              "MORE EVENTS" .
            "</a>" .
          "</h5>" .
        "</div>";
    foreach ($events as $post) {
      $name = $post->post_title;
      $image = get_the_post_thumbnail(
        $post->ID, 'wide-thumbnail', array('class' => 'img-fluid rounded-lg')
      );
      $link = get_permalink($post->ID);
      $output .= "<div class='col-sm-12 col-md-8 col-lg-6 item-block pop-image'><a href='{$link}'>";
      $output .= $image . "<div>{$name}</div>";
      $output .= "</a></div>";
    }
    $output .= "</div>";
    return $output;
  }

  public static function render_offers($atts) {
    $a = shortcode_atts(
      array('full_width' => false),
      $atts
    );
    $a['full_width'] = (bool) $a['full_width'];
    $ads = get_posts(array(
      'post_type' => 'advert',
      'post_status' => 'publish',
      'posts_per_page' => 4,
      'menu_order' => 1,    // featured ads
      'orderby' => array('date' => 'desc'),
    ));
    $container_classes = $a['full_width'] ? 'container-fluid full-width' : 'row justify-content-md-center';
    $output =
      "<div id='home-community-offers' class='{$container_classes}'>" .
        ($a['full_width'] ? "<div class='container'><div class='row justify-content-md-center'>" : '') .
        "<div class='col-24'>" .
          "<h5 class='clearfix'><a href='/community-classifieds/'>COMMUNITY OFFERS</a>" .
            "<a href='/community-classifieds/place-ad/' class='btn btn-sm btn-primary'>" .
              "<i class='fas fa-plus'></i> POST OFFER" .
            "</a>" .
          "</h5>" .
        "</div>";
    foreach ($ads as $post) {
      $name = $post->post_title;
      $image_post = array_pop(adverts_sort_images(get_children(array('post_parent' => $post->ID)), $post->ID));
      $image = wp_get_attachment_image(
        $image_post->ID, 'wide-thumbnail', false, array('class' => 'img-fluid rounded-lg')
      );
      $link = get_permalink($post->ID);
      $output .= "<div class='col-sm-12 col-md-8 col-lg-6 item-block pop-image' data-item='{$post->ID}'><a href='{$link}'>";
      $output .= $image . "<div>{$name}</div>";
      $output .= "</a></div>";
    }
    $output .= ($a['full_width'] ? "</div></div>" : '') . "</div>";
    return $output;
  }

  public static function render_homepage_banner_image($atts) {
    $a = shortcode_atts(
      array(
        'header' => 'Find community. Experience a new world.',
        'image' => '//placekitten.com/1350/675',
      ), $atts
    );
    $output = "<div id='home-banner' class='container-fluid full-width row'><img src='{$a['image']}' />";
    $output .= "<h1>{$a['header']}</h1>";
    $output .=
      "<div class='search-container'>" .
        "<form action='/directory/listings/' class='directory-search-input'>" .
          "<i class='fas fa-search'></i>" .
          "<input type='text' class='form-control' name='search' placeholder=\"Try searching for 'California' or 'Cohousing'\" />" .
          "<input type='submit' class='btn btn-primary btn-sm' value='SEARCH' />" .
        "</form>" .
        "<a href='/directory/search/' class='btn btn-sm btn-transparent'>ADVANCED SEARCH</a>" .
      "</div>";
    $output .= "</div>";
    return $output;
  }

  public static function render_get_involved_block($atts, $content = "") {
    $a = shortcode_atts(
      array(
        'header' => 'Intentional communities model solutions',
        'news_header' => 'JOIN OUR NEWSLETTER',
        'news_text' => 'Free resources, inspiring stories, and special offers!',
      ), $atts
    );
    $mailpoet_form = do_shortcode('[mailpoet_form id="2"]');
    $output = <<<HTML
<div id='get-involved-block' class='container-fluid full-width'>
  <div class='container'>
    <div class='row'>
      <div class='col-md-15 left-column'>
        <h2 class='h1'>{$a['header']}</h2>
        <p>{$content}</p>
      </div>
      <div class='col-md-8 right-column'>
        <h5>{$a['news_header']}</h5>
        <p>{$a['news_text']}</p>
        {$mailpoet_form}
      </div>
    </div>
  </div>
</div>
HTML;
    return $output;
  }

  public static function render_column_section($atts, $content = "") {
    $a = shortcode_atts(
      array(
        'image' => '',
        'header' => '',
        'link' => '',
      ), $atts
    );
    $output = <<<HTML
<div class='homepage-column-section pop-image'>
  <a href="{$a['link']}" class='image-link'>
    <img src="{$a['image']}" class='img-fluid' />
  </a>
  <h3><a href="{$a['link']}">{$a['header']}</a></h3>
  <p>{$content}</p>
  <a href="{$a['link']}">Learn more</a>
</div>
HTML;
    return $output;
  }

  public static function render_primary_story($atts) {
    $a = shortcode_atts(
      array(
        'title' => null,
        'tagline' => null,
        'post' => null,
      ), $atts
    );
    if (is_null($a['post'])) {
      return '';
    } else {
      $post = (int) $a['post'];
    }
    $post = get_post($post);
    if (is_null($post)) {
      return '';
    }

    $image = get_the_post_thumbnail($post->ID, 'large-wide-thumbnail', array('class' => 'img-fluid'));
    $link = get_the_permalink($post->ID);
    $time = human_time_diff(get_the_time('U', $post->ID), current_time('timestamp'));
    $author = get_the_author_meta('display_name', $post->post_author);

    $title = $a['title'] ??  $title = get_the_title($post->ID);

    $tagline = $a['tagline'] ?? strip_tags(explode('.', $post->post_content, 2)[0]);

    $output = <<<HTML
<div class='primary-story pop-image'>
  <a href='{$link}'>
    <div class='image-title'>
      {$image}
      <h3>{$title}</h3>
    </div>
  </a>
  <div class='tagline-author d-block d-xl-flex'>
    <div class='tagline'>{$tagline}</div>
    <div class='author'>{$author} &ndash; {$time} ago</div>
  </div>
</div>
HTML;
    return $output;
  }

  public static function render_secondary_story($atts) {
    $a = shortcode_atts(
      array(
        'title' => null,
        'tagline' => null,
        'post' => null,
      ), $atts
    );
    if (is_null($a['post'])) {
      return '';
    } else {
      $post = (int) $a['post'];
    }
    $post = get_post($post);
    if (is_null($post)) {
      return '';
    }

    $image = get_the_post_thumbnail($post->ID, 'wide-thumbnail', array('class' => 'img-fluid'));
    $link = get_the_permalink($post->ID);
    $time = human_time_diff(get_the_time('U', $post->ID), current_time('timestamp'));
    $author = get_the_author_meta('display_name', $post->post_author);

    $title = $a['title'] ??  $title = get_the_title($post->ID);

    $tagline = $a['tagline'] ?? strip_tags(explode('.', $post->post_content, 2)[0]);

    $output = <<<HTML
<div class='secondary-story row'>
  <div class='col-sm-14 col-md-13 col-lg-12 pop-image'>
    <a href='{$link}'>
      {$image}
    </a>
  </div>
  <div class='col-sm-10 col-md-11 col-lg-12'>
    <h5><a href='{$link}'>{$title}</a></h5>
    <div class='tagline'>{$tagline}</div>
    <div class='author'>{$author} &ndash; {$time} ago</div>
  </div>
</div>
HTML;
    return $output;
  }

  public static function render_full_width_image($atts, $content = '') {
    if ($content === '') { return ''; }
    $a = shortcode_atts(array('class' => ''), $atts);
    $output = <<<HTML
<div class="container-fluid full-width full-width-image {$a['class']}">
  <img src="{$content}" />
</div>
HTML;
    return $output;
  }

  public static function render_interest_block($atts, $content = '') {
    if ($content === '') { return; }
    $a = shortcode_atts(array('heading' => '', 'icon' => ''), $atts);
    $output = <<<HTML
<div class='interest-block'>
  <div class='icon-block text-center'><i class="fa fa-{$a['icon']} fa-3x"></i></div>
  <h6>{$a['heading']}</h6>
  <p>{$content}</p>
</div>
HTML;
    return $output;
  }
}

add_filter('wp_mail_from_name', function($n) { return 'Foundation for Intentional Community'; }, 11);
add_filter('wp_mail_from', function($e) { return 'support@ic.org'; }, 11);

ThemeGeneral::enable_support();
ThemeGeneral::set_thumbnail_sizes();
ThemeGeneral::register_menu();
add_action('wp_enqueue_scripts', array('ThemeGeneral', 'enqueue_assets'));
add_action('admin_enqueue_scripts', array('ThemeGeneral', 'enqueue_admin_assets'));
add_action('enqueue_block_editor_assets', array('ThemeGeneral', 'enqueue_editor_assets'));
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
remove_filter('woocommerce_short_description', 'wpautop');
add_filter('the_content', array('ThemeGeneral', 'auto_paragraphs'));
add_filter('woocommerce_short_description', array('ThemeGeneral', 'auto_paragraphs'));
add_filter('user_can_richedit', array('ThemeGeneral', 'remove_richtext_editor'));
add_shortcode('homepage_recent_posts_widget', array('ThemeGeneral', 'recent_posts'));
add_filter('category_description', 'do_shortcode');
add_filter('upload_mimes', array('ThemeGeneral', 'allow_ebook_mimes'));
add_action('pre_get_posts', array('ThemeGeneral', 'unstick_posts'));
add_shortcode('elm_board_staff', array('ThemeGeneral', 'render_board_and_staff'));
add_shortcode('fic_partners', array('ThemeGeneral', 'render_partners_block'));
add_shortcode('fic_new_arrivals', array('ThemeGeneral', 'render_new_arrivals'));
add_shortcode('fic_upcoming_events', array('ThemeGeneral', 'render_upcoming_events'));
add_shortcode('fic_community_offers', array('ThemeGeneral', 'render_offers'));
add_shortcode('fic_jumbo_image', array('ThemeGeneral', 'render_homepage_banner_image'));
add_shortcode('fic_get_involved', array('ThemeGeneral', 'render_get_involved_block'));
add_shortcode('fic_column_section', array('ThemeGeneral', 'render_column_section'));
add_shortcode('fic_primary_story', array('ThemeGeneral', 'render_primary_story'));
add_shortcode('fic_secondary_story', array('ThemeGeneral', 'render_secondary_story'));
add_shortcode('fic_full_width_image', array('ThemeGeneral', 'render_full_width_image'));
add_shortcode('fic_interest_block', array('ThemeGeneral', 'render_interest_block'));

?>
