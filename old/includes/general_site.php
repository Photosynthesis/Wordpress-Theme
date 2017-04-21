<?php
/** General Customizations to the FIC Wordpress Site
 *
 *
 * @category FIC
 * @package FIC_General
 * @author   Pavan Rikhi <pavan@ic.org>
 * @license  GPLv3 https://www.gnu.org/licenses/gpl-3.0.html
 * @link     http://www.ic.org
 */

class FIC_General
{
  /* Fix AJAX URLs on HTTP pages - Required by WPAdverts */
  public static function fix_ajax_url($url) {
    if (!is_admin() && !is_ssl()) {
      $url = str_replace('https:', 'http:', $url);
    }
    return $url;
  }
}

add_filter('admin_url', array('FIC_General', 'fix_ajax_url'));

?>
