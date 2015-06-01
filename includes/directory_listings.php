<?php
/** General Functions for Formidable Directory Listings
 *
 * This file contains general functions & shortcodes for Directory Listings.
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

    $message = "<p>If you are looking for a Community in the Directory which has " .
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


/** Show multiple lists of Communities, drilling down by Country and then
 * State/Province.
 *
 * US Listings are shown first.
 *
 * @return string HTML containing the lists
 */
function directory_show_directory_geo_list() {
    delete_transient('directory_geographical_lists');
    return FIC_Utils::cache_result(function() {
        global $wpdb;

        $directory_form_id = 2;
        $is_public_field_id = 218;
        $country_field_id = 424;
        //$region_field_id = 428;  // Has less entries
        $region_field_id = 816;
        $state_field_id = 815;
        $entries_query = "
            SELECT countries.country, regions.region, states.state
            FROM `{$wpdb->prefix}frm_items` AS entries
            " . /*  Commented out in order to keep the Counts accurate
                    until hiding drafts works in Formidable
            INNER JOIN (SELECT ID, post_status
                        FROM `{$wpdb->prefix}posts`
                        WHERE post_status='publish')
                   AS posts ON posts.ID=entries.post_id
            */ "
            INNER JOIN (SELECT `item_id`, `meta_value` AS is_public
                        FROM `{$wpdb->prefix}frm_item_metas`
                        WHERE `meta_value`='Yes' AND
                              `field_id`=$is_public_field_id)
                   AS public ON public.item_id=entries.id
            INNER JOIN (SELECT `item_id`, `meta_value` AS country
                        FROM `{$wpdb->prefix}frm_item_metas`
                        WHERE `field_id`=$country_field_id)
                   AS countries ON countries.item_id=entries.id
            LEFT JOIN (SELECT `item_id`, `meta_value` AS region
                       FROM `{$wpdb->prefix}frm_item_metas`
                       WHERE `field_id`=$region_field_id)
                   AS regions ON regions.item_id=entries.id
            LEFT JOIN (SELECT `item_id`, `meta_value` AS state
                       FROM `{$wpdb->prefix}frm_item_metas`
                       WHERE `field_id`=$state_field_id)
                   AS states ON states.item_id=entries.id
            WHERE entries.form_id=$directory_form_id
            ;";
        $entries = $wpdb->get_results($entries_query);

        $countries = array();
        // Count Entries
        foreach ($entries as $entry) {
            if (!array_key_exists($entry->country, $countries)) {
                $countries[$entry->country] = array();
            }
            $region = $entry->country === "United States" ?
                      $entry->state : ucwords($entry->region);
            if (!array_key_exists($region, $countries[$entry->country])) {
                $countries[$entry->country][$region] = 1;
            } else {
                $countries[$entry->country][$region]++;
            }
        }

        // Sort Countries & Regions Alphabetically
        foreach ($countries as &$country) {
            ksort($country);
        }
        ksort($countries);

        // Move US to the Top
        $us = $countries["United States"];
        unset($countries["United States"]);
        $countries = array_merge(array("United States" => $us), $countries);

        // Render as HTML
        $countries_html = '';
        foreach ($countries as $country => $regions) {
            $country_total = array_sum($regions);
            $country_url = "/directory/listings/?cmty-country=$country";
            if ($country === "United States")  {
                $country_class = 'geo-us-state';
                $region_get_parameter = 'cmty-state';
            } else {
                $country_class = 'geo-state';
                $region_get_parameter = 'cmty-prov';
            }
            $region_html = '';
            foreach ($regions as $region => $count) {
                if (empty($region)) {
                    continue;
                } else if ($region === "Kentucky" || $region === "Ohio") {
                    // Start a New Column
                    $region_html .= "</ul></li><li class='$country_class'>
                                     <ul class='$country_class'>";
                }
                $region_url = "$country_url&$region_get_parameter=$region";
                $region_html .= "
                    <li class='geo-state-prov'><a href='$region_url'>
                        $region <span class='geo-count'>($count)</span>
                    </a></li>";
            }

            $countries_html .= "
                <li class='geo-country'><a href='$country_url'>$country
                    <span class='geo-count'>($country_total)</span></a>
                    <ul class='$country_class'>$region_html</ul>
                </li>";
        };

        return '<ul class="geo-country">' . $countries_html . '</ul>';
    }, 'directory_geographical_lists', 1);
}
add_shortcode('show_directory_geo_list', 'directory_show_directory_geo_list');


?>
