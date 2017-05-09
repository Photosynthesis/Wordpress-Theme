<?php

remove_filter('the_content', 'wpautop');


theme_top(); ?>

<a class='mb-3 d-block text-center' href='/fall-fundraising-campaign'>
  <img class='img-fluid' src='/wp-content/uploads/2016/12/FundraiserBanner5.png' />
</a>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
  <div><?php the_content(); ?></div>
</article>

<?php theme_bottom(); ?>
