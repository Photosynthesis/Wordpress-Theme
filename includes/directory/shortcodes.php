<?php
/** Directory Shortcodes **/
class DirectoryShortcodes
{
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
  public static function show_edit_link($atts) {
    extract(shortcode_atts(array('listing_id' => 0), $atts));
    if (current_user_can('edit_plugins')) {
      $entry_edit_url = "/wp-admin/admin.php" .
        "?page=formidable-entries&frm_action=edit&id=$listing_id";
      return "<a href=\"$entry_edit_url\">Edit Listing</a>";
    } else {
      return "";
    }
  }

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
  public static function verify_link($atts) {
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
        $entry_is_valid = $this::validate_entry($listing_id);
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
  /** Ensure that the current data for the Listing would validate the current form */
  private static function validate_entry($entry_id) {
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

  /* Display the Community Name in the Contact-A-Community Form */
  public static function community_name() {
    if (!empty($_GET["cmty"]) && is_numeric($_GET["cmty"])) {
      return do_shortcode('[frm-field-value field_id="9" entry_id="' . $_GET["cmty"] . '"]');
    } else {
      return "the community";
    }
  }

  /* Display the Community Link in the Contact-A-Community Form */
  public static function community_link() {
    if (!empty($_GET["cmty"]) && is_numeric($_GET["cmty"])) {
      return 'Back to <a href="/directory/listings/?entry=' . $_GET["cmty"] . '">' .
        do_shortcode('[frm_cmty_name]') . '</a>';
    }
  }

}

add_shortcode('form_to_mappress_latlng', array('DirectoryShortcodes', 'to_mappress'));
add_shortcode('directory_show_edit_link_if_admin', array('DirectoryShortcodes', 'show_edit_link'));
add_shortcode('directory_verify_listing_link', array('DirectoryShortcodes', 'verify_link'));
add_shortcode('frm_cmty_name', array('DirectoryShortcodes', 'community_name'));
add_shortcode('frm_cmty_link', array('DirectoryShortcodes', 'community_link'));

?>
