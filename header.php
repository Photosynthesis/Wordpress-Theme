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
    <link rel="apple-touch-icon" sizes="180x180" href="/wp-content/themes/fic-theme/img/favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="<?php echo get_stylesheet_directory_uri(); ?>/img/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="<?php echo get_stylesheet_directory_uri(); ?>/img/favicon/favicon-16x16.png">
    <link rel="manifest" href="<?php echo get_stylesheet_directory_uri(); ?>/img/favicon/manifest.json">
    <link rel="mask-icon" href="<?php echo get_stylesheet_directory_uri(); ?>/img/favicon/safari-pinned-tab.svg" color="#f0673a">
    <link rel="shortcut icon" href="<?php echo get_stylesheet_directory_uri(); ?>/img/favicon/favicon.ico">
    <meta name="msapplication-config" content="<?php echo get_stylesheet_directory_uri(); ?>/img/favicon/browserconfig.xml">
    <meta name="theme-color" content="#ffffff">
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
    <div class="col col-md-6 col-xl-5 text-center text-sm-left d-flex justify-content-center align-items-center">
      <a href="/">
        <img class='img-fluid' src="<?php echo get_stylesheet_directory_uri() . '/img/logo-header-full-color.png'; ?>" alt="FIC" />
      </a>
    </div>
    <!-- Nav Menu -->
    <div id="nav-menu" class="col d-flex">
      <nav class="navbar navbar-expand-md navbar-light">
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbar-header"
                aria-controls="navbar-header" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-label">MENU</span> <span class="navbar-toggler-icon"></span>
        </button>

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
    <div class="col col-lg-6 text-right d-none d-sm-block">
      <div>
        <span class='muted-meta'>New to ic.org?</span> <a class='meta' href='/'>Start here.</a>
      </div>
      <div id="header-buttons">
        <div class='d-inline-block meta'><?php
          if (is_user_logged_in()) {
            $current_user = wp_get_current_user();
            echo 'Hello,&nbsp;<strong>' . ucfirst($current_user->user_login) .
              '</strong>&nbsp;(<a href="' . wp_logout_url('/') . '">Log Out</a>)';
          } else {
              echo '<a href="' . wp_login_url(get_permalink()) . '">Log In</a>';
          } ?>
        </div>
        <a class="btn btn-sm btn-light mx-2" href='<?php echo get_permalink(14602); ?>'>SHOP</a>
        <a class="btn btn-sm btn-primary donate-button" href='<?php echo get_permalink(14601); ?>'>DONATE</a>
      </div>
    </div>
  </div>
