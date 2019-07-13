<?php
/** Directory Map Customizations **/
class DirectoryMaps
{
  /** Trigger Map Updates on Entry Updates
   *
   * http://wphostreviews.com/mappress-documentation
   *
   * @param int|string $entry_id The Formidable Entry's ID
   * @param int|string $form_id  The Formidable Form's ID
   *
   * @return null
   */
  public static function update_map($entry_id, $form_id) {
    if ($form_id == 2) {
      global $wpdb;
      $post_id = FrmDB::get_var(
        $wpdb->prefix . "frm_items", array('id' => $entry_id), 'post_id'
      );

      do_action('mappress_update_meta', $post_id);
    }
  }

  /** Generate a Map from Listing's Latitude and Longitude
   *
   * http://formidablepro.com/how-to-create-maps-from-form-entries/
   *
   * @param array $atts The Shortcode Attributes
   *
   * @return string
   */
  public static function to_mappress($atts) {
    extract(shortcode_atts(
      array(
        'width' => '100%', 'height' => 300, 'title' => '', 'body' => '',
        'lat1' => '', 'lng1' => '', 'lat2' => '', 'lng2' => '',
        'address2' => '', 'directions' => 'none'
      ), $atts
    ));

    $mymap = new Mappress_Map(array("width" => $width, "height" => $height));
    $mypoi_1 = new Mappress_Poi(array(
      "title" => $title, "body" => $body,
      "point" => array("lat" => $lat1, "lng" => $lng1)
    ));
    $mymap->pois = array($mypoi_1);
    if ($address2 != '') {
      $mypoi_2 = new Mappress_Poi(
        array("point" => array("lat" => $lat2, "lng" => $lng2))
      );
      $mymap->pois = $mypoi_2;
    }
    return $mymap->display(array('directions' => $directions));
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
  public static function address_to_mappress($atts) {
      extract(shortcode_atts(
        array(
          'width' => '100%', 'height' => 300, 'title' => '', 'body' => '',
          'address1' => '', 'address2' => '', 'directions' => 'none',
          'post_id' => ''
        ), $atts
      ));

      $post_id = self::get_post_id_from_post_content($post_id, $body);
      $poi_array = array('title' => $title, 'body' => $body, 'postid' => $post_id);

      $coords = self::get_coords_from_post_body($body);
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

  /** Display the Map of All Communities
   *
   * Fetch the Map HTML from the DB Unless It Hasn't Been Generated Today
   *
   * @return string The HTML of the Map
   */
  public static function all_listings_map() {
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
            'poiList' => false, 'poiZoom' => '10',
          )
        );
        $map->prepare();
        return $map->display(array('directions' => 'none'));
      }
    }
    return self::generate_new_directories_map();
  }

  /** Retrieve the Directory Listings Coordinates by using it's Post's Content
   *
   * @param string $post_content The Full Contents of the Post
   *
   * @return array
   */
  private static function get_coords_from_post_body($post_content) {
    $directory_listing_id = self::get_directory_id_from_body_post($post_content);
    return self::get_latitude_and_longitude_of_listing($directory_listing_id);
  }

  /** Use the contents of a Post to fetch a Directory Listing's Entry ID
   *
   * @param string $post_content The Full Contents of the Post
   *
   * @return int
   */
  private static function get_directory_id_from_body_post($post_content) {
    global $wpdb;

    $post_id = self::get_post_id_from_post_content($post_content);

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
  private static function get_post_id_from_post_content($post_content) {
    global $wpdb;

    $post_id_query = $wpdb->prepare(
      "SELECT ID FROM " . $wpdb->prefix . "posts
      WHERE post_content=%s AND post_type='directory';",
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
  private static function get_latitude_and_longitude_of_listing($listing_id) {
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

  /** Create a new Mappress map, with each directory listing as a POI
   *
   * If a listing doesn't have a Latitude or Longitude, geocode the address and
   * update the listing's Formidable value.
   *
   * @return string The HTML of the Map
   */
  private static function generate_new_directories_map() {
    global $wpdb;

    $map = new Mappress_Map( array(
      'width' => '99%', 'height' => 480, 'zoom' => '4',
      'center' => array('lat' => 37.961523, 'lng' => -95.939942),
      'poiList' => false, 'poiZoom' => '10',
    ));

    $directory_sql = "
        SELECT DISTINCT * FROM " . $wpdb->prefix . "frm_items AS items
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
        LEFT JOIN (SELECT meta_value AS addr_public, item_id
                    FROM " . $wpdb->prefix . "frm_item_metas
                    WHERE field_id=285)
                AS addr_public_metas ON items.id=addr_public_metas.item_id

        WHERE public_metas.public='Yes' AND items.is_draft=0 AND items.form_id=2
        ";
    $directory_listings = $wpdb->get_results($directory_sql);


    // array of latitude arrays containing array of longitude arrays
    // containing array of PoI info.
    $poi_matrix = array();
    $excluded_points = array(
      array(0, 0),
      array(39.095963, -96.606447),
    );
    foreach ($directory_listings as $listing) {
      $address = self::get_directory_listing_address($listing);
      if (sizeof($address) == 0) {
        continue;
      }
      $post_id = $listing->post_id;
      $post_title = get_the_title($post_id);
      $post_status = get_post_status($post_id);

      if (is_null($post_id) || $post_id === 0 || $post_title == ''
          || $post_status != 'publish') {
        continue;
      }

      $poi_body = $address[0] . "<br />" . join(', ', array_slice($address, 1));
      $poi_info = array(
        'postid' => $post_id,
        'body'   => $poi_body,
        'title'  => $post_title
      );


      if (!is_null($listing->latitude) && !is_null($listing->longitude)) {
        $latitude = $listing->latitude;
        $longitude = $listing->longitude;
      } else {
        $coordinates = self::get_best_coords_from_address($address);
        if (is_null($coordinates)) {
          continue;
        }
        $latitude = $coordinates['latitude'];
        $longitude = $coordinates['longitude'];
        self::set_directory_listings_latitude_longitude(
          $listing->item_id, $latitude, $longitude
        );
      }

      // Filter out (0,0) & default coordinates
      $exclude_poi = false;
      foreach ($excluded_points as $point) {
        if ($latitude == $point[0] && $longitude == $point[1]) {
          $exclude_poi = true;
          break;
        }
      }
      if ($exclude_poi === true) {
        continue;
      }

      // Add to PoI Matrix
      if (isset($poi_matrix[$latitude])) {
        if (isset($poi_matrix[$latitude][$longitude])) {
          $poi_matrix[$latitude][$longitude][] = $poi_info;
        } else {
          $poi_matrix[$latitude][$longitude] = array($poi_info);
        }
      } else {
        $poi_matrix[$latitude] = array($longitude => array($poi_info));
      }
    }

    // Turn Latitude/Longitude Matrix into Array of PoIs
    $pois = array();
    foreach ($poi_matrix as $latitude => $longitudes) {
      foreach ($longitudes as $longitude => $coord_pois) {
        if (sizeof($coord_pois) > 1) {
          $body = "<br/>";
          foreach ($coord_pois as $i => $poi_info) {
            if ($i > 0) { $body .= "\n<br/><hr/><br/>\n"; }
            $body .=
              '<a href="' . get_permalink($poi_info['postid']) . '">' .
                $poi_info['title'] .
              "</a><br/>\n" . $poi_info['body'] . '<br/>';
          }
          $body .= "<br/>";

          $poi_args = array(
            'title' => sizeof($coord_pois) . ' Listings',
            'body' => $body,
            'point' => array('lat' => $latitude, 'lng' => $longitude),
          );
        } else if (sizeof($coord_pois) == 1) {
          $poi_info = $coord_pois[0];
          $poi_args = array(
            'body' => $poi_info['body'],
            'title' =>
              '<a href="' . get_permalink($poi_info['postid']) . '">' .
                $poi_info['title'] .
              '</a>',
            'point' => array('lat' => $latitude, 'lng' => $longitude),
          );
        } else {
          continue;
        }
        $pois[] = new Mappress_Poi($poi_args);
      }
    }

    $map->pois = $pois;
    self::regenerate_map_cache($map);

    return $map->display(array('directions' => 'none'));
  }

  /** Update the All Communities Map's Generation Time & ID
   *
   * @param Mappress_Map $map The New Map to Cache
   *
   * @return void
   */
  private static function regenerate_map_cache($map) {
    $old_map_id = get_option("communities_map_id");
    if ($old_map_id) {
      $maps = new Mappress_Map();
      $maps->delete($old_map_id);
    }
    $map_post_id = 148417;
    $map_id = $map->save($map_post_id);
    update_option("communities_map_generation_time", time());
    update_option("communities_map_id", $map_id);
  }

  /** Return an Array Containing the Full Address of a Directory Listing
   *
   * @param array $listing The Database Row of the Listing
   *
   * @return array
   */
  private static function get_directory_listing_address($listing) {
      $address = array();
      if (!is_null($listing->address) && $listing->addr_public == 'Public') {
          array_push($address, $listing->address);
      }
      if (!is_null($listing->address2) && $listing->addr_public == 'Public') {
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

  /** Reduce the Address Until Coordinates can be Geocoded from it
   *
   * @param array $address_array An Array Containing Parts of the Listing's
   *                             Address, Ordered from Most Specific to Least
   *                             Specific
   *
   * @return array|null An array containing sucessfully geocoded latitude &
   *                    longitude keys.
   */
  private static function get_best_coords_from_address($address_array) {
    $poi_info = array('address' => join(', ', $address_array));
    $poi = new Mappress_Poi($poi_info);
    $poi->geocode();

    $latitude = $poi->point['lat'];
    $longitude = $po->point['lng'];
    if ($latitude == 0 && $longitude == 0) {
      array_shift($address_array);
      if (empty($address_array)) {
        return null;
      }
      return self::get_best_coords_from_address($address_array);
    } else {
      return array('latitude' => $latitude, 'longitude' => $longitude);
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
  private static function set_directory_listings_latitude_longitude($entry_id, $latitude, $longitude) {
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

}

add_filter('frm_after_create_entry', array('DirectoryMaps', 'update_map'), 50, 2);
add_filter('frm_after_update_entry', array('DirectoryMaps', 'update_map'), 50, 2);
add_shortcode('form_to_mappress_latlng', array('DirectoryMaps', 'to_mappress'));
add_shortcode('form_to_mappress', array('DirectoryMaps', 'address_to_mappress'));
add_shortcode('show_map_of_all_directories', array('DirectoryMaps', 'all_listings_map'));

?>
