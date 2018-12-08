<?php
/** Customizations to Wordpress' Built-In Blog Posts **/
class ThemeBlog
{
  const theme_options_meta_box_id = "theme-blog-options";
  const theme_options_nonce = "theme-blog-options-nonce";

  const hide_thumbnail_meta_field = "_theme-hide-thumbnail-blog";

  /* Add the "Theme Options" Meta Box to the Add/Edit Posts Page */
  public static function add_meta_box() {
    add_meta_box(
      self::theme_options_meta_box_id,
      "Theme Options",
      array('ThemeBlog', 'render_theme_options_box'),
      'post',
      'side',
      'low'
    );
  }

  /* Render the "Theme Options" Meta Box */
  public static function render_theme_options_box($post) {
    $checked_attr = get_post_meta($post->ID, self::hide_thumbnail_meta_field, true) !== ""
      ? "checked" : "";
    $field = self::hide_thumbnail_meta_field;

    wp_nonce_field(self::theme_options_nonce, self::theme_options_nonce);
    echo <<<HTML
<p>
  <label>
    <input type='checkbox' id='{$field}' name='{$field}' value='1' {$checked_attr} />
    Hide Featured Image
  </label>
</p>
HTML;
  }

  /* Save the "Theme Options" Form */
  public static function save_theme_options_box($post_id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
      return;
    }
    $nonce = self::theme_options_nonce;
    if (!(isset($_POST[$nonce])) && wp_verify_nonce($_POST[$nonce], $nonce)) {
      return;
    }
    if (!current_user_can('edit_posts')) {
      return;
    }


    if (array_key_exists(self::hide_thumbnail_meta_field, $_POST)) {
      update_post_meta($post_id, self::hide_thumbnail_meta_field, $_POST[self::hide_thumbnail_meta_field]);
    } else {
      delete_post_meta($post_id, self::hide_thumbnail_meta_field);
    }
  }
}

add_action('add_meta_boxes', array('ThemeBlog', 'add_meta_box'));
add_action('save_post', array('ThemeBlog', 'save_theme_options_box'));

?>
