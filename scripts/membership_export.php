<?php
/** Export WP Users That Are FIC Members(`email;firstname;lastname;company`) **/

require_once __DIR__ . '/../../../../wp-load.php';

$fic_membership_group_id = 4;

$user_ids = (new Groups_Group($fic_membership_group_id))->user_ids;

$data = array();
foreach ($user_ids as $user_id) {
  $user = get_userdata($user_id);
  $customer = new WC_Customer($user->ID);

  $company = $customer->get_billing_company();
  $company = $company ? $company : $customer->get_shipping_company();

  $first_name = $user->first_name;
  $first_name = $first_name ? $first_name : $customer->get_billing_first_name();
  $first_name = $first_name ? $first_name : $customer->get_shipping_first_name();

  $last_name = $user->last_name;
  $last_name = $last_name ? $last_name : $customer->get_billing_last_name();
  $last_name = $last_name ? $last_name : $customer->get_shipping_last_name();

  $data[] = array(
    'email' => $user->user_email,
    'firstname' => $first_name,
    'lastname' => $last_name,
    'company' => $company,
  );
}

$file = fopen('membership_export.csv', 'w');
  fputcsv($file, array('email', 'firstname', 'lastname', 'company'), ',');
foreach ($data as $row) {
  fputcsv($file, array($row['email'], $row['firstname'], $row['lastname'], $row['company']), ',');
}
fclose($file);

?>
