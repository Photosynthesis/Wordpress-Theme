<?php
/** Admin Customization Functions **/
class ThemeSettingsMenu
{
  /* Register Theme Settings & Sections */
  public static function register_options() {
    register_setting('fic-theme-settings', 'theme_extra_javascript');
    add_settings_section(
      'fic-theme-javascript',
      'Javascript',
      array(__CLASS__, 'display_section'),
      'fic-theme-settings'
    );
    add_settings_field(
      'extra_javascript-id',
      'Extra Javascript',
      array(__CLASS__, 'display_setting'),
      'fic-theme-settings',
      'fic-theme-javascript',
      array('type' => 'textarea', 'option_name' => 'theme_extra_javascript')
    );
  }

  /* Don't Render Anything Before Sections */
  public static function display_section($section) {}

  /* Render Theme Settings Fields */
  public static function display_setting($args) {
    extract($args);
    $options = esc_attr(stripslashes(get_option($option_name)));
    switch ($type) {
      case 'textarea':
        echo "<textarea name='{$option_name}' rows='40' cols='150'>{$options}</textarea>";
        break;
    }
  }

  /* Render the Theme Settings Form */
  public static function options_page() {
    echo "<div class='wrap'><h1>FIC Theme Settings</h1>\n";
    echo "<form method='post' action='options.php'>\n";
    settings_fields('fic-theme-settings');
    do_settings_sections('fic-theme-settings');
    submit_button();
    echo "</form></div>";
  }
}

add_action('admin_init', array('ThemeSettingsMenu', 'register_options'));

?>
