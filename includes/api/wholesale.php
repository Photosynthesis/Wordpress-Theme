<?php
/** A REST API Exposing Resources for the Wholesale Order Form **/
class APIWholesale
{
  const api_namespace = 'v1/wholesale';
  const manager_email = 'bookstore@ic.org';

  /* Register the Wholesale Endpoints */
  public static function register_routes() {
    register_rest_route(self::api_namespace, '/checkout/', array(
      'methods' => 'POST',
      'callback' => array('APIWholesale', 'checkout'),
    ));
  }

  /* Charge a Customer for their Wholesale Order & Email the Bookstore Mangaer */
  public static function checkout($data) {
    $slug_prices = array(
      "starting-a-community" => 1800,
      "wisdom-of-communities-volumes-1-2-3-4-complete-set" => 7200,
      "communities-directory-book-new-7th-edition" => 1800,
      "together-resilient-building-community" => 1080,
      "a-manual-for-group-facilitators" => 600,
      "building-united-judgment" => 600,
      "ecological-communities-in-europe" => 900,
      "within-reach" => 720,
      // Best of Communities
      "intentional-community-overview-and-starting-a-community" => 900,
      "seeking-and-visiting-community" => 900,
      "leadership-power-and-membership" => 900,
      "good-meetings" => 900,
      "consensus" => 900,
      "agreements-conflict-and-communication" => 900,
      "relationships-intimacy-health-and-well-being" => 900,
      "children-in-community" => 900,
      "community-for-elders" => 900,
      "sustainable-food-energy-transportation" => 900,
      "green-building-ecovillage-design-and-land-preservation" => 900,
      "cohousing-compilation" => 900,
      "cooperative-economics-and-creating-community-where-you-are" => 900,
      "challenges-and-lessons-of-community" => 900,
      "the-peripatetic-communitarian-the-best-of-geoph-kozeny" => 900,
    );


    // Stripe Configuration Check
    if (!defined('STRIPE_MODE') || array_key_exists(STRIPE_MODE, array('LIVE', 'TEST'))) {
      return self::error('Server Configuration Error: STRIPE_MODE');
    }
    if (!defined('STRIPE_KEY_' . STRIPE_MODE)) {
      return self::error('Server Configuration Error: STRIPE_KEY_' . STRIPE_MODE);
    }
    $stripe_api_key = constant('STRIPE_KEY_' . STRIPE_MODE);


    // Pull Data from Request
    $stripe_token = $data['stripeToken'];
    $checkout_args = $data['checkoutArgs'];
    $business_name = $data ['businessName'];
    $contact_name = $data['contactName'];
    $phone_number = $data['phoneNumber'];
    $email_address = $data['emailAddress'];
    $send_emails = $data['sendEmails'];
    $magazine_quantity = self::positive_int_or_zero($data['magazineQuantity']);
    $best_of_set_quantity = self::positive_int_or_zero($data['bestOfSetQuantity']);
    $slug_quantities = $data['slugQuantities'];


    // Validate Data
    if (self::invalid_string($stripe_token)) {
      return self::error('Invalid Stripe Token');
    }

    if (!is_array($checkout_args)) {
      return self::error('Invalid Checkout Addresses');
    } else {
      foreach ($checkout_args as $arg_key => $checkout_arg) {
        if (!is_string($checkout_arg)) {
          return self::error('Checkout Argument "' . $arg_key . '" is Not a String');
        }
      }
    }

    if (self::invalid_string($business_name)) {
      return self::error('Invalid Business Name');
    }
    if (self::invalid_string($contact_name)) {
      return self::error('Invalid Contact Name');
    }
    if (self::invalid_string($phone_number)) {
      return self::error('Invalid Phone Number');
    }
    if (self::invalid_string($email_address)) {
      return self::error('Invalid Email Address');
    }

    if (!is_bool($send_emails)) {
      $send_emails = false;
    }

    if (!is_array($slug_quantities)) {
      return self::error('Invalid Quantities Object');
    }

    foreach ($slug_quantities as $slug => $quantity) {
      if (!array_key_exists($slug, $slug_prices)) {
        unset($slug_quantities[$slug]);
        continue;
      }
      $slug_quantities[$slug] = self::positive_int_or_zero($quantity);
    }


    // Calculate Total
    $shipping_quantity = $best_of_set_quantity;     // $magazine_quantity doesn't qualify
    $product_total = $best_of_set_quantity * 13500;

    foreach ($slug_quantities as $slug => $quantity) {
      if (array_key_exists($slug, $slug_prices)) {
        $shipping_quantity += $quantity;
        $product_total += $slug_prices[$slug] * $quantity;
      }
    }
    if (array_key_exists('wisdom-of-communities-volumes-1-2-3-4-complete-set', $slug_quantities)) {
      // Wisdom Sets should count as 4 items
      $shipping_quantity += 3 * $slug_quantities['wisdom-of-communities-volumes-1-2-3-4-complete-set'];
    }

    if ($magazine_quantity < 3) {
      $magazine_price = 794;
    } elseif ($magazine_quantity < 6) {
      $magazine_price = 556;
    } elseif ($magazine_quantity < 11) {
      $magazine_price = 517;
    } elseif ($magazine_quantity < 16) {
      $magazine_price = 477;
    } else {
      $magazine_price = 397;
    }
    $product_total += $magazine_price * $magazine_quantity;

    if ($shipping_quantity < 10) {
      return self::error('A minimum of 10 items is required.');
    }
    $shipping_total = 100 * $shipping_quantity;

    $checkout_total = $product_total + $shipping_total;


    // Charge Stripe Token
    try {
      \Stripe\Stripe::setApiKey($stripe_api_key);
      $charge = \Stripe\Charge::create(array(
        'currency' => 'USD',
        'amount' => $checkout_total,
        'source' => $stripe_token,
        'description' => 'FIC Wholesale Order - ' . $business_name,
        'shipping' => array(
          'name' => $checkout_args['shipping_name'],
          'address' => array(
            'city' => $checkout_args['shipping_address_city'],
            'country' => $checkout_args['shipping_address_country'],
            'line1' => $checkout_args['shipping_address_line1'],
            'postal_code' => $checkout_args['shipping_address_zip'],
            'state' => $checkout_args['shipping_address_state'],
          ),
        ),
        'metadata' => array(
          'Business Name' => $business_name,
          'Contact Name' => $contact_name,
          'Phone Number' => $phone_number,
          'Email' => $email_address,
          'Send Product & Sale Updates' => $send_emails ? 'Yes' : 'No',
        ),
      ));
    } catch (Exception $e) {
      error_log("Wholesale Stripe Exception:\n$e");
      return self::error('There was an issue charging your credit card - please contact us at bookstore@ic.org');
    }


    // Build Order Summary Table
    $product_rows = "";
    foreach ($slug_quantities as $slug => $quantity) {
      if ($quantity < 1) { continue; }
      $posts = get_posts(array('name' => $slug, 'numberposts' => 1, 'post_type' => 'product'));
      if (sizeof($posts) >= 1) {
        $post = array_shift($posts);
      } else {
        error_log("Could not find Post for slug: \"" . $slug . "\"");
        continue;
      }
      $post_title = $post->post_title;
      $post_url = get_site_url(null, '/community-bookstore/product/' . $slug . '/', 'https');
      $post_price = self::to_dollar($slug_prices[$slug]);
      $post_total = self::to_dollar($slug_prices[$slug] * $quantity);

      $product_rows .=
        "<tr>\n" .
          "<td><a href='{$post_url}'>{$post_title}</a></td>\n" .
          "<td style='text-align:right;'>{$quantity}</td>\n" .
          "<td style='text-align:right;'>{$post_price}</td>\n" .
          "<td style='text-align:right;'>{$post_total}</td>\n" .
        "</tr>\n";
    }
    if ($magazine_quantity > 0) {
      $magazine_dollars = self::to_dollar($magazine_price);
      $magazine_total = self::to_dollar($magazine_price * $magazine_quantity);
      $product_rows .=
        "<tr>\n" .
          "<td>Communities Magazine Subscription</td>\n" .
          "<td style='text-align:right;'>{$magazine_quantity}</td>\n" .
          "<td style='text-align:right;'>{$magazine_dollars}</td>\n" .
          "<td style='text-align:right;'>{$magazine_total}</td>\n" .
        "</tr>\n";
    }
    if ($best_of_set_quantity > 0) {
      $best_of_dollars = self::to_dollar(13500);
      $best_of_total = self::to_dollar(13500 * $best_of_set_quantity);
      $product_rows .=
        "<tr>\n" .
          "<td>Best of Communities Set</td>\n" .
          "<td style='text-align:right;'>{$best_of_set_quantity}</td>\n" .
          "<td style='text-align:right;'>{$best_of_dollars}</td>\n" .
          "<td style='text-align:right;'>{$best_of_total}</td>\n" .
        "</tr>\n";
    }

    $subtotal_dollars = self::to_dollar($product_total);
    $shipping_dollars = self::to_dollar($shipping_total);
    $total_dollars = self::to_dollar($checkout_total);
    $order_table = <<<HTML

<table cellpadding='10' border='1'>
  <thead>
    <tr>
      <th>Product</th>
      <th style='text-align:right'>Quantity</th>
      <th style='text-align:right;'>Price</th>
      <th style='text-align:right;'>Product Total</th>
    </tr>
  </thead>
  <tbody>{$product_rows}</tbody>
  <tfoot style='font-weight:bold;'>
    <tr>
      <td colspan="3" style="text-align:right;">Sub-Total</td>
      <td style="text-align:right;">{$subtotal_dollars}</td>
    </tr>
    <tr>
      <td colspan="3" style="text-align:right;">Shipping Total</td>
      <td style="text-align:right;">{$shipping_dollars}</td>
    </tr>
    <tr>
      <td colspan="3" style="text-align:right;">Total</td>
      <td style="text-align:right;">\${$total_dollars}</td>
    </tr>
  </tfoot>
</table>

HTML;


    // Send Confirmation Email to Customer
    $charge_url = 'https://dashboard.stripe.com/' .
      (STRIPE_MODE === 'TEST' ? 'test/' : '') . "payments/" . $charge->id;
    $confirmation_email = <<<HTML
<h1>Fellowship for Intentional Community Wholesale Order Confirmation</h1>

<p>We have received the following wholesale order from you:</p>

<b>Shipping Address</b>
<address>
{$checkout_args['shipping_name']}<br />
{$checkout_args['shipping_address_line1']}<br />
{$checkout_args['shipping_address_city']}, {$checkout_args['shipping_address_state']} {$checkout_args['shipping_address_zip']}<br />
{$checkout_args['shipping_address_country']}<br />
</address>

<br /><br />

<b>Billing Address</b>
<address>
{$checkout_args['billing_name']}<br />
{$checkout_args['billing_address_line1']}<br />
{$checkout_args['billing_address_city']}, {$checkout_args['billing_address_state']} {$checkout_args['billing_address_zip']}<br />
{$checkout_args['billing_address_country']}<br />
</address>

<br /><br />
{$order_table}

<p>We will send you an update when your order has shipped.</p>

<p>If you have any questions regarding your order, please contact Kim Kanney at
  <a href="mailto:bookstore@ic.org">bookstore@ic.org</a>.</p>
HTML;
    wc_mail($email_address, 'FIC Wholesale Order Confirmation', $confirmation_email);


    // Send Order Email to Manager
    $send_updates_text = $send_emails ? "<li>Yes, send product & sale emails.</li" : "";
    $order_email = <<<HTML
<h1>FIC Wholesale Order Received</h1>

<p>A new wholesale order has been placed:</p>

<ul>
  <li>{$contact_name}</li>
  <li>{$business_name}</li>
  <li>{$phone_number}</li>
  <li>{$email_address}</li>
  {$send_updates_text}
  <li><a href="{$charge_url}">View Stripe Payment</a></li>
</ul>

<b>Shipping Address</b>
<address>
{$checkout_args['shipping_name']}<br />
{$checkout_args['shipping_address_line1']}<br />
{$checkout_args['shipping_address_city']}, {$checkout_args['shipping_address_state']} {$checkout_args['shipping_address_zip']}<br />
{$checkout_args['shipping_address_country']}<br />
</address>

<br /><br />

<b>Billing Address</b>
<address>
{$checkout_args['billing_name']}<br />
{$checkout_args['billing_address_line1']}<br />
{$checkout_args['billing_address_city']}, {$checkout_args['billing_address_state']} {$checkout_args['billing_address_zip']}<br />
{$checkout_args['billing_address_country']}<br />
</address>

<br /><br />

{$order_table}

<br />
<p>
  ---<br />
  This is an automated message sent by the FIC website.
</p>
HTML;
    wc_mail(self::manager_email, 'New Wholesale Order Received', $order_email);


    return array('status' => 'ok');

  }

  private static function invalid_string($value) {
    return !is_string($value) || $value === "";
  }

  private static function error($error_status) {
    return array('status' => $error_status);
  }

  private static function positive_int_or_zero($value) {
    if (is_string($value) && !ctype_digit($value)) {
      return 0;
    } elseif (is_string($value)) {
      $value = (int) $value;
    }
    if (!is_int($value) || $value < 0) {
      return 0;
    } else {
      return $value;
    }
  }

  private static function to_dollar($cents) {
    $whole = (int) floor($cents / 100);
    $fractional = (int) ($cents % 100);
    if ($fractional < 10) { $fractional = "0{$fractional}"; }
    return "{$whole}.{$fractional}";
  }
}

add_action('rest_api_init', array('APIWholesale', 'register_routes'));

?>
