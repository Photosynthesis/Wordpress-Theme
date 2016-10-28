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



/** Show Usernames instead of Display Names for the User ID field */
function directory_usernames_for_user_id($values, $field, $entry_id=false)
{
    $user_field_id = 430;
    if ($field->id == $user_field_id) {
        $users = get_users(array(
            'orderby' => 'login', 'fields' => array('ID', 'user_login')));
        $values['options'] = array();
        foreach ($users as $user) {
            $values['options'][$user->ID] = $user->user_login;
        }
        $values['use_key'] = true;
    }
    return $values;
}
add_filter(
    'frm_setup_new_fields_vars', 'directory_usernames_for_user_id', 20, 2);
add_filter(
    'frm_setup_edit_fields_vars', 'directory_usernames_for_user_id', 20, 3);

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


/** Return a link for marking a Directory Listing as up-to-date if the current
 * user is an admin or the listings editor. If the current page has an update
 * request in it's GET parameters, this shortcode will set the Formidable
 * Entry's Last Verified field to today, with the same restrictions as for
 * displaying the link.
 *
 * Admins are users that have the 'edit_plugins' permission.
 *
 * Two parameters are required:
 *
 * 1. `listing_id` - the formidable entry's ID
 * 2. `post_id` - the formidable entry's post's ID
 *
 * @param array $atts The Shortcode Parameters
 *
 * @return string The Directory Listing's Verify Link in HTML
 */
function directory_show_and_process_verify_link($atts)
{
    global $wpdb;
    extract(shortcode_atts(array('listing_id' => 0, 'post_id' => 0), $atts));
    $post_id = intval($post_id);
    $listing_id = intval($listing_id);
    if ($post_id === 0 || $listing_id === 0) { return ''; }

    $editor_id = get_post($post_id)->post_author;
    $current_user = get_current_user_id();

    if (current_user_can('edit_plugins') || $editor_id == $current_user) {
        $get_parameter = 'verify_as_up_to_date';

        if (isset($_GET[$get_parameter])) {
            $entry_is_valid = directory_validate_entry($listing_id);
            if (!$entry_is_valid) {
                return '<small style="color:red;text-emphasis:bold;">' .
                    'Your Listing could not be verified <br />' .
                    'because it is incomplete, please edit your <br />' .
                    'listing before verifying it.</small>';
            }
            $verify_date_field_id = 937;
            $exists_query = "
                SELECT id FROM {$wpdb->prefix}frm_item_metas
                WHERE `field_id`=$verify_date_field_id
                  AND `item_id`=$listing_id;";
            $results = $wpdb->get_results($exists_query);
            $result_count = $wpdb->num_rows;
            $today = date('Y-m-d');
            if ($result_count === 0) {
                $insert_query = "
                    INSERT INTO {$wpdb->prefix}frm_item_metas
                            (meta_value, field_id, item_id, created_at)
                    VALUES  ('$today', $verify_date_field_id, $listing_id, NOW());";
                $wpdb->get_results($insert_query);
            } else {
                $meta_id = $results[0]->id;
                $update_query = "
                    UPDATE `{$wpdb->prefix}frm_item_metas`
                    SET meta_value='$today'
                    WHERE `id`=$meta_id;";
                $wpdb->get_results($update_query);
            }
            return '<b>Listing Successfully Verfied.</b>';
        }
        return "<a href='.?$get_parameter=1'>Verify as Up-to-Date</a>";
    }

    return '';
}
add_shortcode(
    'directory_verify_listing_link', 'directory_show_and_process_verify_link'
);

/** Ensure that the current data for the Listing would validate the current form */
function directory_validate_entry($entry_id) {
    $community_name_field_id = 9;

    $entry = FrmEntry::getOne($entry_id);
    $data = array('form_id' => 2, 'item_key' => $entry->item_key, 'item_meta' => array());
    $metas = FrmEntryMeta::getAll(array('item_id' => $entry->id));
    foreach ($metas as $meta) {
       $data['item_meta'][$meta->field_id] = $meta->meta_value;
    }
    $errors = FrmEntryValidate::validate($data);
    $name_field_key = "field{$community_name_field_id}";
    if (array_key_exists($name_field_key, $errors)) { unset($errors[$name_field_key]); }
    return empty($errors);
}


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


/** Show checkboxes to filter the directory search results page by:
 *
 * - Is Established?
 * - Is Forming?
 * - Visitors Welcome?
 * - Accepting New Members?
 * - Is FIC Member?
 *
 * The checkboxes are generated by modifying the GET parameters of the current
 * URL.
 *
 * @return string HTML containg the checkboxes
 */
function directory_show_search_filters() {
    $filters = array(
        array('param' => 'open_to_visitors', 'value' => 'Yes',
              'text' => 'Visitors Welcome'),
        array('param' => 'open_to_members', 'value' => 'Yes,%20Yes,%20rarely',
              'text' => 'Accepting New Members'),
        array('param' => 'community_status', 'value' => 'Established',
              'text' => 'Established'),
        array('param' => 'community_status', 'value' => 'Forming,%20Re-forming',
              'text' => 'Forming'),
        array('param' => 'fic_member', 'value' => 'Yes', 'text' => 'FIC Member')

    );

    // Check if a filter is used
    foreach ($filters as &$filter) {
        $filter['used'] = array_key_exists($filter['param'], $_GET) &&
            strpos($_GET[$filter['param']], $filter['value']) !== false;
    }

    // Build the HTML
    $checkboxes = array();
    foreach ($filters as &$filter) {
        $new_get = $_GET;
        if ($filter['used']) {
            $new_get[$filter['param']] = FIC_Utils::remove_from_comma_separated_string(
                $filter['value'], $new_get[$filter['param']]
            );
            if ($new_get[$filter['param']] === '') {
                unset($new_get[$filter['param']]);
            }
            $selected = 'checked';
        } else {
            if (array_key_exists($filter['param'], $new_get) &&
                    $new_get[$filter['param']] !== '') {
                $new_get[$filter['param']] .= ',%20' . $filter['value'];
            } else {
                $new_get[$filter['param']] = $filter['value'];
            }
            $selected = '';
        }
        $url = http_build_query($new_get);
        $checkboxes[] = "<input name='{$filter['param']}' type='checkbox'" .
            " onclick=\"window.location='?{$url}';\" $selected>{$filter['text']}";
    }

    $link_html = join(' | ', $checkboxes);

    return "<div style='float:right;'>Filter: {$link_html}</div>" .
        "<div style='clear:both;'></div>";
}
add_shortcode('show_directory_search_filters', 'directory_show_search_filters');


/** Show multiple lists of Communities, drilling down by Country and then
 * State/Province.
 *
 * US Listings are shown first.
 *
 * @return string HTML containing the lists
 */
function directory_show_directory_geo_list() {
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
            INNER JOIN (SELECT ID, post_status
                        FROM `{$wpdb->prefix}posts`
                        WHERE post_status='publish')
                   AS posts ON posts.ID=entries.post_id
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
    }, 'directory_geographical_lists',  7 * 24 * 60 * 60);
}
add_shortcode('show_directory_geo_list', 'directory_show_directory_geo_list');

/** Return the 12 Tribes Child-Discipline Statement
 *
 * @return string HTML containing the statement.
 */
function directory_twelve_tribes_statement() {
    $content = <<<HTML
<h3>Child Rearing Practice of the Twelve Tribes Communities</h3>
<p>We train our children according to the Word of God as recorded in the Bible.
Part of this training is correcting our little ones for errant behavior.
Setting boundaries for our children is an integral part of building good
character into them at a young age. Loving protection for children is of
supreme importance to us. We do not tolerate violence or abuse, whether
physical, psychological, or verbal, nor do we condone disrespectful or
rebellious behavior. We believe it is the God-given right and responsibility of
parents to discipline their children if they are disobedient or disrespectful
to parental guidance.</p>
<p>God is love. He loves children and His word is clear on what it means to
love your child as Proverbs 13:24 says: &ldquo;Whoever spares the rod
<em>hates</em> his son, but he who <em>loves</em> him is diligent to discipline
him.&rdquo; Proverbs 23:13-14 makes it clear that spanking is an act of love to
save your children from the destruction of their souls, and restore them to the
way they should go. &ldquo;Do not withhold discipline from a child; if you
strike him with a rod ... you will save his soul from destruction.&rdquo; And
Proverbs 22:6 states that if you &ldquo;train up a child in the way he should
go, when he is old he will not depart from it.&rdquo;</p>
<p>The Bible does not describe the rod itself, but surely God did not intend
something that would damage a child. We use a thin, reed-like rod, which stings
but causes no damage or injury. We have purposefully chosen this method,
inspired by God’s love, as part of our child rearing practices because His Word
commands us. We see the good fruit in our children, which is recognized all
over the world, even by those who don’t understand it.</p>
<p>Because we love our children, we discipline them only for attitudes and
actions they know to be wrong, and only after the child admits to wrongdoing
and is willing to receive discipline. Godly discipline is always followed by
forgiveness, reconciliation, and encouragement.</p>
<p><em>For a fuller statement about our thinking about Child Discipline, please
visit: <a href="http://twelvetribes.org/articles/on-child-discipline" target='_blank'>
http://twelvetribes.org/articles/on-child-discipline</a>.</em></p>

<p><em>Publisher’s Note: FIC has a policy of not listing communities in our
Directory that advocate violent practices, and there is controversy over
whether the Twelve Tribes Child Discipline practice crosses that line.
While we are convinced of the sincerity of their belief that their practice
is not violent, we are also aware of visitors and ex-members who hold that
it is. In recognition of this controversy, the Twelve Tribes leadership
agreed to have this note referenced as a regular part of each community’s
listing, so that users of the Directory could be more fully informed and
make their own decision about this important matter.</em></p>
HTML;
    return str_replace('  ', ' ', str_replace("\n", ' ', $content));
}
add_shortcode(
    'directory_twelve_tribes_statement', 'directory_twelve_tribes_statement');


?>
