<?php
/** Directory Database Functions **/
class DirectoryDB
{
  public static $is_member_field_id = 933;
  public static $membership_start_field_id = 977;
  public static $membership_end_field_id = 985;

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
