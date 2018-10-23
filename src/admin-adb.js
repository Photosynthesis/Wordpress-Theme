/* Update the ADB Number field in the Order/Subscription meta boxes when
 * loading a new Customer.
 *
 * Uses global admin object `themeAdminConfig`.
 */
jQuery(function($) {
  var fic_adb_meta_box = {
    init: function() {
      $('#customer_user').on('change', this.change_customer_id);
    },
    change_customer_id: function() {
      var user_id = $('#customer_user').val();
      if (isNaN(user_id)) { return; }
      $.ajax({
        type: "get",
        dataType: "json",
        url: themeAdminConfig.ajaxUrl,
        data: {
          action: "get_user_adb_number",
          user_id: user_id,
          security: themeAdminConfig.ajaxNonce,
        },
        success: function(response) {
          if (!isNaN(response.adb)) {
            $('input#adb_number').val(response.adb);
          } else {
            console.log(response);
          }
        }
      });
    },
  };
  fic_adb_meta_box.init();
});
