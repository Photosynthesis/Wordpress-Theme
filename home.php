<?php ThemeGeneral::top(); ?>


<div class="posts">
  <?php
  if (have_posts()) {
    while (have_posts()) {
      the_post(); ?>
      <div id="post-<?php the_ID(); ?>" class="hentry <?php echo join(" ", get_post_class("post-summary")); ?>">
        <div class="clearfix">
          <?php if (has_post_thumbnail()) { ?>
          <div class="float-sm-left mr-sm-2 mb-1">
            <div class='text-center text-sm-left'>
              <div class="card d-inline-block">
                <a href="<?php the_permalink(); ?>" rel='bookmark'>
                  <?php the_post_thumbnail('post-thumbnail', array('class' => 'p-1 img-fluid')); ?>
                </a>
              </div>
            </div>
          </div>
          <?php } ?>
          <div>
            <h2 class="mb-0 entry-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
            <small class="text-muted pb-3">
              Posted on <span class='published updated'><?php echo get_the_date('F j, Y'); ?></span>
              by <span class='author'><?php the_author_posts_link(); ?></span>
              <?php if (get_comments_number() > 0) { ?>
                <br />
                <a href="<?php comments_link(); ?>"><?php comments_number("0 Comments"); ?></a>
              <?php } ?>
            </small>
            <div class='entry-summary'><?php the_excerpt(); ?></div>
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


<?php ThemeGeneral::bottom(); ?>
