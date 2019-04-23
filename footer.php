<!-- div#body-wrapper is opened -->

  <!-- Footer Support -->
  <div id='footer-support' class='row'>
    <div class='col-md-14'>
      <h3>Our work is made possible thanks to community lovers like you!</h3>
      <p>
        Donate to support a thriving communities movement and become a FIC
        Member for a host of special membership benefits.
      </p>
      <a class='btn btn-primary' href='/support/' target='_blank'>SUPPORT</a>
    </div>
    <div class='col-md-8'>
      <img src='/wp-content/themes/fic-theme/img/membership-badge.png' class='img-fluid' />
    </div>
  </div>


  <!-- Footer Columns -->
  <div id='footer-columns' class='row'>
    <div class='col-md-10 col-lg-8 left-column'>
      <img src='/wp-content/themes/fic-theme/img/logo-full-white.png' class='img-fluid' />
      <h5>OUR MISSION</h5>
      <p>
        To support and promote the development of intentional communities, as a
        pathway towards a more sustainable and just world.
      </p>
      <a class='btn btn-primary' href='/about/'>LEARN MORE</a>
    </div>
    <div class='col-md-5 col-lg-4 middle-column'>
      <h5>SITE PAGES</h5><?php
      $footer_links = array(
        array('title' => 'About FIC', 'link' => '/about/'),
        array('title' => 'Directory', 'link' => '/directory/'),
        array('title' => 'Membership', 'link' => '/membership/'),
        array('title' => 'Donate', 'link' => '/donate/'),
        array('title' => 'Communities Directory', 'link' => '/directory/'),
        array('title' => 'Bookstore', 'link' => '/communities-bookstore/'),
        array('title' => 'Magazine', 'link' => '/communities-magazine/'),
        array('title' => 'Planet Community', 'link' => '/planet-community/'),
        array('title' => 'Events', 'link' => '/events/'),
        array('title' => 'Classifieds', 'link' => '/community-classifieds/'),
        array('title' => 'Knowledgebase', 'link' => '/knowledgebase/'),
        array('title' => 'Blog', 'link' => '/blog/'),
        array('title' => 'Contact', 'link' => '/contact-fic/'),
        array('title' => 'Privacy', 'link' => '/privacy/'),
      );
      echo '<ul>';
      foreach ($footer_links as $link) {
        echo "<li><a href='{$link['link']}' target='_blank'>{$link['title']}</a></li>";
      }
      echo '</ul>';
      ?>
    </div>
    <div class='col-md-8 col-xl-6 right-column'>
      <h5>NEWSLETTER</h5>
      <p class='font-weight-normal'>Community movement news in your inbox!</p>
      <?php echo do_shortcode('[mailpoet_form id="2"]'); ?>
      <div class='text-right icons'>
        <a href='https://www.facebook.com/FellowshipForIntentionalCommunity/' target='_blank'>
          <i class='fab fa-2x fa-facebook-square'></i>
        </a>
        <a href='https://www.youtube.com/channel/UC_bjpd6qhVcA1SuuF8pLRIg' target='_blank'>
          <i class='fab fa-2x fa-youtube'></i>
        </a>
        <a href='/contact-fic/' target='_blank'>
          <i class='fa fa-2x fa-paper-plane'></i>
        </a>
      </div>
    </div>
  </div>


  <!-- Copyright -->
  <div id='footer-copyright' class='meta'>
    <p>
      Foundation for Intentional Community (FIC) is a registered 501(c)(3)
      non-profit organization. Our main office is located at Dancing Rabbit
      Ecovillage, MO, USA.
      <br />
      Stewarding the communities movement for over 30 years!
    </p>

    <div>© 2019 Foundation for Intentional Community. All rights reserved.</div>
  </div>


</div><!-- div#body-wrapper -->
<?php wp_footer(); ?>
</body>
</html>
