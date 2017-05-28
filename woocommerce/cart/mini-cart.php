<?php
/**
 * Mini-cart
 *
 * Contains the markup for the mini-cart, used by the cart widget.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/cart/mini-cart.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @author  WooThemes
 * @package WooCommerce/Templates
 * @version 2.5.0
 */

if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly
}

?>

<?php do_action( 'woocommerce_before_mini_cart' ); ?>

<table class="cart_list product_list_widget table table-sm <?php echo esc_attr( $args['list_class'] ); ?>">

  <?php if ( ! WC()->cart->is_empty() ) : ?>

    <?php do_action( 'woocommerce_before_mini_cart_contents' ); ?>

    <?php
      foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
        $_product     = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
        $product_id   = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );

        if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_widget_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
          $product_name      = apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key );
          $thumbnail         = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image(array(50,50), array('class' => 'mw-100 w-100 h-auto')), $cart_item, $cart_item_key );
          $product_price     = apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key );
          $product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
          ?>
          <tr class="<?php echo esc_attr( apply_filters( 'woocommerce_mini_cart_item_class', 'mini_cart_item', $cart_item, $cart_item_key ) ); ?>">
            <td class="align-middle text-center mini-cart-image-cell">
              <?php if ( ! $_product->is_visible() ) : ?>
                <?php echo str_replace( array( 'http:', 'https:' ), '', $thumbnail ); ?>
              <?php else : ?>
                <a href="<?php echo esc_url( $product_permalink ); ?>">
                  <?php echo str_replace( array( 'http:', 'https:' ), '', $thumbnail ); ?>
                </a>
              <?php endif; ?>
            </td>
            <td>
              <?php if ( ! $_product->is_visible() ) : ?>
                <?php echo $product_name; ?>
              <?php else : ?>
                <a href="<?php echo esc_url( $product_permalink ); ?>">
                  <?php echo $product_name; ?>
                </a>
              <?php endif; ?><br />
              <?php echo WC()->cart->get_item_data( $cart_item ); ?>
              <?php echo apply_filters( 'woocommerce_widget_cart_item_quantity', '<span class="quantity">' . sprintf( '<small class="text-muted">%s &times;</small> <span class="price">%s</span>', $cart_item['quantity'], $product_price ) . '</span>', $cart_item, $cart_item_key ); ?>
            </td>

            <td><strong>
              <?php
              echo apply_filters( 'woocommerce_cart_item_remove_link', sprintf(
                '<a href="%s" class="text-danger remove" aria-label="%s" data-product_id="%s" data-product_sku="%s">&times;</a>',
                esc_url( WC()->cart->get_remove_url( $cart_item_key ) ),
                __( 'Remove this item', 'woocommerce' ),
                esc_attr( $product_id ),
                esc_attr( $_product->get_sku() )
              ), $cart_item_key );
              ?>
            </strong></td>
          </tr>
          <?php
        }
      }
    ?>

    <?php do_action( 'woocommerce_mini_cart_contents' ); ?>

  <?php else : ?>

    <tr><td class='text-center small text-muted font-italic'><?php _e( 'Your Cart is Currently Empty', 'woocommerce' ); ?></td></tr>

  <?php endif; ?>

</table><!-- end product list -->

<?php if ( ! WC()->cart->is_empty() ) : ?>

  <p class="total text-center"><strong><?php _e( 'Subtotal', 'woocommerce' ); ?>:</strong> <?php echo WC()->cart->get_cart_subtotal(); ?></p>

  <?php do_action( 'woocommerce_widget_shopping_cart_before_buttons' ); ?>

  <p class="clearfix">
    <a href="<?php echo esc_url(wc_get_cart_url()); ?>" class='btn btn-secondary float-left'>View Cart</a>
    <a href="<?php echo esc_url(wc_get_checkout_url()); ?>" class='btn btn-primary float-right'>Checkout</a>
  </p>

<?php endif; ?>

<?php do_action( 'woocommerce_after_mini_cart' ); ?>
