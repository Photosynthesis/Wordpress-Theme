SELECT 
    names.meta_value AS contact_name,
    emails.meta_value as contact_email,
    countries.meta_value AS country,
    states.meta_value AS state,
    zip_codes.meta_value AS zip_code

FROM 3uOgy46w_frm_items AS i

-- NAME
RIGHT JOIN
    (SELECT * FROM 3uOgy46w_frm_item_metas WHERE field_id=202)
    AS names on names.item_id=i.id

-- EMAIL
RIGHT JOIN
    (SELECT * FROM 3uOgy46w_frm_item_metas WHERE field_id=199)
    AS emails on emails.item_id=i.id

-- COUNTRY
RIGHT JOIN
    (SELECT * FROM 3uOgy46w_frm_item_metas WHERE field_id=424)
    AS countries on countries.item_id=i.id

-- STATE
RIGHT JOIN
    (SELECT * FROM 3uOgy46w_frm_item_metas WHERE field_id=815)
    AS states on states.item_id=i.id

-- ZIP CODE
RIGHT JOIN
    (SELECT * FROM 3uOgy46w_frm_item_metas WHERE field_id=429)
    AS zip_codes on zip_codes.item_id=i.id

WHERE 
    i.form_id=2 AND
    countries.meta_value='United States' AND
    ( states.meta_value IN ('Arizona', 'Nevada', 'Utah', 'Colorado', 'New Mexico', 'Texas') OR
      ( states.meta_value='California' AND 
        CONVERT(SUBSTRING_INDEX(zip_codes.meta_value, '-', 1), UNSIGNED INTEGER) BETWEEN 90001 AND 93999 
      )
    )
;
