<?php
/** Admin Page Allowing Updating of Only a Listing's User ID
 *
 * This page is used when a Directory Listing needs it's User ID field updated,
 * but has unfilled required fields. Formidable will not allow updating of the
 * Entry without filling in the required fields, so admins cannot change the
 * User ID so that the Listing can be updated... This page circumvents that by
 * presenting a form with inputs for only the Community Name and User ID.
 *
 */
class FIC_Menu_Directory_User
{
    public static $page_name = 'fic-directory-user-update';
    public static $page_title = 'Directory Listing User Update Form';
    public static $post_action = 'fic_dir_user_update';
    public static $community_input_name = 'community-id';
    public static $user_input_name = 'user';
    public static $directory_form_id = 2;
    public static $user_field_id = 430;
    public static $nonce_field = 'fic_duu_verify';

    /** Handle POST requests */
    public static function handle_post() {
        check_admin_referer(self::$nonce_field);
        if (self::valid_form($_POST)) {
            $entry_id = sanitize_text_field($_POST[self::$community_input_name]);
            $field_value = sanitize_text_field($_POST[self::$user_input_name]);
            $entry = FrmEntry::getOne($entry_id);
            $post = get_post($entry->post_id);
            wp_update_post(array('ID' => $post->ID, 'post_author' => $field_value));
            FrmProEntryMeta::update_single_field(array(
                'entry_id' => $entry_id, 'field_id' => self::$user_field_id,
                'value' => $field_value));
            wp_redirect(admin_url('admin.php?page=fic-directory-user-update&m=1'));
            exit;
        }
        wp_redirect(admin_url('admin.php?page=fic-directory-user-update&m=0'));
    }

    /** Ensure a valid Community & User were submitted */
    public static function valid_form($post) {
        if (isset($post[self::$community_input_name]) &&
                isset($post[self::$user_input_name])) {
            $valid_community = $post[self::$community_input_name] !== '' &&
                FrmEntry::getOne($post[self::$community_input_name]) !== NULL;
            $valid_user = $post[self::$user_input_name] !== '' &&
                get_user_by('id', $post[self::$user_input_name]) !== false;
            return $valid_community && $valid_user;
        }
    }

    /** Generate the Options for the Community Dropdown */
    public static function community_options() {
        global $frm_form;
        $options = array();
        $form = $frm_form->getOne(self::$directory_form_id);
        $entries = FrmEntry::getAll(array('it.form_id' => $form->id));
        foreach ($entries as $entry) {
            $post = get_post($entry->post_id);
            $options[] = array('id' => $entry->id, 'title' => $post->post_title);
        }
        usort($options, function ($a, $b) { return $a['title'] > $b['title']; });
        $options = array_map(function ($option) {
            return "<option value='{$option['id']}'>{$option['title']}</option>";
        }, $options);

        return join("\n", $options);
    }

    /** Render the page **/
    public static function render_page() {
        $user_dropdown_options = array(
            'show_option_none' => '---', 'show' => 'user_login', 'id' => 'duu-user',
            'orderby' => 'user_nicename', 'name' => self::$user_input_name);
?>
<div class='wrap'>
  <h2><?php echo self::$page_title; ?></h2><?php
  if (isset($_GET['m']) && $_GET['m'] == '1') { ?>
    <div class='updated fade'><p><strong>
      The Community's User ID was successfully updated.
    </strong></p></div><?php
  } else if (isset($_GET['m']) && $_GET['m'] == '0') { ?>
      <div class='error fade'><p><b>User ID Update Failed!</b></p></div><?php
  } ?>
  <p><b>This form may be used to update only the <code>User ID</code> field of
    a Directory Listing, even if other required fields are not filled in.</b></p>
  <form method="post" action="admin-post.php">
    <input type='hidden' name='action' value='<?php echo self::$post_action; ?>' />
    <?php wp_nonce_field(self::$nonce_field); ?>
    <label for='duu-community'>Community:</label><br />
      <select id='duu-community' name='<?php echo self::$community_input_name; ?>'>
      <option value='' selected>---</option>
      <?php echo self::community_options(); ?>
    </select><br />
    <label for='duu-user'>User:</label><br />
    <?php wp_dropdown_users($user_dropdown_options); ?><br /><br />
    <input type='submit' name='submit' value='Set User' class='button-primary' />
  </form>
</div>
<?php
    }
}
add_action(
    'admin_post_' . FIC_Menu_Directory_User::$post_action,
    array('FIC_Menu_Directory_User', 'handle_post')
);

?>
