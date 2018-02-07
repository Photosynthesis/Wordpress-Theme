<?php
/** Customize Newsletter Plugins **/
class ThemeNewsletter
{
  /* Add Mailpoet Subscribers on Hustle Opt-In Submissions */
  public static function hustle_opt_in_submit() {
    global $hustle;

    $raw_data = array();
    parse_str($_POST['data']['form'], $raw_data);
    if (is_email($raw_data['inc_optin_email'])) {
      $form_data = array();
      foreach ($raw_data as $key => $value) {
      if (preg_match('%inc_optin_%', $key)) {
          $key = str_replace('inc_optin_', '', $key);
      }
      $form_data[$key] = $value;
      }

      $user_data = array('email' => $form_data['email']);
      if (isset($form_data['first_name'])) {
      $user_data['first_name'] = $form_data['first_name'];
      }
      if (isset($form_data['last_name'])) {
      $user_data['last_name'] = $form_data['last_name'];
      }

      try {
        \MailPoet\API\API::MP('v1')->addSubscriber($user_data, array('3'));
      } catch (Exception $e) {}
    }

    $hustle_ajax = new Opt_In_Front_Ajax($hustle);
    $hustle_ajax->submit_optin();
  }
}

add_action('wp_ajax_inc_opt_submit_opt_in', array('ThemeNewsletter', 'hustle_opt_in_submit'), 1);
add_action('wp_ajax_nopriv_inc_opt_submit_opt_in', array('ThemeNewsletter', 'hustle_opt_in_submit'), 1);


?>
