<?php

require_once __DIR__ . '/../../../../wp-load.php';

function main() {
  $customers = get_customers();
  echo "Customers: " . sizeof($customers) ;
  add_to_mailpoet($customers);
  echo "DONE!";
}


/* Get Customers Who Have Orders for CMag 178 Digital Variation */
function get_customers() {
  global $wpdb;
  $query = <<<SQL
SELECT
  es.meta_value AS email, fn.meta_value AS first_name,
  ln.meta_value AS last_name, cs.meta_value AS user_id
FROM {$wpdb->prefix}woocommerce_order_itemmeta AS oim
RIGHT JOIN
  {$wpdb->prefix}woocommerce_order_items
  AS oi
  ON oi.order_item_id=oim.order_item_id
LEFT JOIN
  (SELECT * FROM {$wpdb->prefix}postmeta WHERE meta_key='_customer_user')
  AS cs
  ON cs.post_id=oi.order_id
LEFT JOIN
  (SELECT * FROM {$wpdb->prefix}postmeta WHERE meta_key='_billing_email')
  AS es
  ON es.post_id=oi.order_id
LEFT JOIN
  (SELECT * FROM {$wpdb->prefix}postmeta WHERE meta_key='_billing_first_name')
  AS fn
  ON fn.post_id=oi.order_id
LEFT JOIN
  (SELECT * FROM {$wpdb->prefix}postmeta WHERE meta_key='_billing_last_name')
  AS ln
  ON ln.post_id=oi.order_id
WHERE oim.meta_key='_variation_id' AND oim.meta_value=256858
SQL;
  $results = $wpdb->get_results($query);
  return $results;
}

/* Add the Customers to a MailPoet Segment/List */
function add_to_mailpoet($customers) {
  global $wpdb;
  foreach ($customers as $customer) {
    if ($customer->user_id == 0) {
      $data_source = $customer;
    } else {
      $data_source = get_userdata($customer->user_id);
    }
    if (!$data_source->email) { continue; }
    $user_data = array(
      'email' => $data_source->email,
      'first_name' => $data_source->first_name,
      'last_name' => $data_source->last_name,
    );
    $options = array('send_confirmation_email' => false, 'schedule_welcome_email' => false);
    try {
      $subscriber = \MailPoet\API\API::MP('v1')->getSubscriber($data_source->email);
      \MailPoet\API\API::MP('v1')->subscribeToList($subscriber['id'], '14', $options);
    } catch (Exception $e) {
      print_r($e);
      try {
        \MailPoet\API\API::MP('v1')->addSubscriber(
          $user_data, array('14'), $options
        );
      } catch (Exception $e2) { print_r($e2); }
    }
  }
  $subscribe_query = <<<SQL
UPDATE {$wpdb->prefix}mailpoet_subscribers AS subs
RIGHT JOIN
  (SELECT * FROM {$wpdb->prefix}mailpoet_subscriber_segment WHERE segment_id=14)
  AS seg
  ON seg.subscriber_id=subs.id
SET subs.status='subscribed'
SQL;
  $wpdb->get_results($subscribe_query);
}


main();

?>
