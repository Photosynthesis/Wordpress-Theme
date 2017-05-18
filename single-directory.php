<?php

remove_filter('the_content', 'wpautop');


theme_top(); ?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
  <div><?php the_content(); ?></div>
</article>

<?php theme_bottom(); ?>
