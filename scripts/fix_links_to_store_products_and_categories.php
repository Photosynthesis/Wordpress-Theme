<?php
/** Modify links in every post to use new store structure
 *
 * Product Links change
 *      from    /community-bookstore/<category-name>/<product-name>
 *      and     /products/<category-name>/<product-name>
 *      and     /product/<product-name>
 *      to      /community-bookstore/product/<product-name>
 *
 * Categories change
 *      from    /products/
 *      to      /community-bookstore/category/<category>
 *
 * @category FIC
 * @package  FIC_Scripts
 * @author   Pavan Rikhi <pavan@ic.org>
 * @license  GPLv3 https://www.gnu.org/licenses/gpl-3.0.html
 * @link     http://www.ic.org
 */

//add_shortcode('change_links', 'change_links');
function change_links() {
    change_product_links();
    change_category_links();

}

// Modify the Product Links to Use the Correct URL Structure
function change_product_links() {
    $products = get_product_names_and_categories();

    foreach ($products as $product) {
        $category_name = $product["category"];
        $product_name = $product["product"];

        $links_to_change = array(
            "ic.org/community-bookstore/" . $category_name . "/" . $product_name,
            "ic.org/products/" . $category_name . "/" . $product_name,
            "ic.org/store/" . $category_name . "/" . $product_name,
            "ic.org/store/" . $category_name . "/communities-magazine-" . $product_name,
            "ic.org/product/" . $product_name,);
        $new_link = "ic.org/community-bookstore/product/" . $product_name;

        foreach ($links_to_change as $old_link) {
            replace_post_contents($old_link, $new_link);
//            replace_term_description($old_link, $new_link);
        }
    }
}

// Return an array of `product` and `category` names for all Products
function get_product_names_and_categories() {
    global $wpdb;

    $names_query = "
        SELECT product_posts.post_name, categories.slug
        FROM " . $wpdb->prefix . "posts as product_posts
        LEFT JOIN (SELECT * FROM " . $wpdb->prefix . "term_relationships)
            AS category_relationships
            ON product_posts.ID=category_relationships.object_id
        LEFT JOIN (SELECT * FROM " . $wpdb->prefix . "term_taxonomy
                   WHERE taxonomy='product_cat')
            AS category_terms
            ON category_relationships.term_taxonomy_id=category_terms.term_taxonomy_id
        LEFT JOIN (SELECT * FROM " . $wpdb->prefix . "terms)
            AS categories
            ON categories.term_id=category_terms.term_id
        WHERE product_posts.post_type='product'";
    $names_result = $wpdb->get_results($names_query);

    $names = array();
    foreach ($names_result as $product) {
        $product_slug = $product->post_name;
        $category_slug = $product->slug;
        if (!is_null($product_slug) && !is_null($category_slug)) {
            array_push($names, array('product'  => $product_slug,
                                     'category' => $category_slug));
        }
    }

    return $names;
}

// Modify the Category Links to Use the Correct URL Structure
function change_category_links() {
    $categories = get_category_names();
    foreach ($categories as $category_name) {
        $old_link = "ic.org/products/" . $category_name;
        $new_link = "ic.org/community-bookstore/category/" . $category_name;
        replace_post_contents($old_link, $new_link);
        //replace_term_description($old_link, $new_link);
    }
}

// Return an array of every Category slug
function get_category_names() {
    global $wpdb;

    $category_slug_query = "
        SELECT terms.slug FROM " . $wpdb->prefix . "term_taxonomy as taxonomy
        LEFT JOIN (SELECT * FROM " . $wpdb->prefix . "terms)
            AS terms
            ON terms.term_id=taxonomy.term_id
        WHERE taxonomy.taxonomy='product_cat'";

    $categories = $wpdb->get_results($category_slug_query);

    $category_slugs = array();
    foreach ($categories as $category) {
        array_push($category_slugs, $category->slug);
    }

    return $category_slugs;
}

// Replace any occurence of `old_text` with `new_text` in all posts
function replace_post_contents($old_text, $new_text) {
    global $wpdb;

    $update_content_query = "
        UPDATE " . $wpdb->prefix . "posts
        SET post_content = REPLACE(post_content, '" . $old_text . "', '" . $new_text . "')
        WHERE post_content LIKE '%" . $old_text . "%'";
    $wpdb->get_results($update_content_query);
}

// Replace any occurence of `old_text` with `new_text` in all term descriptions
// This is used to replace text from Communities Magazine Article Categories
function replace_term_description($old_text, $new_text) {
    global $wpdb;

    $update_terms_query = "
        UPDATE " . $wpdb->prefix . "term_taxonomy
        SET description = REPLACE(description, '" . $old_text . "', '" . $new_text . "')
        WHERE description LIKE \"%" . $old_text . "%\"";
    $wpdb->get_results($update_terms_query);
}

?>
