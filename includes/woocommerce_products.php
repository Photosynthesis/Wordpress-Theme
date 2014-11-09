<?php
/** WooCommerce Product Customizations
 *
 * @category FIC
 * @package  FIC_WooCommerce
 * @author   Pavan Rikhi <pavan@ic.org>
 * @license  GPLv3 https://www.gnu.org/licenses/gpl-3.0.html
 * @link     http://www.ic.org
 */


/** Hide the price for Suggested Price Products */
function hide_suggested_prices($price, $product)
{
    if (WC_Name_Your_Price_Helpers::is_nyp($product)) {
        $price = '';
    }
    return $price;
}
remove_filter('woocommerce_get_price_html', 'nyp_price_html');
add_filter('woocommerce_get_price_html', 'hide_suggested_prices', 99, 2);
