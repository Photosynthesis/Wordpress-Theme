<!-- div#body-wrapper is opened -->
  <!-- Show Membership Info, Newsletter Signup, & Social Icons --><?php
  $excluded_membership_footer_posts = array(
    'new-listing', 'contact-fic', 'place-ad', 'contact-communities-magazine',
    'contact-a-community', 'support',
  );
  if (!(is_woocommerce() || in_array(get_post()->post_name, $excluded_membership_footer_posts))) { ?>
  <div id='membership-footer' class='row'>
    <div class="bg-info p-3">
      <div class="row">
        <div class="col-md-16 col-lg-18">
          <h2 class="text-center">Connect to the Communities Movement</h2>
        </div>
        <div class="col-md-8 col-lg-6"></div>
        <!-- Left Section -->
        <div class="col-md-16 col-lg-18 mb-4 mb-md-0">
          <div class="card">
            <div class="card-block">
              <div class="row">
                <div class="col-sm-10 d-flex">
                  <a class="m-auto" href="/community-bookstore/product/fic-membership/" target="_blank">
                    <img class="img-fluid alignnone" src="/wp-content/images/fic-membership-badge.png" alt="" width="288" height="259" />
                  </a>
                </div>
                <div class="col-sm-14">
                  <p class="text-primary font-weight-bold">
                    Support the development of intentional communities and the
                    evolution of cooperative culture.
                  </p>
                  <p>
                    <a href="/community-bookstore/product/fic-membership/">Become an FIC Member</a>
                    to receive special updates, webinars, reports, and discounts to
                    books, events, ads, and more.
                  </p>
                  <p>
                    IC.org is a project of the Fellowship for Intentional Community, a
                    501(c)3 nonprofit organization.
                  </p>
                  <p>
                    Your <a href="/community-bookstore/product/fic-membership/">Membership</a>
                    and <a href="/support/">donations</a> are tax deductible. Please help to
                    further this mission -- together we can change the world!
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
        <!-- Right Section -->
        <div class="col-md-8 col-lg-6">
          <!-- Newsletter Form -->
          <div style="width: 100%;"><?php echo do_shortcode('[wd_hustle id="fic-newsletter-homepage"]'); ?></div>

          <!-- Social Icons -->
          <div class="text-center">
            <a class="mr-2" href="https://www.facebook.com/FellowshipForIntentionalCommunity">
              <img alt="Facebook" style="max-width: 20%;" src="/wp-content/uploads/2018/01/fb_logo.png" />
            </a>
            <a class="mx-auto" href="https://www.youtube.com/channel/UC_bjpd6qhVcA1SuuF8pLRIg/featured">
              <img alt="YouTube" style="max-width: 20%;" src="/wp-content/uploads/2018/01/youtube_logo.jpg" />
            </a>
            <a class="ml-2" href="https://twitter.com/iCdotOrg">
              <img alt="Twitter" style="max-width: 20%;" src="/wp-content/uploads/2018/01/twitter_logo.png" />
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
  <?php } ?>


  <!-- Partners -->
  <?php $footer_logo_path = get_stylesheet_directory_uri() . "/img/footer-logos/"; ?>
  <!-- Track Clicks with Google Analytics --><?php
  function fic_footer_track_click($event_label) {
    return "ga('send', 'event', 'Theme.Footer', 'click', '{$event_label}');";
  }
  ?>
  <hr />
  <div id="partners-footer" class="row">
    <div class="col">
      <div class="text-center mt-1">
        We happily link to the following organizations, all of whom share our strong commitment to promoting community and a more cooperative world:
        <div style="margin-top:12px;margin-bottom:8px;">
          <a href="http://www.cohousing.org" title="Cohousing" target="_blank" onclick="<?php echo fic_footer_track_click('sister_org_coho'); ?>">
            <img src="<?php echo $footer_logo_path; ?>cohousing.png" alt="Cohousing" title="Cohousing" width="98" height="40" hspace="4" border="0">
          </a>
            <a href="http://TheFEC.org/" target="_blank" onclick="<?php echo fic_footer_track_click('sister_org_fec'); ?>">
            <img src="<?php echo $footer_logo_path; ?>fec.png" alt="The Federation of Egalitarian Communities - Communes Coop Community Cooperative Sustainable Intentional" title="The Federation of Egalitarian Communities - Sustainable Intentional Communes Coop Community Cooperative" width="119" height="40" border="0">
          </a>
            <a href="http://www.nasco.coop/" target="_blank" onclick="<?php echo fic_footer_track_click('sister_org_NASCO'); ?>">
            <img src="<?php echo $footer_logo_path; ?>nasco.jpg" alt="North American Students of Cooperation" title="North American Students of Cooperation" width="105" height="40" hspace="4" border="0">
          </a>
            <a href="http://gen.ecovillage.org" target="blank" onclick="<?php echo fic_footer_track_click('sister_org_GEN'); ?>">
            <img src="<?php echo $footer_logo_path; ?>gen.png" alt="Global Ecovillage Network" title="Global Ecovillage Network" width="168" height="40" hspace="4" border="0">
          </a>
            <a href="https://neweconomy.net/" target="blank" onclick="<?php echo fic_footer_track_click('sister_org_NEC'); ?>">
            <img src="<?php echo $footer_logo_path; ?>nec.png" alt="New Economy Coalition" title="New Economy Coalition" width="212" height="40" hspace="4" border="0">
          </a>
        </div>
        <div class="mt-1 mb-1">
          <a href="http://www.numundo.org/" target="blank" onclick="<?php echo fic_footer_track_click('sponsor_NM'); ?>">
            <img src="<?php echo $footer_logo_path; ?>numundo.png" alt="NuMundo" title="NuMondo" height="40" hspace="4" border="0">
          </a>
            <a href="http://www.calcoho.org/" target="blank" onclick="<?php echo fic_footer_track_click('sponsor_calcoho'); ?>">
            <img src="<?php echo $footer_logo_path; ?>calcoho.jpg" alt="California Cohousing" title="California Cohousing" height="40" hspace="4" border="0">
          </a>
            <a href="http://www.communa.org.il" target="blank" onclick="<?php echo fic_footer_track_click('sponsor_ICD'); ?>">
            <img src="<?php echo $footer_logo_path; ?>icd.jpg" alt="International Communes Desk" title="International Communes Desk" height="40" hspace="4" border="0">
          </a>
            <a href="http://www.communa.org.il/icsa" target="blank" onclick="<?php echo fic_footer_track_click('sponsor_ICSA'); ?>">
            <img src="<?php echo $footer_logo_path; ?>icsa.jpg" alt="ICSA" title="ICSA" height="40" hspace="4" border="0">
          </a>
            <a id="csa-footer-logo" href="http://www.communalstudies.org/" target="blank" onclick="<?php echo fic_footer_track_click('sponsor_CSA'); ?>">
            <img src="<?php echo $footer_logo_path; ?>csa.gif" alt="Communal Studies Association" title="Communal Studies Association" height="50" hspace="4" border="0">
          </a>
            <a href="http://www.transitionus.org/" target="blank" onclick="<?php echo fic_footer_track_click('sponsor_TransitionUS'); ?>">
            <img src="<?php echo $footer_logo_path; ?>transition-us.png" alt="Transition US" title="Transition US" height="40" hspace="4" border="0">
          </a>
            <a href="https://nwcommunities.org/" target="blank" onclick="<?php echo fic_footer_track_click('sponsor_NICA'); ?>">
            <img src="<?php echo $footer_logo_path; ?>nica.jpg" alt="NW Intentional Communities Association" title="NW Intentional Communities Association" width="114" height="40" hspace="4" border="0">
          </a>
        </div>
      </div>
    </div>
  </div>

</div>
<?php wp_footer(); ?>
</body>
</html>
