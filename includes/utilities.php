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

    /** Properly remove the $value from the comma and space separated $string
    *
    * Assumes the comma is a literal comma and the space is an HTML-escaped space:
    * ',%20
    *
    * @param string $value The string to remove
    * @param string $string The full comma separated string
    *
    * @return string The original string with $value replaced
    */
    public static function remove_from_comma_separated_string($value, $string) {
        $comma_and_space = ',%20';
        $search_to_replacements = array(
            $comma_and_space . $value . $comma_and_space => $comma_and_space,
            $comma_and_space . $value => '',
            $value . $comma_and_space => '',
            $value => ''
        );
        $new_string = $string;
        foreach ($search_to_replacements as $search => $replacement) {
            $new_string = str_replace($search, $replacement, $string);
            if ($new_string !== $string) {
                break;
            }
        }
        return $new_string;
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

    /** Transform a Formidable `[created-at]` date into an RSS 2.0 compliant
     * date.
     *
     * The shortcode should be used like `[formidable_to_rss_date [created-at]]`.
     *
     * @return string The RSS 2.0 respresentation of the date
     */
    public static function formidable_to_rss_date($atts) {
        $date_string = join(' ', $atts);
        preg_match(
            '/(?<Month>\w+) (?<Day>\d+), (?<Year>\d{4}) at (?<Hour>\d+):(?<Minute>\d{2}) (?<Period>AM|PM)/',
            $date_string, $matches);
        $date = DateTime::createFromFormat(
            'j F Y g i A',
            "$matches[Day] $matches[Month] $matches[Year] $matches[Hour] $matches[Minute] $matches[Period]");
        return $date->format(DateTime::RFC2822);
    }
}
add_shortcode('escape_ampersands', array('FIC_Utils', 'escape_ampersands'));
add_shortcode('formidable_to_rss_date', array('FIC_Utils', 'formidable_to_rss_date'));


?>
