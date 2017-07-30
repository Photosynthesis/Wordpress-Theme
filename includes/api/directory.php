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
   * No filtering, ordering, or pagination is currently supported.
   *
   * The entries are nested under the `listings` key.
   *
   * Each entry has the following fields:
   *
   *    - id
   *    - name
   *    - createdAt
   *    - updatedAt
   *    - openToMembership
   *    - openToVisitors
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
    );

    // Build the Selects
    $selects = array(
      'items.id', 'items.name', 'items.created_at', 'items.updated_at',
      'posts.post_title', 'posts.post_name AS slug',
      'post_images.ID AS imageID', 'post_images.guid AS imageUrl'
    );
    foreach ($meta_fields as $meta_field) {
      $selects[] = "{$meta_field}_metas.meta_value as {$meta_field}";
    }
    $selects = join(",\n  ", $selects);


    // Build the Joins
    $joins = array();
    foreach ($meta_fields as $meta_id => $meta_field) {
      $joins[] = <<<SQL
LEFT JOIN
  (SELECT item_id, field_id, meta_value
   FROM {$wpdb->prefix}frm_item_metas WHERE field_id={$meta_id})
  AS {$meta_field}_metas ON {$meta_field}_metas.item_id=items.id
SQL;
    }

    $joins[] = <<<SQL
INNER JOIN
  (SELECT ID, post_type, post_status, post_title, post_name
   FROM {$wpdb->prefix}posts AS posts
   WHERE (`post_type`='directory' AND `post_status`='publish')
  ) AS posts ON posts.ID=items.post_id
LEFT JOIN
  (SELECT ID, guid
   FROM {$wpdb->prefix}posts
   WHERE `post_type`='attachment'
  ) AS post_images ON post_images.ID={$meta_fields[DirectoryDB::$primary_image_field_id]}_metas.meta_value
SQL;

    $joins = join("\n", $joins);

    // Build the Limit
    $per_page = 15;
    $page = (int) $data['page'];
    $page = $page ? $page : 1;
    $page--;
    $start = $page * $per_page;
    $limit = "LIMIT {$start}, {$per_page}";


    $query = <<<SQL
SELECT
  {$selects}
FROM {$wpdb->prefix}frm_items AS items
{$joins}

WHERE (items.is_draft=0 AND items.form_id=2)
ORDER BY posts.post_title
{$limit}
SQL;

    $entries = $wpdb->get_results($query, ARRAY_A);
    $entries = array_map(array(self, 'clean_list_entry'), $entries);

    if (empty($entries)) {
      return str_replace("\n", ' ', $query);
    }

    // Get Total Listing Count
    $total_count_query = <<<SQL
SELECT COUNT(posts.ID) AS count
FROM {$wpdb->prefix}posts AS posts
INNER JOIN
  (SELECT id, form_id, is_draft, post_id
   FROM {$wpdb->prefix}frm_items
   WHERE form_id=2 AND is_draft=0
  ) AS items ON items.post_id=posts.ID
WHERE post_type='directory' AND post_status='publish'
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
    unset($entry['image_post_id']);

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
