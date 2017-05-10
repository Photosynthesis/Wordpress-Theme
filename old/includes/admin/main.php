<?php
/** This file contains functions related to the FIC Admin Menu.
 *
 * @category FIC
 * @package FIC_Menu
 * @author   Pavan Rikhi <pavan@ic.org>
 * @license  GPLv3 https://www.gnu.org/licenses/gpl-3.0.html
 * @link     http://www.ic.org
 */

/* Load the Directory User Update Sub-Menu */
require 'directory_user_update.php';

/** The FIC_Menu class is responsible for assembling the various FIC_Menu_*
 * classes into a single Top-Level Menu in the Wordpress Admin.
 */
class FIC_Menu
{
    public static $menu_name = 'fic-menu';
    /* Assemble the Admin Menu */
    public static function create_menu() {
        add_menu_page('FIC Admin Menu', 'FIC Menu', 'administrator', self::$menu_name,
            array('FIC_Menu', 'render_page'),
            get_stylesheet_directory_uri('stylesheet_directory') .  "/img/fic-logo.png",
            3);
        add_submenu_page(self::$menu_name, FIC_Menu_Directory_User::$page_title, 
            'Directory User Update', 'administrator', FIC_Menu_Directory_User::$page_name,
            array('FIC_Menu_Directory_User', 'render_page'));
    }
    /* Render the page shown when the top level menu is clicked */
    public static function render_page() {
        echo <<<HTML
            <div class='wrap'><h2>FIC Admin Menu</h2>
            <p>Please select an item from the sub-menu on the left.</p></div>
HTML;
    }
}
add_action('admin_menu', array('FIC_Menu', 'create_menu'));

?>