<?php
/**
 * Shortcode and Functions related to Formidable Directory Listing and Mappress
 * Maps of Directory Listings.
 *
 * This file includes shortcode to display the Map of All Communities, and
 * Single Community Maps based off of a Latitude/Longitude or Address.
 *
 * @category FIC
 * @package  Formidable_Mappress
 * @author   Pavan Rikhi <fic.web.tech@gmail.com>
 * @license  GPLv3 https://www.gnu.org/licenses/gpl-3.0.html
 * @link     http://www.ic.org
 */



/** Map of All Directories
 *
 * Create a new Mappress map, then add each directory listing as a POI
 *
 * If a listing doesn't have a Latitude or Longitude, geocode the address and
 * update the listing's Formidable value
 *
 * @return string
 */
function show_map_of_all_directories()
{   
    global $wpdb;

    $map = new Mappress_Map(
        array(
            'width' => '99%', 'height' => 480, 'zoom' => '4',
            'center' => array('lat' => 37.961523, 'lng' => -95.939942),
            'poiList' => true, 'poiZoom' => '10', 'mashupBody' => 'post',
            'mashupTitle' => 'post', 'mashupClick' => 'poi'
        )
    );

    $directory_sql = "
        SELECT * FROM " . $wpdb->prefix . "frm_items AS items
        LEFT JOIN (SELECT meta_value AS name, item_id
                   FROM " . $wpdb->prefix ."frm_item_metas
                   WHERE field_id=278)
                AS name_metas ON items.id=name_metas.item_id
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
        WHERE public_metas.public='Yes'";
    $directory_listings = $wpdb->get_results($directory_sql);

    $pois = array();
    foreach ($directory_listings as $listing) {
        $post_id = get_post_id_from_directory_id($listing->item_id);
        $address = get_directory_listing_address($listing);
        $poi_info = array("postid"  => $post_id,
                          "body"    => join(', ', $address));


        if (!is_null($listing->latitude) && !is_null($listing->longitude)) {
            $poi_info["point"] = array("lat" => $listing->latitude,
                                       "lng" => $listing->longitude);
            $poi = new Mappress_Poi($poi_info);
        } else {
            $poi = get_best_poi_from_address($address, $poi_info);
            if ($poi->point['lat'] != 0 && $poi->point['lng'] != 0) {
                set_directory_listings_latitude_longitude(
                    $listing->item_id, $poi->point['lat'], $poi->point['lng']
                );
            }
        }

        if ($listing->latitude == 0 && $listing->longitude == 0
            && sizeof($address) > 0
        ) {
            //echo "<br /><br />";
            //echo print_r($listing->item_id) . "<br />";
            //echo print_r($address) . "<br />";
        }

        array_push($pois, $poi);
    }

    $map->pois = $pois;
    return $map->display(array('directions' => 'none'));
}
add_shortcode('show_map_of_all_directories', 'show_map_of_all_directories');

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

/** Reduce the Address Until a Suitable Map PoI is Found
 *
 * @param array $address_array An Array Containing All Parts of the Listing's Address
 * @param array $poi_info      An Array Containing the PoI's title/body/post
 *
 * @return Mappress_Poi
 */
function get_best_poi_from_address($address_array, $poi_info)
{   
    $address = join(', ', $address_array);
    $poi_info["address"] = $address;

    $location = Mappress::$geocoders->geocode($address);

    if (sizeof($address_array) == 0) {
        $poi = new Mappress_Poi($poi_info);
        return $poi;
    }
    if ($location->point['lat'] == 0 && $location->point['lng'] == 0) {
        array_shift($address_array);
        return get_best_poi_from_address($address_array, $post_id);
    } else {
        $poi = new Mappress_Poi($poi_info);
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
        LEFT JOIN (SELECT meta_value AS latitude, item_id FROM " . $wpdb->prefix . "frm_item_metas
                   WHERE field_id=684)
                AS latitude_metas ON items.id=latitude_metas.item_id
        LEFT JOIN (SELECT meta_value AS longitude, item_id FROM " . $wpdb->prefix . "frm_item_metas
                   WHERE field_id=685)
                AS longitude_metas ON items.id=longitude_metas.item_id
        LEFT JOIN (SELECT meta_value AS public, item_id FROM " . $wpdb->prefix . "frm_item_metas
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
                INSERT INTO " . $wpdb->prefix . "frm_item_metas (meta_value, field_id, item_id)
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
                'address1' => '', 'address2' => '', 'directions' => 'none'
            ), $atts
        )
    );

    $post_id = get_post_id_from_post_content($post_content);
    $poi_array = array("title" => $title, "body" => $body, "postid" => $post_id);

    $coords = get_coords_from_post_body($body);
    if (!empty($coords)) {
        $poi_array['lat1'] = $coords['latitude'];
        $poi_array['lng1'] = $coords['longitude'];
        $geocode = false;
    } else {
        $poi_array['address'] = $address1;
        $geocode = true;
    }


    $mymap = new Mappress_Map(array("width" => $width, "height" => $height));
    $mypoi_1 = new Mappress_Poi($poi_array);
    if ($geocode) {
        $mypoi_1->geocode();
    }
    $mymap->pois = array($mypoi_1);
    if ($address2 != '') {
        $mypoi_2 = new Mappress_Poi(array("address" => $address2));
        $mypoi_2->geocode();
        $mymap->pois = $mypoi_2;
    }
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
    $directory_listing_id = get_directory_id_from_body_post($body);
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
        WHERE post_id=" . $post_id . ";";
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

    $post_id_query = "
        SELECT ID FROM " . $wpdb->prefix . "posts
        WHERE post_content=" . $post_content . ";";
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
        LEFT JOIN (SELECT meta_value AS latitude, item_id FROM " . $wpdb->prefix . "frm_item_metas WHERE field_id=684)
                AS latitude_metas ON items.id=latitude_metas.item_id
        LEFT JOIN (SELECT meta_value AS longitude, item_id FROM " . $wpdb->prefix . "frm_item_metas WHERE field_id=685)
                AS longitude_metas ON items.id=longitude_metas.item_id
        LEFT JOIN (SELECT meta_value AS public, item_id FROM " . $wpdb->prefix . "frm_item_metas WHERE field_id=218)
                AS public_metas ON items.id=public_metas.item_id
        WHERE items.id=" . $listing_id . ";";
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
                'directions' => 'none'
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
    // Replace '17' with your form ID
    if ($form_id == 2) {
        //get ID of post to be created global
        global $frmdb;
        $post_id = $frmdb->get_var(
            $frmdb->entries, array('id' => $entry_id), 'post_id'
        );

        // Update the map for that post
        do_action('mappress_update_meta', $post_id);
    }
}
add_filter('frm_after_create_entry', 'my_meta_update', 50, 2);



/** Update Maps for all Directory Listings
 *
 * @return null
 */
function update_all_directory_maps()
{

}


?>
