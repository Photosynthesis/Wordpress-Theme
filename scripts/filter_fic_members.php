<?php
/** Clean Up the FIC Members Group, Keeping Users w/ Active Membership Sub **/

require_once __DIR__ . '/../../../../wp-load.php';
echo ob_get_clean();

$fic_membership_group_id = 4;
$membership_sub_product_id = 14602;

$user_ids = (new Groups_Group($fic_membership_group_id))->user_ids;

foreach ($user_ids as $user_id) {
  $active_subs = get_posts(array(
    'numberposts' => -1,
    'meta_key' => '_customer_user',
    'meta_value' => $user_id,
    'post_type' => 'shop_subscription',
    'post_status' => 'wc-active',
  ));

  $found_membership_sub = false;
  foreach ($active_subs as $sub_post) {
    $sub = new WC_Subscription($sub_post->ID);
    foreach ($sub->get_items() as $sub_item) {
      if ($sub_item->get_product_id() == $membership_sub_product_id) {
        $found_membership_sub = true;
        break;
      }
    }
    if ($found_membership_sub) { break; }
  }
  if (!$found_membership_sub) {
    Groups_User_Group::delete($user_id, $fic_membership_group_id);
  }
}

?>
