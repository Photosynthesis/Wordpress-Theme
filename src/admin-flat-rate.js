'use strict';

var $ = window.jQuery;

$(document).ready(function() {
  // Initialize the Elm application
  var Elm = require('./Admin/FlatRate.elm');
  var node = document.getElementById('elm-admin-flat-rate');
  if (node) {
    var app = Elm.Admin.FlatRate.embed(node, { nonce: themeAdminConfig.restNonce });

    // Scroll to the top of the page, unfocusing any inputs
    app.ports.scrollToTop.subscribe(function() {
      $('input').blur();
      $(window).scrollTop(0);
    });
  }
});
