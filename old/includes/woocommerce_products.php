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
    $price_is_zero =
        (strpos($price, '&#36;0.00') || strpos($price, 'Free!')) !== false;
    if ($is_suggested_price_product || $price_is_zero) {
        $price = '';
    }
    return $price;
}
remove_filter('woocommerce_get_price_html', 'nyp_price_html');
add_filter('woocommerce_get_price_html', 'hide_suggested_prices', 99, 2);


/** Modify the WooCommerce `product` shortcode to open in a new page.
 *
 * You must pass an `id` parameter to the shortcode containg the id of the
 * Product.
 *
 * @param array $atts The Shortcode Parameters
 *
 * @return string The Add to Cart HTML
 */
function product_new_page($atts)
{
    extract(shortcode_atts(array('id' => 0), $atts));
    $text = do_shortcode("[product id='$id']");

    return str_replace('<a', '<a target="_blank"', $text);
}
add_shortcode('product_new_page', 'product_new_page');
