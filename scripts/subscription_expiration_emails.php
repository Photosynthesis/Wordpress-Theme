<?php
/** Send Reminder Emails for Subscriptions that will expire in the next 10/5/1
 * days.
 *
 * This script is run daily by cron.
 */

require_once __DIR__ . '/../../../../wp-load.php';

/* Send Expiration Notices for Subscriptions Expiring in 1, 5, or 10 Days */
function main() {
  $days_before_expiration = array(10, 5, 1);
  foreach ($days_before_expiration as $days_from_now) {
    foreach (get_expiring_subscriptions($days_from_now) as $subscription) {
      send_going_to_expire_email($subscription, $days_from_now);
    }
  }
}

/* Get the timestamp representing the start of the day $days_from_now. */
function get_mysql_datetime_of_day($days_from_now) {
  $timestamp = strtotime("+" . $days_from_now . " days");
  return date('Y-m-d H:i:s', $timestamp);
}

/* Get the timestamp representing the start of the day $days_from_now. */
function get_timestamp_of_day($days_from_now) {
    $timestamp = strtotime("+" . $days_from_now . " days");
    return strtotime(date('Y-m-d', $timestamp));
}
function get_expiring_subscriptions($days_from_now) {
  $expiration_start_date = get_mysql_datetime_of_day($days_from_now);
  $expiration_end_date = get_mysql_datetime_of_day($days_from_now + 1);
  //$expiration_start_date = get_timestamp_of_day($days_from_now);
  //$expiration_end_date = get_timestamp_of_day($days_from_now + 1);
  $args = array(
    'post_type' => 'shop_subscription',
    'post_status' => 'wc-active',
    'posts_per_page' => -1,
    'meta_query' => array(
      'relation' => 'AND',
      array(
        'key' => '_schedule_end',
        'value' => $expiration_end_date,
        'compare' => '<'
      ),
      array(
        'key' => '_schedule_end',
        'value' => $expiration_start_date,
        'compare' => '>='
      )
    )
  );
  $query = new WP_Query($args);

  $subscriptions = array();

  foreach ($query->posts as $post) {
    $subscription = wcs_get_subscription($post->ID);
    if ($subscription->is_manual()) {
      $subscriptions[] = $subscription;
    }
  }
  return $subscriptions;
}

function send_going_to_expire_email($subscription, $days_from_now) {
  $pluralize = $days_from_now > 1 ? "s" : "";
  $customer = get_user_by('ID', $subscription->get_user_id());
  $my_account_page = get_option('woocommerce_myaccount_page_id');
  if ($my_account_page) {
    $my_account_link = get_permalink($my_account_page);
  } else {
    $my_account_link = "https://www.ic.org/my-fic-account/";
  }
  $sub_id = $subscription->get_ID();
  $subscription_link = $my_account_link . "view-subscription/{$sub_id}/";
  $edit_address_link = $my_account_link . "edit-address/shipping/?subscription={$sub_id}";
  $renewal_link = $my_account_link . "?resubscribe={$sub_id}";

  $order_items = $subscription->get_items();
  $needs_shipping = false;

  foreach ($order_items as $item) {
    if ($item->get_product()->needs_shipping()) {
      $needs_shipping = true;
      break;
    }
  }
  reset($order_items);


  $product_name = sizeof($order_items) > 0 ?
    array_shift($order_items)->get_product()->get_name() : "Subscription";

  $delivery_required_text = "";
  if ($needs_shipping) {
    $delivery_address = $subscription->order->get_formatted_shipping_address();
    if ($delivery_address === "") {
      $delivery_address = $subscription->order->get_formatted_billing_address();
    }
    $delivery_address = str_replace("<br/>", "\n\t\t", $delivery_address);

    $delivery_required_text =
      "The delivery address for this subscription is:\n\n" .
      "\t\t{$delivery_address}\n\n" .
      "You can change your address from the Subscription's Change Address Page:\n\n" .
      "\t\t{$edit_address_link}\n\n";
  }


  $to = $customer->data->user_email;
  $subject = "[FIC] Your {$product_name} Expires in {$days_from_now} Day{$pluralize}";
  $message = "Hello {$customer->data->user_nicename}, \n\n" .
    "This is a notification that your {$product_name}, will expire in {$days_from_now} day{$pluralize}.\n\n" .
    "You can renew now by clicking the following link:\n\n" .
    "\t\t{$renewal_link}\n\n".
    "You can switch to automatic renewals by clicking 'Update Subscription' on the Subscription Page:\n\n" .
    "\t\t{$subscription_link}\n\n" .
    "{$delivery_required_text}" ;

  echo $message;
  //wp_mail($to, $subject, $message, array('From: FIC <no-reply@ic.org>'));
}

main();

?>
