<?php theme_top('wc'); ?>


<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
  <div class='mt-3'><?php woocommerce_content(); ?></div>
</article>


<?php theme_bottom('wc'); ?>
