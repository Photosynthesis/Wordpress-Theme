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
    /** Cache & return the result of a performance-heavy function.
     *
     * The $function_to_cache will be passed 0 arguments.
     *
     * @param function $function_to_cache The function to call if there is no
     * cached result.
     * @param string $cache_key The key to store the result under.
     * @param number $timeout The number of seconds to cache the result
     * for.
     *
     * @return The cached or calculated result of the function.
     */
    public static function cache_result($function_to_cache, $cache_key,
                                        $timeout) {
        $cached = get_transient($cache_key);
        if ($cached !== false) {
            return $cached;
        } else {
            $result = $function_to_cache();
            set_transient($cache_key, $result, $timeout);
            return $result;
        }
    }

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
