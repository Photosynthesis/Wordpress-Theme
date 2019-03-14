'use strict';

import { Elm } from './Admin/FlatRate.elm';

var $ = window.jQuery;

$(document).ready(function() {
  // Initialize the Elm application
  var node = document.getElementById('elm-admin-flat-rate');
  if (node) {
    var app = Elm.Admin.FlatRate.init({
      node: node,
      flags: { nonce: themeAdminConfig.restNonce }
    });

    // Scroll to the top of the page, unfocusing any inputs
    app.ports.scrollToTop.subscribe(function() {
      $('input').blur();
      $(window).scrollTop(0);
    });
  }
});
