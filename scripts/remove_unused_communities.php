<?php
/** Remove Duplicate Directory Listing Caused By Import
 *
 * There are duplicate unpublished pages for directory listings that have a
 * last modified date of 12/23/2013. This script removes those posts.
 *
 * @category FIC
 * @package  FIC_Scripts
 * @author   Pavan Rikhi <pavan@ic.org>
 * @license  GPLv3 https://www.gnu.org/licenses/gpl-3.0.html
 * @link     http://www.ic.org
 */

add_shortcode('delete_old_imported_listings', 'delete_old_imported_listings');
function delete_old_imported_listings() {
    global $wpdb, $frm_entry;

    $post_query = "
        SELECT *
        FROM `{$wpdb->posts}`
        WHERE `post_status`='draft' AND
              `post_type`='directory' AND
              DATE(post_modified) = '2013-12-23';";
    $draft_directory_posts = $wpdb->get_results($post_query);

    foreach ($draft_directory_posts as $post) {
        $post_id = $post->ID;
        $entry_query = "
        SELECT *
        FROM `{$wpdb->prefix}frm_items`
        WHERE `post_id`={$post_id};";
        $formidable_entries = $wpdb->get_results($entry_query);
        foreach ($formidable_entries as $entry) {
            $frm_entry->destroy($entry->id);
        }
    }
}


?>
