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
}
add_action('woocommerce_after_my_account',
    array(FIC_WC, 'fix_flickrocket_iframe_display'));


?>
