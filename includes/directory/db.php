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
  // housing block
  public static $land_status_field_id = 954;
  public static $land_size_amount_field_id = 981;
  public static $land_size_units_field_id = 982;
  public static $current_residence_types_field_id = 956;
  public static $planned_residence_types_field_id = 957;
  public static $current_residences_field_id = 263;
  public static $planned_residences_field_id = 264;
  public static $housing_access_field_id = 958;
  public static $land_owner_field_id = 268;
  public static $housing_comments_field_id = 303;
  // membership block
  public static $adult_count_field_id = 254;
  public static $child_count_field_id = 420;
  public static $nonmember_count_field_id = 255;
  public static $percent_male_field_id = 708;
  public static $percent_female_field_id = 709;
  public static $percent_trans_field_id = 710;
  public static $percent_nonbinary_field_id = 991;
  public static $visitor_process_field_id = 258;
  public static $membership_process_field_id = 421;
  public static $membership_comments_field_id = 304;
  // government block
  public static $decision_making_field_id = 250;
  public static $leader_field_id = 251;
  public static $leadership_group_field_id = 252;
  public static $government_comments_field_id = 305;
  // economics block
  public static $has_join_fee_field_id = 246;
  public static $join_fee_field_id = 247;
  public static $has_regular_fees_field_id = 248;
  public static $regular_fees_field_id = 249;
  public static $share_income_field_id = 241;
  public static $contribute_labor_field_id = 244;
  public static $labor_hours_field_id = 245;
  public static $member_debt_field_id = 243;
  public static $economics_comments_field_id = 306;
  // sustainibility block
  public static $energy_infrastructure_field_id = 966;
  public static $renewable_percentage_field_id = 299;
  public static $renewable_sources_field_id = 295;
  public static $planned_renewable_percentage_field_id = 297;
  public static $current_food_field_id = 294;
  public static $planned_food_field_id = 315;
  public static $local_food_field_id = 301;
  // lifestyle block
  public static $facilities_field_id = 967;
  public static $internet_access_field_id = 972;
  public static $internet_speed_field_id = 974;
  public static $cell_service_field_id = 973;
  public static $shared_meals_field_id = 697;
  public static $diet_practices_field_id = 236;
  public static $common_diet_field_id = 237;
  public static $special_diets_field_id = 975;
  public static $alcohol_field_id = 238;
  public static $tobacco_field_id = 239;
  public static $diet_comments_field_id = 307;
  public static $spiritual_practices_field_id = 259;
  public static $religion_expected_field_id = 969;
  public static $education_field_id = 281;
  public static $healthcare_practice_field_id = 970;
  public static $healthcare_comments_field_id = 971;
  public static $healthcare_options_field_id = 282;
  public static $lifestyle_comments_field_id = 976;
  // cohousing block
  public static $cohousing_status_field_id = 718;
  public static $cohousing_completed_field_id = 293;
  public static $cohousing_units_field_id = 206;
  public static $cohousing_shared_building_field_id = 272;
  public static $cohousing_shared_area_field_id = 719;
  public static $cohousing_architect_field_id = 270;
  public static $cohousing_developer_field_id = 269;
  public static $cohousing_lender_field_id = 271;
  // other sections
  public static $additional_comments_field_id = 302;
  public static $gallery_ids_field_id = 229;
  public static $youtube_ids_field_id = 812;
  public static $network_affiliations_field_id = 283;
  public static $other_affiliations_field_id = 279;
  public static $community_affiliations_field_id = 278;
  public static $fair_housing_field_id = 412;
  public static $fair_housing_exceptions_field_id = 414;
  public static $keywords_field_id = 716;
  // hidden/server/misc
  public static $user_id_field_id = 430;
  public static $community_name_field_id = 9;
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
