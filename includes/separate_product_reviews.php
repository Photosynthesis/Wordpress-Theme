
<?php
/** Separate WooCommerce Product Reviews
 *
 * WooCommerce comments are saved as Wordpress Comments, which means they end
 * up getting included in the Comments menu.
 * 
 * Including this file will cause Product Reviews to be removed from the
 * Comments page, moving them into a "Product Reviews" Sub-Menu under the
 * WooCommerce "Products" Menu.
 *
 * Based off of this thread http://wpquestions.com/question/showChrono/id/8687
 * 
 * @category FIC
 * @package  FIC_WooCommerce
 * @author   Pavan Rikhi <pavan@ic.org>
 * @license  GPLv3 https://www.gnu.org/licenses/gpl-3.0.html
 * @link     http://www.ic.org
 */

/** Check the current page and add filters if viewing comments.
 */
function check_current_page($screen)
{
    if ($screen->id == 'edit-comments') {
        add_filter('comments_clauses', 'separate_comments_and_review', 10, 2);
        add_filter('comment_status_links', 'change_comment_status_link');
        if (isset($_GET['post_type'])) {
            add_filter(
                'manage_edit-comments_columns', 'product_reviews_comment_columns'
            );
        }
	}
}
add_action('current_screen', 'check_current_page', 10, 2);


/** Add Product Reviews as a sub-menu of Products */
function add_product_reviews()
{
	$post_type = 'product';
    add_submenu_page(
        "edit.php?post_type={$post_type}",
        __('Product Reviews'),
        __('Product Reviews'),
        'moderate_comments',
        "edit-comments.php?post_type={$post_type}",
        '',
        '',
        59
    );
}
add_action('admin_menu', 'add_product_reviews');


/** Remove Product Reviews from the Comments Query */
function separate_comments_and_review($clauses, $wp_comment_query)
{
    global $wpdb;
	if (! $clauses['join']) {
        $clauses['join'] = "JOIN $wpdb->posts ON $wpdb->posts.ID = $wpdb->comments.comment_post_ID";
    }
	if (!empty($_GET['post_type']) &&$_GET['post_type'] == 'product') {
		if (! $wp_comment_query->query_vars['post_type']) {
            $clauses['where'] .= $wpdb->prepare(
                " AND {$wpdb->posts}.post_type = %s", 'product'
            );
        }
	} else {
		if (! $wp_comment_query->query_vars['post_type' ]) {
            $clauses['where'] .= $wpdb->prepare(
                " AND {$wpdb->posts}.post_type != %s", 'product'
            );
        }
	}
    return $clauses;
}


/** Make Status Links on the Product Reviews page filter Product Reviews
 * instead of all Comments.
 */
function change_comment_status_link($status_links)
{
    if (isset($_GET['post_type'])) {
        $status_links['all'] = '<a href="edit-comments.php?post_type=product&comment_status=all">All</a>';
		$status_links['moderated'] = '<a href="edit-comments.php?post_type=product&comment_status=moderated">Pending</a>';
		$status_links['approved'] = '<a href="edit-comments.php?post_type=product&comment_status=approved">Approved</a>';
		$status_links['spam'] = '<a href="edit-comments.php?post_type=product&comment_status=spam">Spam</a>';
		$status_links['trash'] = '<a href="edit-comments.php?post_type=product&comment_status=trash">Trash</a>';
	}
    return $status_links;
}

/** Make the "Comments" column say "Products Reviews". */
function product_reviews_comment_columns($columns)
{
    $columns['comment'] = __('Review');
    return $columns;
}
