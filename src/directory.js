'use strict';

var $ = window.jQuery;

$(document).ready(function() {
  var Elm = require('./Directory/Main.elm');
  var node = document.getElementById('elm-directory');
  if (node) {
    var app = Elm.Main.embed(node);
  }
});
