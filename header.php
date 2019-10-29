<!DOCTYPE html>
<html <?php language_attributes(); ?>>
  <head>
    <meta charset="<?php bloginfo('charset'); ?>" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <link rel="profile" href="http://gmpg.org/xfn/11" />
    <link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />
    <link href='//fonts.googleapis.com/css?family=Source+Sans+Pro:400,600,700' rel='stylesheet' type='text/css'>
    <?php if (is_singular() && get_option('thread_comments')) { wp_enqueue_script('comment-reply'); } ?>
    <?php wp_head(); ?>
    <!-- From RealFaviconGenerator.net -->
    <link rel="apple-touch-icon" sizes="180x180" href="<?php echo get_stylesheet_directory_uri(); ?>/img/favicon/apple-touch-icon.png?v=M4mloMGjlj">
    <link rel="icon" type="image/png" sizes="32x32" href="<?php echo get_stylesheet_directory_uri(); ?>/img/favicon/favicon-32x32.png?v=M4mloMGjlj">
    <link rel="icon" type="image/png" sizes="16x16" href="<?php echo get_stylesheet_directory_uri(); ?>/img/favicon/favicon-16x16.png?v=M4mloMGjlj">
    <link rel="manifest" href="<?php echo get_stylesheet_directory_uri(); ?>/img/favicon/site.webmanifest?v=M4mloMGjlj">
    <link rel="mask-icon" href="<?php echo get_stylesheet_directory_uri(); ?>/img/favicon/safari-pinned-tab.svg?v=M4mloMGjlj" color="#579c87">
    <link rel="shortcut icon" href="<?php echo get_stylesheet_directory_uri(); ?>/img/favicon/favicon.ico?v=M4mloMGjlj">
    <meta name="apple-mobile-web-app-title" content="Foundation for Intentional Community">
    <meta name="application-name" content="Foundation for Intentional Community">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-config" content="<?php echo get_stylesheet_directory_uri(); ?>/img/favicon/browserconfig.xml?v=M4mloMGjlj">
    <meta name="theme-color" content="#ffffff">
    <!-- Google Search Console -->
    <meta name="google-site-verification" content="kAW6DYo8sJMSvIFYAS0T-_IlmYTeCDtk4QCjc6J0quM" />

    <!-- Global site tag (gtag.js) - Google Ads: 824163499 -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=AW-824163499"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
      gtag('config', 'AW-824163499');
    </script>
    <!-- Google Custom Search -->
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
  </head>
    <body <?php body_class(); ?>>

<!-- Notification Banner --><?php
$banner_content = get_option('theme_banner_content');
if ($banner_content !== "") { ?>
  <div id='top-banner' class='container-fluid'>
    <div class='container'>
      <?php echo $banner_content; ?>
    </div>
  </div><?php
} ?>

<div id="body-wrapper" class="container"><!-- Closed in footer.php -->

  <!-- Header -->
  <div id="site-header" class="row">
    <!-- Logo -->
    <div class="col col-md-10 col-lg-4 col-xl-5 text-center text-sm-left d-flex justify-content-center align-items-center">
      <a href="/">
        <img class='img-fluid' src="<?php echo get_stylesheet_directory_uri() . '/img/logo-header-full-color.png?v=1'; ?>" alt="FIC" />
      </a>
    </div>
    <!-- Nav Menu -->
    <div id="nav-menu" class="d-none d-lg-flex col-lg-15 col-xl-15 pl-0">
      <nav class="navbar navbar-expand-lg navbar-light">
        <div class="collapse navbar-collapse" id="navbar-header">
          <?php
            wp_nav_menu( array(
              'menu'              => 'Primary Menu',
              'theme_location'    => 'primary',
              'depth'             => 2,
              'container'         => '',
              'container_class'   => '',
              'container_id'      => '',
              'menu_class'        => 'navbar-nav',
              'fallback_cb'       => 'WP_Bootstrap_Navwalker::fallback',
              'walker'            => new WP_Bootstrap_Navwalker())
            );
          ?>
          <ul class='navbar-nav'>
            <li class='nav-item'>
              <button class='btn' id='nav-search-icon'>
                <i class='fas fa-search'></i>
              </button>
              <div id='menu-search' style='display:none;'>
                <gcse:search></gcse:search>
              </div>
            </li>
          </ul>
        </div>
      </nav>
    </div>
    <!-- Buttons/Links -->
    <div class="col-auto col-sm col-lg-5 col-xl-4 text-right">
      <div class="d-none d-sm-block">
        <span class='muted-meta'>New to ic.org?</span> <a class='meta' href='/start/'>Start here.</a>
      </div>
      <div id="header-buttons" class="d-none d-sm-flex">
        <div class='d-inline-flex align-items-center meta'><?php
          if (is_user_logged_in()) {
            echo '<a href="' . wp_logout_url('/') . '">Log Out</a>';
            echo '<a class="ml-2" href="/my-fic-account/"><i class="fa fa-2x fa-user"></i></a>';
          } else {
            echo '<a href="' . wp_login_url('/my-fic-account/') . '">Log In</a>';
          }
          if (WC()->cart->get_cart_contents_count() > 0) {
            echo '<a class="ml-2" href="/cart/"><i class="fa fa-2x fa-shopping-cart"></i></a>';
          } ?>
        </div>
        <a class="btn btn-sm btn-light mx-2" href='/communities-bookstore/'>SHOP</a>
        <a class="btn btn-sm btn-primary donate-button" href='/donate/'>DONATE</a>
      </div>
      <!-- Mobile Menu Button -->
      <div class='navbar navbar-light d-lg-none mt-2'>
        <button class="ml-auto navbar-toggler" type="button" data-toggle="collapse" data-target="#mobile-navbar-header"
                aria-controls="navbar-header" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-label">MENU</span> <span class="navbar-toggler-icon"></span>
        </button>
      </div>
    </div>
    <!-- Mobile Nav Menu -->
    <div id="nav-menu" class="col-24 d-lg-none">
      <nav class="navbar navbar-expand-lg navbar-light">
        <div class="collapse navbar-collapse" id="mobile-navbar-header">
          <?php
            wp_nav_menu( array(
              'menu'              => 'Primary Menu',
              'theme_location'    => 'primary',
              'depth'             => 2,
              'container'         => '',
              'container_class'   => '',
              'container_id'      => '',
              'menu_class'        => 'navbar-nav',
              'fallback_cb'       => 'WP_Bootstrap_Navwalker::fallback',
              'walker'            => new WP_Bootstrap_Navwalker())
            );
          ?>
          <ul class='navbar-nav d-sm-none'>
            <?php
              $links = array(
                array('title' => 'PLACE AD', 'link' => '/communities-classifieds/place-ad/'),
                array('title' => 'SHOP', 'link' => '/communities-bookstore/'),
                array('title' => 'DONATE', 'link' => '/donate/'),
              );
              if (is_user_logged_in()) {
                $links[] = array('title' => 'MY ACCOUNT', 'link' => '/my-fic-account/');
                $links[] = array('title' => 'LOG OUT', 'link' => wp_logout_url());
              } else {
                $links[] = array('title' => 'LOG IN', 'link' => wp_login_url('/my-fic-account/'));
              }
              foreach ($links as $link) {
                echo '<li class="nav-item d-md-none">';
                echo "<a href='{$link['link']}' class='nav-link'>{$link['title']}</a>";
                echo '</li>';
              }
            ?>
          </ul>
          <ul class='navbar-nav'>
            <li class='nav-item'>
              <gcse:search></gcse:search>
            </li>
          </ul>
        </div>
      </nav>
    </div>
  </div>
