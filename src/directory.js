'use strict';

import SGoogleMapComponent from 'coffeekraken-s-google-map-component';
import SGoogleMapMarkerComponent from 'coffeekraken-s-google-map-marker-component';

var $ = window.jQuery;

$(document).ready(function() {
  var Elm = require('./Directory/Main.elm');
  var node = document.getElementById('elm-directory');
  if (node) {
    var app = Elm.Directory.Main.embed(node);

    /* Scroll to the top of the main content, if we've scrolled past it. */
    app.ports.scrollTo.subscribe(function (elementId) {
      if ($('#' + elementId).offset().top < $(window).scrollTop()) {
        $('html, body').animate({
          scrollTop: $('#' + elementId).offset().top
        }, 500);
      }
      $(':focus').blur();
    });

    /* Modify the Page Title */
    app.ports.setPageTitle.subscribe(function (newTitle) {
      document.title = newTitle;
    });
  }
});
