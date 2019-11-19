<?php
/** Send Update Reminder Emails for Directory Listings that haven't been
 *  updated or verified in 18 months. The first update reminder will set the
 *  `Update Email Date` field for a listing.
 *
 *  30 days after a listee has been notified, they will be sent a second
 *  reminder. 14 days after the second reminder, the contact, backup contact,
 *  and directory manager will be sent a final notification.
 *
 *  No listings are automatically de-activated - that's up to the directory
 *  manager to handle manually.
 *
 *  This script should be run daily by cron.
 *
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
  $limit = strtotime("18 months ago 00:00:00");
  $verified_date = DirectoryDB::get_item_meta_value(
    DirectoryDB::$verified_date_field_id, $item['id']);
  if ($verified_date === false) {
    $latest_date = $item['updated_at'];
  } else if ($verified_date > $item['updated_at']) {
    $latest_date = $verified_date . " 00:00:00";
  } else {
    $latest_date = $item['updated_at'];
  }
  return strtotime($latest_date) < $limit;
}

/* Send a Notification Email to the Community's Contact Email.
 *
 * If we've sent the first notification email already, send one of the
 * alternatives if it's been 30 or 44 days since then.
 *
 * If the contact email does not exist, the directory manager is notified.
 *
 */
function send_notification_email($item) {
  $community_name = DirectoryDB::get_name($item);
  $contact_name = DirectoryDB::get_item_meta_value(
    DirectoryDB::$contact_name_field_id, $item['id']);
  $contact_email = DirectoryDB::get_item_meta_value(
    DirectoryDB::$contact_email_field_id, $item['id']);
  $listing_link = get_permalink($item['post_id']);

  $verified_date = DirectoryDB::get_item_meta_value(
    DirectoryDB::$verified_date_field_id, $item['id']);
  $latest_date = strtotime(
    ($verified_date && $verified_date > $item['updated_at'])
    ? $verified_date . '00:00:00' : $item['updated_at']
  );

  $previous_update_email_date = DirectoryDB::get_item_meta_value(
    DirectoryDB::$update_email_date_field_id, $item['id']);
  if ($previous_update_email_date !== FALSE) {
    $previous_update_email_date = strtotime($previous_update_email_date . " 00:00:00");
  }

  $update_email_already_sent = $previous_update_email_date !== FALSE
    && $previous_update_email_date >= $latest_date;
  if ($update_email_already_sent) {
    // If already sent, don't email unless we are 30/44 days after that
    $is_thirty_days =
      (strtotime('30 days ago 00:00:00') > $previous_update_email_date) &&
      (strtotime('31 days ago 00:00:00') <= $previous_update_email_date);
    $is_forty_four_days =
      (strtotime('44 days ago 00:00:00') > $previous_update_email_date) &&
      (strtotime('45 days ago 00:00:00') <= $previous_update_email_date);
    if ($is_thirty_days) {
      $email_type = 'second';
    } else if ($is_forty_four_days) {
      $email_type = 'third';
    } else {
      // If first email sent & not time to send others, don't send anything
      return;
    }
  } else {
    $email_type = 'first';
  }

  if ($contact_email === false) {
    $admin_link = "http://www.ic.org/wp-admin/admin.php?page=formidable-entries&frm_action=edit&id={$item['id']}";
    wp_mail("directory@ic.org", "[FIC] Automated Update Reminder Failed",
      "I couldn't send the 'Update Your Listing' email because " .
      "this community doesn't have a contact email!\n\n{$admin_link}\n");
    // Set the Update Email date field(even though we've sent no "Update
    // Email") so the Directory Manager doesn't get spammed with this every
    // day.
    DirectoryDB::update_or_insert_item_meta(
      DirectoryDB::$update_email_date_field_id, $item['id'], date('Y-m-d'));
    return;
  }

  $subject = "[FIC] Please Verify Your Listing is Up to Date";

  $message =
    "Hello " . ($contact_name ? $contact_name : $community_name) . ",\n\n";
  if ($email_type === 'first') {
    $message .=
      "Your Communities Directory Listing, \"{$community_name}\", has not been " .
      "updated or verified in at least 18 months. Please log into your account and " .
      "update the information for your community.\n\n";
  } else if ($email_type === 'second') {
    $message .=
      "Your Communities Directory Listing, \"{$community_name}\", needs your attention. " .
      "The listing has not been updated in over 18 months and will SOON BE REMOVED from " .
      "ic.org/directory.\n\n";
  } else if ($email_type === 'third') {
    $message .=
      "This is your third email reminding you to update your Communities Directory " .
      "listing, \"{$community_name}\". Your community will soon be removed from " .
      "ic.org/directory.\n\n";
  }
  $message .=
    "If you know your information is up-to-date, please log into your account, " .
    "select to edit the listing, and then simply select \"Update\".\n\n" .

    "If you need help with your log-in information or with editing your listing, " .
    "send an email to our Directory Manager at directory@ic.org with your " .
    "Community's name in the message.\n\n" .

    "If you would like to remove your Community's listing, you can either log in " .
    "and remove the listing, or request directory@ic.org to do it for you.\n\n" .

    "Thank You!";

  $headers = array('Reply-To: directory@ic.org');
  $backup_email = DirectoryDB::get_item_meta_value(
    DirectoryDB::$backup_email_field_id, $item['id']);
  if ($backup_email) {
    $headers[] = 'Cc: ' . $backup_email;
  }
  $editor = DirectoryDB::get_item_meta_value(430, $item['id']);
  if ($editor) {
    $headers[] = 'Cc: ' . get_userdata($editor)->user_email;
  }

  if ($email_type === 'third') {
    $headers[] = 'Cc: directory@ic.org';
  }

  wp_mail($contact_email, $subject, $message, $headers);

  if ($email_type === 'first') {
    DirectoryDB::update_or_insert_item_meta(
      DirectoryDB::$update_email_date_field_id, $item['id'], date('Y-m-d'));
  }
}

main();

?>
