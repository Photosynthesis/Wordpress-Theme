'use strict';

/* Include Custom Styles & Bootstrap */
require('./styles.sass');
var jQuery = require('jquery');
require('../node_modules/bootstrap/dist/js/bootstrap.bundle.js');

var $ = jQuery;
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
  var menuCloseTimeout = 300;
  var menuHoverTimer = null;
  var menuCloseTimers = {};
  $('li.menu-item.dropdown').hover(function() {
    var $this = $(this);

    if (menuHoverTimer) {
      clearTimeout(menuHoverTimer);
      menuHoverTimer = null;
    }
    var timerId = $this.attr('id');
    if (menuCloseTimers[timerId]) {
      clearTimeout(menuCloseTimers[timerId]);
      menuCloseTimers[timerId] = null;
    }
    /* Open Submenus on Hover In */
    menuHoverTimer = setTimeout(function() {
      $this.children('.dropdown-menu').stop(true, true).slideDown({
        duration: 'fast',
        start: function() {
          $this.addClass('show');
        }
      });
    }, menuHoverTimeout);
  }, function() {
    if (menuHoverTimer) {
      clearTimeout(menuHoverTimer);
      menuHoverTimer = null;
    }
    /* Close Submenus on Hover Out */
    var $this = $(this);
    menuCloseTimers[$this.attr('id')] = setTimeout(function() {
      $this.children('.dropdown-menu').stop(true, true).slideUp({
        duration: 'fast',
        complete: function() {
          $this.removeClass('show');
        }
      });
    }, menuCloseTimeout);
  });


  /** Google Custom Search **/
  /* Open the search when the icon is clicked */
  $('#nav-search-icon').click(function() {
    $('#nav-search-icon').animate({
      opacity: 0,
      display: 'none',
    }, {
      done: function () {
        $('#nav-search-icon').css('display', 'none');
        $('#menu-search').css('display', 'block').css('opacity', '0').animate({
          opacity: 1,
        });
      }
    });
  })
  /* Wait Until Google's JS has Finished Initializing the Input */
  setTimeout(function() {
    /* Cleanup Look of Search Input */
    var $gscInput = $('input.gsc-input');
    $gscInput.attr('placeholder', 'Search');
    /* Google's JS Resets the Background & Indent on Every Blur */
    $gscInput.on('blur', function() {
      $gscInput.css('text-indent', '');
      $gscInput.css('background', 'inherit');
    });
    $gscInput.blur();
  }, 500);


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
    $('button#adverts-send-message-button').hide('fast');
  });


  /** Donate Page **/
  (function() {
    var nodes = document.getElementsByClassName('donate-form');
    if (nodes.length === 0) {
      return;
    }
    // Set the hidden amount input when selecting/entering an amount.
    var $amountInput = $('.donate-form #amount-input');
    $('.donate-form button.static-amount').each(function(i, el) {
      var amount = $(this).attr('data-amount') + '.00';
      $(this).click(function(ev) {
        removeSuccessClass();
        $(ev.target).removeClass("btn-secondary").addClass('btn-success');
        $amountInput.val(amount);
      });
    });
    $('#other-amount-input').change(function() {
      $amountInput.val($(event.target).val());
    });
    $('#other-amount-input').focus(function(ev) {
      removeSuccessClass();
      $(ev.target).closest('button').addClass('btn-success').removeClass('btn-secondary');
    });

    // Set attribute & variation ID when selecting recurring donations.
    var $recurringAttributeInput = $('#recurring-input');
    var $variationInput = $('#variation-input');
    $('#recurring-checkbox').change(function() {
      if (event.target.checked) {
        $recurringAttributeInput.val("recurring");
        $variationInput.val("224363");
      } else {
        $recurringAttributeInput.val("once");
        $variationInput.val("224364");
      }
    });

    function removeSuccessClass() {
      $('.donate-form button[type="button"]').each(function() {
        $(this).removeClass("btn-success").addClass("btn-secondary");
      })
    }
  })();
  jQuery(document).ready(function($) {
  });
});
