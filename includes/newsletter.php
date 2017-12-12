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
      $user_data['firstname'] = $form_data['first_name'];
      }
      if (isset($form_data['last_name'])) {
      $user_data['lastname'] = $form_data['last_name'];
      }

      $mailpoet_data = array(
      'user' => $user_data,
      'user_list' => array('list_ids' => array('1')),
      );
      $mp_helper = WYSIJA::get('user', 'helper');
      $mp_helper->addSubscriber($mailpoet_data);
    }

    $hustle_ajax = new Opt_In_Front_Ajax($hustle);
    $hustle_ajax->submit_optin();
  }
}

add_action('wp_ajax_inc_opt_submit_opt_in', array('ThemeNewsletter', 'hustle_opt_in_submit'), 1);
add_action('wp_ajax_nopriv_inc_opt_submit_opt_in', array('ThemeNewsletter', 'hustle_opt_in_submit'), 1);


?>
