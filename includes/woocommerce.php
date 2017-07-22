<?php
/** Customize WooCommerce Plugins **/
class ThemeWooCommerce
{
  /* Enable Theme Support for WooCommerce & Lightboxes */
  public static function enable_support() {
    add_theme_support('woocommerce');
    add_theme_support('wc-product-gallery-lightbox');
  }

  /* Disable WooCommerce CSS */
  public static function disable_css() {}

  /* Add Bootstrap Classes to Add-to-Cart Buttons */
  public static function add_to_cart_classes($args, $product) {
    $args['class'] .= ' btn btn-primary btn-block';
    return $args;
  }

  /* Add Bootstrap Classes to the Name Your Price Amount Input Field */
  public static function nyp_input_classes($return, $product_id, $prefix) {
    return str_replace('class="', 'class="form-control text-left ', $return);
  }

  /* Display "Free!" Instead of "$0.00" for Free Products */
  public static function change_free_text($price, $product) {
    if (strpos(strip_tags(html_entity_decode($price)), '$0.00') !== false) {
      return 'Free!';
    }
    return $price;
  }

  /* Hide the Prices for Name Your Price Products */
  public static function remove_suggested_price($price, $product) {
    if (WC_Name_Your_Price_Helpers::is_nyp($product)) {
      return '';
    }
    return $price;
  }

  /* Show the Lowest Price for Price Ranges */
  public static function format_price_range($price, $from, $to) {
    $formatted_from = wc_price($from);
    return "Starting From {$formatted_from}";
  }

  /* Open A Clearfix Tag Before the Result Count & Ordering Dropdown */
  public static function result_count_start() {
    echo '<div class="clearfix mb-3">';
  }

  /* Close the Clearfix Tag After the Result Count & Ordering Dropdown */
  public static function result_count_end() {
    echo '</div>';
  }

  /* Open the Products Widget With a List Group Div Tag */
  public static function products_widget_start($tag) {
    return str_replace('<ul class="', '<div class="list-group ', $tag);
  }

  /* Close the Products Widget's Div Tag */
  public static function products_widget_end($tag) {
    return '</div>';
  }

  /* Add a `Purchased` Column to the Admin Orders Table */
  public static function add_purchased_column_header($columns) {
    $new_columns = array();
    foreach ($columns as $key => $title) {
      if ($key === 'billing_address') {
        $new_columns['order_items'] = __('Purchased', 'woocommerce');
      }
      $new_columns[$key] = $title;
    }
    return $new_columns;
  }

  /* Render the `Purchased` Column in the Admin Orders Table */
  public static function render_purchased_column($column) {
    global $the_order;
    if ($column === 'order_items') {
      $item_count = $the_order->get_item_count();
      $item_count_text =
        apply_filters(
          'woocommerce_admin_order_item_count',
          sprintf(_n('%d item', '%d items', $item_count, 'woocommerce'), $item_count),
          $the_order
        );
      echo "<a href='#' class='show_order_items'>{$item_count_text}</a>";
      $order_items = $the_order->get_items();
      if (sizeof($order_items) > 0) {
        echo "<table class='order_items' cellspacing='0'>";
        foreach ($order_items as $item) {
          $product = apply_filters(
            'woocommerce_order_item_product', $item->get_product(), $item);
          $item_meta = new WC_Order_Item_Meta($item, $product);
          $item_meta_html = $item_meta->display(true, true);
          $item_class = apply_filters(
            'woocommerce_admin_order_item_class', '', $item, $the_order);
          $item_name = apply_filters(
            'woocommerce_order_item_name', $item->get_name(), $item, false);
          echo "<tr class='{$item_class}'>" .
            "<td class='qty'>" . esc_html($item->get_quantity()) . "</td>" .
            "<td class='name'>";
          if ($product) {
            $sku = $product->get_sku();
            $edit_link = get_edit_post_link($product->get_id());
            if (wc_product_sku_enabled() && $sku !== '') { echo "{$sku} - "; }
            echo "<a href='{$edit_link}'>{$item_name}</a>";
          } else {
            echo $item_name;
          }
          if (!empty($item_meta_html)) {
            echo wc_help_tip($item_meta_html);
          }
          echo "</td>";
        }
        echo "</table>";
      }
    }
  }

  // TODO: Refactor into top-level Constants file/class.
  const membership_back_issues_product_id = 242058;
  const fic_membership_group_id = 4;

  /* Provide Back Issue Downloads For A User in the FIC Membership Group. */
  public static function add_back_issue_access($user_id, $group_id) {
    if ($group_id != self::fic_membership_group_id) { return; }

    $back_issues = wc_get_product(self::membership_back_issues_product_id);

    $order = wc_create_order(array(
      'customer_id' => $user_id,
      'customer_note' => 'FIC Membership Allows Access to Back Issue Downloads',
      'created_via' => 'FIC-Membership',
    ));
    $order->add_product($back_issues);
    $order->calculate_totals();
    $order->update_status('completed');
  }

  /* Remove Back Issue Downloads For a User in the FIC Membership Group. */
  public static function remove_back_issue_access($user_id, $group_id) {
    if ($group_id != self::fic_membership_group_id) { return; }

    $orders = wc_get_orders(array(
      'customer_id' => $user_id,
      'created_via' => 'FIC-Membership',
      'limit' => 1,
    ));

    foreach ($orders as $order) {
        wc_delete_shop_order_transients($order->get_id());
        $order->delete();
    }
  }

  /* Show Images of Accepted Payment Methods */
  public static function accepted_payment_methods() {
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

  /* Modify the WooCommerce `[product]` Shortcode to Open in a New Page */
  public static function product_new_page($atts) {
    extract(shortcode_atts(array('id' => 0), $atts));
    $text = do_shortcode("[product id='$id']");

    return str_replace('<a', '<a target="_blank"', $text);
  }
}

/* Move Cross Sells Below the Cart Totals */
remove_action('woocommerce_cart_collaterals', 'woocommerce_cross_sell_display');
add_action('woocommerce_cart_collaterals', 'woocommerce_cross_sell_display', 11);

/* Show 24 Products Per Page */
add_filter('loop_shop_per_page', create_function('$cols', 'return 24;'), 20);

add_action('after_setup_theme', array('ThemeWooCommerce', 'enable_support'));
add_filter('woocommerce_enqueue_styles', array('ThemeWooCommerce', 'disable_css'));
add_filter('woocommerce_loop_add_to_cart_args', array('ThemeWooCommerce', 'add_to_cart_classes'), 10, 2);
add_filter('woocommerce_get_price_input', array('ThemeWooCommerce', 'nyp_input_classes'), 10, 3);
add_filter('woocommerce_get_price_html', array('ThemeWooCommerce', 'change_free_text'), 10, 2);
add_filter('woocommerce_get_price_html', array('ThemeWooCommerce', 'remove_suggested_price'), 11, 2);
add_filter('woocommerce_format_price_range', array('ThemeWooCommerce', 'format_price_range'), 10, 3);
add_action('woocommerce_before_shop_loop', array('ThemeWooCommerce', 'result_count_start'), 19);
add_action('woocommerce_before_shop_loop', array('ThemeWooCommerce', 'result_count_end'), 31);
add_filter('woocommerce_before_widget_product_list', array('ThemeWooCommerce', 'products_widget_start'));
add_filter('woocommerce_after_widget_product_list', array('ThemeWooCommerce', 'products_widget_end'));
add_filter('manage_edit-shop_order_columns', array('ThemeWooCommerce', 'add_purchased_column_header'));
add_action('manage_shop_order_posts_custom_column', array('ThemeWooCommerce', 'render_purchased_column'));
add_action('groups_created_user_group', array('ThemeWooCommerce', 'add_back_issue_access'), 10, 2);
add_action('groups_deleted_user_group', array('ThemeWooCommerce', 'remove_back_issue_access'), 10, 2);
add_shortcode('fic_accepted_payment_methods', array('ThemeWooCommerce', 'accepted_payment_methods'));
add_shortcode('product_new_page', array('ThemeWooCommerce', 'product_new_page'));

?>
