<?php
/** Customize WooCommerce Plugins **/
// TODO: This is getting big, it could be split into multiple files
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
  const membership_back_issues_via = 'FIC-Membership';

  /* Provide Back Issue Downloads For A User in the FIC Membership Group. */
  public static function add_back_issue_access($user_id, $group_id) {
    if ($group_id != self::fic_membership_group_id) { return; }

    $back_issues = wc_get_product(self::membership_back_issues_product_id);

    $order = wc_create_order(array(
      'customer_id' => $user_id,
      'customer_note' => 'FIC Membership Allows Access to Back Issue Downloads',
      'created_via' => self::membership_back_issues_via,
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
      'created_via' => self::membership_back_issues_via,
      'limit' => 1,
    ));

    foreach ($orders as $order) {
        wc_delete_shop_order_transients($order->get_id());
        $order->delete();
    }
  }

  /* Add A Flat Rate Shipping Charge for Applicable Items */
  const flat_shipping_rate_id = "flat_rate:6";
  const flat_rate_variation_ids = array(241498, 241528, 241523);
  const us_flat_rate_cost = 7;
  const global_flat_rate_cost = 15;
  public static function add_flat_rate_charges($rates, $package) {
    $total_count = 0;
    $flat_rate_count = 0;
    foreach ($package['contents'] as $product) {
      $total_count += $product['quantity'];
      if (array_search($product['variation_id'], self::flat_rate_variation_ids) !== false) {
        $flat_rate_count += $product['quantity'];
      }
    }

    $flat_rate_cost = $package['destination']['country'] === 'US' ?
      self::us_flat_rate_cost : self::global_flat_rate_cost;

    if ($flat_rate_count === $total_count) {
      // No Items
      if ($total_count === 0) { return $rates; }

      // Only Flat Rate Items
      $rates[self::flat_shipping_rate_id]->cost = $flat_rate_cost;
      return array(self::flat_shipping_rate_id => $rates[self::flat_shipping_rate_id]);
    }

    unset($rates[self::flat_shipping_rate_id]);

    // No Flat Rate Items
    if ($flat_rate_count === 0) { return $rates; }

    // Mixed Flat Rate & Normal Items

    $normal_items = array();
    while (sizeof($package['contents']) > 0) {
      end($package['contents']);
      $item_key = key($package['contents']);
      reset($package['contents']);
      $item = array_pop($package['contents']);
      if (array_search($item['variation_id'], self::flat_rate_variant_ids) === false) {
        $normal_items[$item_key] = $item;
      }
    }
    $package['contents'] = $normal_items;

    $shipping = WC_Shipping::instance();
    $rates = $shipping->calculate_shipping_for_package($package)['rates'];

    foreach ($rates as &$rate) {
      $rate->cost += $flat_rate_cost;
    }

    return $rates;
  }

  /* Email Customers if their Subscription Renewal's Payment Fails */
  public static function email_customer_on_renewal_fail($subscription) {
    $customer = get_user_by('ID', $subscription->get_user_id());
    $my_account_link = self::get_my_account_page_url();
    $subscription_link = $my_account_link . "view-subscription/{$subscription->get_ID()}/";

    $order_items = $subscription->get_items();
    $product_name = sizeof($order_items) > 0 ?
      array_shift($order_items)->get_product()->get_name() : "Subscription";

    $to = $customer->data->user_email;
    $subject = "[FIC] Your {$product_name} Renewal Payment Has Failed";
    $message = "Hello {$customer->data->user_nicename},\n\n" .
      "This is a notification that your automatic payment for your {$product_name} has failed.\n\n" .
      "You can manually renew by clicking 'Resubscribe' on the Subscription's Page:\n\n" .
      "\t\t{$subscription_link}\n\n";

    wp_mail($to, $subject, $message, array('From: FIC <no-reply@ic.org>'));
  }

  const magazine_subscription_product_id = 13997;
  const product_ids_with_download_notifications = array(
    self::magazine_subscription_product_id,
    self::membership_back_issues_product_id,
  );

  /* Give Download Access to Membership Back Issues & Send New Downloads
   * Notification for Membership Back Issues & Magazine Subscriptions
   *
   * Bits lifted from the Grant Download Permissions plugin.
   */
  public static function give_download_access_and_notify($product_id, $variation_id, $downloadable_files) {
    if (!in_array($product_id, self::product_ids_with_download_notifications)) {
      return;
    } else if ($variation_id) {
      $product_id = $variation_id;
    }

    global $wpdb;

    $product = wc_get_product($product_id);

    $current_downloads = array_keys($product->get_downloads());
    $updated_downloads = array_keys($downloadable_files);
    $new_download_ids = array_filter(array_diff($updated_downloads, $current_downloads));

    if (sizeof($new_download_ids) === 0) { return; }

    $customer_ids = array();
    if ($product_id === self::membership_back_issues_product_id) {
      // Grant Permissions to Back Issues
      $orders = wc_get_orders(array(
        'created_via' => self::membership_back_issues_via
      ));
      foreach ($orders as $order) {
        foreach ($new_download_ids as $new_download_id) {
          wc_downloadable_file_permission($new_download_id, $product_id, $order);
        }
        $customer_ids[] = $order->get_customer_id();
      }
    } else {
      $customers_query = <<<SQL
        SELECT sub_customer.meta_value
        FROM {$wpdb->prefix}posts as subs
        -- Join the Customer ID for the Sub
        INNER JOIN
          (SELECT meta_value, meta_key, post_id
           FROM {$wpdb->prefix}postmeta
           WHERE (`meta_key`='_customer_user')
          ) AS sub_customer ON sub_customer.post_id=subs.ID
        -- Only Keep Subs w/ Orders that Contain the Product
        INNER JOIN
          (SELECT orders.post_type, orders.ID
           FROM {$wpdb->prefix}posts AS orders
           -- Only Keep Orders Matching the Product
           INNER JOIN
             (SELECT items.`order_id` FROM {$wpdb->prefix}woocommerce_order_items AS items
              INNER JOIN
                (SELECT * FROM {$wpdb->prefix}woocommerce_order_itemmeta
                          WHERE (`meta_value`="{$product_id}" AND
                                (`meta_key`="_product_id" OR `meta_key`="_variation_id")
                                )
                ) AS item_meta ON item_meta.order_item_id=items.order_item_id
             ) AS order_items ON order_items.order_id=orders.ID
           WHERE orders.post_type='shop_order'
          ) AS orders ON orders.ID=subs.post_parent
        WHERE (subs.post_type='shop_subscription' AND
               subs.post_status='wc-active')
SQL;
      foreach ($wpdb->get_results($customers_query, ARRAY_N) as $result) {
        $customer_ids[] = $result[0];
      }
      $customer_ids = array_unique($customer_ids);
    }

    // Send Notification Emails to Customers
    $product_name = $product->get_name();
    $subject = "[FIC] Downloads Have Been Added to Your {$product_name}";

    $downloads_page_link = self::get_my_account_page_url() .  "downloads/";
    $common_message =
      "This a notification that your {$product_name} has had new downloads added to it.\n\n" .
      "The new items have been attached to this email, but you can also download them, " .
      "as well as all of your other downloads, from the Downloads section " .
      "of the My Account Page:\n\n" .
      "\t\t{$downloads_page_link}\n\n";

    $attachments = array();
    foreach ($new_download_ids as $new_download_id) {
      $download_url = $downloadable_files[$new_download_id]->get_file();
      $attachments[] = WP_CONTENT_DIR .
        substr($download_url, strpos($download_url, 'wp-content') + 10);
    }

    foreach ($customer_ids as $customer_id) {
      $customer = get_user_by('ID', $customer_id) ;

      $to = $customer->data->user_email;
      $message = "Hello {$customer->data->user_nicename},\n\n{$common_message}";

      wp_mail($to, $subject, $message, array('From: FIC <no-reply@ic.org>'),
        $attachments);
    }
  }

  /* Return a URL to the My Account Page */
  private static function get_my_account_page_url() {
    $my_account_page = get_option('woocommerce_myaccount_page_id');
    if ($my_account_page) {
      $my_account_link = get_permalink($my_account_page);
    } else {
      $my_account_link = "https://www.ic.org/my-fic-account/";
    }
    return $my_account_link;
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
add_filter('woocommerce_package_rates', array('ThemeWooCommerce', 'add_flat_rate_charges'), 99, 2);
add_action('woocommerce_subscription_renewal_payment_failed', array('ThemeWooCommerce', 'email_customer_on_renewal_fail'));
add_action('woocommerce_process_product_file_download_paths', array('ThemeWooCommerce', 'give_download_access_and_notify'), 11, 3);
add_shortcode('fic_accepted_payment_methods', array('ThemeWooCommerce', 'accepted_payment_methods'));
add_shortcode('product_new_page', array('ThemeWooCommerce', 'product_new_page'));

?>
