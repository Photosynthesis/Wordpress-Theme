<?php
/**
 * The Template for displaying start of field.
 *
 * @version 3.0.0
 */

$price_display    = '';
$title_format     = ! empty( $addon['title_format'] ) ? $addon['title_format'] : '';
$addon_type       = ! empty( $addon['type'] ) ? $addon['type'] : '';
$addon_price      = ! empty( $addon['price'] ) ? $addon['price'] : '';
$addon_price_type = ! empty( $addon['price_type'] ) ? $addon['price_type'] : '';
$adjust_price     = ! empty( $addon['adjust_price'] ) ? $addon['adjust_price'] : '';
$required         = ! empty( $addon['required'] ) ? $addon['required'] : '';

if ( 'checkbox' !== $addon_type && 'multiple_choice' !== $addon_type && 'custom_price' !== $addon_type ) {
	$price_prefix = 0 < $addon_price ? '+' : '';
	$price_type   = $addon_price_type;
	$adjust_price = $adjust_price;
	$price_raw    = apply_filters( 'woocommerce_product_addons_option_price', $addon_price, $addon );
	$required     = '1' == $required;

	if ( 'percentage_based' === $price_type ) {
		$price_display = apply_filters( 'woocommerce_product_addons_option_price_html',
			'1' == $adjust_price && $price_raw ? '(' . $price_prefix . $price_raw . '%)' : '',
			$addon,
			0,
			$addon_type
		);
	} else {
		$price_display = apply_filters( 'woocommerce_product_addons_option_price_html',
			'1' == $adjust_price && $price_raw ? '(' . $price_prefix . wc_price( WC_Product_Addons_Helper::get_product_addon_price_for_display( $price_raw ) ) . ')' : '',
			$addon,
			0,
			$addon_type
		);
	}
}
?>

<div class="row form-group mb-2 <?php echo $required ? 'wc-pao-required-addon' : ''; ?> wc-pao-addon wc-pao-addon-<?php echo sanitize_title( $name ); ?>">

	<?php do_action( 'wc_product_addon_start', $addon ); ?>

	<?php
	if ( $name ) {
		if ( 'heading' === $addon_type ) {
		?>
			<h3 class="wc-pao-addon-heading"><?php echo wptexturize( $name ); ?></h3>
		<?php
		} else {
			switch ( $title_format ) {
				case 'heading':
					?>
					<h3 class="wc-pao-addon-name" data-addon-name="<?php echo esc_attr( wptexturize( $name ) ); ?>"><?php echo wptexturize( $name ); ?> <?php echo $required ? '<em class="required" title="' . __( 'Required field', 'woocommerce-product-addons' ) . '">*</em>&nbsp;' : ''; ?><?php echo wp_kses_post( $price_display ); ?></h3>
					<?php
					break;
				case 'hide':
					break;
				case 'label':
				default:
					?>
					<label class="col-8 col-form-label wc-pao-addon-name" data-addon-name="<?php echo esc_attr( wptexturize( $name ) ); ?>"><?php echo wptexturize( $name ); ?> <?php echo $required ? '<em class="required" title="' . __( 'Required field', 'woocommerce-product-addons' ) . '">*</em>&nbsp;' : ''; ?><?php echo wp_kses_post( $price_display ); ?></label>
					<?php
					break;
			}
		}
	}
	?>

	<?php if ( $description ) { ?>
		<?php echo '<div class="wc-pao-addon-description">' . wpautop( wptexturize( $description ) ) . '</div>'; ?>
	<?php }; ?>
  <div class='col-16'>
	<?php do_action( 'wc_product_addon_options', $addon ); ?>
