<!DOCTYPE html>
<html <?php language_attributes(); ?>>
  <head>
    <meta charset="<?php bloginfo('charset'); ?>" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title><?php wp_title(); ?></title>
    <link rel="profile" href="http://gmpg.org/xfn/11" />
    <link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />
    <link href='//fonts.googleapis.com/css?family=Lora' rel='stylesheet' type='text/css'>
    <link href="<?php echo get_stylesheet_directory_uri() . '/dist/styles.css'; ?>" rel='stylesheet' type='text/css'>
    <script type="application/javascript" src="<?php echo get_stylesheet_directory_uri() . '/dist/styles.js'; ?>"></script>
    <script type="application/javascript" src="<?php echo get_stylesheet_directory_uri() . '/dist/base.js'; ?>"></script>
    <?php if (is_singular() && get_option('thread_comments')) { wp_enqueue_script('comment-reply'); } ?>
    <?php wp_head(); ?>
  </head>
    <body <?php body_class(); ?>>

<div id="body-wrapper" class="container"><!-- Closed in footer.php -->

  <!-- Header -->
  <div id="site-header" class="row">
    <div class="col">
      <a href="/" class="banner-image">
      <img src="<?php echo get_stylesheet_directory_uri() . '/img/logo-header-fic.png'; ?>" alt="FIC" height="80" />
      </a>
    </div>
    <div class="col text-right">
      <div id="greeting-logout"><?php
          $current_user = wp_get_current_user();
          if (is_user_logged_in()) {
            echo 'Hello, <strong>' . ucfirst($current_user->user_login) .
              '</strong>! <a href="' . get_permalink(get_option('woocommerce_myaccount_page_id')) .
              '">My Account</a>&nbsp;|&nbsp;<a href="' . wp_logout_url('/') . '">Log out</a>';
          }
          else {
            echo '<a href="' . get_permalink(get_option('woocommerce_myaccount_page_id')) .
              '">My Account</a>&nbsp;|&nbsp;<a href="/wp-login.php">Login</a>';
          } ?>
          <a id="header-cart-icon" href="<?php echo WC()->cart->get_cart_url(); ?>" title="<?php _e('View your shopping cart.'); ?>">
            <span class='cart-icon'></span>&nbsp;&nbsp;
          </a>
      </div>
      <div id="header-buttons">
        <button class="btn btn-sm btn-secondary" onclick="location.href='<?php echo get_permalink(14602); ?>'">Membership</button>
        <button class="btn btn-sm btn-primary donate-button" onclick="location.href='<?php echo get_permalink(14601); ?>'">Donate</button>
      </div>
    </div>
  </div>

  <!-- Nav Menu -->
  <div id="nav-menu" class="row">
    <div class="col-md-18 col-14">
      <nav class="navbar navbar-light navbar-toggleable-sm">
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbar-header"
                aria-controls="navbar-header" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-label">MENU</span> <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbar-header">
          <?php
            wp_nav_menu( array(
              'menu'              => 'Primary Menu',
              'theme_location'    => 'primary',
              'depth'             => 3,
              'container'         => '',
              'container_class'   => '',
              'container_id'      => '',
              'menu_class'        => 'navbar-nav',
              'fallback_cb'       => 'wp_bootstrap_navwalker::fallback',
              'walker'            => new wp_bootstrap_navwalker())
            );
          ?>
        </div>
      </nav>
    </div>
    <div class="col-md-6 col-10">
      <div id="google-search-div">
        <script>
          (function() {
            var cx = 'partner-pub-4810885975061329:dhwcft-rc6r';
            var gcse = document.createElement('script');
            gcse.type = 'text/javascript';
            gcse.async = true;
            gcse.src = (document.location.protocol == 'https:' ? 'https:' : 'http:') +
                '//www.google.com/cse/cse.js?cx=' + cx;
            var s = document.getElementsByTagName('script')[0];
            s.parentNode.insertBefore(gcse, s);
          })();
        </script>
        <gcse:search></gcse:search>
      </div>
    </div>
  </div>