<?php
class ThemeUsers
{
  /* Redirect non-administrators to the My Account WooCommerce Page after login.
   *
   * @param string $redirect_to URL to redirect to.
   * @param string $request URL the user is coming from.
   * @param object $user Logged-in user's data.
   *
   * @return string The URL to redirect to after Login.
   */
  public static function login_redirect($redirect_to, $request, $user) {
    if (isset($user->roles) && is_array($user->roles)) {
      if (!in_array('administrator', $user->roles)) {
        if (strpos($redirect_to, 'wp-admin') !== false) {
          return get_permalink(get_option('woocommerce_myaccount_page_id'));
        }
      }
    }
    return $redirect_to;
  }

  /* Redirect Users to the Homepage When They Log out. */
  public static function logout_redirect() {
    wp_redirect(home_url());
    exit;
  }

  /* Only show the content if the user is logged in, otherwise show a login form
   *
   * @param array $atts The Shortcode Attributes
   * @param string $content The Contents Inside the Shortcode Block
   *
   * @return string The $content or login form HTML
   */
  public static function logged_in_only($atts, $content) {
    if (is_user_logged_in()) {
      return do_shortcode($content);
    } else {
      return self::login_form();
    }
  }

  /* Display a Login Form */
  public static function login_form() {
    global $post;
    if ($post->post_name === "new-listing") {
      $pre_text = "<p class='text-danger font-weight-bold'>You must register and log in before you can create a directory listing.</p>\n" .
        "<p><b>Listing in our Directory is free.</b> Membership is not required - " .
        "though we hope you'll support the work of our non-profit organization " .
        "by <a href='/membership/' target='_blank'>joining</a>.</p>";
    } else {
      $pre_text = "<p class='text-danger'>You must be logged in to view this page.</p>";
    }
    return $pre_text . wp_login_form( array( 'echo' => false ) ) . "\n" .
      wp_register('', '', false) . " | <a href=\"" . wp_lostpassword_url( get_permalink() ) . "\" title=\"Lost your password?\">Lost your password?</a>";
  }

  /* Use the Wordpress Lost Password Page instead of WooCommerce's */
  public static function lost_password_url($lostpassword_url, $redirect) {
    return home_url() . '/wp-login.php?action=lostpassword' . $redirect;
  }

  const adb_meta_key = 'fic_customer_adb';
  const adb_meta_box_id = 'fic_adb_box';
  /* Show an ADB field when editing a User */
  public static function show_adb_input($user) {
    $adb_number = get_user_meta($user->ID, self::adb_meta_key, true);
    $val = esc_attr($adb_number);
    $output = <<<HTML
<h3>ADB Number</h3>
<table class='form-table'>
  <tr>
    <th><label for="adb_number">ADB Number</label></th>
    <td>
      <input
        type="number"
        min="0"
        max="99999"
        step="1"
        id="adb_number"
        name="adb_number"
        value="{$val}"
        class='regular-text'
      />
    </td>
  </tr>
</table>
HTML;
    echo $output;
  }

  /* Update the ADB field for a User */
  public static function update_user_adb($user_id) {
    if (!current_user_can('edit_user', $user_id)) {
      return false;
    }
    if (!empty($_POST['adb_number'])) {
      update_user_meta($user_id, self::adb_meta_key, intval($_POST['adb_number']));
    }
  }

  /* Add the ADB Number meta box to the Order & Subscription pages */
  public static function add_adb_meta_box() {
    add_meta_box(
      self::adb_meta_box_id,
      'ADB Number',
      array('ThemeUsers', 'render_adb_meta_box'),
      array('shop_order', 'shop_subscription'),
      'side',
      'core'
    );
  }

  /* Render the ADB Number meta box */
  public static function render_adb_meta_box() {
    global $post;
    $user_id = wc_get_order($post->ID)->get_user_id();
    $adb_number = get_user_meta($user_id, self::adb_meta_key, true);
    $nonce = wp_create_nonce('adb-meta-box');
    $output = <<<HTML
<input type='hidden' name='adb_nonce' value='{$nonce}' />
<input
  type="number"
  min="0"
  max="99999"
  step="1"
  id="adb_number"
  name="adb_number"
  value="{$adb_number}"
/>
HTML;
    echo $output;
  }

  /* Save the ADB Number on updates */
  public static function update_adb_meta_box($post_id) {
    if (!isset($_POST['adb_nonce'])) {
      return $post_id;
    }
    $nonce = $_REQUEST['adb_nonce'];
    if (!wp_verify_nonce($nonce, 'adb-meta-box')) {
      return $post_id;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
      return $post_id;
    }
    if (!current_user_can('edit_post', $post_id)) {
      return $post_id;
    }
    $post_type = get_post_type($post_id);
    if ($post_type === FALSE) {
      return $post_id;
    }
    if ($post_type === 'shop_order') {
      $user_id = wc_get_order($post_id)->get_user_id();
    } else if ($post_type === 'shop_subscription') {
      $user_id = wcs_get_subscription($post_id)->get_user_id();
    } else {
      return $post_id;
    }
    update_user_meta($user_id, self::adb_meta_key, intval($_POST['adb_number']));
  }

  public static function get_adb_number_for_user() {
    // this is supposed to be misspelled
    check_ajax_referer('theme-admin-ajax', 'security');

    $user_id = intval($_GET['user_id']);
    $adb_number = get_user_meta($user_id, self::adb_meta_key, true);
    echo json_encode(array('adb' => $adb_number));
    exit;
  }
}

add_action('wp_logout', array('ThemeUsers', 'logout_redirect'));

add_filter('login_redirect', array('ThemeUsers', 'login_redirect'), 10, 3);
add_shortcode('logged_in_only', array('ThemeUsers', 'logged_in_only'));
add_shortcode('display_login_form', array('ThemeUsers', 'login_form'));
add_filter('lostpassword_url', array('ThemeUsers', 'lost_password_url'), 10, 2);

add_action('edit_user_profile', array('ThemeUsers', 'show_adb_input'));
add_action('edit_user_profile_update', array('ThemeUsers', 'update_user_adb'));
add_action('add_meta_boxes', array('ThemeUsers', 'add_adb_meta_box'));
add_action('save_post', array('ThemeUsers', 'update_adb_meta_box'));
add_action('wp_ajax_get_user_adb_number', array('ThemeUsers', 'get_adb_number_for_user'));

?>
