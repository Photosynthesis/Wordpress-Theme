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
function theme_top($sidebar='main') {
  $sidebar_class = theme_left_sidebar_css_classes();
  $center_class = theme_main_column_css_classes();
  get_header();
  echo "\n<div class='row'>";
  echo "\n<div id='left-sidebar' class='{$sidebar_class}'>\n";
  dynamic_sidebar("{$sidebar}-left");
  echo "\n</div>\n";
  echo "<div id='main' class='{$center_class}'>";
}

/* Print Out the Closing Center Tag, Right Sidebar, and Footer */
function theme_bottom($sidebar='main') {
  $sidebar_class = theme_right_sidebar_css_classes();
  echo "</div>";
  echo "<div id='right-sidebar' class='{$sidebar_class}'>\n";
  dynamic_sidebar("{$sidebar}-right");
  echo "\n</div></div>\n";
  get_footer();
}


/** Login Page **/
/* Replace the Logo & Change the Background Area & Color */
function theme_customize_login_css() {
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
add_action('login_enqueue_scripts', 'theme_customize_login_css');

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
set_post_thumbnail_size(200);

/* Replace the Read More Text for Excerpts with a Link to the Post */
function theme_excerpt_more($more) {
  return "&hellip; <a class='btn btn-sm btn-link' href='" . get_permalink(get_the_ID()) . "'>" .
    "Read More</a>";
}
add_filter('excerpt_more', 'theme_excerpt_more');



/** WooCommerce **/
/* Woocommerce Hooks */
add_action('after_setup_theme', 'woocommerce_support');
function woocommerce_support() {
  add_theme_support('woocommerce');
}
/* Disable WooCommerce CSS */
add_filter('woocommerce_enqueue_styles', '__return_false');
/* Add Classes to Add to Cart Button */
function theme_wc_add_to_cart_classes($args, $product) {
  $args['class'] .= ' btn btn-primary btn-block';
  return $args;
}
add_filter('woocommerce_loop_add_to_cart_args', 'theme_wc_add_to_cart_classes', 10, 2);
/* Change Text for Free Products */
function theme_wc_change_free_price_text($price, $product) {
  if (strpos(strip_tags(html_entity_decode($price)), '$0.00') !== false) {
    return 'Free!';
  }
  return $price;
}
add_filter('woocommerce_get_price_html', 'theme_wc_change_free_price_text', 10, 2);
/* Show Only the Lowest Price of a Price Range */
function theme_wc_format_price_range($price, $from, $to) {
  $formatted_from = wc_price($from);
  return "Starting From {$formatted_from}";
}
add_filter('woocommerce_format_price_range', 'theme_wc_format_price_range', 10, 3);
/* Surround Result Count & Ordering Dropdown In A Clearfix Div Tag */
function theme_wc_count_ordering_start() {
  echo '<div class="clearfix mb-3">';
}
function theme_wc_count_ordering_end() {
  echo '</div>';
}
add_action('woocommerce_before_shop_loop', 'theme_wc_count_ordering_start', 19);
add_action('woocommerce_before_shop_loop', 'theme_wc_count_ordering_end', 31);
/* Change the Tag of the WC Products Widget to a Div */
function theme_wc_products_widget_start($tag) {
  return str_replace('<ul class="', '<div class="list-group ', $tag);
}
function theme_wc_products_widget_end($tag) {
  return '</div>';
}
add_filter('woocommerce_before_widget_product_list', 'theme_wc_products_widget_start');
add_filter('woocommerce_after_widget_product_list', 'theme_wc_products_widget_end');
/* Show Accepted Payment Method Images */
function theme_wc_accepted_payment_methods() {
    $path = get_stylesheet_directory_uri() . "/img/cc-logos/";

    $method_image_to_name = array(
        'amex' => 'American Express',
        'discover' => 'Discover',
        'mastercard' => 'MasterCard',
        'paypal' => 'PayPal',
        'visa' => 'Visa',
    );

    $content = "<div class='pt-1 text-center'>";
    foreach ($method_image_to_name as $image => $name) {
        $image_path = "{$path}{$image}.png";
        $content .= "<img class='mr-3 mb-3' alt='{$name}' title='{$name}' src='{$image_path}' />";
    }
    $content .= "</div>";

    return $content;
}
add_shortcode('fic_accepted_payment_methods', 'theme_wc_accepted_payment_methods');
/* Customize the Name Your Price Amount Input Field */
function theme_wc_nyp_amount_input($return, $product_id, $prefix) {
  return str_replace('class="', 'class="form-control text-left ', $return);
}
add_filter('woocommerce_get_price_input', 'theme_wc_nyp_amount_input', 10, 3);
/* Add an Image Size for Cart Thumbnails */
add_image_size('cart-thumbnail', 75, 0);
/* Move Cross Sells Below Cart Totals */
remove_action('woocommerce_cart_collaterals', 'woocommerce_cross_sell_display');
add_action('woocommerce_cart_collaterals', 'woocommerce_cross_sell_display', 11);
/* Show 18 Products Per Page */
add_filter('loop_shop_per_page', create_function('$cols', 'return 18;'), 20);
/* Remove Hidden Products from Queries */
function theme_wc_remove_hidden($query, $wc_query) {
  $hidden_meta = array('key' => '_visibility', 'value' => 'hidden', 'compare' => '!=');
  $meta = $query->get('meta_query');
  if (!is_search()) {
    $meta[] = array(
      'relation' => 'AND',
      $hidden_meta,
      array('key' => '_visibility', 'value' => 'search', 'compare' => '!='),
    );
  } else {
    $meta[] = $hidden_meta;
  }
  $query->set('meta_query', $meta);
}
add_action('woocommerce_product_query', 'theme_wc_remove_hidden', 10, 2);


/** WPAdverts **/
/* Allow Theme to Override Shortcode Templates */
function theme_wpadverts_templates($template) {
  $dirs = array(
    get_template_directory() . "/wpadverts/",
    ADVERTS_PATH . "/templates/"
  );

  $basename = basename($template);

  foreach ($dirs as $dir) {
    if (file_exists($dir . $basename)) {
      return $dir . $basename;
    }
  }
}
add_action('adverts_template_load', 'theme_wpadverts_templates');

/* Disable Loading of Page Template for Categories */
function theme_wpadverts_init() {
  remove_filter('template_include', 'adverts_template_include');
}
add_action('init', 'theme_wpadverts_init');

/* Change Category Slug to `community-classifieds` */
function theme_wpadverts_customize_taxonomy($args) {
  if (!isset($args['rewrite'])) {
    $args['rewrite'] = array();
  }
  $args['rewrite']['slug'] = 'community-classifieds';
  return $args;
}
add_action("adverts_register_taxonomy", "theme_wpadverts_customize_taxonomy");


?>
