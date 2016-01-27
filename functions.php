<?php
/* By taking advantage of hooks, filters, and the Custom Loop API, you can make Thesis
 * do ANYTHING you want. For more information, please see the following articles from
 * the Thesis User’s Guide or visit the members-only Thesis Support Forums:
 *
 * Hooks: http://diythemes.com/thesis/rtfm/customizing-with-hooks/
 * Filters: http://diythemes.com/thesis/rtfm/customizing-with-filters/
 * Custom Loop API: http://diythemes.com/thesis/rtfm/custom-loop-api/

---:[ place your custom code below this line ]:---*/

// http://wpengineer.com/2487/
// Disable auto-embeds for WordPress >= v3.5
// remove_filter( 'the_content', array( $GLOBALS['wp_embed'], 'autoembed' ), 8 );

// force mappress update on all directory listings
// http://wphostreviews.com/forums/topic/custom-fields-and-mashup
//  $wpq = new WP_Query('post_type=directory&posts_per_page=-1');
//  foreach($wpq->posts as $postid)
//      do_action('mappress_update_meta', $postid);

// Add additional acceptable tags to author bios pages
// http://wordpress.org/support/topic/how-to-add-and-tags-in-comments
/*add_filter('pre_user_description','fa_allow_tags_in_comments');
function fa_allow_tags_in_comments($data) {
    global $allowedtags;
//  $allowedtags['span'] = array('style'=>array());
    $allowedtags['p'] = array();
    $allowedtags['br'] = array();
        $allowedtags['ul'] = array();
        $allowedtags['li'] = array();
    return $data;
}*/


/*
 * Import Additional Functions, Shortcodes & Customizations
 */

/* Utility Functions */
require_once 'includes/utilities.php';

/* User Functions */
require_once 'includes/users.php';

/* Admin Menu Functions */
require 'includes/admin/main.php';

/* Youtube Functions */
require_once 'includes/youtube.php';


/* General Directory Listing Functions */
require_once 'includes/directory_listings.php';

/* Directory<->Map Functions */
require_once 'includes/formidable_mappress.php';


/* General WooCommerce Customizations */
require 'includes/woocommerce.php';

/* WooCommerce Product Customizations */
require_once 'includes/woocommerce_products.php';

/* Separate WooCommerce Product Reviews from Wordpress Comments */
require_once 'includes/separate_product_reviews.php';


// Scripts
/* Shortcode to Fix Sitewide Links Pointing to Store Products & Categories */
//require_once('scripts/fix_links_to_store_products_and_categories.php');
/* Shortcode to Remove Old, Unpublished, Imported Directory Listings */
//require_once('scripts/remove_unused_communities.php');


add_action('admin_head', 'print_custom_admin_css');
function print_custom_admin_css()
{
    echo '
        <style>
          div#poststuff.woocommerce-reports-wide {
            width: 85% !important;
            float: right !important;
          }
        </style>
    ';
}


// allow more HTML tags in author bios description
remove_filter('pre_user_description','wp_filter_kses');
add_filter('pre_user_description', 'wp_filter_post_kses');

// shortcode to display login form
add_shortcode('display_login_form', 'display_login_form');
function display_login_form() {
    $pre_text = "<h1>Please Log in</h1>\nYou must be logged in to view this page.\n<p>";
    return $pre_text . wp_login_form( array( 'echo' => false ) ) . "\n" . wp_register('', '', false) . " | <a href=\"" . wp_lostpassword_url( get_permalink() ) . "\" title=\"Lost your password?\">Lost your password?</a>";
}
//shortcode to display community name in contact a community form
add_shortcode('frm_cmty_name', 'frm_cmty_name');
function frm_cmty_name() {
    if ( !empty($_GET["cmty"]) && is_numeric($_GET["cmty"]) ) {
        return do_shortcode('[frm-field-value field_id="9" entry_id="' . $_GET["cmty"] . '"]');
    }
    else return "the community";
}
//shortcode to display community link in contact a community form
add_shortcode('frm_cmty_link', 'frm_cmty_link');
function frm_cmty_link() {
        if ( !empty($_GET["cmty"]) && is_numeric($_GET["cmty"]) ) {
        return 'Back to <a href="/directory/listings/?entry=' . $_GET["cmty"] . '">' . do_shortcode('[frm_cmty_name]') . '</a>';
        }
}

// shortcode to display search terms when searching the directory
add_shortcode('directory_search_terms', 'directory_search_terms');
function directory_search_terms() {
    if (!empty($_SERVER["QUERY_STRING"])) {
        foreach ($_GET as $key => &$value) {
            $value = str_replace('~',',',$value);
        }
        $search_output = '<div class="dir-search-results"><h2>Showing all communities that meet the following criteria:</h2><ul>';
        isset( $_GET['frm-search'] ) ? $search_output .= '<li><span class="dir-search-fields">Search terms:</span> ' . $_GET['frm-search'] . '</li>' : '';
        isset( $_GET['cmty-newviz'] ) ? $search_output .= '<li><span class="dir-search-fields">Open to visitors:</span> ' . $_GET['cmty-newviz'] . '</li>' : '';
        isset( $_GET['cmty-newmemb'] ) ? $search_output .= '<li><span class="dir-search-fields">Open to new members:</span> ' . $_GET['cmty-newmemb'] . '</li>' : '';
        isset( $_GET['cmty-forming'] ) ? $search_output .= '<li><span class="dir-search-fields">Community forming status:</span> ' . $_GET['cmty-forming'] . '</li>' : '';
        isset( $_GET['cmty-housing'] ) ? $search_output .= '<li><span class="dir-search-fields">Housing provided by the community:</span> ' . $_GET['cmty-housing'] . '</li>' : '';
        isset( $_GET['cmty-city'] ) ? $search_output .= '<li><span class="dir-search-fields">City:</span> ' . $_GET['cmty-city'] . '</li>' : '';
        isset( $_GET['cmty-prov'] ) ? $search_output .= '<li><span class="dir-search-fields">State/Province:</span> ' . $_GET['cmty-prov'] . '</li>' : '';
        isset( $_GET['cmty-state'] ) ? $search_output .= '<li><span class="dir-search-fields">State:</span> ' . $_GET['cmty-state'] . '</li>' : '';
        isset( $_GET['cmty-country'] ) ? $search_output .= '<li><span class="dir-search-fields">Country:</span> ' . $_GET['cmty-country'] . '</li>' : '';
        isset( $_GET['cmty-rural'] ) ? $search_output .= '<li><span class="dir-search-fields">Rural/Urban/Etc:</span> ' . $_GET['cmty-rural'] . '</li>' : '';
        isset( $_GET['cmty-type'] ) ? $search_output .= '<li><span class="dir-search-fields">Community Type(s):</span> ' . $_GET['cmty-type'] . '</li>' : '';
        isset( $_GET['cmty-diet'] ) ? $search_output .= '<li><span class="dir-search-fields">Dietary Preferences:</span> ' . $_GET['cmty-diet'] . '</li>' : '';
        isset( $_GET['cmty-edu'] ) ? $search_output .= '<li><span class="dir-search-fields">Education Styles:</span> ' . $_GET['cmty-edu'] . '</li>' : '';
        isset( $_GET['cmty-spirit'] ) ? $search_output .= '<li><span class="dir-search-fields">Shared religious/spiritual practice(s):</span> ' . $_GET['cmty-spirit'] . '</li>' : '';
        isset( $_GET['cmty-decision'] ) ? $search_output .= '<li><span class="dir-search-fields">Decision making method:</span> ' . $_GET['cmty-decision'] . '</li>' : '';
        isset( $_GET['cmty-foodprod'] ) ? $search_output .= '<li><span class="dir-search-fields">% of food currently produced:</span> ' . $_GET['cmty-foodprod'] . '</li>' : '';
        isset( $_GET['cmty-localfood'] ) ? $search_output .= '<li><span class="dir-search-fields">% of local (within 150 miles) food:</span> ' . $_GET['cmty-localfood'] . '</li>' : '';
        isset( $_GET['cmty-renewenergy'] ) ? $search_output .= '<li><span class="dir-search-fields">% of renewable energy currently generated:</span> ' . $_GET['cmty-renewenergy'] . '</li>' : '';
        isset( $_GET['cmty-energy'] ) ? $search_output .= '<li><span class="dir-search-fields">Energy Sources:</span> ' . $_GET['cmty-energy'] . '</li>' : '';
        isset( $_GET['cmty-landown'] ) ? $search_output .= '<li><span class="dir-search-fields">Community land owner:</span> ' . $_GET['cmty-landown'] . '</li>' : '';
        isset( $_GET['cmty-network'] ) ? $search_output .= '<li><span class="dir-search-fields">Community Network or Organization Affiliations:</span> ' . $_GET['cmty-network'] . '</li>' : '';
        isset( $_GET['cmty-sharedinc'] ) ? $search_output .= '<li><span class="dir-search-fields">% of shared income:</span> ' . $_GET['cmty-sharedinc'] . '</li>' : '';
        isset( $_GET['cmty-sharedexp'] ) ? $search_output .= '<li><span class="dir-search-fields">% of shared expenses:</span> ' . $_GET['cmty-sharedexp'] . '</li>' : '';
        isset( $_GET['cmty-alcohol'] ) ? $search_output .= '<li><span class="dir-search-fields">Alcohol Use:</span> ' . $_GET['cmty-alcohol'] . '</li>' : '';
        isset( $_GET['cmty-tobacco'] ) ? $search_output .= '<li><span class="dir-search-fields">Tobacco Use:</span> ' . $_GET['cmty-tobacco'] . '</li>' : '';
        isset( $_GET['cmty-healthcare'] ) ? $search_output .= '<li><span class="dir-search-fields">Healthcare Styles:</span> ' . $_GET['cmty-healthcare'] . '</li>' : '';
        isset( $_GET['cmty-idleader'] ) ? $search_output .= '<li><span class="dir-search-fields">Identified leader:</span> ' . $_GET['cmty-idleader'] . '</li>' : '';
        isset( $_GET['cmty-shareddiet'] ) ? $search_output .= '<li><span class="dir-search-fields">Importance of a shared diet:</span> ' . $_GET['cmty-shareddiet'] . '</li>' : '';
        isset( $_GET['cmty-debt'] ) ? $search_output .= '<li><span class="dir-search-fields">Open to members with pre-existing debt:</span> ' . $_GET['cmty-debt'] . '</li>' : '';
        isset( $_GET['cmty-sharedmeals'] ) ? $search_output .= '<li><span class="dir-search-fields">Frequency of shared meals:</span> ' . $_GET['cmty-sharedmeals'] . '</li>' : '';
        isset( $_GET['cmty-sharedarea'] ) ? $search_output .= '<li><span class="dir-search-fields">Shared common area (house, building, or space):</span> ' . $_GET['cmty-sharedarea'] . '</li>' : '';
        isset( $_GET['cmty-leadergroup'] ) ? $search_output .= '<li><span class="dir-search-fields">Core leadership group:</span> ' . $_GET['cmty-leadergroup'] . '</li>' : '';
        isset( $_GET['cmty-minlabor'] ) ? $search_output .= '<li><span class="dir-search-fields">Min labor hours per week:</span> ' . $_GET['cmty-minlabor'] . '</li>' : '';
        isset( $_GET['cmty-maxlabor'] ) ? $search_output .= '<li><span class="dir-search-fields">Max labor hours per week:</span> ' . $_GET['cmty-maxlabor'] . '</li>' : '';
        isset( $_GET['cmty-minjoinfee'] ) ? $search_output .= '<li><span class="dir-search-fields">Max required join fee amount:</span> ' . $_GET['cmty-minjoinfee'] . '</li>' : '';
        isset( $_GET['cmty-maxjoinfee'] ) ? $search_output .= '<li><span class="dir-search-fields">Min required join fee amount:</span> ' . $_GET['cmty-maxjoinfee'] . '</li>' : '';
        isset( $_GET['cmty-minmemb'] ) ? $search_output .= '<li><span class="dir-search-fields">Min number of adult members:</span> ' . $_GET['cmty-minmemb'] . '</li>' : '';
        isset( $_GET['cmty-maxmemb'] ) ? $search_output .= '<li><span class="dir-search-fields">Max number of adult members:</span> ' . $_GET['cmty-maxmemb'] . '</li>' : '';
        isset( $_GET['cmty-minongoingfee'] ) ? $search_output .= '<li><span class="dir-search-fields">Min required ongoing fee amount:</span> ' . $_GET['cmty-minongoingfee'] . '</li>' : '';
        isset( $_GET['cmty-maxongoingfee'] ) ? $search_output .= '<li><span class="dir-search-fields">Max required ongoing fee amount:</span> ' . $_GET['cmty-maxongoingfee'] . '</li>' : '';
        isset( $_GET['cmty-minchildren'] ) ? $search_output .= '<li><span class="dir-search-fields">Min number of children:</span> ' . $_GET['cmty-minchildren'] . '</li>' : '';
        isset( $_GET['cmty-maxchildren'] ) ? $search_output .= '<li><span class="dir-search-fields">Max number of children:</span> ' . $_GET['cmty-maxchildren'] . '</li>' : '';
        isset( $_GET['cmty-minnonmemb'] ) ? $search_output .= '<li><span class="dir-search-fields">Min number of non-member residents:</span> ' . $_GET['cmty-minnonmemb'] . '</li>' : '';
        isset( $_GET['cmty-maxnonmemb'] ) ? $search_output .= '<li><span class="dir-search-fields">Max number of non-member residents:</span> ' . $_GET['cmty-maxnonmemb'] . '</li>' : '';
        $search_output .= '</ul></div>';
        return $search_output;
    }
}


// show comments from most recent to oldest
// http://premium.wpmudev.org/blog/daily-tip-how-to-reverse-wordpress-comment-order-to-show-the-latest-on-top/
if (!function_exists('iweb_reverse_comments')) {
    function iweb_reverse_comments($comments) {
        return array_reverse($comments);
    }
}
add_filter ('comments_array', 'iweb_reverse_comments');

// show category descriptions
// http://www.themethesis.com/tutorials/show-category-descriptions/
/*function cat_desc () {
    if ( is_category() ) { ?>
        <div class="catdesc"><?php echo category_description( $category ); ?></div>
<?php }
}
add_action('thesis_hook_before_teasers_box', 'cat_desc'); */

// replace Ubermenu search shortcode
// http://sevenspark.com/docs/ubermenu-search-bar
/*function custom_searchform(){

    $placeholder = __( 'Search ic.org' , 'ubermenu' );

    $form = '<form class="ubersearch-v2" role="search" method="get" id="searchform-custom" action="' . esc_url( home_url( '/' ) ) . '" >
    <div class="ubersearch">
    <input type="text" value="' . get_search_query() . '" name="s" id="menu-search-text" placeholder="'. $placeholder .'" />
    <input type="submit" id="menu-search-submit" value="'. __( 'Go' , 'ubermenu' ) .'" />
    </div>
    </form>';

    return $form;
}
add_shortcode('custom-ubermenu-search', 'custom_searchform');*/

// remove automatic paragraph breaks on directory
// https://formidablepro.com/help-desk/remove-p-tags-from-paragraph-text/
add_filter('frm_use_wpautop', create_function('', "return false;"));







// allow comments, or not, based on directory form entry
// http://formidablepro.com/help-desk/allow-wp-comments-per-form-posts/
add_filter('frm_validate_field_entry', 'my_custom_validation', 8, 3);
function my_custom_validation($errors, $posted_field, $posted_value){
  if($posted_field->id == 223 and $posted_value == 'No'){ //223 = id of Allow comments field
    $_POST['frm_wp_post']['=comment_status'] = 'closed';
  }
  return $errors;
}

// allow multiple checkboxes in a search form
// http://formidablepro.com/help-desk/use-multiple-checkboxes-in-search-form/
add_filter('frm_where_filter', 'filter_custom_display', 10, 2);
function filter_custom_display($where, $args){
  if(in_array($args['where_opt'], array(424, 291,240,262,283,204,268,297,295,256,257,259,281,282,250,251,241,242,243,294,301,236,237,238,239,697,815))){//set to the IDs of the field you are searching in your data form (NOT the search form)
     $args['where_val'] = explode(', ', $args['where_val']); //this code changes the checkbox values into an array
     $where = '(';
     foreach($args['where_val'] as $v){
       if($where != '(')
         $where .= ' OR ';
       $where .= "meta_value like '%". $v ."%'";
     }
     $where .= ") and fi.id='". $args['where_opt'] ."'";
  }
  return $where;
}





// Author Profile Box, hide on Directory Listings
// http://thesis-blogs.com/add-an-author-profile-box-below-posts-in-thesis/
function post_footer_author() {
if (is_single() && 'directory' != get_post_type() ) { ?>
<div class="post-author">
    <?php if( get_the_author_meta('user_custom_avatar', get_the_author_id()) != '' || get_user_meta(get_the_author_id(), 'simple_local_avatar', true) != '' ) echo get_avatar( get_the_author_id() , 85 ); ?>
    <h4>Article by <?php the_author_posts_link(); /* ?>
    <h4>Article by <a href="<?php the_author_url(); ?>">
    <?php the_author_firstname(); ?> <?php the_author_lastname(); </a>*/?></h4>
    <p><?php the_author_description(); ?></p>
    <p><?php the_author_firstname(); ?> has posted <span><?php the_author_posts(); ?></span> article(s) online.</p>
<?php //    <p>Subscribe to feed via <a href="http://feeds.feedburner.com/thesis-blogs"><b>RSS</b></a> or <a href="#"><b>EMAIL</b></a> to receive updates.</p> ?>
</div><!-- end post-author -->
<?php }
}

//add_action('thesis_hook_after_post_box', 'post_footer_author');

// Add Author bio and avatar at top of author archive page
// http://thesis-blogs.com/custom-author-archive-page-in-thesis/
// Hide Author Archive Page Headline
function hide_author_intro_headline($output) {
        if (is_author()) {
        $output ='<br/>';
        }
return $output;
}

//add_filter('thesis_archive_intro','hide_author_intro_headline');

// Author Archive Page
function author_info() {
if (is_author())
        {
?>
<?php
$curauth = (get_query_var('author_name')) ? get_user_by('slug', get_query_var('author_name')) : get_userdata(get_query_var('author'));
?>
<div class="authorarchive">
<?php if ( get_the_author_meta('user_custom_avatar', get_the_author_id()) != '' || get_user_meta($curauth->ID, 'simple_local_avatar', true) != '' ) echo get_avatar($curauth->ID, 70); ?>
<h4>Archive for <?php the_author_posts_link(); ?></h4>
<? /* <h4>Archive for <a href="<?php echo $curauth->user_url; ?>"><?php echo $curauth->first_name; ?> <?php echo $curauth->last_name; ?></a></h4> */ ?>
<p><?php echo $curauth->description; ?></p>
<p class="hlight"><?php echo $curauth->first_name; ?> has posted <span><?php the_author_posts(); ?></span> article(s) online.</p>
</div>
<?php
        }
}

//add_action('thesis_hook_before_content', 'author_info');

function cat_desc (){
    if(is_category()){
    ?>
    <div class="catdesc"><?php echo category_description( $category ); ?></div>
    <?php }
}
//add_action('thesis_hook_before_teasers_box', 'cat_desc');



// override the fucking broken WC lost password page
// http://codex.wordpress.org/Plugin_API/Filter_Reference/lostpassword_url
function my_lostpwd_page( $lostpassword_url, $redirect ) {
    return home_url() . '/wp-login.php?action=lostpassword' . $redirect;
}
add_filter( 'lostpassword_url', 'my_lostpwd_page' );

/*------------------------------------------------------------------------------*/

/* Start Code to make shortcodes work in product category descriptions */
/* http://www.sunrisecreative.co.uk/make-shortcodes-work-in-product-category-descriptions/ */

/*------------------------------------------------------------------------------*/

function woocommerce_taxonomy_archive_description() {
    if ( is_tax( array( 'product_cat', 'product_tag' ) ) && get_query_var( 'paged' ) == 0 ) {
        $description = term_description();
        if ( $description ) {
            echo '<div>' . do_shortcode( wpautop( wptexturize( $description ) ) ) . '</div>';
        }
    }
}

/*------------------------------------------------------------------------------*/

/*End Code to make shortcodes work in product category descriptions*/

/*------------------------------------------------------------------------------*/

// change related products quantity to 9 products
remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20 );
function custom_output_related_products() {
// Display 9 related products
woocommerce_related_products( 9, 3 );
}
add_action( 'woocommerce_after_single_product_summary', 'custom_output_related_products', 20 );

// Show empty (without products) categories. (to show short code categories)
/* http://wordpress.org/support/topic/plugin-woocommerce-show-empty-product-categories */
add_filter('woocommerce_product_categories_widget_args', 'woocommerce_show_empty_categories');
function woocommerce_show_empty_categories($cat_args){
    $cat_args['hide_empty']=0;
    return $cat_args;
}

// Use WC 2.0 variable price format, now include sale price strikeout
// http://gerhardpotgieter.com/2014/02/13/woocommerce-2-1-variation-prices-revert-to-2-0-format/
add_filter( 'woocommerce_variable_sale_price_html', 'wc_wc20_variation_price_format', 10, 2 );
add_filter( 'woocommerce_variable_price_html', 'wc_wc20_variation_price_format', 10, 2 );
function wc_wc20_variation_price_format( $price, $product ) {
    // Main Price
    $prices = array( $product->get_variation_price( 'min', true ), $product->get_variation_price( 'max', true ) );
    $price = $prices[0] !== $prices[1] ? sprintf( __( '%1$s', 'woocommerce' ), wc_price( $prices[0] ) ) : wc_price( $prices[0] );
    // Sale Price
    $prices = array( $product->get_variation_regular_price( 'min', true ), $product->get_variation_regular_price( 'max', true ) );
    sort( $prices );
    $saleprice = $prices[0] !== $prices[1] ? sprintf( __( '%1$s', 'woocommerce' ), wc_price( $prices[0] ) ) : wc_price( $prices[0] );

    if ( $price !== $saleprice ) {
        $price = '<del>' . $saleprice . '</del> <ins>' . $price . '</ins>';
    }
    return $price;
}

// Display 100 products per page. Goes in functions.php
// http://docs.woothemes.com/document/change-number-of-products-displayed-per-page/
add_filter( 'loop_shop_per_page', create_function( '$cols', 'return 100;' ), 20 );

// show the short description exerpt on product listing pages
// duplicate of woocommerce_template_single_excerpt function, modified
// http://wordpress.org/support/topic/how-to-add-product-description-to-category-or-shop-page
function show_product_short_desc() {
    global $post;

    if ( ! $post->post_excerpt ) return;
    ?>
    <div itemprop="description" id="product_short_desc">
            <?php echo apply_filters( 'woocommerce_short_description', $post->post_excerpt ) ?>
    </div>
<?php }
add_action('woocommerce_after_shop_loop_item_title','show_product_short_desc', 5);


/*add_filter( 'avatar_defaults', 'newgravatar' );
function newgravatar ($avatar_defaults) {
//    $myavatar = get_bloginfo('template_directory') . '/wp-includes/images/blank.gif';
    $myavatar = 'http://wp.ic.org/wp-includes/images/blank.gif';
    $avatar_defaults[$myavatar] = "Blank.gif";
    return $avatar_defaults;
}*/

// Add custom authors page
// http://diythemes.com/thesis/rtfm/contributors-authors-page/
/*function list_all_authors() {
    if (is_page('Communities Authors')) {
    global $wpdb;
    $authors = $wpdb->get_results("SELECT ID, user_nicename from $wpdb->users ORDER BY display_name");
    foreach ($authors as $author ) {
    $aid = $author->ID; ?>
        <div class="author_info <?php the_author_meta('user_nicename',$aid); ?>">
            <span class="author_photo"><?php echo get_avatar($aid,96); ?></span>
            <p><a href="<?php get_bloginfo('url'); ?>/author/<?php the_author_meta('user_nicename', $aid); ?>"><?php the_author_meta('display_name',$aid); ?></a></p>
            <p><?php the_author_meta('description',$aid); ?></p>
            <p class="author_email"><a href="mailto:<?php the_author_meta('user_email', $aid); ?>" title="Send an Email to the Author of this Post">Contact the author</a></p>
        </div>
    <?php }
    }
}
add_action('thesis_hook_custom_template','list_all_authors');
remove_action('thesis_hook_custom_template','thesis_custom_template_sample');*/
// END
