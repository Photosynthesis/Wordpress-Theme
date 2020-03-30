<?php
/* Export acive "subscribers" from WooCommerce based on product ID,
including some item, order, and customer data */

define('WP_USE_THEMES', false);

$base = dirname(dirname(__FILE__));
require($base.'../../../../../wp-load.php');

if(!current_user_can('manage_options')) {
  die("Permission denied");
}


$possible_statuses = array(
  "on-hold",
  "active",
  "cancelled",
  "pending",
  "pending-cancel",
  "expired"
);


if($_GET['action'] != 'export'){
?>
<html>
<head>
  <title>
    Subscription Export
  </title>
</head>
<form method="GET">
  Product IDs</br>
  <input type="text" name="product_ids"/><br><br>
  Product Statuses</br>
  <select multiple name="statuses[]" size=5>
    <?php
    foreach ($possible_statuses as $key => $value) {
      echo"<option>$value</option>\n";
    }
    ?>
  </select>
  <input type="hidden" name="action" value="export"/>
  <input type="submit"/>
</form>
</html>
<?php

exit();

}

/* IDs
FIC Membership: 14602
General doantion: 14601
CMag subscription: 13997 Sub services: 260733 Institutional: 262383
*/

//$product_ids = array(13997,260733,262383);

if($_GET['product_ids']){
  $pids = explode(',',$_GET['product_ids']);
}else{
  die("No product IDs sent");
}



foreach ($pids as $id) {
  $product_ids[] = (int) $id;
}

$statuses = array();
foreach ($_GET['statuses'] as $status) {
  if(in_array($status,$possible_statuses)){
    $statuses[] = "wc-".$status;
  }
}


$errors = array();
$stats = array();

$all_meta_keys = array();

foreach ($product_ids as $product_id) {
  $subs = wcs_get_subscriptions(array(
      'subscription_status' => $statuses,
      'subscriptions_per_page' => - 1,
      'product_id'=>$product_id
  ));


  foreach ($subs as $sub) {
    $sub_data = null;
    $item_metas = null;

    //echo "<pre>";
    //print_r($sub);
    //die;

    $sub_data['order_id'] = $sub->data['parent_id'];
    $sub_data['product_id'] = $product_id;
    $sub_data['customer_id'] = $sub->data['customer_id'];
    $sub_data['status'] = $sub->data['status'];
    $sub_data['total'] = $sub->data['total'];
    $sub_data['first_name']  = $sub->data['billing']['first_name'];
    $sub_data['last_name']  = $sub->data['billing']['last_name'];
    $sub_data['full_name']  = $sub_data['first_name']." ".$sub_data['last_name'];
    $sub_data['email']  = $sub->data['billing']['email'];

    if(is_object($sub->data['date_created'])){
      $sub_data['date_created'] = $sub->data['date_created']->date_i18n();
    }else{
      $sub_data['date_created'] = null;
    }

    if(is_object($sub->data['schedule_start']) && is_object($sub->data['schedule_end'])){
      $sub_data['date_start'] = $sub->data['schedule_start']->date_i18n();
      $sub_data['date_end'] = $sub->data['schedule_end']->date_i18n();
    }else{
      $sub_data['date_start'] = null;
      $sub_data['date_end'] = null;
    }

    // The items array includes, it seems, all the order items. We only want the one that matches our target product ID
    foreach ($sub->items as $item_ob) {
      $item_data = $item_ob->get_data();
      if($item_data['product_id'] == $product_id){
        $item_metas = $item_ob->get_meta_data();
        break;
      }
    }

    //print_r($item_metas);


    //echo "<pre>";
    //print_r($item_data);
    //print_r(get_class_methods ($item_ob));
    //die;

    $sub_data['item_order_id'] = $item_data['order_id'];
    $sub_data['item_name'] = $item_data['name'];
    $sub_data['item_product_id'] = $item_data['product_id'];
    $sub_data['item_variation_id'] = $item_data['variation_id'];
    $sub_data['item_quantity'] = $item_data['quantity'];



    // Put the metas in a single array item, so that we can locate them
    // appropriately once all the meta keys have been captured
    foreach ($item_metas as $meta_ob) {
      $data = array();
      $data = $meta_ob->get_data();
      $key = "item_meta_".$data['key'];
      $sub_data['metas'][$key] = $data['value'];
      $all_meta_keys[$key] = true;
    }

    $output[] = $sub_data;

  }

}

//echo "<pre>";
//print_r($output);
//die;


foreach ($output as $index => $sub) {
  foreach ($sub as $key => $value) {
    if($key == 'metas'){
      foreach (array_keys($all_meta_keys) as $mkey) {
        $export_data[$index][$mkey] = $value[$mkey];
      }
    }else{
      $export_data[$index][$key] = $value;
    }
  }
}


    //echo "<pre>";
    //print_r($export_data);
    //print_r($sub);
    //die;

$headers = array_keys($export_data[0]);

$datetime = date('Y-m-d_His');

$fp = fopen('php://output', 'w');
if ($fp && $output) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="woo_subscriptions_'.$datetime.'.csv"');
    header('Pragma: no-cache');
    header('Expires: 0');
    fputcsv($fp, $headers);
    foreach ($export_data as $row) {
        fputcsv($fp, array_values($row));
    }
    die;
}
?>

