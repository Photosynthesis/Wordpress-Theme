<?php
/** This module overrides the WPAdverts widget, giving it a custom styling. **/

include_once(ADVERTS_PATH . '/includes/class-widget-ads.php');

class ThemeClassifiedsWidget extends Adverts_Widget_Ads
{
  public function __construct() {

    $this->defaults = array(
      'title' => __("Community Classifieds", "fic"),
      'count' => 20,
      'keyword' => '',
      'location' => '',
      'is_featured' => 1,
      'category' => array(),
      'price_min' => 0,
      'price_max' => 0,
      'sort' => 'published',
      'order' => 'desc'
    );


    $grand_parent = get_parent_class(parent::class);
    $grand_parent::__construct(
      'fic-classifieds-widget',
      __("Community Classifieds", "fic"),
      array(
        "description"=> __("Displays list of random featured ads.", "fic"),
        "classname" => 'community-classifieds-widget'
      )
    );
  }

  /* Trim the number of configurable options */
  public function form($instance) {
    $instance = wp_parse_args( (array) $instance, $this->defaults );

    $options = array(
      array(
        "name" => "title",
        "label" => __( "Title" ),
        "type" => "text"
      ),
      array(
        "name" => "count",
        "label" => __( "Count" ),
        "type" => "number",
        "append" => array( "step" => 1, "placeholder" => 5 )
      ),
    );

    $modules = adverts_config('config.module');

    if( isset( $modules["featured"] ) ) {
      $options[] = array(
        "name" => "featured",
        "label" => __( "Show featured Ads only.", "adverts" ),
        "type" => "checkbox",
      );
    }

    include_once ADVERTS_PATH . 'includes/class-html.php';

    foreach( $options as $option ) {
      if( isset( $instance[$option["name"]])) {
        $value = $instance[$option["name"]];
      } else {
        $value = null;
      }

      if( in_array($option["type"], array( "text", "number", "range" ) ) ) {
        $this->input_text( $option, $value );
      } elseif( $option["type"] == "select" ) {
        $this->input_select( $option, $value );
      } elseif( $option["type"] == "checkbox" ) {
        $this->input_checkbox( $option, $value );
      }
    }
  }

  /* Override the widget rendering, rendering as Bootstrap list items */
  public function widget($args, $instance) {
    $instance = wp_parse_args( (array) $instance, $this->defaults );
    $meta = array();
    $sort = strtoupper( $instance["order"] );

    // Featured
    $orderby = array( 'menu_order' => 'DESC' );
    $menu_order = 1;

    // Randomize
    $orderby['rand'] = 'DESC';

    $params = apply_filters( "adverts_widget_list_query", array(
        'post_type' => 'advert',
        'post_status' => 'publish',
        'posts_per_page' => $instance["count"],
        'paged' => 1,
        'orderby' => $orderby,
        'menu_order' => $menu_order
    ));

    $loop = new WP_Query( $params );

    extract($args, EXTR_SKIP);

    echo $before_widget;
    $title = empty($instance['title']) ? ' ' : apply_filters('widget_title', $instance['title']);

    if (!empty($title)) {
      echo $before_title . $title . $after_title;
    }
    $output = "";
    $output = "<div class='list-group'>\n";
    if ($loop->have_posts()) {
      while ($loop->have_posts()) {
        $loop->the_post();
        $permalink = get_the_permalink();
        $ad_title = substr(get_the_title(), 0, 100);
        $location = $this::clean_location(get_post_meta(get_the_ID(), "adverts_location", true));
        $output .= "<a href='{$permalink}' class='list-group-item list-group-item-action'>\n";
        $output .= "<div class='classified-title'>{$ad_title}</div>\n";
        if ($location) {
          $output .= "<div class='ml-auto text-primary'><i class='fa fa-building'></i> {$location}</div>";
        }

        $output .= "</a>\n";
      }
    }
    $output .= "</div>\n";

    echo $output;

    wp_reset_query();

    echo $after_widget;
  }

  /* Trim any leading commas */
  private static function clean_location($location) {
    if (strpos($location, ", ") === 0) {
      return substr($location, 2);
    }

    return $location;
  }
}


add_action('widgets_init', function() { register_widget('ThemeClassifiedsWidget'); });

?>
