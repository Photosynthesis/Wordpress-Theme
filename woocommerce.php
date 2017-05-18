<?php ThemeGeneral::top('wc'); ?>


<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
  <div class='mt-3'><?php woocommerce_content(); ?></div>
</article>


<?php ThemeGeneral::bottom('wc'); ?>
