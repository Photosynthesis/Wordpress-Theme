<?php
/** Directory Database Functions **/
class DirectoryDB
{
  // membership
  public static $is_member_field_id = 933;
  public static $membership_start_field_id = 977;
  public static $membership_end_field_id = 985;
  // overview
  public static $verified_date_field_id = 978;
  public static $primary_image_field_id = 228;
  public static $mission_statement_field_id = 286;
  public static $description_field_id = 277;
  public static $community_status_field_id = 291;
  public static $community_types_field_id = 262;
  public static $started_planning_field_id = 273;
  public static $started_living_together_field_id = 274;
  public static $spiritual_practices_field_id = 259;
  public static $open_to_members_field_id = 257;
  public static $open_to_visitors_field_id = 256;
  // contact
  public static $contact_email_field_id = 199;
  public static $contact_name_field_id = 202;
  public static $backup_email_field_id = 284;
  public static $contact_phone_public_field_id = 201;
  // website
  public static $website_address_field_id = 227;
  public static $business_website_field_id = 717;
  public static $facebook_address_field_id = 197;
  public static $twitter_address_field_id = 198;
  public static $social_address_field_id = 200;
  // address
  public static $is_address_public_field_id = 285;
  public static $public_address_type_field_id = 953;
  public static $address_one_field_id = 425;
  public static $address_two_field_id = 426;
  public static $city_field_id = 427;
  public static $state_field_id = 815;
  public static $province_field_id = 816;
  public static $zipcode_field_id = 429;
  public static $country_field_id = 424;
  public static $latitude_field_id = 684;
  public static $longitude_field_id = 685;
  // disbanded/reforming
  public static $disbanded_year_field_id = 705;
  public static $disbanded_info_field_id = 704;
  public static $reforming_year_field_id = 703;
  public static $reforming_info_field_id = 276;
  // about block
  public static $programs_field_id = 950;
  public static $location_field_id = 952;
  // other sections
  public static $network_affiliations_field_id = 283;
  public static $other_affiliations_field_id = 279;
  public static $keywords_field_id = 716;
  // hidden/server/misc
  public static $update_email_date_field_id = 992;

  /* Return an Array of every published community. */
  public static function get_published_items() {
    global $wpdb;
    $listing_query = <<<SQL
SELECT items.*, posts.post_title
FROM {$wpdb->prefix}frm_items AS items
INNER JOIN
  (SELECT ID, post_type, post_status, post_title
   FROM {$wpdb->prefix}posts AS posts
   WHERE (`post_type`='directory' AND `post_status`='publish')
  ) AS posts ON posts.ID=items.post_id
WHERE (items.is_draft=0 AND items.form_id=2)
SQL;
    return $wpdb->get_results($listing_query, ARRAY_A);
  }

  /* Get the Name of a Community. */
  public static function get_name($item) {
    if ($item['post_title'] && $item['post_title'] !== '') {
      return $item['post_title'];
    } else if ($item['name'] && $item['name'] !== '') {
      return $item['name'];
    } else {
      $meta_name = self::get_item_meta(9, $item['id']);
      if ($meta_name !== false && $meta_name !== '') {
        return $meta_name;
      } else {
        return "<ListingNameNotFound>";
      }
    }
  }

  /* Attempt to find a Community by it's name, otherwise return false. */
  public static function get_community_id_by_name($name) {
    global $wpdb;
    $listing_query = $wpdb->prepare(
      "SELECT names.meta_value AS community_name, posts.post_title AS post_name, items.id AS id " .
      "FROM {$wpdb->prefix}frm_items as items " .
      "LEFT JOIN (SELECT * FROM {$wpdb->prefix}posts WHERE post_type='directory') " .
      "AS posts ON posts.ID=items.post_id " .
      "LEFT JOIN (SELECT * FROM {$wpdb->prefix}frm_item_metas WHERE field_id=9) " .
      "AS names ON names.item_id=items.id " .
      "WHERE items.form_id=2 AND (names.meta_value=%s OR posts.post_title=%s)"
      , $name, $name);
    $results = $wpdb->get_results($listing_query);
    if (sizeof($results) > 0) {
      return $results[0]->id;
    } else {
      return false;
    }
  }

  /* Grab all the meta items for a listing. */
  public static function get_metas($item_id, $field_ids = array()) {
    global $wpdb;
    if (sizeof($field_ids) > 0) {
      $field_string = "(" . join(",", $field_ids) . ")";
      $field_where = "AND field_id IN {$field_string}";
    } else {
      $field_where = "";
    }
    $query = <<<SQL
SELECT field_id, meta_value
FROM {$wpdb->prefix}frm_item_metas
WHERE item_id={$item_id} {$field_where}
SQL;
    return $wpdb->get_results($query, ARRAY_A);
  }

  /* Update or insert an item's field value. */
  public static function update_or_insert_item_meta($field_id, $item_id, $value) {
    $meta_id = self::get_item_meta_id($field_id, $item_id);
    if ($meta_id !== false) {
      self::update_item_meta($meta_id, $value);
    } else {
      self::insert_item_meta($field_id, $item_id, $value);
    }
  }

  /* Return an item's meta id for a field, or false if none exists. */
  public static function get_item_meta_id($field_id, $item_id) {
    global $wpdb;
    $query = $wpdb->prepare(
      "SELECT id FROM {$wpdb->prefix}frm_item_metas " .
      "WHERE field_id=%d AND item_id=%d",
      $field_id, $item_id);
    $results = $wpdb->get_results($query);
    if (sizeof($results) > 0) {
      return $results[0]->id;
    } else {
      return false;
    }
  }

  /* Get a specific meta item, or `false` if one does not exist */
  public static function get_item_meta($field_id, $item_id) {
    global $wpdb;
    $query = $wpdb->prepare(
      "SELECT * FROM {$wpdb->prefix}frm_item_metas " .
      "WHERE field_id=%d AND item_id=%d",
      $field_id, $item_id);
    $results = $wpdb->get_results($query);
    if (sizeof($results) > 0) {
      return $results[0];
    } else {
      return false;
    }
  }

  /* Get the meta value for a meta item, or `false` if one does not exist */
  public static function get_item_meta_value($field_id, $item_id) {
    $item_meta = self::get_item_meta($field_id, $item_id);
    if ($item_meta !== false) {
      return $item_meta->meta_value;
    } else {
      return false;
    }
  }

  /* Update a specific meta value. */
  public static function update_item_meta($meta_id, $value) {
    global $wpdb;
    $query = $wpdb->prepare(
      "UPDATE {$wpdb->prefix}frm_item_metas " .
      "SET meta_value=%s WHERE id=%d",
      $value, $meta_id);
    $wpdb->get_results($query);
  }

  /* Create a meta value representing an item and a field. */
  public static function insert_item_meta($field_id, $item_id, $value) {
    global $wpdb;
    $query = $wpdb->prepare(
      "INSERT INTO {$wpdb->prefix}frm_item_metas " .
      "(meta_value, field_id, item_id, created_at) VALUES " .
      "(%s, %d, %d, NOW())",
      $value, $field_id, $item_id);
    $wpdb->get_results($query);
  }
}

?>
