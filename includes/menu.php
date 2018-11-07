<?php
/** This file contains functions related to the FIC Admin Menu.
 *
 * @category FIC
 * @package FIC_Menu
 * @author   Pavan Rikhi <pavan@ic.org>
 * @license  GPLv3 https://www.gnu.org/licenses/gpl-3.0.html
 * @link     http://www.ic.org
 */

/* Load the Sub-Menu Classes */
require(__DIR__ . '/menu/directory_user_update.php');
require(__DIR__ . '/menu/theme_settings.php');
require(__DIR__ . '/menu/wc_flat_rate.php');

/** The ThemeMenu class is responsible for assembling the various Theme*Menu
 * classes into a single Top-Level Menu in the Wordpress Admin.
 */
class ThemeMenu
{
  public static $menu_name = 'fic-menu';

  /* Assemble the Admin Menu */
  public static function create_menu() {
    add_menu_page('FIC Admin Menu', 'FIC Menu', 'administrator', self::$menu_name,
      array('FIC_Menu', 'render_page'),
      get_stylesheet_directory_uri('stylesheet_directory') .  "/img/logo-admin-fic.png",
      3);

    add_submenu_page(self::$menu_name, ThemeDirectoryUserMenu::$page_title,
      'Directory User Update', 'administrator', ThemeDirectoryUserMenu::$page_name,
      array('ThemeDirectoryUserMenu', 'render_page'));
    add_submenu_page(self::$menu_name, ThemeFlatRateMenu::$page_title,
      'Flat Rate Options', 'administrator', ThemeFlatRateMenu::$page_name,
      array('ThemeFlatRateMenu', 'render_page'));
    add_submenu_page(self::$menu_name, 'FIC Theme Settings', 'Theme Settings',
      'manage_options', 'fic-theme-settings', array('ThemeSettingsMenu', 'options_page')
    );
  }
  /* Render the page shown when the top level menu is clicked */
  public static function render_page() {
    echo <<<HTML
        <div class='wrap'><h2>FIC Admin Menu</h2>
          <p>Please select an item from the sub-menu on the left.</p></div>
HTML;
  }
}
add_action('admin_menu', array('ThemeMenu', 'create_menu'));

?>
