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
    /* Hide the Price Field in the Ad Forms */
    public static function hide_price($form) {
        if ($form['name'] == 'advert') {
            foreach ($form['field'] as $key => $field) {
                if ($field['name'] == 'adverts_price') {
                    unset($form['field'][$key]);
                }
            }
        }
        return $form;
    }

    /* Set the Category Slug to "community-classifieds" */
    public static function customize_taxonomy($args) {
        if(!isset($args["rewrite"])) {
            $args["rewrite"] = array();
        }

        $args["rewrite"]["slug"] = "ad-category";
        return $args;
    }

    /* Allow Using Hidden WooCommerce Products */
    public static function allow_hidden($args) {
        $args["meta_query"][0]["value"] = array("hidden", "visible");
        return $args;
    }
}

add_filter("adverts_form_load", array("FIC_Adverts", "hide_price"));
add_action("adverts_register_taxonomy", array("FIC_Adverts", "customize_taxonomy"));
add_filter("adext_wc_payments_products_new", array("FIC_Adverts", "allow_hidden"));
add_filter("adext_wc_payments_products_renew", array("FIC_Adverts", "allow_hidden"));


?>
