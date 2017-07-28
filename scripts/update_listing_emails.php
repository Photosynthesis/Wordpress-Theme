<?php
/** Send Update Reminder Emails for Directory Listings that haven't been
 *  updated or verified in 18 months.
 *
 *  This script is run daily by cron.
 */

require_once __DIR__ . '/../../../../wp-load.php';

/* Send Update Reminders for Listings Un-Updated/-Verified Listings */
function main() {
  array_map(
    'send_notification_email',
    array_filter(DirectoryDB::get_published_items(), 'is_old_enough')
  );
}

/* Filter Out Listings That Have Been Verified or Updated in the Past 18 Months */
function is_old_enough($item) {
  $upper_limit = strtotime("18 months ago");
  $lower_limit = strtotime("18 months 1 day ago");
  $verified_date = DirectoryDB::get_item_meta_value(
    DirectoryDB::$verified_date_field_id, $item['id']);
  if ($verified_date === false) {
    $latest_date = $item['updated_at'];
  } else if ($verified_date > $item['updated_at']) {
    $latest_date = $verified_date;
  } else {
    $latest_date = $item['updated_at'];
  }
  if ($item['id'] === 41814) { print_r($latest_date); }
  if (strtotime($latest_date) > $upper_limit ||
      strtotime($latest_date) < $lower_limit) {
    return false;
  }
  return true;
}

/* Send a Notification Email to the Community's Contact Email */
function send_notification_email($item) {
  $community_name = DirectoryDB::get_name($item);
  $contact_name = DirectoryDB::get_item_meta_value(
    DirectoryDB::$contact_name_field_id, $item['id']);
  $contact_email = DirectoryDB::get_item_meta_value(
    DirectoryDB::$contact_email_field_id, $item['id']);
  $listing_link = get_permalink($item['post_id']);

  if ($contact_email === false) {
    $admin_link = "http://www.ic.org/wp-admin/admin.php?page=formidable-entries&frm_action=edit&id={$item['id']}";
    wp_mail("directory@ic.org", "[FIC] Automated Update Reminder Failed",
      "I couldn't send the '18 months since your last update' email because " .
      "this community doesn't have a contact email!\n\n{$admin_link}");
    return;
  }

  $subject = "[FIC] Please Verify Your Listing is Up to Date";

  $message =
    "Hello " . ($contact_name ? $contact_name : $community_name) . ",\n\n" .
    "It looks like your Directory Listing, \"{$community_name}\", hasn't been " .
    "updated or verified in over 18 months.\n\n" .
    "Please review & update your listing if any information has changed. If everything " .
    "is still up to date, you can simply log in & click \"Verify Listing\" on your " .
    "Listing's page:\n\n\t\t{$listing_link}\n\n" .
    "Thanks!\n\n" .
    "---\n\nThis is an automated message. For support with your Listing, please contact " .
    "directory@ic.org.\n\n";


  wp_mail($contact_email, $subject, $message);
}

main();

?>
