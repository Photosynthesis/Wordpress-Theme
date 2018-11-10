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
   *    - visitorProcess
   *    - membershipProcess
   *    - membershipComments
   *    - leadershipGroup
   *    - governmentComments
   *    - networkAffiliations
   *    - otherAffiliations
   *    - keywords
   *
   *
   *    TODO:
   *    * imageUrl
   *    * thumbnailUrl
   *
   *    * rest of fields... maybe farm each info-block to separate cleanup function.
   *
   *    * isOwner
   *    * isAdmin
   *
   *    * Special message if hidden by user
   *    * Create nonce in shortcode for checking user status, send to Elm, add
   *      to AJAX req header:
   *      https://developer.wordpress.org/rest-api/using-the-rest-api/authentication/
   *    * Validate listing route - just check that required fields are valid?
   *      Validate all fields?
   *
   *
   */
  public static function entry($data) {
    global $wpdb;

    // Field ID -> JSON Key
    $public_fields = array(
      // Fields with more complex processing
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
      DirectoryDB::$visitor_process_field_id => 'visitorProcess',
      DirectoryDB::$membership_process_field_id => 'membershipProcess',
      DirectoryDB::$membership_comments_field_id => 'membershipComments',
      DirectoryDB::$decision_making_field_id => 'decisionMaking',
      DirectoryDB::$leader_field_id => 'leader',
      DirectoryDB::$leadership_group_field_id => 'leadershipGroup',
      DirectoryDB::$government_comments_field_id => 'governmentComments',
      DirectoryDB::$network_affiliations_field_id => 'networkAffiliations',
      DirectoryDB::$other_affiliations_field_id => 'otherAffiliations',
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
    // TODO: If Owner, add additional fields?
    return self::clean_detail_entry($data);
  }

  /* Transform the raw database values into our API Spec */
  public static function clean_detail_entry(&$entry) {
    $entry['id'] = self::clean_id($entry['id']);
    $entry['updated_at'] = self::clean_date($entry['updated_at']);
    $entry['created_at'] = self::clean_date($entry['created_at']);

    if ($entry['contact_address_public'] === 'Public') {
      $entry_type = $entry['contact_address_type'] === 'Community address'
        ? 'community' : 'mailing';
      $entry['contactAddress'] = array(
        'lineOne' => $entry['address_one'],
        'lineTwo' => $entry['line_two'],
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

    self::unserialize_and_convert_case($entry);

    $empty_fields = array('missionStatement', 'description') ;
    foreach ($empty_fields as $field) {
      if (!isset($entry[$field])) {
        $entry[$field] = "";
      }
    }

    $empty_array_fields = array(
      'communityTypes', 'currentResidenceTypes', 'plannedResidenceTypes',
      'housingAccess',
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
    );
    foreach ($optional_int_fields as $field) {
      if (isset($entry[$field])) {
        $entry[$field] = (int) $entry[$field];
      }
    }

    $float_fields = array('landSizeAmount');
    foreach ($float_fields as $field) {
      if ($entry[$field]) {
        $entry[$field] = (float) $entry[$field];
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

    $tilde_array_fields = array('programs', 'currentResidenceTypes', 'plannedResidenceTypes');
    foreach ($tilde_array_fields as $field) {
      foreach ($entry[$field] as $i => $field_value) {
        $entry[$field][$i] = self::clean_escapes($field_value);
      }
    }

    $tilde_fields = array('decisionMaking', 'leader');
    foreach ($tilde_fields as $field) {
      if (isset($entry[$field])) {
        $entry[$field] = self::clean_escapes($entry[$field]);
      }
    }

    if (is_string($entry['networkAffiliations'])) {
      $entry['networkAffiliations'] = array($entry['networkAffiliations']);
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
   *    - imageUrl
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

    // Add Search Filter
    // TODO: Will need to change this when we write our own detail view
    if ($data['search']) {
      $post_content_select = ", post_content";
      if (is_array($data['search'])) { $data['search'] = join(" ", $data['search']); }
      $search_values = explode(" ", $data['search']);
      $selects[] = "posts.post_content";
      $where_clauses = array();
      foreach ($search_values as $search_value) {
        $escaped = esc_sql($search_value);
        $where_clauses[] = "posts.post_content LIKE '%{$escaped}%'";
        $where_clauses[] = "posts.post_title LIKE '%{$escaped}%'";
      }
      $where_clauses = "(" . join(" OR ", $where_clauses) . ")";
      $wheres .= " AND {$where_clauses}";
    } else {
      $post_content_select = "";
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
  post_images.ID AS imageID, post_images.guid AS imageUrl,
  image_post_id_metas.meta_value AS image_post_id {$selects}
FROM {$wpdb->prefix}frm_items AS items
INNER JOIN
  (SELECT ID, post_type, post_status, post_title, post_name {$post_content_select}
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
LEFT JOIN
  (SELECT ID, guid
   FROM {$wpdb->prefix}posts
   WHERE `post_type`='attachment'
  ) AS post_images ON post_images.ID={$meta_fields[DirectoryDB::$primary_image_field_id]}_metas.meta_value

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

    $entries = array_map(array(self, 'clean_list_entry'), $entries);

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
    unset($entry['image_post_id']);
    unset($entry['post_content']);

    if ($entry['post_title']) {
      $entry['name'] = $entry['post_title'];
    }
    $entry['name'] = html_entity_decode($entry['name']);
    unset($entry['post_title']);

    $entry['state'] = self::clean_state($entry['state'], $entry['province']);
    unset($entry['province']);

    if ($entry['imageID']) {
      $entry['thumbnailUrl'] = wp_get_attachment_thumb_url($entry['imageID']);
      if (!$entry['thumbnailUrl']) {
        $entry['thumbnailUrl'] = null;
      }
    } else {
      $entry['thumbnailUrl'] = null;
    }
    unset($entry['imageID']);

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

}

add_action('rest_api_init', array('APIDirectory', 'register_routes'));

?>
