'use strict';

import { Elm } from './BoardStaff.elm';

var $ = window.jQuery;

$(document).ready(function() {
  var node = document.getElementById('elm-board-staff');
  if (node) {
    // boardStaffData is set by `elm_board_staff` shortcode
    var app = Elm.BoardStaff.init({
      node: node,
      flags: boardStaffData
    });
  }
})
