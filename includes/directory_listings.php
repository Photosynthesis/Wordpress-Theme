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
 * @param array $atts The Shortcode Parameter
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


/** Show an additional 404 message if the URL is in the 'directory' sub-URI
 * ("ic.org/directory/*").
 *
 * This is used to provide clarity because unapproved and hidden listings
 * return 404 errors.
 *
 * No shortcode parameters are used.
 *
 * @param array $atts Shortcode attributes
 *
 * @return string The additional HTML message to display, or an empty string.
 */
function directory_show_message_if_404_in_directory($atts)
{
    $request_uri = $_SERVER['REQUEST_URI'];
    $sub_uri = "/directory/";
    $request_starts_with_sub_uri = strpos($request_uri, $sub_uri) === 0;

    $message = "<p>If you are looking for a Community on the Directory which has " .
        "not yet been approved, you may see this message. Contact " .
        "<a href=\"mailto:directory@ic.org\">Directory@ic.org</a> with any " .
        "questions.</p>";

    if ($request_starts_with_sub_uri) {
        return $message;
    } else {
        return "";
    }
}
add_shortcode(
    'directory_show_message_if_404_in_directory',
    'directory_show_message_if_404_in_directory'
);

?>
