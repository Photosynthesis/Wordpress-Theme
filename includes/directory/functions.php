<?php
/** General Directory Actions & Filters **/
class ThemeDirectory
{
  /* When Draft Listings are updated, send the Directory Manager a notification */
  public static function send_manager_notification($entry_id, $form_id) {
      $entry = FrmEntry::getOne($entry_id, true);
      $user_not_admin = !current_user_can('edit_plugins');
      if ($form_id == 2 && $entry->metas[920] != 'publish' && $user_not_admin) {
          $MANAGER_EMAIL = "directory@ic.org";
          $listing_name = get_the_title($entry->post_id);
          $edit_link = "https://www.ic.org/wp-admin/admin.php" .
              "?page=formidable-entries&frm_action=edit&id={$entry_id}";
          $subject = "Draft Listing `{$listing_name}` Has Been Updated";
          $message = "{$listing_name} has updated their Directory Listing:\n" .
              "<a href='{$edit_link}' target='_blank'>\n{$edit_link}\n</a>";
          wp_mail($MANAGER_EMAIL, $subject, $message);
      }
  }

  /* Reset the "Update Email Date" when an entry is updated */
  public static function reset_update_email_date($entry_id, $form_id) {
    if ($form_id == 2) {
      DirectoryDB::update_or_insert_item_meta(
        DirectoryDB::$update_email_date_field_id, $entry_id, '');
    }
  }

  /* Show Usernames instead of Display Names for the User ID field */
  public static function use_usernames($values, $field, $entry_id=false) {
      $user_field_id = 430;
      if ($field->id == $user_field_id) {
          $users = get_users(array(
              'orderby' => 'login', 'fields' => array('ID', 'user_login')));
          $values['options'] = array();
          foreach ($users as $user) {
              $values['options'][$user->ID] = $user->user_login;
          }
          $values['use_key'] = true;
      }
      return $values;
  }

  /* Allow Comments on Post if Allowed in Entry */
  public static function set_comment_status($errors, $posted_field, $posted_value) {
    if ($posted_field->id == 223 && $posted_value == 'No') {
      $_POST['frm_wp_post']['=comment_status'] = 'closed';
    }
    return $errors;
  }

  /* Make Search Checkboxes Join with ORs instead of ANDs */
  public static function or_checkboxes($where, $args){
    if ($args['display']->ID == 148525) {
      //set to the IDs of the field you are searching in your data form (NOT the search form)
      $field_ids = array(424,291,240,262,283,204,268,297,295,256,257,259,281,282,
                         250,251,241,242,243,294,301,236,237,238,239,697,815);
      if (in_array($args['where_opt'], $field_ids)) {
        $args['where_val'] = explode(', ', $args['where_val']);
        $where = '(';
        foreach($args['where_val'] as $v) {
          if ($where != '(') {
            $where .= ' OR ';
          }
          if ($args['where_is'] == 'LIKE') {
            $where .= "meta_value " . $args['where_is'] . " '%". $v ."%'";
          } else {
            $where .= "meta_value " . $args['where_is'] . " '" . $v . "'";
          }
        }
        $where .= ") and fi.id='" . $args['where_opt'] . "'";
      }
    }
    return $where;
  }

}

add_action('frm_after_update_entry', array('ThemeDirectory', 'send_manager_notification'), 10, 2);
add_action('frm_after_update_entry', array('ThemeDirectory', 'reset_update_email_date'), 10, 2);
add_filter('frm_setup_new_fields_vars', array('ThemeDirectory', 'use_usernames'), 20, 2);
add_filter('frm_setup_edit_fields_vars', array('ThemeDirectory', 'use_usernames'), 20, 3);
add_filter('frm_validate_field_entry', array('ThemeDirectory', 'set_comment_status'), 8, 3);
add_filter('frm_where_filter', array('ThemeDirectory', 'or_checkboxes'), 10, 2);

?>
