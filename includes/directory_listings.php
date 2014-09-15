<?php
/** General Functions for Formidable Directory Listings
 *
 * This file contains general functions for Directory Listings.
 *
 * @category FIC
 * @package  Formidable_Directory
 * @author   Pavan Rikhi <pavan@ic.org>
 * @license  GPLv3 https://www.gnu.org/licenses/gpl-3.0.html
 * @link     http://www.ic.org
 */


/** Return an Edit link for the Directory Listing if the Current User is an
 * Administrator. This is checked by checking for the `edit_plugins`
 * permission.
 *
 * One shortcode parameter is required: `listing_id`, which determines what
 * Edit Link is returned.
 *
 * @param array $atts The Shortcode Attributes
 *
 * @return string The Directory Listing's Edit Link in HTML
 */
function directory_show_edit_link_if_admin($atts)
{
    extract(shortcode_atts(array('listing_id' => 0), $atts));
    if (current_user_can('edit_plugins')) {
        $entry_edit_url = "/wp-admin/admin.php" .
            "?page=formidable-entries&frm_action=edit&id=$listing_id";
        return "<a href=\"$entry_edit_url\">Edit Listing</a>";
    } else {
        return "";
    }
}
add_shortcode(
    'directory_show_edit_link_if_admin', 'directory_show_edit_link_if_admin'
);

?>
