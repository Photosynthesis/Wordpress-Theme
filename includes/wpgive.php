<?php
/** Customizations for the WPGive Plugins **/
class ThemeWPGive
{
  const membership_form_id = 280072;

  /* Disable Renewal Emails for Donations From Every Form But Membership */
  public static function disable_renewal_emails($send, $subscription_id, $notice_id) {
    $subscription = new Give_Subscription($subscription_id);
    if ($subscription->form_id === self::membership_form_id) {
      return true;
    }
    return false;
  }
}


add_filter('give_recurring_send_renewal_reminder', array('ThemeWPGive', 'disable_renewal_emails'), 20, 3);

?>
