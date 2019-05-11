<?php 
/** Customize the Tribe Events Calendar Plugin **/
class ThemeEvents
{
  /* Change the Homepage Title from Upcoming Events to Community Events */
  public static function customize_homepage_title($title, $depth) {
    return str_replace('Upcoming', 'Community', $title);
  }
}

add_filter('tribe_get_events_title', array('ThemeEvents', 'customize_homepage_title'), 10, 2);

?>
