<?php
/** A REST API for Updating the Board & Staff Profiles **/
class APIBoardStaff
{
  const api_namespace = 'v1/board-staff';

  public static function register_routes() {
    register_rest_route(self::api_namespace, '/get/', array(
      'methods' => 'GET',
      'callback' => array('APIBoardStaff', 'get_data'),
      'permission_callback' => function() {
        return current_user_can('manage_options');
      },
    ));

    register_rest_route(self::api_namespace, '/set/', array(
      'methods' => 'POST',
      'callback' => array('APIBoardStaff', 'set_data'),
      'permission_callback' => function() {
        return current_user_can('manage_options');
      },
    ));
  }

  public static function get_data($data) {
    return ThemeGeneral::get_board_and_staff_data();
  }

  public static function set_data($data) {
    $profile_data = array('board' => $data['board'], 'staff' => $data['staff']);
    if (!isset($profile_data['board'])) {
      return array('errors' => 'Missing board data');
    }
    if (!isset($profile_data['staff'])) {
      return array('errors' => 'Missing staff data');
    }
    foreach ($profile_data['staff'] as $i => $profile) {
      if (!is_string($profile['name']) || !is_string($profile['image']) || !is_string($profile['bio'])) {
        return array('errors' => 'Invalid Staff Profile: #' . $i);
      }
    }
    foreach ($profile_data['board'] as $i => $profile) {
      if (!is_string($profile['name']) || !is_string($profile['image']) || !is_string($profile['bio'])) {
        return array('errors' => 'Invalid Board Profile: #' . $i);
      }
    }

    ThemeGeneral::set_board_and_staff_data($profile_data);

    return array('status' => 'success');
  }
}

add_action('rest_api_init', array('APIBoardStaff', 'register_routes'));

?>
