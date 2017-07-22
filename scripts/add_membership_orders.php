<?php
/** Add Orders Granting Access for FIC Members to the Back Issues **/

require_once __DIR__ . '/../../../../wp-load.php';

$membership_back_issues_product_id = 242058;
$fic_membership_group_id = 4;

if (file_exists('memberships_to_process.php')) {
  require 'memberships_to_process.php';
} else {
  $user_ids = (new Groups_Group($fic_membership_group_id))->user_ids;
}

$back_issues = wc_get_product($membership_back_issues_product_id);

while (sizeof($user_ids) > 0) {
  $user_id = array_pop($user_ids);

  $order = wc_create_order(array(
    'customer_id' => $user_id,
    'customer_note' => 'FIC Membership Allows Access to Back Issue Downloads',
    'created_via' => 'FIC-Membership'
  ));
  $order->add_product($back_issues);
  $order->calculate_totals();
  $order->update_status('completed');

  file_put_contents(
    "memberships_to_process.php",
    '<?php $user_ids = ' . var_export($user_ids, true) . "; ?>\n"
  );
}

echo "FINISHED!";


?>
