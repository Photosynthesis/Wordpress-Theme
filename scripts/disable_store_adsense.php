<?php
/** Add the required HTML for disabling the bottom 'Quick Adsense' ads from
 * the bottom of all Store pages.
 */

include('./wp-blog-header.php');

function main() {
    global $wpdb;

    $quick_sense_disable = "<!--OffEnd-->";

    $products_query = "SELECT * FROM {$wpdb->prefix}posts WHERE `post_type`='product'";
    $products = $wpdb->get_results($products_query);
    foreach ($products as $product) {
        if (strpos($product->post_content, $quick_sense_disable) == false) {
            $update_query = "
                UPDATE {$wpdb->prefix}posts
                SET post_content=CONCAT('{$quick_sense_disable}', post_content)
                WHERE ID={$product->ID}";
            $wpdb->get_results($update_query);
        }
    }

    $categories_query = "SELECT * FROM {$wpdb->prefix}term_taxonomy WHERE `taxonomy`='product_cat'";
    $categories = $wpdb->get_results($categories_query);
    foreach ($categories as $category) {
        if (strpos($category->description, $quick_sense_disable) == false) {
            $update_query = "
                UPDATE {$wpdb->prefix}term_taxonomy
                SET description=CONCAT('{$quick_sense_disable}', description)
                WHERE term_taxonomy_id={$category->term_taxonomy_id}";
            $wpdb->get_results($update_query);
        }
    }
}

main();
?>
