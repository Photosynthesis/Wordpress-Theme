<?php
/** WooCommerce Product Customizations
 *
 * @category FIC
 * @package  FIC_WooCommerce
 * @author   Pavan Rikhi <pavan@ic.org>
 * @license  GPLv3 https://www.gnu.org/licenses/gpl-3.0.html
 * @link     http://www.ic.org
 */


/** Hide the price for Suggested/Zero Price Products */
function hide_suggested_prices($price, $product)
{
    $is_suggested_price_product = WC_Name_Your_Price_Helpers::is_nyp($product);
    // `&#36;` is the HTML entity for `$`
    $price_is_zero = strpos($price, '&#36;0.00') !== false;
    if ($is_suggested_price_product || $price_is_zero) {
        $price = '';
    }
    return $price;
}
remove_filter('woocommerce_get_price_html', 'nyp_price_html');
add_filter('woocommerce_get_price_html', 'hide_suggested_prices', 99, 2);
