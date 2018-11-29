<!-- div#body-wrapper is opened -->
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
