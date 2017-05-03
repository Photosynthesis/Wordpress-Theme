<?php
/**
 * Single Product Price Input
 *
 * @author    Kathy Darling
 * @package   WC_Name_Your_Price/Templates
 * @version     2.1
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>

<div class="nyp my-2" <?php echo WC_Name_Your_Price_Helpers::get_data_attributes( $product_id, $prefix ); ?> >
  <div class='row form-group mb-0'>

    <?php do_action( 'woocommerce_nyp_before_price_input', $product_id ); ?>

    <label for="nyp" class='col-form-label col-8 mb-0'>
        <?php printf( _x( '%s (%s)', 'In case you need to change the order of Name Your Price ( $currency_symbol )', 'wc_name_your_price' ), stripslashes ( get_option( 'woocommerce_nyp_label_text', __( 'Name Your Price', 'wc_name_your_price' ) ) ), get_woocommerce_currency_symbol() ); ?>
    </label>


    <div class='col-10 mb-0'>
      <?php echo WC_Name_Your_Price_Helpers::get_price_input( $product_id, $prefix ); ?>
    </div>

    <?php do_action( 'woocommerce_nyp_after_price_input', $product_id ); ?>

  </div>
</div>
