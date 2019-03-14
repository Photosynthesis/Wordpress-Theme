'use strict';

import SGoogleMapComponent from 'coffeekraken-s-google-map-component';
import SGoogleMapMarkerComponent from 'coffeekraken-s-google-map-marker-component';
import { Elm } from './Directory/Main.elm';

var $ = window.jQuery;

$(document).ready(function() {
  var node = document.getElementById('elm-directory');
  if (node) {
    var app = Elm.Directory.Main.init({
      node: node,
      flags: {
        nonce: themeRestConfig.nonce,
        location: location.href
      },
    });

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

    /* Change the URL & inform Elm of the change. */
    app.ports.pushUrl.subscribe(function (url) {
      history.pushState({}, '', url);
      app.ports.onUrlChange.send(location.href);
    });

    /* Inform the Elm app of navigation changes. */
    window.addEventListener('popstate', function() {
      app.ports.onUrlChange.send(location.href);
    })
  }
});
