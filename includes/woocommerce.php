<?php
/** General Customizations to WooCommerce for the FIC Wordpress Site
 *
 * @category FIC
 * @package  FIC_WC
 * @author   Pavan Rikhi <pavan@ic.org>
 * @license  GPLv3 https://www.gnu.org/licenses/gpl-3.0.html
 * @link     http://www.ic.org
 */

class FIC_WC
{
    /** Reduce the height inline-style from the FlickRocket iframe */
    public static function fix_flickrocket_iframe_display($user) {
        echo <<<javascript
<script type='text/javascript'>
jQuery(document).ready(function() {
    // Fix the wrapping div
    jQuery('#post-8 > div > div > div:nth-child(9)').css(
        {'margin-top': '10px', 'margin-bottom': '0', 'width': '100%'});
    // Fix the iFrame
    jQuery('.woocommerce div iframe').height('301px');
});
</script>
javascript;
    }

    private static $membership_product_id = 14602;

    public static function activate_directory_listing_membership($subscription) {
        global $wpdb;
        $notification_email = 'fic.virginia@gmail.com';

        foreach ($subscription->get_items() as $item) {
            if ($item['product_id'] == FIC_WC::$membership_product_id &&
                    $item['pa_membership-category'] == 'community') {
                $community_name = $item['Name of Individual, Community, or Organization:'];

                $community_id = FIC_DIR_DB::get_community_id_by_name($community_name);
                if ($community_id !== false) {
                    $order_date = date_parse($subscription->order_date);

                    /* Enable Membership */
                    FIC_DIR_DB::update_or_insert_item_meta(
                        FIC_DIR_DB::$is_member_field_id, $community_id, "Yes");

                    /* Set Start Date */
                    $start_date = FIC_DIR_DB::get_item_meta(
                        FIC_DIR_DB::$membership_start_field_id, $community_id);
                    if ($start_date === false || $start_date->meta_value === '') {
                        $start_timestamp = FIC_WC::timestamp_from_date($order_date);
                        $start_date = strftime('%m/%d/%Y', $start_timestamp);
                        FIC_DIR_DB::update_or_insert_item_meta(
                            FIC_DIR_DB::$membership_start_field_id, $community_id,
                            $start_date);
                    }

                    /* Update Expiration Date */
                    $order_date['year'] += 1;
                    $membership_end_timestamp = FIC_WC::timestamp_from_date($order_date);
                    $membership_end_date = strftime('%m/%d/%Y', $membership_end_timestamp);
                    $existing_end_date = FIC_DIR_DB::get_item_meta(
                        FIC_DIR_DB::$membership_end_field_id, $community_id);
                    if ($existing_end_date !== false) {
                        $expiration = date_parse($existing_end_date->meta_value);
                        if ($expiration['error_count'] > 0) {
                            FIC_DIR_DB::update_or_insert_item_meta(
                                FIC_DIR_DB::$membership_end_field_id,
                                $community_id, $membership_end_date);
                        } else {
                            $expiration['year'] += 1;
                            $new_end_date = strftime('%m/%d/%Y',
                                FIC_WC::timestamp_from_date($expiration));
                            FIC_DIR_DB::update_or_insert_item_meta(
                                FIC_DIR_DB::$membership_end_field_id,
                                $community_id, $new_end_date);
                        }
                    } else {
                        FIC_DIR_DB::update_or_insert_item_meta(
                            FIC_DIR_DB::$membership_end_field_id,
                            $community_id, $membership_end_date
                        );
                    }
                    $order = $subscription->get_last_order('all', 'any');
                    $subscription_url = get_admin_url(
                        null, 'post.php?action=edit&post=' . $order->id);
                    $msg = "Successfully enabled Community Membership for '{$community_name}'!" .
                        "\n\nSubscription: {$subscription_url}";
                    wp_mail($notification_email, '[FIC] Automatic Community Membership Activation Successful.', $msg);
                } else {
                    $order = $subscription->get_last_order('all', 'any');
                    $subscription_url = get_admin_url(
                        null, 'post.php?action=edit&post=' . $order->id);
                    $msg = "Could not match the Community Membership for '{$community_name}' with a Directory Listing." .
                        "\n\nSubscription: {$subscription_url}";
                    wp_mail($notification_email, '[FIC] Automatic Community Membership Activation Failed', $msg);
                }
            }
        }
    }

    private static function timestamp_from_date($date) {
        return mktime(
            $date['hour'], $date['minute'], $date['second'],
            $date['month'], $date['day'], $date['year']);
    }

    public static function validate_community_name_exists($is_valid, $product_id) {
        global $wpdb;
        if ($is_valid && $product_id == FIC_WC::$membership_product_id) {
            $subscription_product = new WC_Product_subscription($product_id);
            if ($_POST['attribute_pa_membership-category'] == 'community') {
                $community_name = $_POST['addon-14602-name-of-individual-community-or-organ-1'][0];
                if (FIC_DIR_DB::get_community_id_by_name($community_name) === false) {
                    wc_add_notice(__("Community category members are entitled to have a membership badge added to their online directory listing. We couldn't find a matching community in our directory so a membership badge will not be automatically activated or extended. If you have a listing and want to have the member badge added to the listing (or renewed), please re-add the Membership to your cart with the name of the community exactly as it appears in our online Directory or contact support@ic.org and provide the name of the listed community.", 'fic-wc'), 'notice');
                } else {
                    wc_add_notice(__('We were able to find your community in our Directory. A membership badge for your intentional community will be added(or extended) after checkout.', 'fic-wc'));
                }
                return $is_valid;
            }
        }
        return $is_valid;
    }

    public static function show_accepted_payment_methods() {
        $path = get_stylesheet_directory_uri() . "/img/cc-logos/";

        $method_image_to_name = array(
            'amex' => 'American Express',
            'discover' => 'Discover',
            'mastercard' => 'MasterCard',
            'paypal' => 'PayPal',
            'visa' => 'Visa',
        );

        $content = "<span class='payment-methods-widget'>";
        foreach ($method_image_to_name as $image => $name) {
            $image_path = "{$path}{$image}.png";
            $content .= "<span class='payment-method-image'>";
            $content .= "<img alt='{$name}' title='{$name}' src='{$image_path}' />";
            $content .= "</span>";
        }
        $content .= "</span>";

        return $content;
    }
}


add_action('woocommerce_after_my_account',
    array('FIC_WC', 'fix_flickrocket_iframe_display'));

add_action('woocommerce_subscription_payment_complete',
    array('FIC_WC', 'activate_directory_listing_membership'));

add_action('woocommerce_add_to_cart_validation',
    array('FIC_WC', 'validate_community_name_Exists'), 10, 2);

add_shortcode('fic_accepted_payment_methods',
    array('FIC_WC', 'show_accepted_payment_methods'));

?>
