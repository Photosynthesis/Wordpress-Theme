<?php
/** Custom Functions and Shortcodes for Users
 *
 * @category FIC
 * @package  Users
 * @author   Pavan Rikhi <pavan@ic.org>
 * @license  GPLv3 https://www.gnu.org/licenses/gpl-3.0.html
 * @link     http://www.ic.org
 */


/* Redirect non-administrators to the My Account WooCommerce Page after login.
 *
 * @param string $redirect_to URL to redirect to.
 * @param string $request URL the user is coming from.
 * @param object $user Logged-in user's data.
 *
 * @return string The URL to redirect to after Login.
 */
function my_account_login_redirect($redirect_to, $request, $user)
{
    global $user;
    if (isset($user->roles) && is_array($user->roles)) {
        if (!in_array('administrator', $user->roles)) {
            if (strpos('wp-admin', $redirect_to) !== false) {
                return get_permalink(get_option('woocommerce_myaccount_page_id'));
            }
        }
    }
    return $redirect_to;
}
add_filter('login_redirect', 'my_account_login_redirect', 10, 3);

/* Only show the content if the user is logged in, otherwise show a login form
 *
 * @param array $atts The Shortcode Attributes
 * @param string $content The Contents Inside the Shortcode Block
 *
 * @return string The $content or login form HTML
 */
function logged_in_only($atts, $content)
{
    if (is_user_logged_in()) {
        return do_shortcode($content);
    } else {
        return do_shortcode('[display_login_form]');
    }
}
add_shortcode('logged_in_only', 'logged_in_only');

/** Escape the body of User Password Reset Emails.
 *
 * Some plugin is defaulting all emails to HTML. This messes up the password
 * reset emails because Wordpress puts the reset URL in angle brackets, like
 * `<reset-url>`. Since the content type is HTML, email clients read this as an
 * invalid HTML tag and the link is not displayed. We avoid this by escaping
 * the password reset emails so that the angle brackets are escaped and the
 * link appears.
 */
add_filter('retrieve_password_message', 'esc_html', 99, 1);

?>
