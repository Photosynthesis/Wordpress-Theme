<?php
/** Utility Functions for FIC's Wordpress Site
 *
 * @category FIC
 * @package  FIC_Utils
 * @author   Pavan Rikhi <pavan@ic.org>
 * @license  GPLv3 https://www.gnu.org/licenses/gpl-3.0.html
 * @link     http://www.ic.org
 */


class FIC_Utils
{
    /** HTML-Escape Ampersands
     *
     * Takes one parameter, `content`.
     *
     * @param array $atts The Shortcode Parameter
     *
     * @return string The HTML-escaped content.
     */
    public static function escape_ampersands($atts) {
        extract(shortcode_atts(array('content' => ''), $atts));
        $escaped_content = str_replace('&', '&amp;', $content);
        return $escaped_content;
    }
}
add_shortcode('escape_ampersands', array('FIC_Utils', 'escape_ampersands'));


?>
