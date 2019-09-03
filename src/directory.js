'use strict';

import SGoogleMapComponent from 'coffeekraken-s-google-map-component';
import SGoogleMapMarkerComponent from 'coffeekraken-s-google-map-marker-component';
import { Elm } from './Directory/Main.elm';

var $ = window.jQuery;

// Elm Application
$(document).ready(function() {
  var node = document.getElementById('elm-directory');
  if (node) {
    var app = Elm.Directory.Main.init({
      node: node,
      flags: {
        nonce: "themeRestConfig" in window ? themeRestConfig.nonce : "",
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

// Directory Homepage
$(document).ready(function() {
  var nodes = document.getElementsByClassName('page-id-17');
  if (nodes.length === 0) {
    return;
  }

  /* Set the Community Status When Toggling the Establish / Forming Checkboxes */
  var $hidden_input = $("input[name='community_status']");
  var $established = $("input[name='is_established']");
  var $forming = $("input[name='is_forming']");

  var established_checked = $established[0].checked;
  var forming_checked = $forming[0].checked;
  updateHiddenInput($hidden_input);

  $established.change(function(ev) {
    established_checked = ev.target.checked;
    updateHiddenInput($hidden_input);
  });
  $forming.change(function(ev) {
    forming_checked = ev.target.checked;
    updateHiddenInput($hidden_input);
  });

  function updateHiddenInput($hidden) {
    var value = [];
    if (established_checked) {
      value.push("Established");
    }
    if (forming_checked) {
      value.push("Forming, Re-forming");
    }
    $hidden[0].value = value.join(", ");
  }

});
