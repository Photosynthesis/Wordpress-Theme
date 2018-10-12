<?php
ThemeGeneral::top();

ThemeGeneral::image_banner();


if (have_posts()) {
  while (have_posts()) {
    the_post(); ?>
    <div class="post">
      <div id="main" class="entry">
        <?php the_content(); ?>
      </div>
        </div><?php
  }
}


ThemeGeneral::bottom();
