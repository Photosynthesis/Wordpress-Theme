<?php
require_once('wp-admin/includes/image.php');

/* Transfer ads from AWPCP to WP Adverts */
function main() {
  create_wpadverts_ads(get_awpcp_ad_arrays());
}

/* Return a list of associative arrays with details of the AWPCP ads */
function get_awpcp_ad_arrays() {
  // AWPCP Category ID -> WP-Adverts Category Taxonomy ID
  $category_map = array(
    '19' => 969,
    '23' => 970,
    '24' => 971,
    '1'  => 972,
    '5'  => 973,
    '3'  => 974,
    '22' => 975,
    '4'  => 976,
    '9'  => 977,
    '26' => 978,
    '25' => 979,
    '18' => 980,
    '10' => 981,
  );
  // AWPCP Payment Term ID -> WP-Adverts WooCommerce Product ID
  $payment_map = array(
    '1' => 233295,
    '2' => 233296,
    '3' => 233297,
    '4' => 233298,
    '5' => 233299,
    '6' => 232602,
  );

  $ads = AWPCP_Ad::get_enabled_ads();
  $result = array();
  foreach ($ads as $ad) {
    // Get Payment Info
    $payment = AWPCP_Payment_Transaction::find_by_id($ad->ad_transaction_id);
    if ($payment !== null && strpos($payment->get_status(), "Completed") !== false) {
      $payment_term = $payment->get("payment-term-id");
      $payment_product = array_key_exists($payment_term, $payment_map) ?
        $payment_map[$payment_term] : null;
    } else {
      $payment_product = null;
    }
    // Create Export Data
    $data = array(
      'contact_email' => $ad->ad_contact_email,
      'contact_location' => get_location_of_awpcp_ad($ad),
      'contact_name' => $ad->ad_contact_name,
      'contact_phone' => $ad->ad_contact_phone,
      'user_id' => $ad->user_id,
      'name' => $ad->ad_title,
      'content' => $ad->ad_details,
      'start_date' => $ad->ad_startdate,
      'expiration_timestamp' => strtotime($ad->ad_enddate),
      'category_term_id' => $category_map[$ad->ad_category_id],
      'images' => awpcp_media_api()->find_by_ad_id($ad->ad_id),
      'payment' => $payment_product,
    );
    array_push($result, $data);
  }

  return $result;
}

/* Combine AWPCP's multiple location fields into a single field for WPAdverts */
function get_location_of_awpcp_ad($ad) {
  $result = "";
  $fields = array($ad->city, $ad->state, $ad->country);
  foreach ($fields as $field) {
    if ($field !== '') {
      if ($result !== '') {
        $result .= ", ";
      }
      $result .= $field;
    }
  }
  return $result;
}

/* Create the WP Adverts ads from a list of associative arrays */
function create_wpadverts_ads($awpcp_ad_arrays) {
  $image_directory = wp_upload_dir()['basedir'] . "/awpcp/";
  foreach ($awpcp_ad_arrays as $ad) {
    // Create the Post
    $meta_keys_to_values = array(
      '_expiration_date' => $ad['expiration_timestamp'],
      'adverts_person' => $ad['contact_name'],
      'adverts_email' => $ad['contact_email'],
      'adverts_phone' => $ad['contact_phone'],
      'adverts_location' => $ad['contact_location'],
    );
    $new_post = array(
      'comment_status' => 'closed',
      'ping_status' => 'closed',
      'post_status' => 'publish',
      'post_type' => 'advert',
      'meta_input' => $meta_keys_to_values,
      'post_author' => $ad['user_id'],
      'post_category' => array(),
      'post_content' => $ad['content'],
      'post_date' => $ad['start_date'],
      'post_title' => $ad['name'],
      'term_input' => array(
        'advert_category' => array($ad['category_term_id']),
      ),
    );
    $post_id = wp_insert_post($new_post);
    // Set the Category
    wp_set_object_terms($post_id, $ad['category_term_id'], 'advert_category');
    // Add Images
    $attachments = array();
    foreach ($ad['images'] as $image) {
      if ($image->status !== 'Approved') { continue; }
      $filename = $image_directory . $image->path;
      $attachment_id = wp_insert_attachment(
        array(
          'post_title' => $image->name,
          'post_content' => '',
          'post_status' => 'publish',
          'post_mime_type' => $image->mime_type,
        ), $filename, $post_id
      );
      $attachment_data = wp_generate_attachment_metadata($attachment_id, $filename);
      wp_update_attachment_metadata($attachment_id, $attachment_data);
      if ($image->is_primary === '1') {
        array_unshift($attachments, $attachment_id);
      } else {
        array_push($attachments, $attachment_id);
      }
    }
    if (sizeof($attachments) > 0) {
      add_post_meta($post_id, '_adverts_attachments_order', join(',', $attachments));
    }
    // Create WC Order
    if ($ad['payment'] !== null) {
      global $woocommerce;
      $order = wc_create_order(array('customer_id' => $ad['user_id']));
      $order->add_product(get_product($ad['payment']), 1);
      $order->calculate_totals();
      $order->update_status("Completed", "Imported AWPCP Order.", true);
      update_post_meta($order->id, '_advert_id', $post_id);
      update_post_meta($order->id, 'adverts_wc_payment_processed', 1);
      wp_update_post(array('ID' => $order->id, 'post_date' => $ad['start_date']));
    }
  }
}


main();
?>
