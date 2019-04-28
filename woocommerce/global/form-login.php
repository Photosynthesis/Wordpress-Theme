<?php
/**
 * Login form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/global/form-login.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see       https://docs.woocommerce.com/document/template-structure/
 * @author    WooThemes
 * @package   WooCommerce/Templates
 * @version   3.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly.
}

if ( is_user_logged_in() ) {
  return;
}

?>
<form class="woocomerce-form woocommerce-form-login login" method="post" <?php if ( $hidden ) echo 'style="display:none;"'; ?>>
  <div class='card mb-3'><div class='card-body'>

  <?php do_action( 'woocommerce_login_form_start' ); ?>

  <?php if ( $message ) echo wpautop( wptexturize( $message ) ); ?>

  <div class='row'>
    <div class="form-group col-12">
      <label for="username"><?php esc_html_e( 'Username or email', 'woocommerce' ); ?> <span class="text-danger required">*</span></label>
      <input type="text" class="form-control input-text" name="username" id="username" />
    </div>
    <div class="form-group col-12">
      <label for="password"><?php esc_html_e( 'Password', 'woocommerce' ); ?> <span class="text-danger required">*</span></label>
      <input class="form-control input-text" type="password" name="password" id="password" />
    </div>
  </div>

  <?php do_action( 'woocommerce_login_form' ); ?>

  <?php wp_nonce_field( 'woocommerce-login', 'woocommerce-login-nonce' ); ?>
  <div class='form-group'>
    <button type="submit" class="mr-2 btn btn-primary button" name="login" value="<?php esc_attr_e( 'Login', 'woocommerce' ); ?>"><?php esc_html_e('Login', 'woocommerce'); ?></button>
    <input type="hidden" name="redirect" value="<?php echo esc_url( $redirect ) ?>" />
    <div class='form-check d-inline'>
      <label class="form-check-label woocommerce-form__label woocommerce-form__label-for-checkbox inline">
        <input class="form-check-input woocommerce-form__input woocommerce-form__input-checkbox" name="rememberme" type="checkbox" id="rememberme" value="forever" /> <span><?php esc_html_e( 'Remember me', 'woocommerce' ); ?></span>
      </label>
    </div>
  </div>
  <div class="lost_password">
    <a href="<?php echo esc_url( wp_lostpassword_url() ); ?>"><?php esc_html_e( 'Forgot your password?', 'woocommerce' ); ?></a>
  </div>

  <?php do_action( 'woocommerce_login_form_end' ); ?>

  </div></div>
</form>
