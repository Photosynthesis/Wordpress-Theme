'use strict';

var $ = window.jQuery;

$(document).ready(function() {
  var Elm = require('./Admin/BoardStaff.elm');
  var node = document.getElementById('elm-admin-board-staff');
  if (node) {
    var app = Elm.Admin.BoardStaff.embed(node, { nonce: themeAdminConfig.restNonce });

    // Scroll to the top of the page, unfocusing any inputs
    app.ports.scrollToTop.subscribe(function() {
      $('input').blur();
      $(window).scrollTop(0);
    });
  }
});
