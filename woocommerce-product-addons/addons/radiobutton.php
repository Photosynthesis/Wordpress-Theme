<?php
/**
 * The Template for displaying radio button field.
 *
 * @version 3.0.0
 */

$loop          = 0;
$field_name    = ! empty( $addon['field_name'] ) ? $addon['field_name'] : '';
$addon_key     = 'addon-' . sanitize_title( $field_name );
$required      = ! empty( $addon['required'] ) ? $addon['required'] : '';
$current_value = isset( $_POST[ $addon_key ] ) && isset( $_POST[ $addon_key ][0] ) ? wc_clean( $_POST[ $addon_key ][0] ) : '';
?>

<?php if ( empty( $required ) ) { ?>
	<div class="form-check form-check-inline form-row-wide wc-pao-addon-<?php echo sanitize_title( $field_name ); ?>">
		<label class='form-check-label'>
			<input type="radio" class="form-check-input wc-pao-addon-field wc-pao-addon-radio" value="" name="addon-<?php echo sanitize_title( $field_name ); ?>[]" />&nbsp;&nbsp;<?php esc_html_e( 'None', 'woocommerce-product-addons' ); ?>
		</label>
	</div>
<?php } ?>

<?php
	foreach ( $addon['options'] as $i => $option ) {
		$loop++;

		$price        = ! empty( $option['price'] ) ? $option['price'] : '';
		$price_prefix = 0 < $price ? '+' : '';
		$price_type   = ! empty( $option['price_type'] ) ? $option['price_type'] : '';
		$price_raw    = apply_filters( 'woocommerce_product_addons_option_price', $price, $option );
		$label        = ! empty( $option['label'] ) ? $option['label'] : '';

		if ( 'percentage_based' === $price_type ) {
			$price_display = apply_filters( 'woocommerce_product_addons_option_price_html',
				$price_raw ? '(' . $price_prefix . $price_raw . '%)' : '',
				$option,
				$i,
				'radiobutton'
			);
		} else {
			$price_display = apply_filters( 'woocommerce_product_addons_option_price_html',
				$price_raw ? '(' . $price_prefix . wc_price( WC_Product_Addons_Helper::get_product_addon_price_for_display( $price_raw ) ) . ')' : '',
				$option,
				$i,
				'radiobutton'
			);
		}
		?>
		<div class="form-check form-check-inline form-row-wide wc-pao-addon-wrap-<?php echo sanitize_title( $field_name ); ?>">
			<label class='form-check-label'>
				<input type="radio" class="form-check-input wc-pao-addon-field wc-pao-addon-radio" name="addon-<?php echo sanitize_title( $field_name ); ?>[]" data-raw-price="<?php echo esc_attr( $price_raw ); ?>" data-price="<?php echo WC_Product_Addons_Helper::get_product_addon_price_for_display( $price_raw ); ?>" data-price-type="<?php echo esc_attr( $price_type ); ?>" value="<?php echo sanitize_title( $label ); ?>" <?php checked( $current_value, 1 ); ?> <?php if ( WC_Product_Addons_Helper::is_addon_required( $addon ) ) { echo 'required'; } ?> data-label="<?php echo esc_attr( wptexturize( $label ) ); ?>" />&nbsp;&nbsp;<?php echo wptexturize( $label . ' ' . $price_display ); ?>
			</label>
		</div>
<?php } ?>
