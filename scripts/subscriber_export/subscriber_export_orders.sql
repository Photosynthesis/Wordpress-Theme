SELECT `metas`.*, `items`.`order_id`, `items`.`order_item_name`
FROM `3uOgy46w_woocommerce_order_itemmeta`
AS `sub_prod_metas`

LEFT JOIN (
    SELECT `meta_key` as `order_meta_key`, `meta_value` as `order_meta_value`,
           `order_item_id`
    FROM `3uOgy46w_woocommerce_order_itemmeta`
    WHERE `meta_key` = '_subscription_period'
       OR `meta_key` = '_subscription_interval'
       OR `meta_key` = '_subscription_start_date'
       OR `meta_key` = '_line_subtotal'
       OR `meta_key` = '_line_total'
       OR `meta_key` = 'pa_subscription-length'
       OR `meta_key` = 'pa_subscription-length'
       OR `meta_key` = 'pa_country'
       OR `meta_key` = 'pa_digital-only'
       OR `meta_key` = 'pa_delivery'
       OR `meta_key` = 'Previous Subscriber?'
       OR `meta_key` = 'Starting Issue'
       OR `meta_key` = '13997-If Previous Subscriber, Name or Subscription #:'
) AS `metas`
  ON `sub_prod_metas`.`order_item_id`=`metas`.`order_item_id`

LEFT JOIN (
    SELECT DISTINCT `order_id`, `order_item_id`, `order_item_name`
    FROM `3uOgy46w_woocommerce_order_items`
) AS `items`
  ON `items`.`order_item_id`=`sub_prod_metas`.`order_item_id`

WHERE `sub_prod_metas`.`meta_key` = '_product_id'
    /* Subs */
    AND `sub_prod_metas`.`meta_value` = 13997
    /* Gift Subs & Combos */
    /*AND (`sub_prod_metas`.`meta_value` = 136292 OR
         `sub_prod_metas`.`meta_value` = 14247  OR
         `sub_prod_metas`.`meta_value` = 14132  OR
         `sub_prod_metas`.`meta_value` = 14134)*/



ORDER BY `items`.`order_id`
