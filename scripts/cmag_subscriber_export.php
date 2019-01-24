<?php

require_once __DIR__ . '/../../../../wp-load.php';

function main() {
  $cmag_product_ids = array(13997, 136292);

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
      if (in_array($product->get_product_id(), $cmag_product_ids)) {
        $subscriber_ids[] = $subscription->get_customer_id();
        break;
      }
    }
  }

  $file = fopen('cmag_export.csv', 'w');
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
