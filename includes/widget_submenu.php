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
        case "foundation-for-intentional-community": case "board-staff":
        case "kozeny-communitarian-award": case "policies": case "contact-fic":
        case "donate": case "about":
          $menu_slug = 'about';
          break;
        case "communities-magazine-home": case "contact-communities-magazine":
          $menu_slug = 'cmag';
          break;
        case "community-classifieds": case "place-ad": case "community-forming":
        case "community-with-opening": case "seeking-community":
        case "consultants-educators-professional": case "retreats-classes-workshops":
        case "opportunities-jobs-internships-businesses":
        case "crafts-gifts-products-services": case "events":
        case "cohousing-openings-and-real-estate": case "jobs-opportunities":
          $menu_slug = 'offers';
          break;
        case "donate": case "contact-fic":
          $menu_slug = false;
          break;
        case "wholesale": case "cart": case "checkout":
          $menu_slug = 'bookstore';
          break;
        default:
          $menu_slug = '';
      }
    }

    if ($menu_slug !== '' && $menu_slug !== false) {
      if ($menu_slug == 'directory') {
        $tabs = array(
          array('title' => 'EXPLORE', 'id' => 'explore', 'href' => 'explore'),
          array('title' => 'COMMUNITY TYPES', 'id' => 'types', 'href' => 'types'),
        );
        $panes = array(
          array('id' => 'explore', 'tab' => 'explore', 'menu' => 'directory'),
          array('id' => 'types', 'tab' => 'types', 'menu' => 'directory-types'),
        );

        self::render_tabbed_menus($tabs, $panes);
      } else if ($menu_slug == 'bookstore') {
        $tabs = array(
          array('title' => 'BOOKSTORE', 'id' => 'bookstore', 'href' => 'bookstore'),
          array('title' => 'TOPICS', 'id' => 'topics', 'href' => 'topics'),
        );
        $panes = array(
          array('id' => 'bookstore', 'tab' => 'bookstore', 'menu' => 'bookstore'),
          array('id' => 'topics', 'tab' => 'topics', 'menu' => 'bookstore-topics'),

        );
        self::render_tabbed_menus($tabs, $panes);
      } else if ($menu_slug == 'cmag') {
        $tabs = array(
          array('title' => 'LEARN MORE', 'id' => 'cmag', 'href' => 'cmag'),
          array('title' => 'TOPICS', 'id' => 'topics', 'href' => 'topics'),
        );
        $panes = array(
          array('id' => 'cmag', 'tab' => 'cmag', 'menu' => 'cmag'),
          array('id' => 'topics', 'tab' => 'topics', 'menu' => 'cmag-topics'),

        );
        self::render_tabbed_menus($tabs, $panes);
      } else {
        echo '<div class="submenu-wrapper widget">';
        echo "<h5>LEARN MORE</h5>";
        wp_nav_menu(array(
          'container_class' => 'submenu-widget',
          'theme_location' => $menu_slug,
          'depth' => 1,
        ));
        echo '</div>';
      }
    } else if ($menu_slug !== false){
      self::render_blog_menu();
    } else {
      // An element is required for properly styling the banner ad widgets.
      echo "<div class='d-none widget'></div>";
    }
  }

  private static function render_tabbed_menus($tabs, $panes) {
    echo "<div class='widget tabbed-submenu-wrapper'>";
    self::render_tabs($tabs);
    self::render_tab_panes($panes);
    echo "</div>";
  }

  private static function render_tabs($tabs) {
    echo "<ul class='nav nav-tabs' role='tablist'>";
    foreach ($tabs as $i => $tab) {
      $link_class = $i === 0 ? ' active' : '';
      $aria_selected = $i === 0 ? 'true' : 'false';
      echo <<<HTML
<li class='nav-item'>
  <a class='nav-link {$link_class}' id='{$tab["id"]}-tab' data-toggle='tab' href='#{$tab["href"]}' aria-controls='{$tab["href"]}' aria-selected='{$aria_selected}'>
    {$tab["title"]}
  </a>
</li>
HTML;
    }
    echo "</ul>";
  }

  // Render Tab Panes Containing Nav Menus
  private static function render_tab_panes($panes) {
    echo "<div class='tab-content' id='submenuContent'>";

    foreach ($panes as $id => $pane) {
      $pane_class = $id === 0 ? 'show active' : '';
      echo "<div class='tab-pane fade {$pane_class}' id='{$pane['id']}' role='tabpanel' aria-labelledby='{$pane['tab']}-tab'>";
      wp_nav_menu(array('theme_location' => $pane['menu'], 'depth' => 1));
      echo "</div>";
    }

    echo "</div>";
  }

  private static function render_blog_menu() {
    echo "<div class='widget tabbed-submenu-wrapper'>";

    // Render the tabs
    $tabs = array(
      array('title' => 'POSTS', 'id' => 'posts', 'href' => 'popular-posts'),
      array('title' => 'CATEGORIES', 'id' => 'categories', 'href' => 'categories'),
    );
    self::render_tabs($tabs);

    echo "<div class='tab-content' id='submenuContent'>";

    // Render the posts
    echo "<div class='tab-pane fade show active' id='popular-posts' role='tabpanel' aria-labelledby='posts-tab'>";
    $sticky_posts = new WP_Query(array(
      'post__in' => get_option('sticky_posts'),
    ));
    if ($sticky_posts->found_posts < 1) { return; }
    foreach ($sticky_posts->posts as $post) {
      $name = $post->post_title;
      $categories = get_the_category($post->ID);
      array_shift($categories); // Pop off the 'All Blog Posts' Category
      $category = strtoupper($categories[0]->name);
      $image = get_the_post_thumbnail(
        $post->ID, array(80, 80), array('class' => 'img-fluid my-auto')
      );
      $link = get_permalink($post->ID);
      echo <<<HTML
<a href='{$link}' class='pop-image'>
  <div class='row'>
    <div class='col-8 p-0 d-flex'>
      {$image}
    </div>
    <div class='col-16 d-flex flex-column'>
      <div class='muted-meta font-weight-normal mt-auto'>{$category}</div>
      <div class='mb-auto'>{$name}</div>
    </div>
  </div>
</a>
HTML;
    }
    echo "</div>";

    // Render the categories
    echo "<div class='tab-pane fade' id='categories' role='tabpanel' aria-labelledby='categories-tab'>";
      wp_nav_menu(array('theme_location' => 'categories', 'depth' => 1));
    echo "</div>";

    echo "</div></div>";
  }
}


/* Add the Widget to Wordpress. */
add_action('widgets_init', function() { register_widget('SubMenu_Widget'); });

?>
