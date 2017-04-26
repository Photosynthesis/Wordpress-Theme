<?php theme_top(); ?>


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
      the_post(); ?>
      <div id="post-<?php the_ID(); ?>" class="<?php echo join(" ", get_post_class("post-summary")); ?>">
        <div class="clearfix">
          <div class="float-left mr-2 mb-1">
            <div class="card">
              <a href="<?php the_permalink(); ?>">
                <?php the_post_thumbnail('post-thumbnail'); ?>
              </a>
            </div>
          </div>
          <div>
            <h2 class="mb-0"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
            <small class="text-muted pb-3">
              Posted on <?php echo get_the_date('F j, Y'); ?>
              by <?php the_author_posts_link(); ?><br />
              <a href="<?php comments_link(); ?>"><?php comments_number("0 Comments"); ?></a>
            </small>
            <?php the_excerpt(); ?>
          </div>
        </div>
        <div class="text-muted"><small>
          Filed Under: <?php the_category(', ', ''); ?>
        </small></div>
      </div>
      <hr />
  <?php }
  } ?>
</div>
<div class='paginate clearfix'>
  <div class='float-left'><?php next_posts_link('&laquo; Older Posts'); ?></div>
  <div class='float-right'><?php previous_posts_link('Newer Posts &raquo;', ''); ?></div>
</div>


<?php theme_bottom(); ?>
