<?php

function load_include($path) {
  require_once(get_template_directory() . "/includes/{$path}");
}

/** Dependencies **/
//require_once(get_template_directory() . "/depends/stripe/init.php");

/** Utility Functions **/
load_include('utilities.php');

/** General Site & Layout Functions **/
load_include('general.php');
load_include('widget_submenu.php');

/** Bootstrap Nav & Comment Builders **/
load_include('bootstrap_menu_walker.php');
load_include('bootstrap_comment_walker.php');

/** Admin Menus **/
load_include('menu.php');

/** Blog Post Customizations **/
load_include('blog_posts.php');
/** User Customizations **/
load_include('users.php');

/** Wholesale Order Form **/
load_include('wholesale.php');


/** Plugins **/
/* Directory */
load_include('directory.php');
/* Events Calendar */
load_include('events.php');
/* Newsletter - MailPoet & Hustle Pro */
load_include('newsletter.php');
/* WooCommerce */
load_include('woocommerce.php');
/* WPAdverts */
load_include('wpadverts.php');
load_include('widget_classifieds.php');
/* WPGive */
load_include('wpgive.php');


/** REST API **/
load_include('api.php');

?>
