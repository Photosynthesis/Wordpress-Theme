<?php ThemeGeneral::top(); ?>

<?php ThemeGeneral::image_banner(); ?>


<?php if (is_category()) { ?>
  <h1><?php echo single_cat_title(); ?></h1>
<?php } else { ?>
  <h1><?php echo get_the_archive_title(); ?></h1>
<?php } ?>
<hr class="mt-2" />

<p><?php the_archive_description(); ?></p>
<div class="posts">
  <?php
  if (have_posts()) {
    while (have_posts()) {
      the_post();
      get_template_part('content', 'archive');
    }
  } ?>
</div>
<div class='paginate clearfix'>
  <div class='float-left'><?php next_posts_link('&laquo; Older Posts'); ?></div>
  <div class='float-right'><?php previous_posts_link('Newer Posts &raquo;', ''); ?></div>
</div>


<?php ThemeGeneral::bottom(); ?>
