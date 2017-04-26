<?php
theme_top();


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


theme_bottom();
