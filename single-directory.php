<?php

remove_filter('the_content', 'wpautop');


ThemeGeneral::top(); ?>

<?php ThemeGeneral::image_banner(); ?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
  <div><?php the_content(); ?></div>
</article>

<?php ThemeGeneral::bottom(); ?>
