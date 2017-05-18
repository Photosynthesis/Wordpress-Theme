<?php
/** Send Reminder & Renewal Emails for Ads that will expire in the next 5/1/0
 *  days.
 *  
 *  This script is run daily by cron.
 */

require_once __DIR__ . '/../../../../wp-load.php';


/* Send Expiration Notices for Ads Expiring in 1 or 5 Days */
function main() {
    $days_before_expiration = array(5, 1, 0);
    foreach ($days_before_expiration as $days_from_now) {
        foreach (get_expiring_ads($days_from_now) as $ad) {
            send_going_to_expire_email($ad, $days_from_now);
        }
    }
}


/* Get the timestamp representing the start of the day $days_from_now. */
function get_timestamp_of_day($days_from_now) {
    $timestamp = strtotime("+" . $days_from_now . " days");
    return strtotime(date('Y-m-d', $timestamp));
}


/* Get every ad that expires in $days_from_now. */
function get_expiring_ads($days_from_now) {
    $expiration_start_date = get_timestamp_of_day($days_from_now);
    $expiration_end_date = get_timestamp_of_day($days_from_now + 1);
    $args = array(
        'post_type' => 'advert',
        'meta_query' => array(
            'relation' => 'AND',
            array(
                'key' => '_expiration_date',
                'value' => $expiration_end_date,
                'compare' => '<'
            ),
            array(
                'key' => '_expiration_date',
                'value' => $expiration_start_date,
                'compare' => '>='
            )
        )
    );
    if ($days_from_now !== 0) {
        $args['post_status'] = 'publish';
    }
    $query = new WP_Query($args);
    return $query->posts;
}


/* Send an Expiration Notification to the Owner of an Ad */
function send_going_to_expire_email($ad, $days_from_now) {
    $pluralize = $days_from_now > 1 ? "s" : "";
    $ad_owner = get_user_by('ID', $ad->post_author);
    $my_account_page = get_option('woocommerce_myaccount_page_id');
    if ($my_account_page) {
        $my_account_link = get_permalink($my_account_page);
    } else {
        $my_account_link = "https://www.ic.org/my-fic-account/";
    }
    $renewal_link = $my_account_link . "?advert_renew={$ad->ID}";
    $ad_link = get_post_permalink($ad->ID);

    $to = $ad_owner->data->user_email;

    if ($days_from_now !== 0) {
        $subject = "[FIC] Your Ad Expires in {$days_from_now} Day{$pluralize}";
        $message = "Hello {$ad_owner->data->user_nicename},\n\n" .
            "This is a notification that your classified ad, \"{$ad->post_title}\", will expire in {$days_from_now} day{$pluralize}.\n\n" .
            "You can view your ad here:\n\n" .
            "\t\t{$ad_link}\n\n" .
            "To renew your ad now, you can click the following link:\n\n" .
            "\t\t{$renewal_link}\n\n" .
            "You can also view, edit, or renew any of your ads from the My Account page:\n\n" .
            "\t\t{$my_account_link}\n"
            ;
    } else {
        $subject = "[FIC] Your Ad Has Expired";
        $message = "Hello {$ad_owner->data->user_nicename},\n\n" .
            "This is a notification that your classified ad, \"{$ad->post_title}\", has expired.\n\n" .
            "To renew your ad, you can click the following link:\n\n" .
            "\t\t{$renewal_link}\n\n"
            ;
    }
    $message .= "\n\n---\nFor assistance with ads and for special offers, please contact ads@ic.org";

    wp_mail($to, $subject, $message, array('From: FIC <no-reply@ic.org>'));
}


main();

?>
