'use strict';

var $ = window.jQuery;

$(document).ready(function() {
  var Elm = require('./Directory/Main.elm');
  var node = document.getElementById('elm-directory');
  if (node) {
    var app = Elm.Main.embed(node);
    app.ports.scrollTo.subscribe(function (elementId) {
      if ($('#' + elementId).offset().top < $(window).scrollTop()) {
        $('html, body').animate({
          scrollTop: $('#' + elementId).offset().top
        }, 500);
      }
      $(':focus').blur();
    });
  }
});
