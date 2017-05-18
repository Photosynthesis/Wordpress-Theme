<?php
/**
 * Export a CSV of Title,URLs for WC Categories
 */

$categories = array('back-issues', 'community-books', 'community-bookstore-videos');

foreach ($categories as $category) {
    $category_query = new WP_Query(array(
        'product_cat' => $category, 'nopaging' => true,
    ));
    $output_file = fopen($category . ".csv", "w");
    foreach ($category_query->posts as $post) {
        $post_url = "http://ic.org/community-bookstore/product/{$post->post_name}";
        fwrite($output_file, "\"{$post->post_title}\",\"{$post_url}\"\n");
    }
    fclose($output_file);
}

?>
