UPDATE `3uOgy46w_em_locations`
    SET `location_slug` = CONCAT(`location_slug`, '-event-location')
    WHERE (`location_slug` <> '' OR `location_slug` IS NOT NULL) AND
          `location_slug` NOT LIKE '%-event-location%'
    ;
UPDATE `3uOgy46w_posts`
    SET `guid` = REPLACE(`guid`, `post_name`, CONCAT(`post_name`, '-event-location'))
    WHERE `post_name` <> '' AND `post_type` = 'location' AND
          `guid` NOT LIKE '%-event-location%'
    ;
UPDATE `3uOgy46w_posts`
    SET `post_name` = CONCAT(`post_name`, '-event-location')
    WHERE `post_name` <> '' AND `post_type` = 'location' AND
          `post_name` NOT LIKE '%-event-location%'
    ;


UPDATE `3uOgy46w_em_locations`
    SET `location_name` = CONCAT(`location_name`, ' (Event Location)')
    WHERE `location_name` <> '' OR `location_name` IS NOT NULL AND
          `location_name` NOT LIKE '% (Event Location)%'
    ;
UPDATE `3uOgy46w_posts`
    SET `post_title` = CONCAT(`post_title`, ' (Event Location)')
    WHERE `post_title` <> '' AND `post_type` = 'location' AND
          `post_title` NOT LIKE '% (Event Location)%'
    ;
