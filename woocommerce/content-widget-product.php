<?php
/**
 * The template for displaying product widget entries.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/content-widget-product.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see   https://docs.woocommerce.com/document/template-structure/
 * @author  WooThemes
 * @package WooCommerce/Templates
 * @version 3.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

global $product; ?>

<a class='list-group-item list-group-item-action text-center' href="<?php echo esc_url( $product->get_permalink() ); ?>">
  <?php do_action('woocommerce_widget_product_item_start', $args); ?>
  <?php echo $product->get_image('post-thumbnail', array('class' => 'mw-100 h-auto mx-auto')); ?>
  <h5 class="product-title mt-2 w-100"><?php echo $product->get_name(); ?></h5>
  <?php if ( ! empty( $show_rating ) ) : ?>
    <?php echo wc_get_rating_html( $product->get_average_rating() ); ?>
  <?php endif; ?>
  <span class='font-weight-bold price w-100'><?php echo $product->get_price_html(); ?></span>
  <?php do_action('woocommerce_widget_product_item_end', $args); ?>
</a>
