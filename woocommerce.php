<?php

if (is_shop()) {
  get_header();
  echo "<div class='row'><div class='col mt-2'>";
} else {
  ThemeGeneral::top('wc');
  ThemeGeneral::image_banner();
}
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
  <div class='mt-3'><?php woocommerce_content(); ?></div>
</article>


<?php
if (is_shop()) {
  echo "</div></div>";
  get_footer();
} else {
  ThemeGeneral::bottom('wc');
}
?>
