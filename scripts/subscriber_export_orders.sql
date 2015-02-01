SELECT `metas`.*, `items`.`order_id`
FROM `3uOgy46w_woocommerce_order_itemmeta`
AS `sub_prod_metas`

LEFT JOIN (
    SELECT `meta_key` as `order_meta_key`, `meta_value` as `order_meta_value`,
           `order_item_id`
    FROM `3uOgy46w_woocommerce_order_itemmeta`
    WHERE `meta_key` = '_subscription_period'
       OR `meta_key` = '_subscription_interval'
       OR `meta_key` = '_subscription_start_date'
       OR `meta_key` = 'pa_country'
       OR `meta_key` = 'pa_digital-only'
       OR `meta_key` = '13997-If Previous Subscriber, Name or Subscription #:'
) AS `metas`
  ON `sub_prod_metas`.`order_item_id`=`metas`.`order_item_id`

LEFT JOIN (
    SELECT DISTINCT `order_id`, `order_item_id`
    FROM `3uOgy46w_woocommerce_order_items`
) AS `items`
  ON `items`.`order_item_id`=`sub_prod_metas`.`order_item_id`

WHERE `sub_prod_metas`.`meta_key` = '_product_id'
  AND `sub_prod_metas`.`meta_value` = 13997

ORDER BY `items`.`order_id`
