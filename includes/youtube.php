<?php
/** Youtube-related Shortcodes.
 *
 * @category FIC
 * @package  Youtube
 * @author   Pavan Rikhi <pavan@ic.org>
 * @license  GPLv3 https://www.gnu.org/licenses/gpl-3.0.html
 * @link     http://www.ic.org
 */


/** Return an embedded HTML5 Youtube video player for a specific video.
 *
 * One shortcode parameter is required: `vid` which should be a URL pointing to
 * the video.
 *
 * Optional paramters are `width` & `height`, which control the size of the
 * embedded player.
 *
 * @param array $atts The Shortcode Parameters
 *
 * @return string HTML containing the embedded player.
 */
function youtube_embedded_player($atts)
{
    extract(
        shortcode_atts(
            array(
                'vid' => '',
                'height' => 480,
                'width' => 720,
            ), $atts
        )
    );

    if ($vid !== '') {
        $vid = str_replace('watch?v=', 'embed/', $vid);
        $vid = str_replace('http:', '', $vid);
        $vid = str_replace('https:', '', $vid);
        return "<iframe type='text/html' frameborder='0' allowfullscreen
                        width='{$width}'
                        height='{$height}'
                        src='{$vid}?html5=1&origin=http://www.ic.org'></iframe>";
    } else {
        return '';
    }
}
add_shortcode('youlist', 'youtube_embedded_player');


?>
