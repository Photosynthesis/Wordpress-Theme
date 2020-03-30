<?php


if($_POST['export_action'] == 'export' && $_POST['config']){
  $raw_json = $_POST['config'];
}else{
  $raw_json = file_get_contents(dirname( __FILE__ ).'/configurations/default.json');
}

$config = json_decode($raw_json,true);

if(!$config){
  echo $config;
  echo $raw_json;
  die("Invalid configuration JSON");
}

define('WP_USE_THEMES', false);

$base = dirname(dirname(__FILE__));
require($base.'../../../../../wp-load.php');

if(!current_user_can('manage_options')) {
  die("Permission denied");
}

$default_config = array(
  'mode' => "export",
  'conditions' => array(
   ['2-a-cmty-forming','includes','Established'],
   ['fic-member','doesNotInclude','Yes'],
   ['1-adr-country','includes','United States'],
  ),
  'match_type' => "AND",
  'callbacks' => array(
    'full_address' => 'full_address'
  ),
  'include_fields' => array(
    'title',
    'user_email',
    '1-h-contact-email',
    '1-k-alt-email',
    'post_status',
    '1-j-contact-name',
    'full_address',
    '2-a-cmty-forming',
    '1-adr-country',
    '1-adr-city2',
    '1-adr-city',
    '1-adr-state',
    '1-adr-zip-code',
    '1-adr-street1',
    '1-adr-street2',
    'fic-member'
  )
);

foreach ($config as $key => $value) {
  $default_config[$key] = $value;
}
$config = $default_config;

function callback_full_address($title,$community){
  $fields = array(
    '1-adr-street1',
    '1-adr-street2',
    '1-adr-city',
    '1-adr-city2',
    '1-adr-state',
    '1-adr-zip-code',
    '1-adr-country',
  );
  $out = "";
  $inter = "";
  foreach ($fields as $key) {
    if($community[$key]){
      $out .= $inter.$community[$key];
      $inter = ", ";
    }
  }
  return $out;
}

######################## END OF SETTINGS ########################

$errors = array();
$stats = array();

if($_POST['export_action'] != 'export'){
  ?>
  <html>
  <head>
    <title>Configurable Directory Export</title>
  </head>
  <body>
  <form action="" method="POST">
    <textarea style="max-width:800px; height:500px; width:100%;padding:15px;" name="config"><?php echo $raw_json ?></textarea><br>
    <input type="submit" value="Export"/>
    <input type="hidden" name="export_action" value="export" />
  </form>

<br><br>
  <b>Available fields</b>
  <table class="nowrap" cellspacing="0">
<thead><tr><th title="3uOgy46w_frm_fields.name">field_name</th><th title="3uOgy46w_frm_fields.field_key">field_key</th><th title="3uOgy46w_frm_fields.id">field_id</th></tr></thead>
<tbody><tr><td>What percentage of energy does your community currently source from renewables?</td><td>3-l-cur-renew-energy</td><td>299</td></tr><tr class="odd"><td>Community name</td><td>1-a-cmtyname</td><td>9</td></tr><tr><td>Additional "Community Membership" Comments:</td><td>5-g-memb-comments</td><td>304</td></tr><tr class="odd"><td>Number of planned residences</td><td>3-c-plan-residences</td><td>264</td></tr><tr><td>HIDDEN - How much land does your Community have? (indicate acres or hectares)</td><td>3-d-land-acres</td><td>267</td></tr><tr class="odd"><td>What percentage of energy does your community aspire to source from renewables?</td><td>3-m-plan-renew-energy</td><td>297</td></tr><tr><td>Facebook profile (URL)</td><td>1-e-fb-url</td><td>197</td></tr><tr class="odd"><td>Do you have internet access at your community?</td><td>8-internet</td><td>972</td></tr><tr><td>How good is the mobile phone signal at your community?</td><td>8-cell-service</td><td>973</td></tr><tr class="odd"><td>Is your internet access at your community fast?</td><td>8-internet-speed</td><td>974</td></tr><tr><td>How many Children are in your community?</td><td>5-b-child-members</td><td>420</td></tr><tr class="odd"><td>Please briefly describe the membership process for your community.</td><td>5-g-member-proc</td><td>421</td></tr><tr><td>Twitter profile (URL)</td><td>1-f-twit-url</td><td>198</td></tr><tr class="odd"><td>Which sources of renewable energy does your community use?</td><td>3-n-energy-sources</td><td>295</td></tr><tr><td>Contact Email (public)</td><td>1-h-contact-email</td><td>199</td></tr><tr class="odd"><td>Other profile, blog, or link (URL)</td><td>1-g-other-soc-url</td><td>200</td></tr><tr><td>Contact Phone (public)</td><td>1-i-contact-phone</td><td>201</td></tr><tr class="odd"><td>Contact Name(s) (public)</td><td>1-j-contact-name</td><td>202</td></tr><tr><td>Make Directory Listing Public</td><td>10-a-profile-pub</td><td>218</td></tr><tr class="odd"><td>Do you want your community to appear in the Communities Directory book?</td><td>10-b-incl-in-dir-book</td><td>219</td></tr><tr><td>Number of Housing Units</td><td>3-house-units</td><td>206</td></tr><tr class="odd"><td>I. Community Information</td><td>7my2c5</td><td>207</td></tr><tr><td>II. About your Community</td><td>x69sxa</td><td>208</td></tr><tr class="odd"><td>VII. Community Sustainability Practices</td><td>f6rdng</td><td>209</td></tr><tr><td>IV. Community Membership</td><td>3n3pi1</td><td>211</td></tr><tr class="odd"><td>V. Community Governance</td><td>vsvaww</td><td>212</td></tr><tr><td>VI. Community Economics</td><td>qavate</td><td>213</td></tr><tr class="odd"><td>VIII. Community Lifestyle</td><td>mqj3dt</td><td>214</td></tr><tr><td>X. Community Directory Listing Confirmation</td><td>1wue79</td><td>217</td></tr><tr class="odd"><td>IX. Community Images &amp; Videos</td><td>diak8f</td><td>216</td></tr><tr><td>Subscribe to FIC Newsletter</td><td>10-c-fic-newsletter</td><td>220</td></tr><tr class="odd"><td>Receive Postal Mail from FIC</td><td>10-d-fic-post-mail</td><td>221</td></tr><tr><td>Can we share your contact information with like-minded organizations?</td><td>10-e-partner-info</td><td>222</td></tr><tr class="odd"><td>Allow Comments</td><td>10-f-allow-comments</td><td>223</td></tr><tr><td>Youtube Videos</td><td>9-c-youtube1</td><td>225</td></tr><tr class="odd"><td>State</td><td>1-adr-state</td><td>815</td></tr><tr><td>Hidden Last verified date</td><td>11-hidden-last-verified</td><td>813</td></tr><tr class="odd"><td>Website address (URL -- must start with http://)</td><td>1-d-web-url</td><td>227</td></tr><tr><td>What percentage of your food does your community currently produce?</td><td>8-b-cur-food-prod</td><td>294</td></tr><tr class="odd"><td>Primary Image</td><td>9-a-primary-img</td><td>228</td></tr><tr><td>Gallery images</td><td>9-b-img-gallery</td><td>229</td></tr><tr class="odd"><td>Dietary Preferences supported or allowed:</td><td>8-e-diet-prefs</td><td>236</td></tr><tr><td>Are members of your community expected to share a common diet?</td><td>8-f-eat-same</td><td>237</td></tr><tr class="odd"><td>Are community meals accommodating of special diets?</td><td>8-special-diets</td><td>975</td></tr><tr><td>Alcohol Use</td><td>8-g-alcohol</td><td>238</td></tr><tr class="odd"><td>Tobacco Use</td><td>8-h-tobacco</td><td>239</td></tr><tr><td>Has your community leased, purchased, or secured the land?</td><td>3-land-status</td><td>954</td></tr><tr class="odd"><td>Number of current residences</td><td>3-b-cur-residences</td><td>263</td></tr><tr><td>To what extent do members of your community share income?</td><td>7-a-share-income</td><td>241</td></tr><tr class="odd"><td>Additional "Community Housing" Comments:</td><td>3-o-comments</td><td>303</td></tr><tr><td>Is your community willing to consider people for membership with pre-existing debt?</td><td>7-c-memb-debt</td><td>243</td></tr><tr class="odd"><td>Are members in your community regularly required to contribute labor to the group?</td><td>7-d-contrib-labor</td><td>244</td></tr><tr><td>How many hours are required weekly?</td><td>7-e-labor-hours</td><td>245</td></tr><tr class="odd"><td>Does your community require a fee or buy-in for joining?</td><td>7-f-join-fee-bool</td><td>246</td></tr><tr><td>How much is the fee or buy-in for joining, in US dollars?</td><td>7-g-join-fee-amt</td><td>247</td></tr><tr class="odd"><td>Do members of your community regularly contribute dues, fees, or share in expenses?</td><td>7-h-regular-fee-bool</td><td>248</td></tr><tr><td>How much is the ongoing contribution (per month), in US dollars?</td><td>7-i-ongoing-fee-amt</td><td>249</td></tr><tr class="odd"><td>Which decision-making method does your Community primarily use?</td><td>6-a-decision-method</td><td>250</td></tr><tr><td>Does your community have an identified leader?</td><td>6-b-leader</td><td>251</td></tr><tr class="odd"><td>Does your community have a core leadership group?</td><td>6-c-leader-group</td><td>252</td></tr><tr><td>Additional "Community Diet" Comments:</td><td>8-g-diet-comments</td><td>307</td></tr><tr class="odd"><td>How many Adults are in your community?</td><td>5-a-adult-members</td><td>254</td></tr><tr><td>How many people live day to day at your community who are not members?</td><td>5-c-non-members</td><td>255</td></tr><tr class="odd"><td>Is your community open to visitors?</td><td>5-d-new-viz</td><td>256</td></tr><tr><td>Is your community open to new members?</td><td>5-f-new-members</td><td>257</td></tr><tr class="odd"><td>Please describe what someone should do if they would like to visit your community.</td><td>5-e-viz-proc</td><td>258</td></tr><tr><td>What spiritual traditions are practiced at your community?</td><td>5-f-spiritual</td><td>259</td></tr><tr class="odd"><td>Healthcare Coverage Options</td><td>5-h-healthcare</td><td>282</td></tr><tr><td>Community Type(s)</td><td>2-c-cmty-type</td><td>262</td></tr><tr class="odd"><td>Is your community's location primarily:</td><td>2-b-cmty-rural</td><td>952</td></tr><tr><td>What percentage of your food is currently sourced locally? (within 150 miles)</td><td>8-d-food-local</td><td>301</td></tr><tr class="odd"><td>Who owns title to the land or controls the lease?</td><td>3-e-land-owner</td><td>268</td></tr><tr><td>Other Community Network or Organization Affiliations</td><td>2-l-other-afil</td><td>279</td></tr><tr class="odd"><td>Community Affiliations</td><td>2-j-cmty-affil</td><td>278</td></tr><tr><td>Developer (of Community or Cohousing Project)</td><td>3-developer</td><td>269</td></tr><tr class="odd"><td>Architect (of Community Building(s), Cohousing Development, Housing, etc.)</td><td>3-architect</td><td>270</td></tr><tr><td>Commercial Lender (if used)</td><td>3-lender</td><td>271</td></tr><tr class="odd"><td>Do you share a common area (house, building, or space)?</td><td>3-common-area</td><td>272</td></tr><tr><td>What year did your group begin planning your community?</td><td>2-d-year-plan</td><td>273</td></tr><tr class="odd"><td>What year did your group begin living together, or when do you plan to?</td><td>2-f-year-live</td><td>274</td></tr><tr><td>III. Community Housing</td><td>388nn8</td><td>280</td></tr><tr class="odd"><td>If your community is re-forming, you may share more about this here:</td><td>2-reforming-info</td><td>276</td></tr><tr><td>Community Description</td><td>2-h-cmty-desc</td><td>277</td></tr><tr class="odd"><td>Education Options</td><td>5-g-education</td><td>281</td></tr><tr><td>Community Network or Organization Affiliations</td><td>2-k-network-afil</td><td>283</td></tr><tr class="odd"><td>Alternative Email (not public, just for backup)</td><td>1-k-alt-email</td><td>284</td></tr><tr><td>Make address public or private</td><td>1-c-adr-pub</td><td>285</td></tr><tr class="odd"><td>Mission Statement/Community Focus</td><td>2-i-mission-statement</td><td>286</td></tr><tr><td>The year when construction was (or will be) completed</td><td>3-year-const-compl</td><td>293</td></tr><tr class="odd"><td>Additional "Community Government" Comments:</td><td>6-d-govt-comments</td><td>305</td></tr><tr><td>Additional "About Your Community" Comments:</td><td>2-m-abt-cmty-comments</td><td>302</td></tr><tr class="odd"><td>Additional "Community Economics" comments:</td><td>7-j-econ-comments</td><td>306</td></tr><tr><td>Do you consider your intentional community:</td><td>2-a-cmty-forming</td><td>291</td></tr><tr class="odd"><td>User ID</td><td>uwaf05</td><td>430</td></tr><tr><td>If you selected No above, please check all relevant boxes below:</td><td>4-b-fhl-no</td><td>414</td></tr><tr class="odd"><td>What percentage of your food does your community aspire to produce?</td><td>8-c-plan-food-prod</td><td>315</td></tr><tr><td>What is your community's relationship to energy infrastructure?</td><td>7-grid-status</td><td>966</td></tr><tr class="odd"><td>Does your community follow Fair-Housing Laws?</td><td>4-a-fhl</td><td>412</td></tr><tr><td>Hidden auto increment variable</td><td>0-auto-increment</td><td>422</td></tr><tr class="odd"><td>Country</td><td>1-adr-country</td><td>424</td></tr><tr><td>Street address line 1</td><td>1-adr-street1</td><td>425</td></tr><tr class="odd"><td>Street address line 2</td><td>1-adr-street2</td><td>426</td></tr><tr><td>City/Town/Village</td><td>1-adr-city</td><td>427</td></tr><tr class="odd"><td>State/Province</td><td>1-adr-prov</td><td>428</td></tr><tr><td>Postal code</td><td>1-adr-zip-code</td><td>429</td></tr><tr class="odd"><td>Hidden original directory listing ID number</td><td>0-hidden-old-id</td><td>698</td></tr><tr><td>Hidden Display on CoHo US</td><td>11-hidden-coho-us</td><td>688</td></tr><tr class="odd"><td>Hidden Slug field</td><td>11-hidden-slug</td><td>431</td></tr><tr><td>Is your community affiliated with a specific religion or spiritual path?</td><td>5-spirit-bool</td><td>814</td></tr><tr class="odd"><td>Hidden Full Address Field</td><td>11-hidden-full-adr</td><td>681</td></tr><tr><td>Hidden Approximate Address Field</td><td>11-hidden-approx-adr</td><td>682</td></tr><tr class="odd"><td>Map Address</td><td>1-l-map</td><td>683</td></tr><tr><td>Latitude</td><td>1-m-latitude</td><td>684</td></tr><tr class="odd"><td>Longitude</td><td>1-n-longitude</td><td>685</td></tr><tr><td>Community Map Location (if you want a map included on your listing)</td><td>6z83ig</td><td>686</td></tr><tr class="odd"><td>Hidden Combined Lat,Lng Field</td><td>11-hidden-latlng</td><td>687</td></tr><tr><td>Hidden Display on Nica</td><td>11-hidden-nica</td><td>689</td></tr><tr class="odd"><td>Hidden Has someone called the community?</td><td>11-hidden-called</td><td>690</td></tr><tr><td>Hidden admin notes</td><td>11-hidden-admin-notes</td><td>691</td></tr><tr class="odd"><td>Hidden FHL Review Status</td><td>11-hidden-fhl-reviewed</td><td>692</td></tr><tr><td>Hidden FHL Notes</td><td>11-hidden-fhl-notes</td><td>693</td></tr><tr class="odd"><td>Hidden FHL Edited Status</td><td>11-hidden-fhl-edited</td><td>694</td></tr><tr><td>Hidden ORIGINAL (non FHL compliant) Description</td><td>11-hidden-fhl-orig-desc</td><td>695</td></tr><tr class="odd"><td>What percentage of your population is comprised of female-identified individuals?</td><td>5-i-percent-female</td><td>709</td></tr><tr><td>How frequently do all (or many) of your community members eat together?</td><td>8-a-eat-together</td><td>697</td></tr><tr class="odd"><td>Hidden Is the Website Address (URL) good?</td><td>11-hidden-url-good</td><td>699</td></tr><tr><td>Hidden Should this be a resource?</td><td>11-hidden-should-be-resource</td><td>700</td></tr><tr class="odd"><td>Hidden Is the public contact email address good?</td><td>11-hidden-contact-email-good</td><td>701</td></tr><tr><td>Hidden Contact (physical) address status</td><td>11-hidden-contact-adr-status</td><td>702</td></tr><tr class="odd"><td>When did your community reform?</td><td>2-year-reform</td><td>703</td></tr><tr><td>If your Community has disbanded, you may share more about this here:</td><td>2-disbanded-info</td><td>704</td></tr><tr class="odd"><td>What percentage of your population is comprised of male-identified individuals?</td><td>5-h-percent-male</td><td>708</td></tr><tr><td>When did your Community disband?</td><td>2-q-year-disbanded</td><td>705</td></tr><tr class="odd"><td>What percentage of your population is comprised of trans gender identified individuals?</td><td>5-j-percent-trans</td><td>710</td></tr><tr><td>Hidden ARCHIVE Open to new children</td><td>11-archive-open-new-children</td><td>711</td></tr><tr class="odd"><td>Hidden ARCHIVE Restrictions on relationships between consenting adults</td><td>11-archive-relation-restricts</td><td>712</td></tr><tr><td>Hidden ARCHIVE Age focus</td><td>11-archive-age-focus</td><td>713</td></tr><tr class="odd"><td>Hidden ARCHIVE Queer friendly</td><td>11-archive-queer-friendly</td><td>714</td></tr><tr><td>Community Keywords</td><td>2-n-cmty-keywords</td><td>716</td></tr><tr class="odd"><td>Your Community's Business, Organization, or Project Link (URL)</td><td>1-l-biz-org-url</td><td>717</td></tr><tr><td>If you're a Cohousing community, what is your site status?</td><td>3-cohousing-status</td><td>718</td></tr><tr class="odd"><td>If you share a common area, approximately how big is it (in square feet)?</td><td>3-common-area-sqft</td><td>719</td></tr><tr><td>Other Links</td><td>1-r-other-links</td><td>720</td></tr><tr class="odd"><td>List of Youtube or Vimeo videos</td><td>9-videos-list</td><td>721</td></tr><tr><td>Hidden Updated By (user)</td><td>11-hidden-updated-by</td><td>723</td></tr><tr class="odd"><td>Hidden Created By (user)</td><td>11-hidden-created-at2</td><td>727</td></tr><tr><td>Hidden Updated At (date)</td><td>11-hidden-updated-at</td><td>726</td></tr><tr class="odd"><td>Hidden Created At (date)</td><td>11-hidden-created-at</td><td>725</td></tr><tr><td>List of Youtube videos (by video ID)</td><td>9-youtube-ids</td><td>812</td></tr><tr class="odd"><td>State or Province -- please also enter that info here</td><td>rsoa7p</td><td>816</td></tr><tr><td>Hidden Call notes</td><td>11-hidden-call-notes</td><td>673</td></tr><tr class="odd"><td>Post Status</td><td>glicpv</td><td>920</td></tr><tr><td>Hidden Field</td><td>is7tv9</td><td>919</td></tr><tr class="odd"><td>End Section</td><td>go2lj5</td><td>922</td></tr><tr><td>End Section</td><td>8chihw</td><td>923</td></tr><tr class="odd"><td>End Section</td><td>nje893</td><td>925</td></tr><tr><td>End Section</td><td>7ofo4y</td><td>926</td></tr><tr class="odd"><td>End Section</td><td>x2adir</td><td>924</td></tr><tr><td>End Section</td><td>y2es8c</td><td>927</td></tr><tr class="odd"><td>End Section</td><td>bgh1ag</td><td>929</td></tr><tr><td>End Section</td><td>bgh1ag2</td><td>930</td></tr><tr class="odd"><td>End Section</td><td>bgh1ag3</td><td>931</td></tr><tr><td>End Section</td><td>bgh1ag4</td><td>932</td></tr><tr class="odd"><td>Hidden Is FIC Member</td><td>fic-member</td><td>933</td></tr><tr><td>Programs and Activities</td><td>2-programs-activities</td><td>950</td></tr><tr class="odd"><td>Contact Phone (private)</td><td>h8q21u</td><td>949</td></tr><tr><td>Community address or mailing address?</td><td>1-address-type</td><td>953</td></tr><tr class="odd"><td>Is there an expectation that community members follow certain healthcare practices?</td><td>rcp2zy</td><td>970</td></tr><tr><td>Hidden Date Became FIC Member</td><td>fic-membership-date</td><td>977</td></tr><tr class="odd"><td>If residences are available to your members, what types?</td><td>3-cur-resid-types</td><td>956</td></tr><tr><td>If residences are planned for your members, what types?</td><td>3-plan-resid-types</td><td>957</td></tr><tr class="odd"><td>How can one access the housing in your community?</td><td>3-a-cmty-housing</td><td>958</td></tr><tr><td>If so, please describe:</td><td>8-health-care-expct-descr</td><td>971</td></tr><tr class="odd"><td>Additional "Community Lifestyle" Comments:</td><td>8-lifestyle-comments</td><td>976</td></tr><tr><td>Do you share common facilities?</td><td>8-common-facil</td><td>967</td></tr><tr class="odd"><td>Are your community members expected to practice this tradition?</td><td>8-spirit-practice-expct</td><td>969</td></tr><tr><td>Hidden Last Verified Date</td><td>last-verified</td><td>978</td></tr><tr class="odd"><td>How much land does your Community have?</td><td>land-size-amount</td><td>981</td></tr><tr><td>Land Size Units</td><td>land-size-units</td><td>982</td></tr><tr class="odd"><td>Hidden Date FIC Membership Expires</td><td>g365c</td><td>985</td></tr><tr><td>What percentage of your population is comprised of non binary identified individuals?</td><td>u5l5m</td><td>991</td></tr><tr class="odd"><td>Hidden Update Email Date</td><td>update-email-date</td><td>992</td></tr></tbody></table>
</body>
</html>
  <?php
  exit;
}

global $wpdb;

$pfx = "3uOgy46w_";

// Get all the relevant form meta fields
$sql = "SELECT frm_fields.name AS field_name, frm_fields.field_key AS field_key, frm_fields.ID AS field_id
FROM {$pfx}frm_fields frm_fields
WHERE frm_fields.form_id = 2";

$meta_fields = $wpdb->get_results( $sql, ARRAY_A );

// Reshape for accessibility
$mf2 = array();
foreach ($meta_fields as $field) {
  $mf2[$field['field_key']] = $field;
}
$meta_fields = $mf2;


// Get all the directory posts
$posts_sql = "SELECT
p.post_title AS title,
p.ID AS post_id,
p.post_status,
p.post_date,
u.ID AS user_id,
u.user_email AS user_email,
u.display_name AS user_name
FROM {$pfx}posts p
JOIN {$pfx}users u
ON u.ID = p.post_author
WHERE p.post_status = 'publish'
AND p.post_type = 'directory';
";

$posts = $wpdb->get_results( $posts_sql, ARRAY_A );


// Add the meta data
$communities = array();

$count = 0;

foreach ($posts as $key => $community) {

  $form_sql = "SELECT fields.name AS field_name, metas.meta_value AS value, fields.field_key AS field_key
  FROM {$pfx}frm_items items, {$pfx}frm_item_metas metas, {$pfx}frm_fields fields
  WHERE items.post_id = {$community['post_id']}
  AND items.ID = metas.item_id
  AND fields.ID = metas.field_id
  ";

  $frm_data = $wpdb->get_results($form_sql, ARRAY_A);

  // Reorganize for accessibility
  $frm_data_2 = array();
  // We seem to have to loop this twice, because wpdb doesn't let us set keys. Would be more efficient to just have one template array we reuse here.
  foreach ($meta_fields as $field) {
    $frm_data_2[$field['field_key']] = "";
  }

  foreach ($frm_data as $key => $row) {
    $frm_data_2[$row['field_key']] = $row['value'];
  }

  // Turns out the form item is where the actual update date is stored...
  $frm_item_data = $wpdb->get_results("SELECT * FROM {$pfx}frm_items WHERE post_id = '{$community['post_id']}'", ARRAY_A);

  $frm_item_data = $frm_item_data[0];

  $frm_data_2['item_updated_at'] = $frm_item_data['updated_at'];

  $communities[] = array_merge($community,$frm_data_2);

  $count++;

}

// Filter records
if(fic_safe_count($config['include_fields']) < 1){
  // Add post fields
  foreach (array_keys($communities[0]) as $field_name) {
    $include_fields[] = $field_name;
  }
  // Add form meta fields
  foreach ($meta_fields as $row) {
    $include_fields[] = $row['field_key'];
  }
}else{
  $include_fields = $config['include_fields'];
}


$export_data = array();

$stats['included'] = 0;
$stats['skipped'] = 0;

foreach ($communities as $c) {
  if(count($config['conditions']) > 0){

    $matches = false;
    $unmatches = false;
    $include = false;

    foreach ($config['conditions'] as $condition) {
      if(check_condition($c,$condition)){
        $matches = true;
      }else{
        $unmatches = true;
      }
    }

    if($matches == true && ($config['matchtype'] == "OR" || !$unmatches)){
      $include = true;
    }
  }else{
    $include = true;
  }

  if($include == true){
      $out = array();

      foreach ($include_fields as $field) {
        if($config['callbacks'][$field]){
          if(is_callable('callback_'.$config['callbacks'][$field])){
            $func_name = 'callback_'.$config['callbacks'][$field];
            $out[$field] = $func_name($c[$field],$c);
          }else{
            $errors[] = "Uncallable callback: ".$config['callbacks'][$field];
          }
        }else{
          $out[$field] = $c[$field];
        }
      }
      $stats['included']++;
      $export_data[] = $out;
    }else{
      $stats['skipped']++;
    }
  }

if($mode == 'testing'){
  echo "<pre>Testing:";
  print_r($include_fields);
  print_r($config['conditions']);
  echo "\n Match type: {$config['match_type']} \n";
  print_r($errors);
  print_r($stats);
  echo $posts_sql;
  print_r($export_data);
  print_r($meta_fields);
  exit;
}

//$headers = array_keys($communities[0]);

if(count($export_data) < 1){
  die("No data to export");
}

foreach ($include_fields as $field) {
  if($meta_fields[$field]){
    $headers[] = $meta_fields[$field]['field_name'];
  }else{
    $headers[] = $field;
  }
}

$datetime = date('Y-m-d_His');

$fp = fopen('php://output', 'w');
if ($fp && $export_data) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="directory_contacts_export_'.$datetime.'.csv"');
    header('Pragma: no-cache');
    header('Expires: 0');
    fputcsv($fp, $headers);
    foreach ($export_data as $row) {
        fputcsv($fp, array_values($row));
    }
    die;
}



function check_condition($node,$condition){

  list($subject, $predicate, $object) = $condition;

  if(!isset($node[$subject])) return false;

  switch ($predicate){
    case 'equals':
      if($node[$subject] == $object) return true;
      break;
    case 'doesNotEqual':
      if($node[$subject] != $object) return true;
      break;
    case 'isGreaterThan':
      if($node[$subject] > $object) return true;
      break;
    case 'isLessThan':
      if($node[$subject] < $object) return true;
      break;
    case 'isIn':
      if(strpos($object,$node[$subject]) !== false) return true;
      break;
    case 'includes':
      if(strpos($node[$subject],$object) !== false) return true;
      break;
    case 'doesNotInclude':
      if(strpos($node[$subject],$object) === false) return true;
      break;

    default: return false;
  }

}
function fic_safe_count($a){
  if(is_countable($a)){
    return count($a);
  }else{
    return false;
  }
}
?>
