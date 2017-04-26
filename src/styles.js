'use strict';

/* Include Custom Styles & Bootstrap */
//require('style!css!font-awesome/css/font-awesome.css');
require('./styles.sass');
window.jQuery = require('../node_modules/jquery/dist/jquery.js');
window.Tether = require('../node_modules/tether/dist/js/tether.js');
require('../node_modules/bootstrap/dist/js/bootstrap.js');

var $ = window.jQuery;
$(document).ready(function() {
  /* Disable Click Actions When Touching Dropdown Nav Menus */
  $('li.menu-item.dropdown').on('touchend', function(event) {
    var $dropdown = $(this);
    if ($dropdown.hasClass('show'))  {
      $dropdown.removeClass('show');
      $dropdown.find('li.menu-item.dropdown').removeClass('show');
    } else {
      $dropdown.addClass('show');
      $('li.menu-item.dropdown').not(function(i, el) {
        return ($(el).find($dropdown).length !== 0) || (el === $dropdown[0]);
      }).removeClass('show');
    }
    event.preventDefault();
    event.stopPropagation();
  });
  /* Click Links When Touching Non-Dropdown Nav Items */
  $('li.menu-item').not('.dropdown').on('touchend', function(event) {
    $(this).find('a')[0].click();
    event.preventDefault();
    event.stopPropagation();
  });
  /* Hide Any Shown Menus When Touching Outside a Menu */
  $('body').on('touchend', function(event) {
    $('li.menu-item.dropdown').removeClass('show');
  });

  /* WPAdverts */
  /* Toggle Contact Form on Button Click */
  $('body.advert-template-default button#adverts-send-message-button').click(function() {
    $('body.advert-template-default .adverts-contact-box').toggleClass('d-block');
  });
});
