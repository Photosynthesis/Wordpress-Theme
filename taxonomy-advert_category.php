<?php theme_top(); ?>


<h1><?php echo str_replace('Category: ', '', get_the_title()); ?></h1>
<hr class="mt-2" />
<p><?php the_archive_description(); ?></p>
<div class="posts">
  <?php
  if (have_posts()) {
    global $wp_query;
    remove_filter('the_content', 'adverts_the_content');
    echo shortcode_adverts_list(array("category" => $wp_query->get_queried_object_id()));
  } ?>
</div>
<div class='paginate clearfix'>
  <div class='float-left'><?php next_posts_link('&laquo; Older Posts'); ?></div>
  <div class='float-right'><?php previous_posts_link('Newer Posts &raquo;', ''); ?></div>
</div>


<?php theme_bottom(); ?>
