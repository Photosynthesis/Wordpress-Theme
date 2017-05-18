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
    $args['rewrite']['slug'] = 'community-classifieds';
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

  /* Allow Using Hidden Products as Ad Plans */
  public static function allow_hidden_products($args) {
    $args["meta_query"][0]["value"] = array("hidden", "visible");
    return $args;
  }
}

add_action('adverts_template_load', array('ThemeWPAdverts', 'override_templates'));
add_action('init', array('ThemeWPAdverts', 'disable_css'), 100);
add_filter('admin_url', array('ThemeWPAdverts', 'fix_ajax_url'));
add_action('adverts_register_taxonomy', array('ThemeWPAdverts', 'customize_taxonomy'));
add_filter('adverts_form_load', array('ThemeWPAdverts', 'hide_price'));
add_filter('adext_wc_payments_products_new', array('ThemeWPAdverts', 'allow_hidden_products'));
add_filter('adext_wc_payments_products_renew', array('ThemeWPAdverts', 'allow_hidden_products'));

?>
