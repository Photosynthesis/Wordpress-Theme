'use strict';

/* Include Custom Styles & Bootstrap */
require('./styles.sass');
window.jQuery = require('../node_modules/jquery/dist/jquery.js');
window.Tether = require('../node_modules/tether/dist/js/tether.js');
require('../node_modules/bootstrap/dist/js/bootstrap.js');

var $ = window.jQuery;
$(document).ready(function() {
  /** Navbar **/
  /* Menu Touching */
  var touchDragging = false;
  /* Set as Dragging on Touch Move Events */
  $('body').on('touchmove', function() { touchDragging = true; })
  /* Reset Dragging Status on New Touches */
  $('body').on('touchstart', function() { touchDragging = false; })
  /* Handle Touches on Items with Sub-Menus */
  $('li.menu-item.dropdown').on('touchend', function(event) {
    if (touchDragging) { return; }
    var $dropdown = $(this);
    if ($dropdown.hasClass('show'))  {
      /* Hide the Clicked Menu */
      $dropdown.children('.dropdown-menu').slideUp('fast');
      $dropdown.removeClass('show');
      /* Hide any Open Sub-Menus */
      $dropdown.find('li.menu-item.dropdown').removeClass('show')
        .children('.dropdown-menu').slideUp('fast');
    } else {
      /* Hide Any Shown Sibling Menus */
      $dropdown.siblings('.dropdown').find('.dropdown-menu').slideUp('fast');
      /* Show the Touched Menu */
      $dropdown.children('.dropdown-menu').slideDown('fast');
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
    if (touchDragging) { return; }
    $(this).find('a')[0].click();
    event.preventDefault();
    event.stopPropagation();
  });
  /* Hide Any Shown Menus When Touching Outside a Menu */
  $('body').on('touchend', function(event) {
    if (touchDragging) { return; }
    $('li.menu-item.dropdown').removeClass('show')
      .children('.dropdown-menu').slideUp('fast');
  });

  /* Menu Hovering */
  var menuHoverTimeout = 325;
  var menuCloseTimeout = 100;
  var menuHoverTimer = null;
  $('li.menu-item.dropdown').hover(function() {
    if (menuHoverTimer) {
      clearTimeout(menuHoverTimer);
      menuHoverTimer = null;
    }
    /* Open Submenus on Hover In */
    var $this = $(this);
    menuHoverTimer = setTimeout(function() {
      $this.children('.dropdown-menu').stop(true, true).slideDown('fast');
      $this.addClass('show');
    }, menuHoverTimeout);
  }, function() {
    if (menuHoverTimer) {
      clearTimeout(menuHoverTimer);
      menuHoverTimer = null;
    }
    /* Close Submenus on Hover Out */
    var $this = $(this);
    setTimeout(function() {
      $this.children('.dropdown-menu').stop(true, true).slideUp('fast');
      $this.removeClass('show');
    }, menuCloseTimeout);
  });


  /** WooCommerce **/
  /* Fix Classes When Clicking Tabs */
  $('.woocommerce-tabs.wc-tabs-wrapper li.nav-item a').on('click', function(event) {
    $('.woocommerce-tabs.wc-tabs-wrapper li.nav-item a').removeClass('active');
    $(this).addClass('active');
  });


  /** WPAdverts **/
  /* Toggle Contact Form on Button Click */
  $('body.advert-template-default button#adverts-send-message-button').click(function() {
    $('body.advert-template-default .adverts-contact-box').slideToggle('fast');
  });
});
