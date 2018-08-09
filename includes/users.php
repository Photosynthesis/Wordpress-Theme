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
}

add_action('wp_logout', array('ThemeUsers', 'logout_redirect'));

add_filter('login_redirect', array('ThemeUsers', 'login_redirect'), 10, 3);
add_shortcode('logged_in_only', array('ThemeUsers', 'logged_in_only'));
add_shortcode('display_login_form', array('ThemeUsers', 'login_form'));
add_filter('lostpassword_url', array('ThemeUsers', 'lost_password_url'), 10, 2);

?>
