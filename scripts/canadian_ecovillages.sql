select posts.post_title as 'Name', contact.meta_value as 'Contact Email'
from 3uOgy46w_frm_items as items
join (select meta_value, item_id from 3uOgy46w_frm_item_metas
      where field_id=424) as country on country.item_id=items.id
join (select meta_value, item_id from 3uOgy46w_frm_item_metas
      where field_id=199) as contact on contact.item_id=items.id
join (select meta_value, item_id from 3uOgy46w_frm_item_metas
      where field_id=262) as types on types.item_id=items.id
join (select ID, post_title from 3uOgy46w_posts
      where post_type='directory') as posts on posts.ID=items.post_id

where country.meta_value like 'canada'
  and types.meta_value like '%ecovillage%'
