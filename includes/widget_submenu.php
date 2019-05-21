<?php
/** This module contains a widget for rendering submenus depedning on the
  * current page.
  *
  **/

/* Define the Widget for Wordpress */
class SubMenu_Widget extends WP_Widget
{
  /* Setup the Widget */
  public function __construct() {
    $widget_ops = array(
      'classname' => 'submenu-widget',
      'description' => 'Per-Page Sub-Menus',
    );
    parent::__construct('submenu-widget', 'SubMenu Widget', $widget_ops);
  }

  /* Render the menu, depending on the current Post's slug. */
  public function widget($args, $instance) {
    $post = get_post();
    $post_name = $post->post_name;
    $post_type = $post->post_type;
    $menu_title = 'LEARN MORE';
    if ($post_type === 'directory' || $post_name === 'directory') {
      // All Directory Posts
      $menu_slug = 'directory';
    } else if ($post_type === 'advert') {
      // WP Adverts Pages
      $menu_slug = 'offers';
    } else if (is_shop() || $post_type === 'product') {
      // Bookstore Pages
      $categories = array_map(function($c) { return $c->slug; },
        get_the_terms($post->ID, 'product_cat'));
      $is_cmag = $post_type ==='product' &&
        (in_array('current-issue', $categories)
        || in_array('back-issues', $categories)
        || in_array('advertising', $categories)
        || $post_name === 'subscription'
      );
      if ($is_cmag) {
        $menu_slug = 'cmag';
      } else if ($post_name === 'communities-directory-book-new-7th-edition') {
        $menu_slug = 'directory';
      } else {
        $menu_slug = 'bookstore';
        $menu_title = 'BOOKSTORE';
      }
    } else if ($post_type === 'tribe_events') {
      // Events Pages
      /* BUG: Events Plugin gives the wrong post-name on it's pages
      if ($post_name === 'events') {
        $menu_slug = 'offers';
      } else {
        // Ignore Event Pages
        $menu_slug = false;
      }
      */
      $menu_slug = 'offers';
    } else if (is_category('communities-articles')) {
      // CMag Blog Category
      $menu_slug = 'cmag';
    } else {
      switch ($post->post_name) {
        case "the-fellowship-for-intentional-community": case "board-staff":
        case "kozeny-communitarian-award": case "policies": case "contact-fic":
        case "donate":
          $menu_slug = 'about';
          break;
        case "communities-magazine-home": case "contact-communities-magazine":
          $menu_slug = 'cmag';
          break;
        case "community-classifieds": case "place-ad": case "community-forming":
        case "community-with-opening": case "seeking-community":
        case "consultants-educators-professional": case "retreats-classes-workshops":
        case "land-houses-real-estate": case "opportunities-jobs-internships-businesses":
        case "crafts-gifts-products-services":
          $menu_slug = 'offers';
          break;
        case "donate": case "contact-fic":
          $menu_slug = false;
          break;
        case "wholesale":
          $menu_slug = 'bookstore';
          $menu_title = 'BOOKSTORE';
          break;
        default:
          $menu_slug = '';
      }
    }

    if ($menu_slug !== '' && $menu_slug !== false) {
      echo '<div class="submenu-wrapper widget">';
      echo "<h5>{$menu_title}</h5>";
      wp_nav_menu(array(
        'container_class' => 'submenu-widget',
        'theme_location' => $menu_slug,
        'depth' => 1,
      ));
      echo '</div>';
    } else if ($menu_slug !== false){
      self::render_blog_menu();
    } else {
      // An element is required for properly styling the banner ad widgets.
      echo "<div class='d-none widget'></div>";
    }
  }

  private static function render_blog_menu() {
    $sticky_posts = new WP_Query(array(
      'post__in' => get_option('sticky_posts'),
    ));
    if (sizeof($sticky_posts) < 1) { return; }
    echo "<div class='widget blogmenu-wrapper'>";
    foreach ($sticky_posts->posts as $post) {
      $name = $post->post_title;
      $categories = get_the_category($post->ID);
      array_shift($categories); // Pop off the 'All Blog Posts' Category
      $category = strtoupper($categories[0]->name);
      $image = get_the_post_thumbnail(
        $post->ID, array(80, 80), array('class' => 'img-fluid')
      );
      $link = get_permalink($post->ID);
      echo <<<HTML
<a href='{$link}' class='pop-image'>
  <div class='row'>
    <div class='col-8 p-0'>
      {$image}
    </div>
    <div class='col-16'>
      <div class='muted-meta font-weight-normal'>{$category}</div>
      <div>{$name}</div>
    </div>
  </div>
</a>
HTML;
    }
    echo "</div>";
  }
}

class SubMenu_Walker extends Walker_Nav_Menu
{

}


/* Add the Widget to Wordpress. */
add_action('widgets_init', function() { register_widget('SubMenu_Widget'); });

?>
