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
  /* Replace and Expand the Logo */
  public static function customize_login_logo() {
    $logo_path = get_stylesheet_directory_uri() . "/img/login-logo.png";
    echo <<<CSS
      <style type="text/css">
        #login h1 a, .login h1 a {
          background-image: url("{$logo_path}");
          background-size: auto;
          width: 300px;
          padding-bottom: 30px;
        }
      </style>
CSS;
  }

  /* Link the Login Logo to the Home Page */
  public static function customize_login_logo_url() {
    return home_url();
  }

  /* Set the Login Logo's Link Title to the Name of the Site */
  public static function customize_login_logo_title() {
    return 'Fellowship of Intentional Community';
  }

  /* Fix AJAX URLs on HTTP pages */
  public static function fix_ajax_url($url) {
    if (!is_admin() && !is_ssl()) {
      $url = str_replace('https:', 'http:', $url);
    }
    return $url;
  }
}

add_action('login_enqueue_scripts', array('FIC_General', 'customize_login_logo'));
add_action('login_headerurl', array('FIC_General', 'customize_login_logo_url'));
add_action('login_headertitle', array('FIC_General', 'customize_login_logo_title'));
add_filter('admin_url', array('FIC_General', 'fix_ajax_url'));

?>
