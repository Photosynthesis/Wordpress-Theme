<?php
/** A REST API for Updating the Flat Rate Shipping Options **/
class APIFlatRate
{
  const api_namespace = 'v1/flat-rate';

  public static function register_routes() {
    register_rest_route(self::api_namespace, '/get/', array(
      'methods' => 'GET',
      'callback' => array('APIFlatRate', 'get_options'),
      'permission_callback' => function() {
        return current_user_can('manage_options');
      },
    ));
    register_rest_route(self::api_namespace, '/set/', array(
      'methods' => 'POST',
      'callback' => array('APIFlatRate', 'set_options'),
      'permission_callback' => function() {
        return current_user_can('manage_options');
      },
    ));
  }

  const countries_error_text = "Countries must have 2-character code & valid price.";
  const global_error_text = "Global Price must be a number.";
  const variation_id_error_text = "Variation IDs must be whole numbers.";
  const product_id_error_text = "Product IDs must be whole numbers.";

  /* Get the current flat rate options */
  public static function get_options($data) {
    return ThemeWooCommerce::get_flat_rate_options();
  }

  /* Validate & Save the submitted options. For invalid submissions, return an
   * errors object with lists of errors for each rate.
   */
  public static function set_options($data) {
    $options = $data['options'];
    $cmag_errors = array();
    if (!self::test_float($options['cmag']['global'])) {
      $cmag_errors[] = self::global_error_text;
    }
    if (!self::validate_countries($options['cmag']['countries'])) {
      $cmag_errors[] = self::countries_error_text;
    }

    $other_errors = array();
    $has_other_errors = FALSE;
    foreach($options['others'] as $name => $other_rate) {
      $other_errors[$name] = array();

      if (!self::test_float($other_rate['global'])) {
        $other_errors[$name][] = self::global_error_text;
      }
      if (!self::validate_countries($other_rate['countries'])) {
        $other_errors[$name][] = self::countries_error_text;
      }
      if (!self::validate_ids($other_rate['products'])) {
        $other_errors[$name][] = self::product_id_error_text;
      }
      if (!self::validate_ids($other_rate['variations'])) {
        $other_errors[$name][] = self::variation_id_error_text;
      }

      if (!empty($other_errors[$name])) {
        $has_other_errors = TRUE;
      }
    }

    if (!empty($cmag_errors) || $has_other_errors) {
      return array('cmag' => $cmag_errors, 'others' => $other_errors);
    } else {
      ThemeWooCommerce::set_flat_rate_options($options);
      return array('status' => "success");
    }
  }


  /* Validation Helpers */

  /* Are the countries an associative array of codes to prices? */
  private static function validate_countries($countries) {
    if (!is_array($countries)) {
      return false;
    }
    $is_valid = true;
    foreach ($countries as $country => $price) {
      $is_valid = self::test_country($country) && self::test_float($price);
      if (!$is_valid) {
        break;
      }
    }
    return $is_valid;
  }
  /* Are the IDs an array of ints? */
  private static function validate_ids($ids) {
    if (!is_array($ids)) {
      return false;
    }
    $is_valid = true;
    foreach ($ids as $id) {
      $is_valid = self::test_int($id);
      if (!$is_valid) {
        break;
      }
    }
    return $is_valid;
  }

  /* Is the value a valid float? */
  private static function test_float($val) {
    return $val === (string)((float) $val);
  }
  /* Is the value a valid int? */
  private static function test_int($val) {
    return $val === (string)((float) $val);
  }
  /* Is the value a valid name? */
  private static function test_name($val) {
    return is_string($val) && strlen($val) > 0;
  }
  /* Is the value a valid country code? */
  private static function test_country($val) {
    return strlen($val) === 2 && ctype_upper($val);
  }
}

add_action('rest_api_init', array('APIFlatRate', 'register_routes'));

?>
