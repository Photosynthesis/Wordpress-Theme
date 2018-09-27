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
  const wisdom_authors_digital_product_id = 267811;
  const wisdom_digital_group_id = 5;
  const wisdom_digital_via = 'FIC-Wisdom-Authors';
  // Array of orders to create when a user joins a group
  const group_orders = array(
    array(
      'product_id' => self::membership_back_issues_product_id,
      'group_id' => self::fic_membership_group_id,
      'via' => self::membership_back_issues_via,
      'note' => 'FIC Membership Allows Access to Back Issue Downloads',
    ),
    array(
      'product_id' => self::wisdom_authors_digital_product_id,
      'group_id' => self::wisdom_digital_group_id,
      'via' => self::wisdom_digital_via,
      'note' => 'Authoring a Wisdom Article Gives Access to All Wisdom Volumes',
    )
  );

  /* Add related orders for the group the user is joining */
  public static function add_group_order($user_id, $group_id) {
    foreach (self::group_orders as $group_order) {
      self::add_order_on_group_join(
        $group_order['product_id'],
        $group_order['group_id'],
        $group_order['via'],
        $group_order['note'],
        $user_id,
        $group_id
      );
    }
  }

  /* Delete related orders for the group the user is leaving */
  public static function remove_group_order($user_id, $group_id) {
    foreach (self::group_orders as $group_order) {
      self::remove_order_on_group_leave(
        $group_order['group_id'],
        $group_order['via'],
        $user_id,
        $group_id
      );
    }
  }

  /* Add an order for the given product if the group ids match */
  public static function add_order_on_group_join($product_id, $target_group_id, $order_via, $note, $user_id, $group_id) {
    if ($group_id != $target_group_id) { return; }

    $product = wc_get_product($product_id);

    $order = wc_create_order(array(
      'customer_id' => $user_id,
      'customer_note' => $note,
      'created_via' => $order_via,
    ));
    $order->add_product($product);
    $order->calculate_totals();
    $order->update_status('completed');
  }

  /* Delete all orders with the given `via` line if the group ids match */
  public static function remove_order_on_group_leave($target_group_id, $order_via, $user_id, $group_id) {
    if ($group_id != $target_group_id) { return; }

    $orders = wc_get_orders(array(
      'customer_id' => $user_id,
      'created_via' => $order_via,
      'limit' => 0,
    ));

    foreach ($orders as $order) {
        wc_delete_shop_order_transients($order->get_id());
        $order->delete();
    }
  }

  const flat_rate_shipping_id = "flat_rate:6";
  const international_base_cost = 18;
  const dropshipped_variation_ids = array(241498, 241528, 241523);
  const board_game_product_id = 262010;
  const wisdom_volume_variation_ids = array(264135, 264138, 264125, 259375);
  const wisdom_set_variation_id = 265599;
  const wall_calendar_product_id = 267438;
  /* Apply various flat rate shipping plans to a cart */
  public static function apply_flat_rate_charges($rates, $package) {
    /* array of:
      *  countries => (code => dollar charge)
      *  global => fallback dollar charge
      *  variations => [variation ids]
      *  products => [product ids]
      *  ignore_domestic => bool to fallback to automatic domestic prices
      */
    $flat_rates = array(
      // dropshipped
      array(
        'countries' => array('US' => 7),
        'global' => 15,
        'variations' => self::dropshipped_variation_ids,
        'products' => array(),
        'ignore_domestic' => FALSE,
      ),
      // board game
      array(
        'countries' => array('US' => 9.50, 'CA' => 25),
        'global' => 60,
        'variations' => array(),
        'products' => array(self::board_game_product_id),
        'ignore_domestic' => FALSE,
      ),
      // wisdom volumes
      array(
        'countries' => array(),
        'global' => 8,
        'variations' => self::wisdom_volume_variation_ids,
        'products' => array(),
        'ignore_domestic' => TRUE,
      ),
      // wisdom set
      array(
        'countries' => array(),
        'global' => 18,
        'variations' => array(self::wisdom_set_variation_id),
        'products' => array(),
        'ignore_domestic' => TRUE,
      ),
      // wall calendar
      array(
        'countries' => array('CA' => 10),
        'global' => 14,
        'variations' => array(),
        'products' => array(self::wall_calendar_product_id),
        'ignore_domestic' => TRUE,
      )
    );

    /* Ignore empty carts */
    if (count($package['contents']) === 0) { return $rates; }

    $shipping_country = $package['destination']['country'];

    /* Count the occurence of each flat rate's product */
    // initialize counts
    $flat_rate_counts = array();
    foreach ($flat_rates as $index => $flat_rate) {
      $flat_rate_counts[$index] = 0;
    }

    $total_count = 0;
    // track products & variants for mixed cart case
    $flat_variations_in_package = array();
    $flat_products_in_package = array();
    foreach ($package['contents'] as $product) {
      $total_count += $product['quantity'];
      foreach ($flat_rates as $index => $flat_rate) {
        $variation_match = array_search($product['variation_id'], $flat_rate['variations']) !== FALSE;
        $product_match = array_search($product['product_id'], $flat_rate['products']) !== FALSE;
        $is_exempt_domestic = $shipping_country === 'US' && $flat_rate['ignore_domestic'] === TRUE;
        if (($variation_match || $product_match) && !$is_exempt_domestic) {
          $flat_rate_counts[$index] += $product['quantity'];
          if ($variation_match) {
            $flat_variations_in_package[] = $product['variation_id'];
          } else {
            $flat_products_in_package[] = $product['product_id'];
          }
          // Only charge one flat rate at most to each product
          break;
        }
      }
    }
    $total_flat_rate_count = array_sum($flat_rate_counts);

    /* Return the original rates or base international rate if no flat charges apply */
    if ($total_flat_rate_count === 0) {
      if ($shipping_country == 'US') {
        unset($rates[self::flat_rate_shipping_id]);
        return $rates;
      } else {
        $rates[self::flat_rate_shipping_id]->cost = self::international_base_cost;
        return array(self::flat_rate_shipping_id => $rates[self::flat_rate_shipping_id]);
      }
    }


    /* Determine the cost for each applicable flat rate */
    $flat_rate_costs = array();
    foreach ($flat_rate_counts as $index => $count) {
      $flat_rate_costs[$index] = NULL;
      foreach ($flat_rates[$index]['countries'] as $country_code => $cost) {
        if ($shipping_country === $country_code) {
          $flat_rate_costs[$index] = $cost * $count;
          break;
        }
      }
      if (is_null($flat_rate_costs[$index])) {
        $flat_rate_costs[$index] = $flat_rates[$index]['global'] * $count;
      }
    }
    $total_flat_rate_cost = array_sum($flat_rate_costs);


    /* Return the flat rate cost if purchasing only flat rate items */
    if ($total_count === $total_flat_rate_count) {
      $rates[self::flat_rate_shipping_id]->cost = $total_flat_rate_cost;
      return array(self::flat_rate_shipping_id => $rates[self::flat_rate_shipping_id]);
    }


    /* If international, add base international rate to flat rate item total */
    if ($shipping_country !== 'US') {
      $rates[self::flat_rate_shipping_id]->cost = $total_flat_rate_cost + self::international_base_cost;
      return array(self::flat_rate_shipping_id => $rates[self::flat_rate_shipping_id]);
    }


    /* Calculate the cost of the non-flat rate items & return combination */
    // Disable the flat rate shipping option
    unset($rates[self::flat_rate_shipping_id]);
    // Make new contents array with no flat rate items
    $normal_items = array();
    foreach ($package['contents'] as $item_key => $item) {
      $variation_match = array_search($item['variation_id'], $flat_variations_in_package) !== FALSE;
      $product_match = array_search($item['product_id'], $flat_products_in_package) !== FALSE;
      if (!($variation_match || $product_match)) {
        $normal_items[$item_key] = $item;
      }
    }
    // Calculate shipping cost with new contents
    $package['contents'] = $normal_items;
    $wc_shipping = WC_Shipping::instance();
    $rates = $wc_shipping->calculate_shipping_for_package($package)['rates'];
    // Increase calculated costs by flat rate cost
    foreach ($rates as &$rate) {
      $rate->cost += $total_flat_rate_cost;
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
add_action('groups_created_user_group', array('ThemeWooCommerce', 'add_group_order'), 10, 2);
add_action('groups_deleted_user_group', array('ThemeWooCommerce', 'remove_group_order'), 10, 2);
add_filter('woocommerce_package_rates', array('ThemeWooCommerce', 'apply_flat_rate_charges'), 99, 2);
add_action('woocommerce_subscription_renewal_payment_failed', array('ThemeWooCommerce', 'email_customer_on_renewal_fail'));
add_action('woocommerce_process_product_file_download_paths', array('ThemeWooCommerce', 'give_download_access_and_notify'), 11, 3);
add_shortcode('fic_accepted_payment_methods', array('ThemeWooCommerce', 'accepted_payment_methods'));
add_shortcode('product_new_page', array('ThemeWooCommerce', 'product_new_page'));

?>
