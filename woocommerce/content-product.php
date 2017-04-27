<?php
/**
 * The template for displaying product content within loops
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/content-product.php.
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
 * @version 3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly
}

global $product;

// Ensure visibility
if ( empty( $product ) || ! $product->is_visible() ) {
  return;
}
?>
<li <?php post_class('text-center col-12 col-sm-8 col-xl-6 mb-4'); ?>>
  <a href='<?php the_permalink(); ?>'>
    <div class='card h-100'>
      <img src='<?php the_post_thumbnail_url(); ?>' class='mw-100 mx-auto card-img-top' />
      <h4 class='mt-2 px-1'><?php the_title(); ?></h4>
      <div class='price-and-cart'>
        <strong><?php woocommerce_template_loop_price(); ?></strong>
        <?php woocommerce_template_loop_add_to_cart(); ?>
      </div>
    </div>
  </a>
</li>
