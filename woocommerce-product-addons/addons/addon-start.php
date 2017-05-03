<div class="row form-group mb-2 <?php if ( 1 == $required ) echo 'required-product-addon'; ?> product-addon-<?php echo sanitize_title( $name ); ?>">

  <?php do_action( 'wc_product_addon_start', $addon ); ?>

  <?php if ( $name ) : ?>
    <label class="col-8 col-fomr-label addon-name"><?php echo wptexturize( $name ); ?> <?php if ( 1 == $required ) echo '<small class="required text-danger" title="' . __( 'Required field', 'woocommerce-product-addons' ) . '">*</small>'; ?></label>
  <?php endif; ?>

  <?php if ( $description ) : ?>
    <?php echo '<div class="addon-description">' . wpautop( wptexturize( $description ) ) . '</div>'; ?>
  <?php endif; ?>

  <div class='col-16'>
    <?php do_action( 'wc_product_addon_options', $addon ); ?>
