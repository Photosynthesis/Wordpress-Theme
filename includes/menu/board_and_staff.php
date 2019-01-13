<?php
/** Admin Page For Editing/Adding Board & Staff Profiles.
 *
 * This is a simple wrapper around the Elm application.
 *
 * See `/includes/api/board_staff.php` for the POST handling code.
 */
class ThemeBoardStaffMenu
{
  public static $page_name = 'fic-board-staff';
  public static $page_title = 'FIC Board & Staff Profiles';

  public static function render_page() {
    echo '<div id="elm-admin-board-staff"></div>';
  }
}

?>
