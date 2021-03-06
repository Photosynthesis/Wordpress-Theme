<?php
/** A REST API Exposing Directory Listing Resources **/
class APIDirectory
{
  const api_namespace = 'v1/directory';

  /* Register the Directory Endpoints */
  public static function register_routes() {
    register_rest_route(self::api_namespace, '/entry/', array(
      'methods' => 'GET',
      'callback' => array('APIDirectory', 'entry'),
    ));
    register_rest_route(self::api_namespace, '/entries/', array(
      'methods' => 'GET',
      'callback' => array('APIDirectory', 'entries'),
    ));
    register_rest_route(self::api_namespace, '/data-commons/', array(
      'methods' => 'GET',
      'callback' => array('APIDirectory', 'data_commons'),
    ));
    register_rest_route(self::api_namespace, '/entry/validate/', array(
      'methods' => 'POST',
      'callback' => array('APIDirectory', 'validate_entry'),
    ));
  }

  /* Return Data for a Single Entry.
   *
   * Requires a single `slug` parameter to fetch the Entry data.
   *
   * The returned entry has the following fields:
   *
   *    - id
   *    - name
   *    - slug
   *    - isOwner
   *    - isAdmin
   *    - image
   *    - missionStatement
   *    - description
   *    - createdAt
   *    - updatedAt
   *    - communityStatus
   *    - startedPlanning
   *    - startedLivingTogether
   *    - openToMembership
   *    - openToVisitors
   *    - city
   *    - state
   *    - country
   *    - isFicMember
   *    - communityTypes
   *    - programs
   *    - location
   *    - currentResidenceTypes
   *    - plannedResidenceTypes
   *    - housingAccess
   *    - adultCount
   *    - decisionMaking
   *    - leader
   *    - hasJoinFee
   *    - hasRegularFees
   *    - sharedIncome
   *    - contributeLabor
   *    - fairHousingComplaint
   *
   * As well as the following optional fields:
   *
   *    - websiteUrl
   *    - businessUrl
   *    - facebookUrl
   *    - twitterUrl
   *    - socialUrl
   *    - contactName
   *    - contactPhone
   *    - contactAddress
   *        - lineOne
   *        - lineTwo
   *        - zipCode
   *        - type
   *    - ficMembershipStart
   *    - disbandedData
   *        - year
   *        - info
   *    - reformingData
   *        - year
   *        - info
   *    - mapCoordinates
   *        - latitude
   *        - longitude
   *    - landStatus
   *    - landSizeAmount
   *    - landSizeUnits
   *    - currentResidences
   *    - plannedResidences
   *    - landOwner
   *    - housingComments
   *    - childCount
   *    - nonmemberCount
   *    - percentMale
   *    - percentFemale
   *    - percentTrans
   *    - percentNonBinary
   *    - visitorProcess
   *    - membershipProcess
   *    - membershipComments
   *    - leadershipGroup
   *    - governmentComments
   *    - joinFee
   *    - regularFees
   *    - laborHours
   *    - memberDebt
   *    - energyInfrastructure
   *    - renewablePercentage
   *    - renewableSources
   *    - plannedRenewablePercentage
   *    - currentFoodPercentage
   *    - plannedFoodPercentage
   *    - localFoodPercentage
   *    - facilities
   *    - internetAccess
   *    - internetSpeed
   *    - cellService
   *    - sharedMeals
   *    - dietaryPractices
   *    - commonDiet
   *    - specialDiets
   *    - alcohol
   *    - tobacco
   *    - dietComments
   *    - spiritualPractices
   *    - religionExpected
   *    - education
   *    - commonHealthcarePractice
   *    - healthcareComments
   *    - healthcareOptions
   *    - lifestyleComments
   *    - cohousing
   *        - siteStatus
   *        - yearCompleted
   *        - housingUnits
   *        - hasSharedBuilding
   *        - sharedBuildingArea
   *        - architect
   *        - developer
   *        - lender
   *    - additionalComments
   *    - galleryImages
   *    - youtubeIds
   *    - networkAffiliations
   *    - otherAffiliations
   *    - communityAffiliations
   *    - keywords
   *
   *
   *    TODO:
   *     * Special message if hidden by user? We may remove that option though
   *
   */
  public static function entry($data) {
    global $wpdb;

    // Field ID -> JSON Key
    $public_fields = array(
      // Fields with more complex processing
      DirectoryDB::$primary_image_field_id => 'image_post_id',
      DirectoryDB::$is_address_public_field_id => 'contact_address_public',
      DirectoryDB::$public_address_type_field_id => 'contact_address_type',
      DirectoryDB::$address_one_field_id => 'address_one',
      DirectoryDB::$address_two_field_id => 'address_two',
      DirectoryDB::$zipcode_field_id => 'zip_code',
      DirectoryDB::$is_member_field_id => 'is_fic_member',
      DirectoryDB::$membership_start_field_id => 'membership_start_date',
      DirectoryDB::$disbanded_year_field_id => 'disbanded_year',
      DirectoryDB::$disbanded_info_field_id => 'disbanded_info',
      DirectoryDB::$reforming_year_field_id => 'reforming_year',
      DirectoryDB::$reforming_info_field_id => 'reforming_info',
      DirectoryDB::$latitude_field_id => 'latitude',
      DirectoryDB::$longitude_field_id => 'longitude',
      DirectoryDB::$fair_housing_field_id => 'fair_housing_complaint',
      DirectoryDB::$fair_housing_exceptions_field_id => 'fair_housing_exceptions',
      DirectoryDB::$gallery_ids_field_id => 'gallery_image_ids',
      DirectoryDB::$cohousing_status_field_id => 'cohousing_status',
      DirectoryDB::$cohousing_completed_field_id => 'cohousing_completed',
      DirectoryDB::$cohousing_units_field_id => 'cohousing_units',
      DirectoryDB::$cohousing_shared_building_field_id => 'cohousing_has_building',
      DirectoryDB::$cohousing_shared_area_field_id =>'cohousing_building_area',
      DirectoryDB::$cohousing_architect_field_id => 'cohousing_architect',
      DirectoryDB::$cohousing_developer_field_id => 'cohousing_developer',
      DirectoryDB::$cohousing_lender_field_id => 'cohousing_lender',
      DirectoryDB::$user_id_field_id => 'user_id',
      // Fields that just need simple cleanup
      DirectoryDB::$community_status_field_id => 'communityStatus',
      DirectoryDB::$mission_statement_field_id => 'missionStatement',
      DirectoryDB::$description_field_id => 'description',
      DirectoryDB::$started_planning_field_id => 'startedPlanning',
      DirectoryDB::$started_living_together_field_id => 'startedLivingTogether',
      DirectoryDB::$open_to_members_field_id => 'openToMembership',
      DirectoryDB::$open_to_visitors_field_id => 'openToVisitors',
      DirectoryDB::$website_address_field_id => 'websiteUrl',
      DirectoryDB::$business_website_field_id => 'businessUrl',
      DirectoryDB::$facebook_address_field_id => 'facebookUrl',
      DirectoryDB::$twitter_address_field_id => 'twitterUrl',
      DirectoryDB::$social_address_field_id => 'socialUrl',
      DirectoryDB::$contact_name_field_id => 'contactName',
      DirectoryDB::$contact_phone_public_field_id => 'contactPhone',
      DirectoryDB::$city_field_id => 'city',
      DirectoryDB::$state_field_id => 'state',
      DirectoryDB::$province_field_id => 'province',
      DirectoryDB::$country_field_id => 'country',
      DirectoryDB::$community_types_field_id => 'communityTypes',
      DirectoryDB::$programs_field_id => 'programs',
      DirectoryDB::$location_field_id => 'location',
      DirectoryDB::$land_status_field_id => 'landStatus',
      DirectoryDB::$land_size_amount_field_id => 'landSizeAmount',
      DirectoryDB::$land_size_units_field_id => 'landSizeUnits',
      DirectoryDB::$current_residence_types_field_id => 'currentResidenceTypes',
      DirectoryDB::$current_residences_field_id => 'currentResidences',
      DirectoryDB::$planned_residence_types_field_id => 'plannedResidenceTypes',
      DirectoryDB::$planned_residences_field_id => 'plannedResidences',
      DirectoryDB::$housing_access_field_id => 'housingAccess',
      DirectoryDB::$land_owner_field_id => 'landOwner',
      DirectoryDB::$housing_comments_field_id => 'housingComments',
      DirectoryDB::$adult_count_field_id => 'adultCount',
      DirectoryDB::$child_count_field_id => 'childCount',
      DirectoryDB::$nonmember_count_field_id => 'nonmemberCount',
      DirectoryDB::$percent_male_field_id => 'percentMale',
      DirectoryDB::$percent_female_field_id => 'percentFemale',
      DirectoryDB::$percent_trans_field_id => 'percentTrans',
      DirectoryDB::$percent_nonbinary_field_id => 'percentNonBinary',
      DirectoryDB::$visitor_process_field_id => 'visitorProcess',
      DirectoryDB::$membership_process_field_id => 'membershipProcess',
      DirectoryDB::$membership_comments_field_id => 'membershipComments',
      DirectoryDB::$decision_making_field_id => 'decisionMaking',
      DirectoryDB::$leader_field_id => 'leader',
      DirectoryDB::$leadership_group_field_id => 'leadershipGroup',
      DirectoryDB::$government_comments_field_id => 'governmentComments',
      DirectoryDB::$has_join_fee_field_id => 'hasJoinFee',
      DirectoryDB::$join_fee_field_id => 'joinFee',
      DirectoryDB::$has_regular_fees_field_id => 'hasRegularFees',
      DirectoryDB::$regular_fees_field_id => 'regularFees',
      DirectoryDB::$share_income_field_id => 'sharedIncome',
      DirectoryDB::$contribute_labor_field_id => 'contributeLabor',
      DirectoryDB::$labor_hours_field_id => 'laborHours',
      DirectoryDB::$member_debt_field_id => 'memberDebt',
      DirectoryDB::$economics_comments_field_id => 'economicsComments',
      DirectoryDB::$energy_infrastructure_field_id => 'energyInfrastructure',
      DirectoryDB::$renewable_percentage_field_id => 'renewablePercentage',
      DirectoryDB::$renewable_sources_field_id => 'renewableSources',
      DirectoryDB::$planned_renewable_percentage_field_id => 'plannedRenewablePercentage',
      DirectoryDB::$current_food_field_id => 'currentFoodPercentage',
      DirectoryDB::$planned_food_field_id => 'plannedFoodPercentage',
      DirectoryDB::$local_food_field_id => 'localFoodPercentage',
      DirectoryDB::$facilities_field_id => 'facilities',
      DirectoryDB::$internet_access_field_id => 'internetAccess',
      DirectoryDB::$internet_speed_field_id => 'internetSpeed',
      DirectoryDB::$cell_service_field_id => 'cellService',
      DirectoryDB::$shared_meals_field_id => 'sharedMeals',
      DirectoryDB::$diet_practices_field_id => 'dietaryPractices',
      DirectoryDB::$common_diet_field_id => 'commonDiet',
      DirectoryDB::$special_diets_field_id => 'specialDiets',
      DirectoryDB::$alcohol_field_id => 'alcohol',
      DirectoryDB::$tobacco_field_id => 'tobacco',
      DirectoryDB::$diet_comments_field_id => 'dietComments',
      DirectoryDB::$spiritual_practices_field_id => 'spiritualPractices',
      DirectoryDB::$religion_expected_field_id => 'religionExpected',
      DirectoryDB::$education_field_id => 'education',
      DirectoryDB::$healthcare_practice_field_id => 'commonHealthcarePractice',
      DirectoryDB::$healthcare_comments_field_id => 'healthcareComments',
      DirectoryDB::$healthcare_options_field_id => 'healthcareOptions',
      DirectoryDB::$lifestyle_comments_field_id => 'lifestyleComments',
      DirectoryDB::$additional_comments_field_id => 'additionalComments',
      DirectoryDB::$youtube_ids_field_id => 'youtubeIds',
      DirectoryDB::$network_affiliations_field_id => 'networkAffiliations',
      DirectoryDB::$other_affiliations_field_id => 'otherAffiliations',
      DirectoryDB::$community_affiliations_field_id => 'communityAffiliations',
      DirectoryDB::$keywords_field_id => 'keywords',
    );

    // Validate Parameters
    if (!$data['slug']) {
      return new WP_Error('no_slug', 'Missing slug parameter', array('status' => 400));
    }

    // Fetch Base Entry Data
    $entry_query = <<<SQL
SELECT i.id AS id, post_title AS name, post_name AS slug, updated_at, created_at, p.ID as post_ID
FROM {$wpdb->prefix}frm_items AS i
LEFT JOIN (SELECT * FROM {$wpdb->prefix}posts WHERE post_type='directory')
  AS p ON p.ID=i.post_id
WHERE form_id=2 AND post_name='{$data['slug']}'
SQL;
    $data = $wpdb->get_row($entry_query, ARRAY_A);
    if (!$data) {
      return new WP_Error('entry_not_found', 'Directory Listing Not Found', array('status' => 404));
    }

    // Fetch Entry Metas
    $entry_id = $data['id'];
    $entry_metas_query = <<<SQL
SELECT field_id, id, meta_value
FROM {$wpdb->prefix}frm_item_metas as m
WHERE item_id={$data['id']};
SQL;
    $field_to_meta = $wpdb->get_results($entry_metas_query, OBJECT_K);

    // Add Public Fields to Data
    foreach ($public_fields as $field_id => $json_key) {
      $data[$json_key] = $field_to_meta[$field_id]->meta_value;
    }
    return self::clean_detail_entry($data);
  }

  /* Transform the raw database values into our API Spec */
  public static function clean_detail_entry(&$entry) {
    $entry['id'] = self::clean_id($entry['id']);
    $entry['updated_at'] = self::clean_date($entry['updated_at']);
    $entry['created_at'] = self::clean_date($entry['created_at']);

    // Primary Image
    if ($entry['image_post_id']) {
      $entry['image'] = self::get_image_data($entry['image_post_id'], 'large');
    } else {
      $entry['image'] = null;
    }
    unset($entry['image_post_id']);

    // Contact Address
    if ($entry['contact_address_public'] === 'Public') {
      $entry_type = $entry['contact_address_type'] === 'Community address'
        ? 'community' : 'mailing';
      $entry['contactAddress'] = array(
        'lineOne' => $entry['address_one'],
        'lineTwo' => $entry['address_two'],
        'zipCode' => $entry['zip_code'],
        'type' => $entry_type,
      );
    } else {
      $entry['contactAddress'] = null;
    }
    unset($entry['contact_address_public']);
    unset($entry['contact_address_type']);
    unset($entry['address_one']);
    unset($entry['address_two']);
    unset($entry['zip_code']);

    // Map Coordinates
    if (isset($entry['latitude']) && isset($entry['longitude']) &&
        !($entry['latitude'] == 0 && $entry['longitude'] == 0) &&
        !($entry['latitude'] == "39.095963" && $entry['longitude'] == "-96.606447")) {
      $entry['mapCoordinates'] = array(
        'latitude' => (float) $entry['latitude'],
        'longitude' => (float) $entry['longitude'],
      );
    }
    unset($entry['latitude']);
    unset($entry['longitude']);

    // Add FIC Membership Information
    if ($entry['is_fic_member'] === "Yes") {
      $entry['isFicMember'] = true;
      if ($entry['ficMembershipStart']) {
        $entry['ficMembershipStart'] = date('Y', strtotime($entry['membership_start_date']));
      } else {
        $entry['ficMembershipStart'] = "";
      }
    } else {
      $entry['isFicMember'] = false;
    }
    unset($entry['is_fic_member']);
    unset($entry['membership_start_date']);

    // Add Disbanded Data
    if (stripos('disbanded', $entry['communityStatus']) !== false) {
      if ($entry['disbanded_year'] || $entry['disbanded_info']) {
        $entry['disbandedData'] = array(
          'year' => $entry['disbanded_year'],
          'info' => $entry['disbanded_info'],
        );
      }
    }
    unset($entry['disbanded_year']);
    unset($entry['disbanded_info']);
    // Add Reforming Data
    if (stripos('re-forming', $entry['communityStatus']) !== false) {
      if ($entry['reforming_year'] || $entry['reforming_info']) {
        $entry['reformingData'] = array(
          'year' => $entry['reforming_year'],
          'info' => $entry['reforming_info'],
        );
      }
    }
    unset($entry['reforming_year']);
    unset($entry['reforming_info']);

    // Cohousing Fields
    if (strpos(strtolower($entry['communityTypes']), 'cohousing') !== false) {
      $cohousing_data = array(
        'siteStatus' => $entry['cohousing_status'],
        'yearCompleted' =>
          isset($entry['cohousing_completed'])
            ? (int) $entry['cohousing_completed'] : null,
        'housingUnits' =>
          isset($entry['cohousing_units'])
            ? (int) $entry['cohousing_units'] : null,
        'hasSharedBuilding' =>
          isset($entry['cohousing_has_building'])
            ? ($entry['cohousing_has_building'] === "Yes") : null,
        'sharedBuildingArea' =>
          isset($entry['cohousing_building_area'])
            ? (int) $entry['cohousing_building_area'] : null,
        'architect' => $entry['cohousing_architect'],
        'developer' => $entry['cohousing_developer'],
        'lender' => $entry['cohousing_lender'],
      );
      $entry['cohousing'] = $cohousing_data;
    }
    $coho_fields = array(
      'status', 'completed', 'units', 'has_building', 'building_area',
      'architect', 'developer', 'lender'
    );
    foreach ($coho_fields as $field) {
      unset($entry['cohousing_' . $field]);
    }

    // FHL
    $entry['fair_housing_complaint'] = $entry['fair_housing_complaint'] === "Yes";

    // User/Admin Data
    $current_user = wp_get_current_user();
    $entry['is_admin'] = in_array('administrator', $current_user->roles);
    $entry['is_owner'] = ((int) $entry['user_id']) === $current_user->ID;
    unset($entry['user_id']);

    self::unserialize_and_convert_case($entry);

    // Gallery
    if (is_array($entry['galleryImageIds']) && sizeof($entry['galleryImageIds']) > 0) {
      $entry['galleryImages'] = array();
      foreach ($entry['galleryImageIds'] as $image_id) {
        $img_data = self::get_image_data($image_id, 'thumbnail');
        if (!is_null($img_data)) {
          $entry['galleryImages'][] = $img_data;
        }
      }
    }
    unset($entry['galleryImageIds']);

    if ($entry['youtubeIds']) {
      $entry['youtubeIds'] = explode(",", $entry['youtubeIds']);
    }
    if (is_array($entry['youtubeIds'])) {
      foreach ($entry['youtubeIds'] as $index => $youtubeId) {
        $entry['youtubeIds'][$index] =
          str_replace('http://www.youtube.com/watch?v=', '',
          str_replace('https://www.youtube.com/watch?v=', '',
            $youtubeId
          )
          );
      }
    }

    if (!$entry['fairHousingComplaint'] && $entry['fairHousingExceptions']) {
      $entry['fairHousingComplaint'] = true;
    }
    unset($entry['fairHousingExceptions']);

    $empty_fields = array('missionStatement', 'description') ;
    foreach ($empty_fields as $field) {
      if (!isset($entry[$field])) {
        $entry[$field] = "";
      }
    }

    $empty_array_fields = array(
      'communityTypes', 'currentResidenceTypes', 'plannedResidenceTypes',
      'housingAccess', 'education', 'healthcareOptions', 'programs', 'facilities',
      'dietaryPractices', 'spiritualPractices',
    );
    foreach ($empty_array_fields as $field) {
      if (!$entry[$field]) {
        $entry[$field] = array();
      }
    }

    $int_fields = array('startedPlanning', 'startedLivingTogether', 'adultCount');
    foreach ($int_fields as $field) {
      $entry[$field] = (int) $entry[$field];
    }
    $optional_int_fields = array(
      'currentResidences', 'plannedResidences', 'childCount', 'nonmemberCount',
      'joinFee', 'regularFees', 'laborHours',
    );
    foreach ($optional_int_fields as $field) {
      if (isset($entry[$field])) {
        $entry[$field] = (int) $entry[$field];
      }
    }

    $float_fields = array('landSizeAmount');
    foreach ($float_fields as $field) {
      if (isset($entry[$field])) {
        $entry[$field] = (float) $entry[$field];
      }
    }

    $yes_no_fields = array('hasJoinFee', 'hasRegularFees');
    foreach ($yes_no_fields as $field) {
      if ($entry[$field]) {
        $entry[$field] = $entry[$field] === "Yes";
      }
    }

    $entry['state'] = self::clean_state($entry['state'], $entry['province']);
    unset($entry['province']);

    if (!$entry['openToMembership']) {
      $entry['openToMembership'] = "No";
    }
    if (!$entry['openToVisitors']) {
      $entry['openToVisitors'] = "No";
    }

    $url_fields = array('websiteUrl', 'businessUrl', 'facebookUrl', 'twitterUrl', 'socialUrl');
    foreach ($url_fields as $field) {
      $entry[$field] = self::clean_url($entry[$field]);
    }
    $nullable_fields = array('contactName', 'contactPhone', 'landStatus');
    foreach ($nullable_fields as $field) {
      if (!$entry[$field]) {
        $entry[$field] = null;
      }
    }

    $tilde_array_fields = array(
      'programs', 'currentResidenceTypes', 'plannedResidenceTypes',
      'dietaryPractices', 'healthcareOptions',
    );
    foreach ($tilde_array_fields as $field) {
      if (!is_array($entry[$field])) {
        continue;
      }
      foreach ($entry[$field] as $i => $field_value) {
        $entry[$field][$i] = self::clean_escapes($field_value);
      }
    }

    $tilde_fields = array(
      'decisionMaking', 'leader', 'renewablePercentage', 'plannedRenewablePercentage',
      'currentFoodPercentage', 'localFoodPercentage', 'alcohol', 'tobacco',
    );
    foreach ($tilde_fields as $field) {
      if (isset($entry[$field])) {
        $entry[$field] = self::clean_escapes($entry[$field]);
      }
    }

    $string_or_array_fields = array(
      'networkAffiliations', 'spiritualPractices', 'renewableSources',
      'dietaryPractices',
    );
    foreach ($string_or_array_fields as $field) {
      if (is_string($entry[$field])) {
        $entry[$field] = array($entry[$field]);
      }
    }

    // TODO: Fix these fields, they are required for published listings!
    if (!$entry['location']) {
      $entry['location'] = 'to be determined';
    }
    if (!$entry['contributeLabor']) {
      $entry['contributeLabor'] = "No";
    }
    $required_bool_fields = array('hasJoinFee', 'hasRegularFees',);
    foreach ($required_bool_fields as $field) {
      if (!isset($entry[$field]) || is_null($entry[$field])) {
        $entry[$field] = false;
      }
    }

    // Decode any html entities in strings or arrays of strings
    foreach ($entry as $field => $value) {
      $is_string_field = is_string($value);
      $is_string_array = is_array($value) && is_string($value[0]);
      if ($is_string_field) {
        $entry[$field] = html_entity_decode($value);
      } else if ($is_string_array) {
        $new_value = array();
        foreach ($entry[$field] as $field_value) {
          $new_value[] = html_entity_decode($field_value);
        }
        $entry[$field] = $new_value;
      }
    }

    return $entry;
  }

  /* Return All Published Entries.
   *
   * Responses are paginated by a fixed amount(currently 25). The page number
   * may be set via the `page` query parameter. The total item count is nested
   * under the `totalCount` key. The entries are nested under the `listings`
   * key.
   *
   *
   * Filtering may be done via the following query parameters:
   *
   *    - description
   *    - status
   *    - type
   *    - spiritual
   *    - visitors
   *    - members
   *    - membership
   *    - search
   *
   * You can separate multiple choices with commas, or by submitting arrays
   * (`?visitors[]=Yes&visitors[]=No`).
   *
   *
   * Ordering may be done via the `order` query parameter. By default, the
   * route will order results by Community Name. Valid values are:
   *
   *    - updated
   *    - created
   *
   *
   * Each entry has the following fields:
   *
   *    - id
   *    - name
   *    - slug
   *    - thumbnailUrl
   *    - createdAt
   *    - updatedAt
   *    - communityStatus
   *    - communityTypes
   *    - city
   *    - state
   *    - country
   *    - openToMembership
   *    - openToVisitors
   *
   */
  public static function entries($data) {
    global $wpdb;

    $meta_fields = array(
      DirectoryDB::$primary_image_field_id => 'image_post_id',
      DirectoryDB::$community_types_field_id => 'communityTypes',
      DirectoryDB::$community_status_field_id => 'communityStatus',
      DirectoryDB::$open_to_members_field_id => 'openToMembership',
      DirectoryDB::$open_to_visitors_field_id => 'openToVisitors',
      DirectoryDB::$city_field_id => 'city',
      DirectoryDB::$state_field_id => 'state',
      DirectoryDB::$province_field_id => 'province',
      DirectoryDB::$country_field_id => 'country',
      DirectoryDB::$is_member_field_id => 'ficMember',
      DirectoryDB::$description_field_id => 'description',
      DirectoryDB::$spiritual_practices_field_id => 'spiritual',
    );

    // Build additional Select, Join, & Where Clauses for Filters
    $selects = array();
    $joins = array();
    $wheres = "";

    $filters = array(
      'visitors' => array(
        'id' => DirectoryDB::$open_to_visitors_field_id, 'compare' => 'LIKE%'),
      'members' => array(
        'id' => DirectoryDB::$open_to_members_field_id, 'compare' => '='),
      'status' => array(
        'id' => DirectoryDB::$community_status_field_id, 'compare' => 'LIKE%'),
      'membership' => array(
        'id' => DirectoryDB::$is_member_field_id, 'compare' => '='),
      'type' => array(
        'id' => DirectoryDB::$community_types_field_id, 'compare' => '%LIKE%'),
      'description' => array(
        'id' => DirectoryDB::$description_field_id, 'compare' => '%LIKE%'),
      'spiritual' => array(
        'id' => DirectoryDB::$spiritual_practices_field_id, 'compare' => '%LIKE%'),
      'country' => array(
        'id' => DirectoryDB::$country_field_id, 'compare' => '='),
      'state' => array(
        'id' => DirectoryDB::$state_field_id, 'compare' => '='),
      'province' => array(
        'id' => DirectoryDB::$province_field_id, 'compare' => '='),
    );

    // Add Meta Filters
    foreach ($filters as $filter_param => $filter) {
      $filter_value = $data[$filter_param];
      if ($filter_value) {
        if (is_array($filter_value)) {
          $filter_value = join(",", $filter_value);
        }
        $field_id = $filter['id'];
        $selects[] = "{$meta_fields[$field_id]}_metas.meta_value AS {$meta_fields[$field_id]}";
        $joins[] = <<<SQL
          INNER JOIN
            (SELECT field_id, id, item_id, meta_value
             FROM {$wpdb->prefix}frm_item_metas
             WHERE field_id={$field_id}
            ) AS {$meta_fields[$field_id]}_metas ON {$meta_fields[$field_id]}_metas.item_id=items.id
SQL;
        $values = explode(",", $filter_value);
        $where_clauses = array();
        foreach ($values as $value) {
          $escaped = esc_sql($value);
          if ($filter['compare'] == '=')  {
            $comparison = "='{$escaped}'";
          } else if ($filter['compare'] == 'LIKE%') {
            $comparison = " LIKE '{$escaped}%'";
          } else if ($filter['compare'] == '%LIKE%') {
            $comparison = " LIKE '%{$escaped}%'";
          }
          $where_clauses[] = "{$meta_fields[$field_id]}_metas.meta_value{$comparison}";
        }
        $where_clauses = "(" . join(" OR ", $where_clauses) . ")";
        $wheres .= " AND {$where_clauses}";
      }
    }

    // Handle Search Filter - filter by entries that have matching meta values
    if ($data['search']) {
      if (is_array($data['search'])) { $data['search'] = join(" ", $data['search']); }
      $search_values = explode(" ", $data['search']);
      $search_where_clause = array();
      foreach ($search_values as $search_value) {
        $escaped = esc_sql($search_value);
        $search_where_clause[] = "meta_value LIKE '%{$escaped}%'";
      }
      $search_where_clause = "(" . join(" OR ", $search_where_clause) . ")";
      $search_query = <<<SQL
SELECT DISTINCT item_id
FROM {$wpdb->prefix}frm_item_metas
WHERE {$search_where_clause}
SQL;
      $search_entry_ids = $wpdb->get_results($search_query, ARRAY_N);
      $search_entry_ids = array_map(function($a) { return $a[0]; }, $search_entry_ids);
      $wheres .= " AND (items.id IN (" . join(", ", $search_entry_ids) . ") )";
    }

    if (sizeof($selects) > 0) {
      $selects = ", " . join(", ", $selects);
    } else {
      $selects = "";
    }
    $joins = join("\n", $joins);


    // Build the Limit
    // TODO: Configurable per_page value
    $per_page = 25;
    $page = (int) $data['page'];
    $page = $page ? $page : 1;
    $page--;
    $start = $page * $per_page;
    $limit = "LIMIT {$start}, {$per_page}";


    // Build the Ordering
    $ordering = $data['order'];
    if ($ordering === 'updated') {
      $order_by = "items.updated_at DESC";
    } else if ($ordering === 'created') {
      $order_by = "items.created_at DESC";
    } else {
      $order_by = "posts.post_title";
    }
    $order_by = "ORDER BY {$order_by}";


    // Build & Run the Results Query
    $query = <<<SQL
SELECT
  items.id, items.name, items.created_at, items.updated_at,
  posts.post_title, posts.post_name AS slug,
  image_post_id_metas.meta_value AS image_post_id {$selects}
FROM {$wpdb->prefix}frm_items AS items
INNER JOIN
  (SELECT ID, post_type, post_status, post_title, post_name
   FROM {$wpdb->prefix}posts AS posts
   WHERE (`post_type`='directory' AND `post_status`='publish')
  ) AS posts ON posts.ID=items.post_id
LEFT JOIN
  (SELECT item_id, field_id, meta_value
   FROM {$wpdb->prefix}frm_item_metas WHERE field_id=218)
  AS public_metas ON public_metas.item_id=items.id
LEFT JOIN
  (SELECT item_id, field_id, meta_value
   FROM {$wpdb->prefix}frm_item_metas WHERE field_id=228)
  AS image_post_id_metas ON image_post_id_metas.item_id=items.id

{$joins}

WHERE (items.is_draft=0 AND items.form_id=2 AND public_metas.meta_value <> "No" {$wheres})
GROUP BY slug

{$order_by}
{$limit}
SQL;

    $entries = $wpdb->get_results($query, ARRAY_A);


    // Remove the Fields That Are Only Used For Filtering
    unset($meta_fields[DirectoryDB::$is_member_field_id]);
    unset($meta_fields[DirectoryDB::$description_field_id]);

    // Get & Assign the Metas for each Entry
    foreach ($entries as &$entry) {
      $metas = DirectoryDB::get_metas($entry['id'], array_keys($meta_fields));
      foreach ($metas as $meta) {
        $entry[$meta_fields[$meta['field_id']]] = $meta['meta_value'];
      }
    }

    $entries = array_map('self::clean_list_entry', $entries);

    // Get Total Listing Count for the specified Query Parameters
    $total_count_query = <<<SQL
SELECT COUNT(posts.ID) AS count {$selects}
FROM {$wpdb->prefix}posts AS posts
INNER JOIN
  (SELECT id, form_id, is_draft, post_id
   FROM {$wpdb->prefix}frm_items
   WHERE form_id=2 AND is_draft=0
  ) AS items ON items.post_id=posts.ID
LEFT JOIN
  (SELECT item_id, field_id, meta_value
   FROM {$wpdb->prefix}frm_item_metas WHERE field_id=218)
  AS public_metas ON public_metas.item_id=items.id
{$joins}
WHERE post_type='directory' AND post_status='publish' AND public_metas.meta_value <> "No" {$wheres}
SQL;
    $total_count = (int) $wpdb->get_row($total_count_query, ARRAY_A)['count'];

    return array(
      'listings' => $entries,
      'totalCount' => $total_count,
    );
  }

  /* Transform the SQL Row into our API Spec */
  public static function clean_list_entry(&$entry) {
    $entry['id'] = self::clean_id($entry['id']);
    $entry['updated_at'] = self::clean_date($entry['updated_at']);
    $entry['created_at'] = self::clean_date($entry['created_at']);
    unset($entry['post_content']);

    if ($entry['post_title']) {
      $entry['name'] = $entry['post_title'];
    }
    $entry['name'] = html_entity_decode($entry['name']);
    unset($entry['post_title']);

    $entry['state'] = self::clean_state($entry['state'], $entry['province']);
    unset($entry['province']);

    if ($entry['image_post_id']) {
      $image_data = self::get_image_data($entry['image_post_id'], 'thumbnail');
      $entry['thumbnailUrl'] = $image_data['thumbnailUrl'];
    } else {
      $entry['thumbnailUrl'] = null;
    }
    unset($entry['image_post_id']);

    self::unserialize_and_convert_case($entry);

    // TODO: The communities that these are necessary for should be fixed, these are required fields!
    if (!$entry['communityTypes']) {
      $entry['communityTypes'] = array();
    }
    if (!$entry['openToMembership']) {
      $entry['openToMembership'] = "No";
    }
    if (!$entry['openToVisitors']) {
      $entry['openToVisitors'] = "No";
    }

    return $entry;
  }

  /* Attempt to unserialize all fields & convert snake case fields to camel case */
  public static function unserialize_and_convert_case(&$entry) {
    foreach (array_keys($entry) as $key) {
      if (is_array($entry[$key])) {
        continue;
      }
      // Unserialize Arrays
      $unserialized = unserialize($entry[$key]);
      if (is_array($unserialized)) {
        $entry[$key] = array_values($unserialized);
      }

      // Convert Any snake_case Keys to camelCase
      if (strpos($key, '_') !== false) {
        $camelCaseKey = str_replace('_', '', lcfirst(ucwords($key, '_')));
        $entry[$camelCaseKey] = $entry[$key];
        unset($entry[$key]);
      }
    }
  }

  /* Fetch the src & thumbnail src for an Image post. */
  private static function get_image_data($post_id, $size) {
    $post_guid = get_post($post_id)->guid;
    if (!$post_guid) {
      return null;
    } else {
      $post_guid = str_replace('http://', 'https://', $post_guid);
    }
    $size_src = wp_get_attachment_image_src($post_id, $size)[0];
    if (!$size_src) {
      $size_src = null;
    } else {
      $size_src = str_replace('http://', 'https://', $size_src);
    }
    return array(
      'imageUrl' => $post_guid,
      'thumbnailUrl' => $size_src,
    );
  }

  public static function clean_id($id) {
    return (int) $id;
  }

  public static function clean_date($date) {
    return date('c', strtotime($date));
  }

  // Merge the state & province fields
  public static function clean_state($state, $province) {
    if (!$state && $province) {
      return $province;
    }
    return $state;
  }

  // Return a valid URL or null
  public static function clean_url($value) {
    if (!filter_var($value, FILTER_VALIDATE_URL)) {
      return null;
    }
    return $value;
  }

  // Swap tildes w/ commas & unescape quotes
  public static function clean_escapes($value) {
    return str_replace("\'", "'", str_replace('~', ',', $value));
  }

  /* Return Published Entries for the Data Commons Co-op to Sync With.
   *
   * This route is accessible via GET requests. An `apiKey` parameter is
   * required & should match the `DCC_API_KEY` defined in `wp-config.php or
   * `local_settings.php`. If the parameter is incorrect or not included, the
   * route will return a 401 error. If the `DCC_API_KEY` has not been defined,
   * a 500 error will be thrown.
   *
   * A successful request will return a record with a single field, `listings`,
   * whose value is an array of records with the following fields:
   *
   *    - id
   *    - name
   *    - slug
   *    - types
   *    - description
   *    - missionStatement
   *    - latitude (decimal as a string or null)
   *    - longitude (decimal as a string or null)
   *    - addressLineOne (string or null)
   *    - addressLineTwo (string or null)
   *    - addressType ('mailing', 'community', or null)
   *    - city
   *    - state
   *    - postalCode (string or null)
   *    - country
   *
   * The optional address fields are included if the listing's address has been
   * set to `Public`.
   *
   * Each request will include 100 listings. The page of listings can be
   * specified via the `page` parameter.
   *
   */
  public static function data_commons($data) {
    global $wpdb;

    // Check auth. 401 for wrong key, 500 for no key defined
    if (!defined('DCC_API_KEY')) {
      return new WP_Error(
        'unconfigured_key',
        'Server Config Error: Data Commons Co-Op API Key Not Defined',
        array('status' => 500)
      );
    } else if (!array_key_exists('apiKey', $data->get_params())) {
      return new WP_Error(
        'no_api_key',
        'Unauthorized: Route Requires API Key',
        array('status' => 401)
      );
    } else if ($data['apiKey'] !== DCC_API_KEY) {
      return new WP_Error(
        'invalid_api_key',
        'Unauthorized: Invalid API Key',
        array('status' => 401)
      );
    }

    $meta_fields = array(
      218 => 'is_listing_public',
      DirectoryDB::$is_address_public_field_id => 'is_address_public',
      DirectoryDB::$community_types_field_id => 'types',
      DirectoryDB::$description_field_id => 'description',
      DirectoryDB::$mission_statement_field_id => 'missionStatement',
      DirectoryDB::$latitude_field_id => 'latitude',
      DirectoryDB::$longitude_field_id => 'longitude',
      DirectoryDB::$address_one_field_id => 'addressLineOne',
      DirectoryDB::$address_two_field_id => 'addressLineTwo',
      DirectoryDB::$public_address_type_field_id => 'addressType',
      DirectoryDB::$city_field_id => 'city',
      DirectoryDB::$state_field_id => 'state',
      DirectoryDB::$province_field_id => 'province',
      DirectoryDB::$zipcode_field_id => 'postalCode',
      DirectoryDB::$country_field_id => 'country',
    );

    $meta_selects = array();
    $meta_joins = array();

    foreach ($meta_fields as $field_id => $field_key) {
      $meta_selects[] = $field_key . ".meta_value AS " . $field_key;
      $meta_joins[] = <<<SQL
        LEFT JOIN
          (SELECT item_id, field_id, meta_value
           FROM {$wpdb->prefix}frm_item_metas WHERE field_id={$field_id}
          ) AS {$field_key} ON {$field_key}.item_id=items.id
SQL;
    }

    $meta_selects = join(', ', $meta_selects);
    $meta_joins = join("\n", $meta_joins);

    $per_page = 100;
    $page = (int) $data['page'];
    $page = $page ? $page : 1;
    $start = ($page - 1) * $per_page;
    $limit = "LIMIT {$start}, {$per_page}";

    $query = <<<SQL
SELECT
  items.id, items.name, posts.post_title, posts.post_name AS slug, {$meta_selects}
FROM {$wpdb->prefix}frm_items as items
INNER JOIN
  (SELECT ID, post_type, post_status, post_title, post_name
   FROM {$wpdb->prefix}posts AS posts
   WHERE (`post_type`='directory' AND `post_status`='publish')
  ) AS posts ON posts.ID=items.post_id

{$meta_joins}

WHERE (items.is_draft=0 AND items.form_id=2 AND is_listing_public.meta_value <> "No")

ORDER BY items.id
{$limit}
SQL;

    $entries = stripslashes_deep($wpdb->get_results($query, ARRAY_A));

    $address_fields = array(
      'latitude', 'longitude', 'addressLineOne', 'addressLineTwo',
      'addressType', 'postalCode'
    );

    // Cleanup each entry
    foreach ($entries as $key => $entry) {
      $entries[$key]['id'] = (int) $entry['id'];
      // remove fields only used in query
      unset($entries[$key]['is_listing_public']);

      // clean name
      if (!$entry['name'] && $entry['post_title']) {
        $entries[$key]['name'] = $entry['post_title'];
      }
      unset($entries[$key]['post_title']);

      // clean state/province
      if (!$entry['state'] && $entry['province']) {
        $entries[$key]['state'] = $entry['province'];
      }
      unset($entries[$key]['province']);

      // null address if not public
      if ($entry['is_address_public'] === 'Private') {
        foreach ($address_fields as $address_field) {
          $entries[$key][$address_field] = NULL;
        }
      } else {
        $entries[$key]['addressType'] =
          ($entry['addressType'] === 'Community address')
          ? 'community'
          : 'mailing';
        // Hide default/(0,0) lat/long
        if (($entry['latitude'] == 0 && $entry['longitude'] == 0) ||
            ($entry['latitude'] == "39.095963" && $entry['longitude'] == "-96.606447")) {
          $entries[$key]['latitude'] = NULL;
          $entries[$key]['longitude'] = NULL;
        }
      }
      unset($entries[$key]['is_address_public']);

      // unserialize & clean community types
      $unserialized_types = unserialize($entry['types']);
      $raw_types =
        is_array($unserialized_types)
        ? $unserialized_types
        : array($entry['types']);
      $community_types = array();
      foreach ($raw_types as $community_type) {
        switch (strtolower($community_type)) {
        case "cohousing (individual homes within group owned property.)":
          $community_types[] =
            "Cohousing (individual homes within group property)";
          break;
        case "commune (organized around sharing almost everything.)":
          $community_types[] =
            "Commune (organized around sharing almost everything)";
          break;
        case "ecovillage (organized around ecology and sustainability.)":
          $community_types[] =
            "Ecovillage (organized around ecology and sustainability)";
          break;
        case "traditional or indigenous community":
          $community_types[] =
            "Traditional or Indigenous";
          break;
        case "other":
        case "ethical business~ investment group~ or alternative currency":
        case "land trust":
        case "neighborhood or community housing association":
        case "neighborhood, community housing, or homeowner\\'s association":
        case "organizations~ resources~ or networks":
        case "other":
        case "school~ educational institute or experience":
        case "unspecified, or other":
        case "volunteer~ internship~ apprenticeship~ or wwoof’ing":
          $community_types[] =
            "Unspecified or Other";
          break;
        case "shared housing (multiple individuals sharing a dwelling.)":
        case "shared housing or co-living (multiple individuals sharing a dwelling.)":
        case "shared housing, cohouseholding, or coliving (multiple individuals sharing a dwelling.)":
          $community_types[] =
            "Shared Housing, Cohouseholding, or Coliving (multiple individuals sharing a dwelling)";
          break;
        case "spiritual or religious community or organization":
        case "spiritual or religious community":
          $community_types[] =
            "Spiritual or Religious";
          break;
        case "student housing or student co-op":
          $community_types[] =
            "Student Housing or Student Co-Op";
          break;
        case "transition town (post-petroleum and off-grid communities.)":
        case "transition town or eco-neighborhood (focused on energy/resource resiliency)":
          $community_types[] =
            "Transition Town or Eco-Neighborhood (focused on energy/resource resiliency)";
          break;
        case "":
          break;
        default:
          error_log("DCC API ROUTE - No Decoder for Type\n\t{$community_type}");
        }
      }
      $entries[$key]['types'] = $community_types;
    }
    return $entries;
  }

  /* Ensure that the Community's data passes validation. */
  public static function validate_entry($data) {
    $community_id = $data['communityId'];
    $community_user = DirectoryDB::get_item_meta_value(
      DirectoryDB::$user_id_field_id, $community_id);
    if (!is_int($data['communityId']) || $community_user === false) {
      return new WP_Error(404, 'Community Not Found');
    }

    if (!(current_user_can('administrator') || ((int) $community_user) === get_current_user_id())) {
      return new WP_Error(403, 'You do not have permission to validate this Community.');
    }

    $is_valid = empty(self::run_entry_validation($community_id));
    if ($is_valid) {
      DirectoryDB::update_or_insert_item_meta(
        DirectoryDB::$verified_date_field_id, $community_id, date('Y-m-d'));
    }
    return array('isValid' => $is_valid);
  }

  /* Run the validation for a Community, returning any errors.
   *
   * Note that any errors for the Name, Latitude, or Longitude fields are
   * ignored.
   */
  public static function run_entry_validation($community_id) {
    $exempt_field_ids = array(
      DirectoryDB::$community_name_field_id, DirectoryDB::$latitude_field_id,
      DirectoryDB::$longitude_field_id,
    );

    $entry = FrmEntry::getOne($community_id);
    $data = array('form_id' => 2, 'item_key' => $entry->item_key, 'item_meta' => array());
    $metas = FrmEntryMeta::getAll(array('item_id' => $entry->id));
    foreach ($metas as $meta) {
      $data['item_meta'][$meta->field_id] = $meta->meta_value;
    }
    $errors = FrmEntryValidate::validate($data);

    foreach ($exempt_field_ids as $exempt_field) {
      $field_key = "field{$exempt_field}";
      if (array_key_exists($field_key, $errors)) {
        unset($errors[$field_key]);
      }
    }
    return $errors;
  }

}

add_action('rest_api_init', array('APIDirectory', 'register_routes'));

?>
