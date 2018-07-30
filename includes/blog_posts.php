<?php
/** Customizations to Wordpress' Built-In Blog Posts **/
class ThemeBlogPosts
{
  public static $pinned_meta_box_id = "theme-pinned-blog-posts";
  public static $pinned_meta_field = "_theme-is-pinned-post";
  public static $pinned_nonce = "theme-pinned-nonce";

  /* Add the "Pin Post" Meta Box to the Add/Edit Posts Page */
  public static function add_meta_box() {
    add_meta_box(
      self::$pinned_meta_box_id,
      "Pin Post",
      array('ThemeBlogPosts', 'render_pinned_box'),
      'post',
      'side',
      'low'
    );
  }

  /* Render the "Pin Post" Meta Box */
  public static function render_pinned_box($post) {
    $checked_attr = get_post_meta($post->ID, self::$pinned_meta_field, true) !== ''
      ? "checked" : "";
    $field = self::$pinned_meta_field;

    wp_nonce_field(self::$pinned_nonce, self::$pinned_nonce);
    echo <<<HTML
<p>
  <label>
    <input type='checkbox' id='{$field}' name='{$field}' value='1' {$checked_attr} />
    Pin to Home page
  </label>
</p>
HTML;
  }

  /* Save the "Pin Post" Form */
  public static function save_pinned_box($post_id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
      return;
    }
    $nonce = self::$pinned_nonce;
    if (!(isset($_POST[$nonce]) && wp_verify_nonce($_POST[$nonce], $nonce))) {
      return;
    }
    if (!current_user_can('edit_posts')) {
      return;
    }


    if (array_key_exists(self::$pinned_meta_field, $_POST)) {
      update_post_meta($post_id, self::$pinned_meta_field, $_POST[self::$pinned_meta_field]);
    } else {
      delete_post_meta($post_id, self::$pinned_meta_field);
    }
  }
}

add_action('add_meta_boxes', array('ThemeBlogPosts', 'add_meta_box'));
add_action('save_post', array('ThemeBlogPosts', 'save_pinned_box'));

?>
