<?php

require_once __DIR__ . '/../../../../wp-load.php';

function main() {
  $recurring_donation_product_ids = array(183173, 183172, 183171, 183170, 183179, 183178, 183187, 183186, 183188, 183184, 183183, 183182, 183196, 183195, 242253);

  $sub_posts = get_posts(array(
    'numberposts' => -1,
    'post_type' => 'shop_subscription',
    'post_status' => 'wc-active',
  ));

  $subscriber_ids = array();
  foreach ($sub_posts as $sub_post) {
    $subscription_id = $sub_post->ID;
    $subscription = new WC_Subscription($subscription_id);
    $products = $subscription->get_items();
    foreach ($products as $product) {
      if (is_null($product->get_product_id())) {
        echo "null id:\n";
        print_r($product);
        echo "\n";
      }
      if (in_array($product->get_product_id(), $recurring_donation_product_ids)) {
        $subscriber_ids[] = $subscription->get_customer_id();
        break;
      }
    }
  }

  $file = fopen('woo_recurring_donation_export.csv', 'w');
  foreach($subscriber_ids as $user_id) {
    $user = get_userdata($user_id);
    $first_name = $user->first_name;
    $last_name = $user->last_name;
    fputcsv($file, array($user->user_email, $first_name, $last_name), ',');
  }
  fclose($file);
}

main();
?>
