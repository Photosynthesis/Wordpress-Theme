<?php
/** Wholesale Page **/
class WholesalePage
{
  public static function render_elm() {
    return "<div id='elm-wholesale'></div>";
  }
}

add_shortcode('wholesale_elm', array('WholesalePage', 'render_elm'));

?>
