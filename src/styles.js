'use strict';

/* Include Custom Styles & Bootstrap */
//require('style!css!font-awesome/css/font-awesome.css');
require('./styles.sass');
window.jQuery = require('../node_modules/jquery/dist/jquery.js');
window.Tether = require('../node_modules/tether/dist/js/tether.js');
require('../node_modules/bootstrap/dist/js/bootstrap.js');

var $ = window.jQuery;
$(document).ready(function() {
  /* Touching Items With Dropdowns */
  $('li.menu-item.dropdown').on('touchend', function(event) {
    var $dropdown = $(this);
    if ($dropdown.hasClass('show'))  {
      /* Hide the Clicked Menu */
      $dropdown.children('.dropdown-menu').slideUp();
      $dropdown.removeClass('show');
      /* Hide any Open Sub-Menus */
      $dropdown.find('li.menu-item.dropdown').removeClass('show').children('.dropdown-menu').slideUp();
    } else {
      /* Hide Any Shown Sibling Menus */
      $dropdown.siblings('.dropdown').find('.dropdown-menu').slideUp();
      /* Show the Touched Menu */
      $dropdown.children('.dropdown-menu').slideDown();
      $dropdown.addClass('show');
      /* Hide All Other Menus */
      $('li.menu-item.dropdown').not(function(i, el) {
        return ($(el).find($dropdown).length !== 0) || (el === $dropdown[0]);
      }).removeClass('show');
    }
    /* Disable Click Actions When Touching Dropdown Nav Menus */
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
    $('li.menu-item.dropdown').removeClass('show').children('.dropdown-menu').slideUp();
  });
  $('li.menu-item.dropdown').hover(function() {
    /* Open Submenus on Hover In */
    $(this).children('.dropdown-menu').slideDown();
    $(this).addClass('show');
  }, function() {
    /* Close Submenus on Hover Out */
    $(this).children('.dropdown-menu').slideUp();
    $(this).removeClass('show');
  });

  /* WooCommerce */
  /* Fix Classes When Clicking Tabs */
  $('.woocommerce-tabs.wc-tabs-wrapper li.nav-item a').on('click', function(event) {
    $('.woocommerce-tabs.wc-tabs-wrapper li.nav-item a').removeClass('active');
    $(this).addClass('active');
  });

  /* WPAdverts */
  /* Toggle Contact Form on Button Click */
  $('body.advert-template-default button#adverts-send-message-button').click(function() {
    $('body.advert-template-default .adverts-contact-box').slideToggle('fast');
  });
});
