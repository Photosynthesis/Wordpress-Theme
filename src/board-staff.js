'use strict';

var $ = window.jQuery;

$(document).ready(function() {
  var Elm = require('./BoardStaff.elm');
  var node = document.getElementById('elm-board-staff');
  if (node) {
    // boardStaffData is set by `elm_board_staff` shortcode
    var app = Elm.BoardStaff.embed(node, boardStaffData);
  }
})
