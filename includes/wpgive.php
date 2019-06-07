<?php
/** Customizations for the WPGive Plugins **/
class ThemeWPGive
{
  /* De-Queue the WP Give Scripts on the Place Ad Page */
  public static function dequeue_wpgive_scripts() {
    if (is_page(100)) {
      wp_deregister_script('give_ffm_frontend');
      wp_dequeue_script('give_ffm_frontend');
    }
  }
}


add_action('wp_print_scripts', array('ThemeWPGive', 'dequeue_wpgive_scripts'), 100);

?>
