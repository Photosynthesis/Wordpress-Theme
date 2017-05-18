<?php
/** Transfer selected options from a field the options used to
 * be a part of to another field that the options are now a part of
 */
include('./wp-blog-header.php');


function main() {
    global $wpdb;

    $old_field_id = 262;
    $new_field_id = 950;

    $old_options_to_new_options = array(
        'Transition Town (post-petroleum and off-grid communities.)' =>
            'Transition Town (post-petroleum and off-grid communities.)',
        'School~ Educational Institute or Experience' =>
            'School~ Educational Institute or Experience',
        'Volunteer~ Internship~ Apprenticeship~ or WWOOF’ing' =>
            'Volunteer, Internship, Apprenticeship~ or WWOOF’ing',
        'Neighborhood, Community Housing, or Homeowner\'s Association' =>
            'Neighborhood, Community Housing, or Homeowner\'s Association',
        'Ethical Business~ Investment Group~ or Alternative Currency' =>
            'Ethical Business~ Investment Group~ or Alternative Currency',
        'Organizations~ Resources~ or Networks' =>
            'Organizations, Resources, or Networks',
    );

    $all_listings_query = "SELECT id FROM {$wpdb->prefix}frm_items WHERE `form_id`=2";
    $all_listings = $wpdb->get_results($all_listings_query);

    foreach ($all_listings as $listing) {
        $id = $listing->id;

        $old_value_query = "SELECT meta_value FROM {$wpdb->prefix}frm_item_metas
                            WHERE `field_id`=$old_field_id AND `item_id`=$id";
        $old_value_results = $wpdb->get_results($old_value_query);
        if ($wpdb->num_rows == 0) { continue; }
        $old_values = maybe_unserialize($old_value_results[0]->meta_value);

        foreach ($old_options_to_new_options as $old_value => $new_value) {
            if (in_array($old_value, $old_values)) {
                $new_field_query = "
                    SELECT id, meta_value FROM {$wpdb->prefix}frm_item_metas
                    WHERE `field_id`=$new_field_id AND `item_id`=$id";
                $new_results = $wpdb->get_results($new_field_query);
                if ($wpdb->num_rows == 0) {
                    $new_meta_value = maybe_serialize(array($new_value));
                    $insert_query = $wpdb->prepare("
                        INSERT INTO {$wpdb->prefix}frm_item_metas
                        (meta_value, item_id, field_id, created_at) VALUES
                        ('%s', $id, $new_field_id, NOW());", $new_meta_value);
                    $wpdb->get_results($insert_query);
                } else {
                    $old_meta_value = maybe_unserialize($new_results[0]->meta_value);
                    $old_meta_value[] = $old_options_to_new_options[$old_value];
                    $new_meta_value = maybe_serialize($old_meta_value);
                    $update_query = $wpdb->prepare("
                        UPDATE {$wpdb->prefix}frm_item_metas
                        SET meta_value='%s'
                        WHERE `item_id`=$id AND `field_id`=$new_field_id", $new_meta_value);
                    $wpdb->get_results($update_query);
                }
            }
        }
    }
}


main();

?>
