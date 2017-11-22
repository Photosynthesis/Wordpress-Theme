<?php
/** A REST API Exposing Directory Listing Resources **/
class APIDirectory
{
  const api_namespace = 'v1/directory';

  /* Register the Directory Endpoints */
  public static function register_routes() {
    register_rest_route(self::api_namespace, '/entries/', array(
      'methods' => 'GET',
      'callback' => array('APIDirectory', 'entries'),
    ));
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
          if ($filter['compare'] == '=')  {
            $comparison = "='{$value}'";
          } else if ($filter['compare'] == 'LIKE%') {
            $comparison = " LIKE '{$value}%'";
          } else if ($filter['compare'] == '%LIKE%') {
            $comparison = " LIKE '%{$value}%'";
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
        $where_clauses[] = "posts.post_content LIKE '%{$search_value}%'";
        $where_clauses[] = "posts.post_title LIKE '%{$search_value}%'";
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
    $entry['id'] = (int) $entry['id'];
    $entry['updated_at'] = date('c', strtotime($entry['updated_at']));
    $entry['created_at'] = date('c', strtotime($entry['created_at']));
    unset($entry['image_post_id']);
    unset($entry['post_content']);

    if ($entry['post_title']) {
      $entry['name'] = $entry['post_title'];
    }
    unset($entry['post_title']);

    if (!$entry['state'] && $entry['province']) {
      $entry['state'] = $entry['province'];
    }
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

}

add_action('rest_api_init', array('APIDirectory', 'register_routes'));

?>
