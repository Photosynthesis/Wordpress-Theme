<?php
/** Utility Functions for FIC's Wordpress Site
 *
 * @category FIC
 * @package  Utilities
 * @author   Pavan Rikhi <pavan@ic.org>
 * @license  GPLv3 https://www.gnu.org/licenses/gpl-3.0.html
 * @link     http://www.ic.org
 */


/** HTML-Escape Ampersands
 *
 * Takes one parameter, `content`.
 *
 * @param array $atts The Shortcode Parameter
 *
 * @return string The HTML-escaped content.
 */
function escape_ampersands($atts)
{
    extract(shortcode_atts(array('content' => ''), $atts));
    $escaped_content = str_replace('&', '&amp;', $content);
    return $escaped_content;
}
add_shortcode('escape_ampersands', 'escape_ampersands');

?>
