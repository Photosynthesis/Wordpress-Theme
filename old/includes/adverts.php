<?php
/** Customizations for the WPAdverts Plugin
 * @category FIC
 * @package  FIC_Adverts
 * @author   Pavan Rikhi <pavan@ic.org>
 * @license  GPLv3 https://www.gnu.org/licenses/gpl-3.0.html
 * @link     http://www.ic.org
 */

class FIC_Adverts
{
    /* Allow Using Hidden WooCommerce Products */
    public static function allow_hidden($args) {
        $args["meta_query"][0]["value"] = array("hidden", "visible");
        return $args;
    }
}

add_filter("adext_wc_payments_products_new", array("FIC_Adverts", "allow_hidden"));
add_filter("adext_wc_payments_products_renew", array("FIC_Adverts", "allow_hidden"));


?>
