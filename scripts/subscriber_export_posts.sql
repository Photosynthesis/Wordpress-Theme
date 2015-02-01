SELECT posts.ID, posts.post_date, posts.post_type, post_metas.meta_key,
       post_metas.meta_value, order_number_meta.meta_value as order_number
FROM `3uOgy46w_posts`
AS `posts`

LEFT JOIN (
    SELECT *
    FROM `3uOgy46w_postmeta`
    WHERE `meta_key` = '_billing_first_name'
       OR `meta_key` = '_billing_last_name'
       OR `meta_key` = '_shipping_first_name'
       OR `meta_key` = '_shipping_last_name'
) AS `post_metas`
  ON `post_metas`.`post_id` = `posts`.`ID`

LEFT JOIN (
    SELECT *
    FROM `3uOgy46w_postmeta`
    WHERE `meta_key` = '_order_number'
) AS `order_number_meta`
  ON `order_number_meta`.`post_id` = `posts`.`ID`

WHERE `posts`.`post_type` = 'shop_order'
  AND (`posts`.`post_date` >= '2014-03-01' AND
       `posts`.`post_date` < '2014-12-31' + interval 1 day)
