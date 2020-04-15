<?php
/** Customize the WPAdverts Plugin **/
class ThemeWPAdverts
{
  /* Allow Overriding Shortcode Templates */
  public static function override_templates($template) {
    $dirs = array(
      get_template_directory() . "/wpadverts/",
      ADVERTS_PATH . "/templates/"
    );

    $basename = basename($template);

    foreach ($dirs as $dir) {
      if (file_exists($dir . $basename)) {
        return $dir . $basename;
      }
    }
  }

  /* Disable Payments CSS */
  public static function disable_css() {
    wp_deregister_style('adverts-wc-payments-frontend');
  }

  /* Fix Protocol of AJAX URLs */
  public static function fix_ajax_url($url) {
    if (!is_admin() && !is_ssl()) {
      $url = str_replace('https:', 'http:', $url);
    }
    return $url;
  }

  /* Change Category Slugs to `community-classifieds` */
  public static function customize_taxonomy($args) {
    if (!isset($args['rewrite'])) {
      $args['rewrite'] = array();
    }
    $args['rewrite']['slug'] = 'classifieds-category';
    return $args;
  }

  /* Hide Price Field */
  public static function hide_price($form) {
    if ($form['name'] == 'advert') {
      foreach ($form['field'] as $key => $field) {
        if ($field['name'] == 'adverts_price') {
          unset($form['field'][$key]);
        }
      }
    }
    return $form;
  }

  /* Allow Using Hidden Products as Ad Plans
  Appears to be obsolete and causing some product not to show up.
  Keeping as backup pending further testing (2020-04-14)
  public static function allow_hidden_products($args) {
    $args["meta_query"] = array(array(
      'key' => '_visibility',
      'value' => array("hidden", "visible"),
      'compare' => 'IN',
    ));
    return $args;
  }
  */

  /* Customize the email sent when an Ad is responded to. */
  public static function customize_contact_email($mail, $post_id, $form) {
    $post = get_post($post_id);
    $mail["subject"] = $post->post_title . " - " . $mail["subject"];

    $sender = $form->get_value("message_name");
    $sender_email = $form->get_value("message_email");

    $from_text = "";
    if ($sender !== null) {
      $from_text .= " from {$sender}";
    }
    if ($sender_email !== null) {
      $from_text .= " <{$sender_email}>";
    }

    $mail['message'] =
      "You have received a reply to your ic.org Classifieds listing{$from_text}.\n" .
      "---\n\n" .
      $mail['message'] . "\n\n" .
      "---\n" .
      "This is an automated message from the ic.org website - we do not screen any messages before relaying them to you. Please contact ads@ic.org for questions or to report abuse."
      ;

    return $mail;
  }
}

add_action('adverts_template_load', array('ThemeWPAdverts', 'override_templates'));
add_action('init', array('ThemeWPAdverts', 'disable_css'), 100);
add_filter('admin_url', array('ThemeWPAdverts', 'fix_ajax_url'));
add_action('adverts_register_taxonomy', array('ThemeWPAdverts', 'customize_taxonomy'));
add_filter('adverts_form_load', array('ThemeWPAdverts', 'hide_price'));
/*
These appear to be obsolete, but retaining for now until we've done further testing
(2020-04-14)
add_filter('adext_wc_payments_products_new', array('ThemeWPAdverts', 'allow_hidden_products'));
add_filter('adext_wc_payments_products_renew', array('ThemeWPAdverts', 'allow_hidden_products'));
*/
add_filter('adverts_contact_form_email', array('ThemeWPAdverts', 'customize_contact_email'), 10, 3);

?>
