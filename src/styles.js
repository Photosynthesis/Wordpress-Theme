'use strict';

/* Include Custom Styles & Bootstrap */
//require('style!css!font-awesome/css/font-awesome.css');
require('./styles.sass');
window.jQuery = require('../node_modules/jquery/dist/jquery.slim.js');
window.Tether = require('../node_modules/tether/dist/js/tether.js');
require('../node_modules/bootstrap/dist/js/bootstrap.js');

/* Open Nav Menus on Hover for Desktop Users */
var $ = window.jQuery;
$(document).ready(function() {
  var navbarToggle = '.navbar-toggler';
  $('.dropdown, .dropup').each(function() {
    var dropdown = $(this),
      dropdownToggle = $('[data-toggle="dropdown"]', dropdown),
      dropdownHoverAll = dropdownToggle.data('dropdown-hover-all') || false;

    // Mouseover
    dropdown.hover(function(){
      var notMobileMenu = $(navbarToggle).length > 0 && $(navbarToggle).css('display') === 'none';
      if ((dropdownHoverAll == true || (dropdownHoverAll == false && notMobileMenu))) {
        dropdownToggle.trigger('click');
      }
    })
  });
});
