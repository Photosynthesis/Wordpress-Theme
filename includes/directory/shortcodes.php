<?php
/** Directory Shortcodes **/
class DirectoryShortcodes
{
  /* Return the HTML div necessary for Elm to render to */
  public static function render_elm() {
    return "<div id='elm-directory'></div>";
  }

  /** Return an Edit link for the Directory Listing if the Current User is an
   * Administrator. This is checked by checking for the `edit_plugins`
   * permission.
   *
   * One shortcode parameter is required: `listing_id`, which determines what
   * Edit Link is returned.
   *
   * @param array $atts The Shortcode Parameter
   *
   * @return string The Directory Listing's Edit Link in HTML
   */
  public static function show_edit_link($atts) {
    extract(shortcode_atts(array('listing_id' => 0), $atts));
    if (current_user_can('edit_plugins')) {
      $entry_edit_url = "/wp-admin/admin.php" .
        "?page=formidable-entries&frm_action=edit&id=$listing_id";
      return "<a href=\"$entry_edit_url\">Edit Listing</a>";
    } else {
      return "";
    }
  }

  /** Return a link for marking a Directory Listing as up-to-date if the current
   * user is an admin or the listings editor. If the current page has an update
   * request in it's GET parameters, this shortcode will set the Formidable
   * Entry's Last Verified field to today, with the same restrictions as for
   * displaying the link.
   *
   * Admins are users that have the 'edit_plugins' permission.
   *
   * Two parameters are required:
   *
   * 1. `listing_id` - the formidable entry's ID
   * 2. `post_id` - the formidable entry's post's ID
   *
   * @param array $atts The Shortcode Parameters
   *
   * @return string The Directory Listing's Verify Link in HTML
   */
  public static function verify_link($atts) {
    global $wpdb;
    extract(shortcode_atts(array('listing_id' => 0, 'post_id' => 0), $atts));
    $post_id = intval($post_id);
    $listing_id = intval($listing_id);
    if ($post_id === 0 || $listing_id === 0) { return ''; }

    $editor_id = get_post($post_id)->post_author;
    $current_user = get_current_user_id();

    if (current_user_can('edit_plugins') || $editor_id == $current_user) {
      $get_parameter = 'verify_as_up_to_date';

      if (isset($_GET[$get_parameter])) {
        $entry_is_valid = self::validate_entry($listing_id);
        if (!$entry_is_valid) {
          return '<small style="color:red;text-emphasis:bold;">' .
            'Your Listing could not be verified <br />' .
            'because it is incomplete, please edit your <br />' .
            'listing before verifying it.</small>';
        }
        $verify_date_field_id = DirectoryDB::$verified_date_field_id;
        $exists_query = "
          SELECT id FROM {$wpdb->prefix}frm_item_metas
          WHERE `field_id`=$verify_date_field_id
            AND `item_id`=$listing_id;";
        $results = $wpdb->get_results($exists_query);
        $result_count = $wpdb->num_rows;
        $today = date('Y-m-d');
        if ($result_count === 0) {
          $insert_query = "
            INSERT INTO {$wpdb->prefix}frm_item_metas
                    (meta_value, field_id, item_id, created_at)
            VALUES  ('$today', $verify_date_field_id, $listing_id, NOW());";
          $wpdb->get_results($insert_query);
        } else {
          $meta_id = $results[0]->id;
          $update_query = "
            UPDATE `{$wpdb->prefix}frm_item_metas`
            SET meta_value='$today'
            WHERE `id`=$meta_id;";
          $wpdb->get_results($update_query);
        }
        return '<b class="text-success">Listing Successfully Verfied.</b>';
      }
      return "<a href='.?$get_parameter=1'>Verify as Up-to-Date</a>";
    }

    return '';
  }
  /** Ensure that the current data for the Listing would validate the current form */
  private static function validate_entry($entry_id) {
    $exempt_field_ids = array(9, 684, 685);     // name, lat, long

    $entry = FrmEntry::getOne($entry_id);
    $data = array('form_id' => 2, 'item_key' => $entry->item_key, 'item_meta' => array());
    $metas = FrmEntryMeta::getAll(array('item_id' => $entry->id));
    foreach ($metas as $meta) {
      $data['item_meta'][$meta->field_id] = $meta->meta_value;
    }
    $errors = FrmEntryValidate::validate($data);

    foreach ($exempt_field_ids as $exempt_field) {
      $field_key = "field{$exempt_field}";
      if (array_key_exists($field_key, $errors)) {
        unset($errors[$field_key]);
      }
    }
    return empty($errors);
  }

  /* Display the Community Name in the Contact-A-Community Form */
  public static function community_name() {
    if (!empty($_GET["cmty"]) && is_numeric($_GET["cmty"])) {
      return do_shortcode('[frm-field-value field_id="9" entry_id="' . $_GET["cmty"] . '"]');
    } else {
      return "the community";
    }
  }

  /* Display the Community Link in the Contact-A-Community Form */
  public static function community_link() {
    if (!empty($_GET["cmty"]) && is_numeric($_GET["cmty"])) {
      return 'Back to <a href="/directory/listings/?entry=' . $_GET["cmty"] . '">' .
        do_shortcode('[frm_cmty_name]') . '</a>';
    }
  }

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
  public static function embed_youtube($atts) {
    extract(shortcode_atts(
      array('vid' => '', 'height' => 480, 'width' => 720,), $atts
    ));

    $vids = explode(',', $vid);
    $output = '';
    foreach ($vids as $vid_id) {
      $vid_id = str_replace('http://www.youtube.com/watch?v=', '', $vid_id);
      $vid_id = str_replace('https://www.youtube.com/watch?v=', '', $vid_id);
      $output .= "<iframe type='text/html' frameborder='0' allowfullscreen
                          width='{$width}'
                          height='{$height}'
                          src='//www.youtube.com/embed/{$vid_id}?html5=1&origin=http://www.ic.org'></iframe>";
    }
    return $output;
  }

  /** Show checkboxes to filter the directory search results page by:
   *
   * - Is Established?
   * - Is Forming?
   * - Visitors Welcome?
   * - Accepting New Members?
   * - Is FIC Member?
   *
   * The checkboxes are generated by modifying the GET parameters of the current
   * URL.
   *
   * @return string HTML containg the checkboxes
   */
  public static function search_filters() {
      $filters = array(
          array('param' => 'open_to_visitors', 'value' => 'Yes',
                'text' => 'Visitors Welcome'),
          array('param' => 'open_to_members', 'value' => 'Yes',
                'text' => 'Accepting New Members'),
          array('param' => 'community_status', 'value' => 'Established',
                'text' => 'Established'),
          array('param' => 'community_status', 'value' => 'Forming, Re-forming',
                'text' => 'Forming'),
          array('param' => 'fic_member', 'value' => 'Yes', 'text' => 'FIC Member')

      );

      // Check if a filter is used
      foreach ($filters as &$filter) {
          $filter['used'] = array_key_exists($filter['param'], $_GET) &&
              strpos(urldecode($_GET[$filter['param']]), $filter['value']) !== false;
      }

      // Build the HTML
      $checkboxes = array();
      foreach ($filters as &$filter) {
          $new_get = $_GET;
          if ($filter['used']) {
              $new_get[$filter['param']] = ThemeUtilities::remove_from_comma_separated_string(
                  $filter['value'], $new_get[$filter['param']]
              );
              if ($new_get[$filter['param']] === '') {
                  unset($new_get[$filter['param']]);
              }
              $selected = 'checked';
          } else {
              if (array_key_exists($filter['param'], $new_get) &&
                      $new_get[$filter['param']] !== '') {
                  $new_get[$filter['param']] .= ',%20' . $filter['value'];
              } else {
                  $new_get[$filter['param']] = $filter['value'];
              }
              $selected = '';
          }
          $url = http_build_query($new_get);
          $checkboxes[] = "<input name='{$filter['param']}' type='checkbox'" .
              " onclick=\"window.location='?{$url}';\" $selected>{$filter['text']}";
      }

      $link_html = join(' | ', $checkboxes);

      return "<div class='small' style='float:right;'>Filter: {$link_html}</div>" .
          "<div style='clear:both;'></div>";
  }

  /** Show multiple lists of Communities, drilling down by Country and then
   * State/Province.
   *
   * US Listings are shown first.
   *
   * @return string HTML containing the lists
   */
  public static function geo_list() {
      return ThemeUtilities::cache_result(function() {
          global $wpdb;

          $directory_form_id = 2;
          $is_public_field_id = 218;
          $country_field_id = 424;
          $region_field_id = 816;
          $state_field_id = 815;
          $entries_query = "
              SELECT countries.country, regions.region, states.state
              FROM `{$wpdb->prefix}frm_items` AS entries
              INNER JOIN (SELECT ID, post_status
                          FROM `{$wpdb->prefix}posts`
                          WHERE post_status='publish')
                     AS posts ON posts.ID=entries.post_id
              INNER JOIN (SELECT `item_id`, `meta_value` AS is_public
                          FROM `{$wpdb->prefix}frm_item_metas`
                          WHERE `meta_value`='Yes' AND
                                `field_id`=$is_public_field_id)
                     AS public ON public.item_id=entries.id
              INNER JOIN (SELECT `item_id`, `meta_value` AS country
                          FROM `{$wpdb->prefix}frm_item_metas`
                          WHERE `field_id`=$country_field_id)
                     AS countries ON countries.item_id=entries.id
              LEFT JOIN (SELECT `item_id`, `meta_value` AS region
                         FROM `{$wpdb->prefix}frm_item_metas`
                         WHERE `field_id`=$region_field_id)
                     AS regions ON regions.item_id=entries.id
              LEFT JOIN (SELECT `item_id`, `meta_value` AS state
                         FROM `{$wpdb->prefix}frm_item_metas`
                         WHERE `field_id`=$state_field_id)
                     AS states ON states.item_id=entries.id
              WHERE entries.form_id=$directory_form_id
              ;";
          $entries = $wpdb->get_results($entries_query);

          $countries = array();
          // Count Entries
          foreach ($entries as $entry) {
              if (!array_key_exists($entry->country, $countries)) {
                  $countries[$entry->country] = array();
              }
              $region = $entry->country === "United States" ?
                        $entry->state : ucwords($entry->region);
              if (!array_key_exists($region, $countries[$entry->country])) {
                  $countries[$entry->country][$region] = 1;
              } else {
                  $countries[$entry->country][$region]++;
              }
          }

          // Sort Countries & Regions Alphabetically
          foreach ($countries as &$country) {
              ksort($country);
          }
          ksort($countries);

          // Move US to the Top
          $us = $countries["United States"];
          unset($countries["United States"]);
          $countries = array_merge(array("United States" => $us), $countries);

          // Render as HTML
          $countries_html = '';
          foreach ($countries as $country => $regions) {
              $country_total = array_sum($regions);
              $country_url = "/directory/listings/?country=$country";
              if ($country === "United States")  {
                  $country_class = 'geo-us-state';
                  $region_get_parameter = 'state';
              } else {
                  $country_class = 'geo-state';
                  $region_get_parameter = 'province';
              }
              $region_html = '';
              foreach ($regions as $region => $count) {
                  if (empty($region)) {
                      continue;
                  } else if ($region === "Kentucky" || $region === "Ohio") {
                      // Start a New Column
                      $region_html .= "</ul></li><li class='$country_class'>
                                       <ul class='$country_class'>";
                  }
                  $region_url = "$country_url&$region_get_parameter=$region";
                  $region_html .= "
                      <li class='geo-state-prov'><a href='$region_url'>
                          $region <span class='geo-count'>($count)</span>
                      </a></li>";
              }

              $countries_html .= "
                  <li class='geo-country'><a href='$country_url'>$country
                      <span class='geo-count'>($country_total)</span></a>
                      <ul class='$country_class'>$region_html</ul>
                  </li>";
          };

          return '<ul class="geo-country">' . $countries_html . '</ul>';
      }, 'directory_geographical_lists',  7 * 24 * 60 * 60);
  }

  /* Display a List of Search Terms When Searching Listings */
  public static function search_terms() {
    if (!empty($_SERVER["QUERY_STRING"])) {
      foreach ($_GET as $key => &$value) {
        $value = str_replace('~',',',$value);
      }
      $search_output = '<div class="dir-search-results"><h2>Showing all communities that meet the following criteria:</h2><ul>';
      isset( $_GET['frm-search'] ) ? $search_output .= '<li><span class="dir-search-fields">Search terms:</span> ' . $_GET['frm-search'] . '</li>' : '';
      isset( $_GET['cmty-newviz'] ) ? $search_output .= '<li><span class="dir-search-fields">Open to visitors:</span> ' . $_GET['cmty-newviz'] . '</li>' : '';
      isset( $_GET['cmty-newmemb'] ) ? $search_output .= '<li><span class="dir-search-fields">Open to new members:</span> ' . $_GET['cmty-newmemb'] . '</li>' : '';
      isset( $_GET['cmty-forming'] ) ? $search_output .= '<li><span class="dir-search-fields">Community forming status:</span> ' . $_GET['cmty-forming'] . '</li>' : '';
      isset( $_GET['cmty-housing'] ) ? $search_output .= '<li><span class="dir-search-fields">Housing provided by the community:</span> ' . $_GET['cmty-housing'] . '</li>' : '';
      isset( $_GET['cmty-city'] ) ? $search_output .= '<li><span class="dir-search-fields">City:</span> ' . $_GET['cmty-city'] . '</li>' : '';
      isset( $_GET['cmty-prov'] ) ? $search_output .= '<li><span class="dir-search-fields">State/Province:</span> ' . $_GET['cmty-prov'] . '</li>' : '';
      isset( $_GET['cmty-state'] ) ? $search_output .= '<li><span class="dir-search-fields">State:</span> ' . $_GET['cmty-state'] . '</li>' : '';
      isset( $_GET['cmty-country'] ) ? $search_output .= '<li><span class="dir-search-fields">Country:</span> ' . $_GET['cmty-country'] . '</li>' : '';
      isset( $_GET['cmty-rural'] ) ? $search_output .= '<li><span class="dir-search-fields">Rural/Urban/Etc:</span> ' . $_GET['cmty-rural'] . '</li>' : '';
      isset( $_GET['cmty-type'] ) ? $search_output .= '<li><span class="dir-search-fields">Community Type(s):</span> ' . $_GET['cmty-type'] . '</li>' : '';
      isset( $_GET['cmty-diet'] ) ? $search_output .= '<li><span class="dir-search-fields">Dietary Preferences:</span> ' . $_GET['cmty-diet'] . '</li>' : '';
      isset( $_GET['cmty-edu'] ) ? $search_output .= '<li><span class="dir-search-fields">Education Styles:</span> ' . $_GET['cmty-edu'] . '</li>' : '';
      isset( $_GET['cmty-spirit'] ) ? $search_output .= '<li><span class="dir-search-fields">Shared religious/spiritual practice(s):</span> ' . $_GET['cmty-spirit'] . '</li>' : '';
      isset( $_GET['cmty-decision'] ) ? $search_output .= '<li><span class="dir-search-fields">Decision making method:</span> ' . $_GET['cmty-decision'] . '</li>' : '';
      isset( $_GET['cmty-foodprod'] ) ? $search_output .= '<li><span class="dir-search-fields">% of food currently produced:</span> ' . $_GET['cmty-foodprod'] . '</li>' : '';
      isset( $_GET['cmty-localfood'] ) ? $search_output .= '<li><span class="dir-search-fields">% of local (within 150 miles) food:</span> ' . $_GET['cmty-localfood'] . '</li>' : '';
      isset( $_GET['cmty-renewenergy'] ) ? $search_output .= '<li><span class="dir-search-fields">% of renewable energy currently generated:</span> ' . $_GET['cmty-renewenergy'] . '</li>' : '';
      isset( $_GET['cmty-energy'] ) ? $search_output .= '<li><span class="dir-search-fields">Energy Sources:</span> ' . $_GET['cmty-energy'] . '</li>' : '';
      isset( $_GET['cmty-landown'] ) ? $search_output .= '<li><span class="dir-search-fields">Community land owner:</span> ' . $_GET['cmty-landown'] . '</li>' : '';
      isset( $_GET['cmty-network'] ) ? $search_output .= '<li><span class="dir-search-fields">Community Network or Organization Affiliations:</span> ' . $_GET['cmty-network'] . '</li>' : '';
      isset( $_GET['cmty-sharedinc'] ) ? $search_output .= '<li><span class="dir-search-fields">% of shared income:</span> ' . $_GET['cmty-sharedinc'] . '</li>' : '';
      isset( $_GET['cmty-sharedexp'] ) ? $search_output .= '<li><span class="dir-search-fields">% of shared expenses:</span> ' . $_GET['cmty-sharedexp'] . '</li>' : '';
      isset( $_GET['cmty-alcohol'] ) ? $search_output .= '<li><span class="dir-search-fields">Alcohol Use:</span> ' . $_GET['cmty-alcohol'] . '</li>' : '';
      isset( $_GET['cmty-tobacco'] ) ? $search_output .= '<li><span class="dir-search-fields">Tobacco Use:</span> ' . $_GET['cmty-tobacco'] . '</li>' : '';
      isset( $_GET['cmty-healthcare'] ) ? $search_output .= '<li><span class="dir-search-fields">Healthcare Styles:</span> ' . $_GET['cmty-healthcare'] . '</li>' : '';
      isset( $_GET['cmty-idleader'] ) ? $search_output .= '<li><span class="dir-search-fields">Identified leader:</span> ' . $_GET['cmty-idleader'] . '</li>' : '';
      isset( $_GET['cmty-shareddiet'] ) ? $search_output .= '<li><span class="dir-search-fields">Importance of a shared diet:</span> ' . $_GET['cmty-shareddiet'] . '</li>' : '';
      isset( $_GET['cmty-debt'] ) ? $search_output .= '<li><span class="dir-search-fields">Open to members with pre-existing debt:</span> ' . $_GET['cmty-debt'] . '</li>' : '';
      isset( $_GET['cmty-sharedmeals'] ) ? $search_output .= '<li><span class="dir-search-fields">Frequency of shared meals:</span> ' . $_GET['cmty-sharedmeals'] . '</li>' : '';
      isset( $_GET['cmty-sharedarea'] ) ? $search_output .= '<li><span class="dir-search-fields">Shared common area (house, building, or space):</span> ' . $_GET['cmty-sharedarea'] . '</li>' : '';
      isset( $_GET['cmty-leadergroup'] ) ? $search_output .= '<li><span class="dir-search-fields">Core leadership group:</span> ' . $_GET['cmty-leadergroup'] . '</li>' : '';
      isset( $_GET['cmty-minlabor'] ) ? $search_output .= '<li><span class="dir-search-fields">Min labor hours per week:</span> ' . $_GET['cmty-minlabor'] . '</li>' : '';
      isset( $_GET['cmty-maxlabor'] ) ? $search_output .= '<li><span class="dir-search-fields">Max labor hours per week:</span> ' . $_GET['cmty-maxlabor'] . '</li>' : '';
      isset( $_GET['cmty-minjoinfee'] ) ? $search_output .= '<li><span class="dir-search-fields">Max required join fee amount:</span> ' . $_GET['cmty-minjoinfee'] . '</li>' : '';
      isset( $_GET['cmty-maxjoinfee'] ) ? $search_output .= '<li><span class="dir-search-fields">Min required join fee amount:</span> ' . $_GET['cmty-maxjoinfee'] . '</li>' : '';
      isset( $_GET['cmty-minmemb'] ) ? $search_output .= '<li><span class="dir-search-fields">Min number of adult members:</span> ' . $_GET['cmty-minmemb'] . '</li>' : '';
      isset( $_GET['cmty-maxmemb'] ) ? $search_output .= '<li><span class="dir-search-fields">Max number of adult members:</span> ' . $_GET['cmty-maxmemb'] . '</li>' : '';
      isset( $_GET['cmty-minongoingfee'] ) ? $search_output .= '<li><span class="dir-search-fields">Min required ongoing fee amount:</span> ' . $_GET['cmty-minongoingfee'] . '</li>' : '';
      isset( $_GET['cmty-maxongoingfee'] ) ? $search_output .= '<li><span class="dir-search-fields">Max required ongoing fee amount:</span> ' . $_GET['cmty-maxongoingfee'] . '</li>' : '';
      isset( $_GET['cmty-minchildren'] ) ? $search_output .= '<li><span class="dir-search-fields">Min number of children:</span> ' . $_GET['cmty-minchildren'] . '</li>' : '';
      isset( $_GET['cmty-maxchildren'] ) ? $search_output .= '<li><span class="dir-search-fields">Max number of children:</span> ' . $_GET['cmty-maxchildren'] . '</li>' : '';
      isset( $_GET['cmty-minnonmemb'] ) ? $search_output .= '<li><span class="dir-search-fields">Min number of non-member residents:</span> ' . $_GET['cmty-minnonmemb'] . '</li>' : '';
      isset( $_GET['cmty-maxnonmemb'] ) ? $search_output .= '<li><span class="dir-search-fields">Max number of non-member residents:</span> ' . $_GET['cmty-maxnonmemb'] . '</li>' : '';
      $search_output .= '</ul></div>';
      return $search_output;
    }
  }


  /** Return the 12 Tribes Child-Discipline Statement
   *
   * @return string HTML containing the statement.
   */
  public static function twelve_tribes_statement() {
    $content = <<<HTML
<h3>Child Rearing Practice of the Twelve Tribes Communities</h3>
<p>We train our children according to the Word of God as recorded in the Bible.
Part of this training is correcting our little ones for errant behavior.
Setting boundaries for our children is an integral part of building good
character into them at a young age. Loving protection for children is of
supreme importance to us. We do not tolerate violence or abuse, whether
physical, psychological, or verbal, nor do we condone disrespectful or
rebellious behavior. We believe it is the God-given right and responsibility of
parents to discipline their children if they are disobedient or disrespectful
to parental guidance.</p>
<p>God is love. He loves children and His word is clear on what it means to
love your child as Proverbs 13:24 says: &ldquo;Whoever spares the rod
<em>hates</em> his son, but he who <em>loves</em> him is diligent to discipline
him.&rdquo; Proverbs 23:13-14 makes it clear that spanking is an act of love to
save your children from the destruction of their souls, and restore them to the
way they should go. &ldquo;Do not withhold discipline from a child; if you
strike him with a rod ... you will save his soul from destruction.&rdquo; And
Proverbs 22:6 states that if you &ldquo;train up a child in the way he should
go, when he is old he will not depart from it.&rdquo;</p>
<p>The Bible does not describe the rod itself, but surely God did not intend
something that would damage a child. We use a thin, reed-like rod, which stings
but causes no damage or injury. We have purposefully chosen this method,
inspired by God’s love, as part of our child rearing practices because His Word
commands us. We see the good fruit in our children, which is recognized all
over the world, even by those who don’t understand it.</p>
<p>Because we love our children, we discipline them only for attitudes and
actions they know to be wrong, and only after the child admits to wrongdoing
and is willing to receive discipline. Godly discipline is always followed by
forgiveness, reconciliation, and encouragement.</p>
<p><em>For a fuller statement about our thinking about Child Discipline, please
visit: <a href="http://twelvetribes.org/articles/on-child-discipline" target='_blank'>
http://twelvetribes.org/articles/on-child-discipline</a>.</em></p>

<p><em>Publisher’s Note: FIC has a policy of not listing communities in our
Directory that advocate violent practices, and there is controversy over
whether the Twelve Tribes Child Discipline practice crosses that line.
While we are convinced of the sincerity of their belief that their practice
is not violent, we are also aware of visitors and ex-members who hold that
it is. In recognition of this controversy, the Twelve Tribes leadership
agreed to have this note referenced as a regular part of each community’s
listing, so that users of the Directory could be more fully informed and
make their own decision about this important matter.</em></p>
HTML;
    return str_replace('  ', ' ', str_replace("\n", ' ', $content));
  }


  /* Display 3 Featured FIC-Member Communities for the Main Page */
  public static function featured_communities() {
    global $wpdb;

    $query = "
      SELECT
          posts.post_title, posts.post_name, status.meta_value AS community_status,
          countries.meta_value AS country, states.meta_value AS state,
          provinces.meta_value AS province, cities.meta_value AS city,
          visitors.meta_value AS visitors_accepted, members.meta_value AS members_accepted,
          types.meta_value AS type, images.meta_value AS image
      FROM {$wpdb->prefix}frm_items as entries
      INNER JOIN {$wpdb->prefix}posts AS posts ON entries.post_id=posts.ID
      INNER JOIN
        (SELECT meta_value, item_id FROM {$wpdb->prefix}frm_item_metas WHERE field_id=291)
        AS status ON status.item_id=entries.id
      INNER JOIN
        (SELECT meta_value, item_id FROM {$wpdb->prefix}frm_item_metas WHERE field_id=424)
        AS countries ON countries.item_id=entries.id
      INNER JOIN
        (SELECT meta_value, item_id FROM {$wpdb->prefix}frm_item_metas WHERE field_id=815)
        AS states ON states.item_id=entries.id
      INNER JOIN
        (SELECT meta_value, item_id FROM {$wpdb->prefix}frm_item_metas WHERE field_id=816)
        AS provinces ON provinces.item_id=entries.id
      INNER JOIN
        (SELECT meta_value, item_id FROM {$wpdb->prefix}frm_item_metas WHERE field_id=427)
        AS cities ON cities.item_id=entries.id
      INNER JOIN
        (SELECT meta_value, item_id FROM {$wpdb->prefix}frm_item_metas WHERE field_id=256)
        AS visitors ON visitors.item_id=entries.id
      INNER JOIN
        (SELECT meta_value, item_id FROM {$wpdb->prefix}frm_item_metas WHERE field_id=257)
        AS members ON members.item_id=entries.id
      INNER JOIN
        (SELECT meta_value, item_id FROM {$wpdb->prefix}frm_item_metas WHERE field_id=262)
        AS types ON types.item_id=entries.id
      INNER JOIN
        (SELECT meta_value, item_id FROM {$wpdb->prefix}frm_item_metas WHERE field_id=228)
        AS images ON images.item_id=entries.id
      INNER JOIN
        (SELECT meta_value, item_id FROM {$wpdb->prefix}frm_item_metas WHERE field_id=933 AND meta_value='Yes')
        AS fic_member ON fic_member.item_id=entries.id
      WHERE entries.form_id=2 AND posts.post_status='publish'
      ORDER BY RAND()
      LIMIT 3
      ;";
    $results = $wpdb->get_results($query);

    $output = "<div class='row featured-communities'>";
    foreach ($results as $listing) {
      if (is_array(unserialize($listing->type))) {
        $types = implode(', ', unserialize($listing->type));
      } else {
        $types = $listing->type;
      }
      if ($types !== "") {
        $types = "<b>Community Types:</b> " . $types;
      }
      $image_src = wp_get_attachment_image_src($listing->image, 'card-image')[0];
      $region = $listing->state == "" ? $listing->province : $listing->state;
      $location = implode(', ', array_filter(array($listing->city, $region, $listing->country)));
      if (strpos($listing->community_status, "Established") !== false) {
        $status = "Established (4+ adults, 2+ years)";
      } else if (strpos($listing->community_status, "Forming") !== false) {
        $status = "Forming";
      } else if (strpos($listing->community_status, "Re-forming") !== false) {
        $status = "Re-Forming";
      } else {
        $status = "Disbanded";
      }
      $output .= <<<HTML
        <div class='col-md-8'>
          <div class="card h-100">
            <a href="/directory/{$listing->post_name}/" class="h-100">
              <div class='text-center d-flex flex-column mb-3 community-img'
                   style='background-image:url("{$image_src}");'>
              </div>
              <div class="card-body px-4 pb-4 mt-auto">
                <h3 class="card-title">{$listing->post_title}</h3>
                <h5 class="card-text">{$location}</h5>
                <h6><em>{$status}</em></h6>
                <b>Visitors Accepted:</b> {$listing->visitors_accepted}
                <br />
                <b>Open to New Members:</b> {$listing->members_accepted}
                <br />
                {$types}
              </div>
            </a>
          </div>
        </div>
HTML;
    }

    $output .= "</div>";

    return $output;
  }

}

add_shortcode('directory_elm', array('DirectoryShortcodes', 'render_elm'));
add_shortcode('directory_show_edit_link_if_admin', array('DirectoryShortcodes', 'show_edit_link'));
add_shortcode('directory_verify_listing_link', array('DirectoryShortcodes', 'verify_link'));
add_shortcode('frm_cmty_name', array('DirectoryShortcodes', 'community_name'));
add_shortcode('frm_cmty_link', array('DirectoryShortcodes', 'community_link'));
add_shortcode('youlist', array('DirectoryShortcodes', 'embed_youtube'));
add_shortcode('show_directory_search_filters', array('DirectoryShortcodes', 'search_filters'));
add_shortcode('show_directory_geo_list', array('DirectoryShortcodes', 'geo_list'));
add_shortcode('directory_search_terms', array('DirectoryShortcodes', 'search_terms'));
add_shortcode('directory_twelve_tribes_statement', array('DirectoryShortcodes', 'twelve_tribes_statement'));
add_shortcode('directory_featured_communities', array('DirectoryShortcodes', 'featured_communities'));

?>
