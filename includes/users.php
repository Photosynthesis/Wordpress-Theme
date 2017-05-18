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
    global $user;
    if (isset($user->roles) && is_array($user->roles)) {
      if (!in_array('administrator', $user->roles)) {
        if (strpos($redirect_to, 'wp-admin') !== false) {
          return get_permalink(get_option('woocommerce_myaccount_page_id'));
        }
      }
    }
    return $redirect_to;
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
      return this::login_form();
    }
  }

  /* Display a Login Form */
  public static function login_form() {
    $pre_text = "<h1>Please Log In</h1><p class='text-danger'>You must be logged in to view this page.</p>";
    return $pre_text . wp_login_form( array( 'echo' => false ) ) . "\n" .
      wp_register('', '', false) . " | <a href=\"" . wp_lostpassword_url( get_permalink() ) . "\" title=\"Lost your password?\">Lost your password?</a>";
  }

  /* Use the Wordpress Lost Password Page instead of WooCommerce's */
  public static function lost_password_url($lostpassword_url, $redirect) {
    return home_url() . '/wp-login.php?action=lostpassword' . $redirect;
  }
}

add_filter('login_redirect', array('ThemeUsers', 'login_redirect'), 10, 3);
add_shortcode('logged_in_only', array('ThemeUsers', 'login_redirect'));
add_shortcode('display_login_form', array('ThemeUsers', 'login_form'));
add_filter('lostpassword_url', array('ThemeUsers', 'lost_password_url'));

?>
