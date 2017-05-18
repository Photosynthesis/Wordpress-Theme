<?php
/* Export All AWPCP Ads to a CSV */

require_once "wp-load.php";

function main() {
    $ads = AWPCP_Ad::query(array());

    $output = array();
    foreach ($ads as $ad) {
        $payment_term = $ad->get_payment_term();
        $data = array(
            $ad->ad_contact_email,
            $ad->ad_title,
            get_userdata($ad->user_id)->user_login,
            $ad->ad_startdate,
            $payment_term->name,
            $payment_term->duration_amount,
        );
        $output[] = $data;
    }

    $csv_file = fopen("awpcp_export.txt", "w");
    fputcsv($csv_file, 
        array("email", "title", "user", "date", "price", "category", "duration"));
    foreach ($output as $fields) {
        fputcsv($csv_file, $fields);
    }
    fclose($csv_file);
}

main();
?>
