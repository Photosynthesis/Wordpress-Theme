<?php

define('WP_USE_THEMES', false);

$base = dirname(dirname(__FILE__));
require($base.'../../../../../wp-load.php');

if(!current_user_can('manage_options')) {
  die("Permission denied");
}


$errors = array();
$stats = array();

global $wpdb;

$pfx = "3uOgy46w_";

$sql = "SELECT * FROM {$pfx}give_subscriptions s
JOIN {$pfx}give_donors d ON s.customer_id = d.id
LEFT JOIN {$pfx}users u ON d.user_id = u.ID WHERE s.status = 'active'";

$active_subscriptions = $wpdb->get_results( $sql, ARRAY_A );

$headers = array_keys($active_subscriptions[0]);

if(count($active_subscriptions) < 1){
  die("No data to export");
}

$datetime = date('Y-m-d_His');

$fp = fopen('php://output', 'w');
if ($fp && $active_subscriptions) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="active_subscriptions_'.$datetime.'.csv"');
    header('Pragma: no-cache');
    header('Expires: 0');
    fputcsv($fp, $headers);
    foreach ($active_subscriptions as $row) {
        fputcsv($fp, array_values($row));
    }
    die;
}
?>
