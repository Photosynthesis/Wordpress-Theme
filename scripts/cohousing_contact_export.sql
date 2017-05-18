SELECT p.post_title AS "Community Name", ce.meta_value AS "Contact Email"
FROM 3uOgy46w_frm_items AS i
RIGHT JOIN
    (SELECT * FROM 3uOgy46w_frm_item_metas WHERE field_id=199)
    AS ce ON ce.item_id=i.id
RIGHT JOIN
    (SELECT * FROM 3uOgy46w_frm_item_metas WHERE field_id='262')
    AS coh ON coh.item_id=i.id
RIGHT JOIN 3uOgy46w_posts AS p ON p.ID=i.post_id
WHERE coh.meta_value LIKE '%Cohousing%'
