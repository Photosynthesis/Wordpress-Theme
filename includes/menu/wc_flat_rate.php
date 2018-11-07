<?php
/** Admin Page Allowing Customization of WooCommerce Flat Rate Options.
 *
 * It is a ultralight wrapper around an Elm application.
 *
 * See `/includes/api/flat_rate.php` for the POST handling code.
 */
class ThemeFlatRateMenu
{
  public static $page_name = 'fic-flate-rate';
  public static $page_title = 'FIC WooCommerce Flat Rate Settings';

  public static function render_page() {
    echo '<div id="elm-admin-flat-rate"></div>';
  }
}

?>
