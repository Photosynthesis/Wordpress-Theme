'use strict';

import { Elm } from './Admin/BoardStaff.elm';

var $ = window.jQuery;

$(document).ready(function() {
  var node = document.getElementById('elm-admin-board-staff');
  if (node) {
    var app = Elm.Admin.BoardStaff.init({
      node: node,
      flags: { nonce: themeAdminConfig.restNonce },
    });

    // Scroll to the top of the page, unfocusing any inputs
    app.ports.scrollToTop.subscribe(function() {
      $('input').blur();
      $(window).scrollTop(0);
    });
  }
});
