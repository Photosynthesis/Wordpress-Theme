<?php
/** Custom Map Functions and Shortcodes for Directory Listings
 *
 * This file ties together Directory Listings from the Formidable plugin and
 * Google Maps generated by the Mappress plugin. It's functions are responsible
 * for creating, updating and aggregating Maps of Communities.
 *
 * @category FIC
 * @package  Formidable_Mappress
 * @author   Pavan Rikhi <pavan@ic.org>
 * @license  GPLv3 https://www.gnu.org/licenses/gpl-3.0.html
 * @link     http://www.ic.org
 */




/** Display the Map of All Communities
 *
 * Fetch the Map HTML from the DB Unless It Hasn't Been Generated Today
 *
 * @return string The HTML of the Map
 */
function show_map_of_all_directories()
{
    $cached_map_generation_time = get_option("communities_map_generation_time");
    $map_id = get_option("communities_map_id");
    if ($cached_map_generation_time && $map_id) {
        $cached_map_generation_time = intval($cached_map_generation_time);
        $yesterdays_time = time() - (24 * 60 * 60);
        if ($cached_map_generation_time > $yesterdays_time) {
            $map_id = get_option("communities_map_id");
            $maps = new Mappress_Map();
            $map = $maps->get($map_id);
            $map->options->update(
                array(
                    'width' => '99%', 'height' => 480, 'zoom' => '4',
                    'center' => array('lat' => 37.961523, 'lng' => -95.939942),
                    'poiList' => true, 'poiZoom' => '10',
                )
            );
            $map->prepare();
            return $map->display(array('directions' => 'none'));
        }
    }
    return generate_new_directories_map();
}
add_shortcode('show_map_of_all_directories', 'show_map_of_all_directories');

/** Create a new Mappress map, with each directory listing as a POI
 *
 * If a listing doesn't have a Latitude or Longitude, geocode the address and
 * update the listing's Formidable value
 *
 * @return string The HTML of the Map
 */
function generate_new_directories_map()
{
    global $wpdb;

    $map = new Mappress_Map(
        array(
            'width' => '99%', 'height' => 480, 'zoom' => '4',
            'center' => array('lat' => 37.961523, 'lng' => -95.939942),
            'poiList' => true, 'poiZoom' => '10',
        )
    );

    $directory_sql = "
        SELECT * FROM " . $wpdb->prefix . "frm_items AS items
        LEFT JOIN (SELECT meta_value AS address, item_id
                   FROM " . $wpdb->prefix . "frm_item_metas
                   WHERE field_id=425)
                AS add_metas ON items.id=add_metas.item_id
        LEFT JOIN (SELECT meta_value AS address2, item_id
                   FROM " . $wpdb->prefix . "frm_item_metas
                   WHERE field_id=426)
                AS add2_metas ON items.id=add2_metas.item_id
        LEFT JOIN (SELECT meta_value AS city, item_id
                   FROM " . $wpdb->prefix . "frm_item_metas
                   WHERE field_id=427)
                AS city_metas ON items.id=city_metas.item_id
        LEFT JOIN (SELECT meta_value AS state, item_id
                   FROM " . $wpdb->prefix . "frm_item_metas
                   WHERE field_id=815)
                AS state_metas ON items.id=state_metas.item_id
        LEFT JOIN (SELECT meta_value AS province, item_id
                   FROM " . $wpdb->prefix . "frm_item_metas
                   WHERE field_id=428)
                AS prov_metas ON items.id=prov_metas.item_id
        LEFT JOIN (SELECT meta_value AS zipcode, item_id
                   FROM " . $wpdb->prefix . "frm_item_metas
                   WHERE field_id=429)
                AS zip_metas ON items.id=zip_metas.item_id
        LEFT JOIN (SELECT meta_value AS country, item_id
                   FROM " . $wpdb->prefix . "frm_item_metas
                   WHERE field_id=424)
                AS country_metas ON items.id=country_metas.item_id
        LEFT JOIN (SELECT meta_value AS latitude, item_id
                   FROM " . $wpdb->prefix . "frm_item_metas
                   WHERE field_id=684)
                AS latitude_metas ON items.id=latitude_metas.item_id
        LEFT JOIN (SELECT meta_value AS longitude, item_id
                   FROM " . $wpdb->prefix . "frm_item_metas
                   WHERE field_id=685)
                AS longitude_metas ON items.id=longitude_metas.item_id
        LEFT JOIN (SELECT meta_value AS public, item_id
                   FROM " . $wpdb->prefix . "frm_item_metas
                   WHERE field_id=218)
                AS public_metas ON items.id=public_metas.item_id
        LEFT JOIN (SELECT meta_value AS add_public, item_id
                   FROM " . $wpdb->prefix . "frm_item_metas
                   WHERE field_id=285)
                AS add_public_metas ON items.id=add_public_metas.item_id

        WHERE public_metas.public='Yes' AND add_public_metas.add_public='Public'
        ";
    $directory_listings = $wpdb->get_results($directory_sql);


    $excluded_points = array(
        array(0, 0),
        array(39.095963, -96.606447),
    );
    $pois = array();
    foreach ($directory_listings as $listing) {
        $address = get_directory_listing_address($listing);
        if (sizeof($address) == 0) {
            continue;
        }
        $post_id = get_post_id_from_directory_id($listing->item_id);
        $post_title = get_the_title($post_id);
        $post_status = get_post_status($post_id);

        if ($post_title == '' || $post_status != 'publish') { continue; }

        $poi_body = $address[0] . "<br />" . join(', ', array_slice($address, 1));
        $poi_info = array(
            'postid' => $post_id,
            'body'   => $poi_body,
            'title'  => '<a href="' . get_permalink($post_id) . '">' .
                        $post_title .'</a>'
        );


        if (!is_null($listing->latitude) && !is_null($listing->longitude)) {
            $poi_info['point'] = array('lat' => $listing->latitude,
                                       'lng' => $listing->longitude);
            $poi = new Mappress_Poi($poi_info);
        } else {
            $poi = get_best_poi_from_address($address, $poi_info);
            set_directory_listings_latitude_longitude(
                $listing->item_id, $poi->point['lat'], $poi->point['lng']
            );
        }

        $exclude_poi = false;
        foreach ($excluded_points as $point) {
            if ($poi->point['lat'] == $point[0] && $poi->point['lng'] == $point[1]) {
                $exclude_poi = true;
                break;
            }
        }

        if (!$exclude_poi) {
            array_push($pois, $poi);
        }
    }

    $map->pois = $pois;
    regenerate_map_cache($map);

    return $map->display(array('directions' => 'none'));
}

/** Update the All Communities Map's Generation Time & ID
 *
 * @param Mappress_Map $map The New Map to Cache
 *
 * @return void
 */
function regenerate_map_cache($map)
{
    $old_map_id = get_option("communities_map_id");
    if ($old_map_id) {
        $maps = new Mappress_Map();
        $maps->delete($old_map_id);
    }
    $map_id = $map->save(148417);
    update_option("communities_map_generation_time", time());
    update_option("communities_map_id", $map_id);
}

/** Return an Array Containing the Full Address of a Directory Listing
 *
 * @param array $listing The Database Row of the Listing
 *
 * @return array
 */
function get_directory_listing_address($listing)
{
    $address = array();
    if (!is_null($listing->address)) {
        array_push($address, $listing->address);
    }
    if (!is_null($listing->address2)) {
        array_push($address, $listing->address2);
    }
    if (!is_null($listing->city)) {
        array_push($address, $listing->city);
    }
    if (!is_null($listing->state)) {
        array_push($address, $listing->state);
    }
    if (!is_null($listing->province) && is_null($listing->state)) {
        array_push($address, $listing->province);
    }
    if (!is_null($listing->zipcode)) {
        array_push($address, $listing->zipcode);
    }
    if (!is_null($listing->country)) {
        array_push($address, $listing->country);
    }
    return $address;
}

/** Return the Post Id of the Directory Listing
 *
 *  @param int|string $listing_id The ID of the Listing's Formidable Entry
 *
 *  @return int
 */
function get_post_id_from_directory_id($listing_id)
{
    global $wpdb;

    $directory_query = "
        SELECT post_id FROM " . $wpdb->prefix . "frm_items
        WHERE id=" . $listing_id . ";";
    $directory_listings = $wpdb->get_results($directory_query);

    if (!empty($directory_listings)) {
        return intval($directory_listings[0]->post_id);
    }
}

/** Reduce the Address Until a Suitable Map PoI is Found
 *
 * @param array $address_array An Array Containing Parts of the Listing's
 *                             Address, Ordered from Most Specific to Least
 *                             Specific
 * @param array $poi_info      An Array Containing the PoI's title/body/post
 *
 * @return Mappress_Poi
 */
function get_best_poi_from_address($address_array, $poi_info)
{
    $address = join(', ', $address_array);
    $poi_info['address'] = $address;
    $poi = new Mappress_Poi($poi_info);
    $poi->geocode();

    if (sizeof($address_array) == 0) {
        return $poi;
    }
    if ($poi->point['lat'] == 0 && $poi->point['lng'] == 0) {
        array_shift($address_array);
        return get_best_poi_from_address($address_array, $poi_info);
    } else {
        return $poi;
    }
}

/** Update or Set the Directory Listing's Latitude & Longitude
 *
 * @param int|string $entry_id  The Listing's Formidable Entry ID
 * @param float      $latitude  The New Latitude
 * @param float      $longitude The New Longitude
 *
 * @return null
 */
function set_directory_listings_latitude_longitude($entry_id, $latitude, $longitude)
{
    global $wpdb;

    $get_latt_long_query = "
        SELECT * FROM " . $wpdb->prefix . "frm_items AS items
        LEFT JOIN (SELECT meta_value AS latitude, item_id
                   FROM " . $wpdb->prefix . "frm_item_metas
                   WHERE field_id=684)
                AS latitude_metas ON items.id=latitude_metas.item_id
        LEFT JOIN (SELECT meta_value AS longitude, item_id
                   FROM " . $wpdb->prefix . "frm_item_metas
                   WHERE field_id=685)
                AS longitude_metas ON items.id=longitude_metas.item_id
        LEFT JOIN (SELECT meta_value AS public, item_id
                   FROM " . $wpdb->prefix . "frm_item_metas
                   WHERE field_id=218)
                AS public_metas ON items.id=public_metas.item_id
        WHERE public_metas.public='Yes' AND items.id=" . $entry_id . ";";
    $directory_listings = $wpdb->get_results($get_latt_long_query);

    if (empty($directory_listings)) {
        return;
    } else {
        $directory_listing = $directory_listings[0];

        if (is_null($directory_listing->latitude)) {
            $insert_latitude_query = "
                INSERT INTO " . $wpdb->prefix . "frm_item_metas
                (meta_value, field_id, item_id)
                VALUES (" . $latitude . ", 684, " . $entry_id . ");";
            $wpdb->get_results($insert_latitude_query);
        } else {
            $update_latitude_query = "
                UPDATE " . $wpdb->prefix . "frm_item_metas
                SET meta_value=" . $latitude ."
                WHERE item_id=" . $entry_id ." AND field_id=684;";
            $wpdb->get_results($update_latitude_query);
        }

        if (is_null($directory_listing->longitude)) {
            $insert_longitude_query = "
                INSERT INTO " . $wpdb->prefix . "frm_item_metas
                       (meta_value, field_id, item_id)
                VALUES (" . $longitude . ", 685, " . $entry_id . ");";
            $wpdb->get_results($insert_longitude_query);
        } else {
            $update_longitude_query = "
                UPDATE " . $wpdb->prefix . "frm_item_metas
                SET meta_value=" . $longitude ."
                WHERE item_id=" . $entry_id ." AND field_id=685;";
            $wpdb->get_results($update_longitude_query);
        }
    }

}

/** Delete Latitudes and Longitudes of Listings at (0, 0)
 *
 * @param array $listings The Directory Listings, with a latitude and longitude
 *                        attribute.
 *
 * @return null
 */
function delete_entries_at_zero_zero($listings)
{
    global $wpdb;
    foreach ($listings as $listing) {
        if (!is_null($listing->latitude) && !is_null($listing->longitude)) {
            if ($listing->latitude == 0 && $listing->longitude == 0) {
                $delete_lat_sql = "DELETE FROM " . $wpdb->prefix . "frm_item_metas
                                   WHERE field_id=684
                                   AND item_id=" . $listing->item_id;
                $delete_lng_sql = "DELETE FROM " . $wpdb->prefix . "frm_item_metas
                                   WHERE field_id=685
                                   AND item_id=" . $listing->item_id;
                $wpdb->get_results($delete_lat_sql);
                $wpdb->get_results($delete_lng_sql);
            }
        }
    }
}




/** Generate a Map from a Directory Form Entry
 * Use the Entry's Latitude & Longitude instead of the Address if they exist.
 *
 * http://formidablepro.com/how-to-create-maps-from-form-entries/
 *
 * @param array $atts The Shortcode Attributes
 *
 * @return string
 */
function form_to_mappress($atts)
{
    extract(
        shortcode_atts(
            array(
                'width' => '100%', 'height' => 300, 'title' => '', 'body' => '',
                'address1' => '', 'address2' => '', 'directions' => 'none',
                'post_id' => ''
            ), $atts
        )
    );

    $post_id = get_post_id_from_post_content($post_id, $body);
    $poi_array = array('title' => $title, 'body' => $body, 'postid' => $post_id);

    $coords = get_coords_from_post_body($body);
    if (!empty($coords)) {
        $poi_array['lat1'] = $coords['latitude'];
        $poi_array['lng1'] = $coords['longitude'];
        $geocode = false;
    } else {
        $poi_array['address'] = $address1;
        $geocode = true;
    }


    $mymap = new Mappress_Map(array('width' => $width, 'height' => $height));
    $mypoi_1 = new Mappress_Poi($poi_array);
    if ($geocode) {
        $mypoi_1->geocode();
    }
    if ($mypoi_1->point['lat'] == 0 && $mypoi_1->point['lng'] == 0) { return ''; }
    $mymap->pois = array($mypoi_1);

    return $mymap->display(array('directions' => $directions));
}
add_shortcode('form_to_mappress', 'form_to_mappress');

/** Retrieve the Directory Listings Coordinates by using it's Post's Content
 *
 * @param string $post_content The Full Contents of the Post
 *
 * @return array
 */
function get_coords_from_post_body($post_content)
{
    $directory_listing_id = get_directory_id_from_body_post($post_content);
    return get_latitude_and_longitude_of_listing($directory_listing_id);
}

/** Use the contents of a Post to fetch a Directory Listing's Entry ID
 *
 * @param string $post_content The Full Contents of the Post
 *
 * @return int
 */
function get_directory_id_from_body_post($post_content)
{
    global $wpdb;

    $post_id = get_post_id_from_post_content($post_content);

    $directory_id_query = "
        SELECT id FROM " . $wpdb->prefix . "frm_items
        WHERE post_id='" . $post_id . "';";
    $directory_results = $wpdb->get_results($directory_id_query);
    if (!empty($directory_results)) {
        return intval($directory_results[0]->id);
    }
}

/** Return the ID of the Post with the given Content
 *
 * @param string $post_content The Full Contents of the Post
 *
 * @return int
 */
function get_post_id_from_post_content($post_content)
{
    global $wpdb;

    $post_id_query = $wpdb->prepare(
        "SELECT ID FROM " . $wpdb->prefix . "posts
        WHERE post_content=%s;",
        $post_content
    );
    $posts_results = $wpdb->get_results($post_id_query);
    if (!empty($posts_results)) {
        return intval($posts_results[0]->ID);
    }
}

/** Return an array containing the Directory Listing's `latitude` and `longitude`
 *
 * @param int|string $listing_id The Listing's Formidable Entry ID
 *
 * @return array
 */
function get_latitude_and_longitude_of_listing($listing_id)
{
    global $wpdb;

    $get_latt_long_query = "
        SELECT * FROM " . $wpdb->prefix . "frm_items AS items
        LEFT JOIN (SELECT meta_value AS latitude, item_id
                   FROM " . $wpdb->prefix . "frm_item_metas WHERE field_id=684)
                AS latitude_metas ON items.id=latitude_metas.item_id
        LEFT JOIN (SELECT meta_value AS longitude, item_id
                   FROM " . $wpdb->prefix . "frm_item_metas WHERE field_id=685)
                AS longitude_metas ON items.id=longitude_metas.item_id
        LEFT JOIN (SELECT meta_value AS public, item_id
                   FROM " . $wpdb->prefix . "frm_item_metas WHERE field_id=218)
                AS public_metas ON items.id=public_metas.item_id
        WHERE items.id='" . $listing_id . "';";
    $directory_listings = $wpdb->get_results($get_latt_long_query);

    if (empty($directory_listings)) {
        return array();
    } else {
        $directory_listing = $directory_listings[0];
        return array(
            'latitude'  => $directory_listing->latitude,
            'longitude' => $directory_listing->longitude);
    }
}



/** Generate a Map from an Existing Latitude and Longitude
 *
 * http://formidablepro.com/how-to-create-maps-from-form-entries/
 *
 * @param array $atts The Shortcode Attributes
 *
 * @return string
 */
function form_to_mappress_latlng($atts)
{
    extract(
        shortcode_atts(
            array(
                'width' => '100%', 'height' => 300, 'title' => '', 'body' => '',
                'lat1' => '', 'lng1' => '', 'lat2' => '', 'lng2' => '',
                'address2' => '', 'directions' => 'none'
            ), $atts
        )
    );

    $mymap = new Mappress_Map(array("width" => $width, "height" => $height));
    $mypoi_1 = new Mappress_Poi(
        array("title" => $title, "body" => $body, "point" => array(
                "lat" => $lat1, "lng" => $lng1))
    );
    $mymap->pois = array($mypoi_1);
    if ($address2 != '') {
        $mypoi_2 = new Mappress_Poi(
            array("point" => array("lat" => $lat2, "lng" => $lng2))
        );
        $mymap->pois = $mypoi_2;
    }
    return $mymap->display(array('directions' => $directions));
}
add_shortcode('form_to_mappress_latlng', 'form_to_mappress_latlng');



/** Trigger Map Updates on Entry Updates
 *
 * http://wphostreviews.com/mappress-documentation
 *
 * @param int|string $entry_id The Formidable Entry's ID
 * @param int|string $form_id  The Formidable Form's ID
 *
 * @return null
 */
function my_meta_update($entry_id, $form_id)
{
    // Restrict to Directory Listings
    if ($form_id == 2) {
        //get ID of post to be created
        global $frmdb;
        $post_id = $frmdb->get_var(
            $frmdb->entries, array('id' => $entry_id), 'post_id'
        );

        // Update the map for that post
        do_action('mappress_update_meta', $post_id);
    }
}
add_filter('frm_after_create_entry', 'my_meta_update', 50, 2);
add_filter('frm_after_update_entry', 'my_meta_update', 50, 2);



/** Update Maps for all Directory Listings
 *
 * @return null
 */
function update_all_directory_maps()
{

}


?>
