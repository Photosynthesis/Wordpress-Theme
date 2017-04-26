<?php theme_top(); ?>


<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
  <div class='clearfix'><?php
    $thumbnail = get_the_post_thumbnail();
    if ($thumbnail != '') { ?>
      <div class="float-left mr-2 mb-2">
        <div class="card"><a href="<?php the_permalink(); ?>">
          <?php echo $thumbnail; ?>
        </a></div>
      </div><?php
    } ?>
    <div>
      <h1><?php the_title(); ?></h1>
    </div>
  </div><?php

  if (!is_page()) { ?>
    <p>
      <small class="text-muted pb-3">
        Posted on <?php echo get_the_date('F j, Y'); ?>
        by <?php the_author_posts_link(); ?> -
        <a href="<?php comments_link(); ?>"><?php comments_number("0 Comments"); ?></a>
      </small>
    </p><?php
  } ?>

  <div class='mt-3'><?php the_content(); ?></div>

</article>


<?php
if (!is_page()) { ?>
  <div class="text-muted"><small>
    Filed Under: <?php the_category(', ', ''); ?>
  </small></div>

  <hr />

  <!-- Next/Previous Links -->
  <div class="clearfix post-links mb-4">
    <div class='float-left w-40'><?php previous_post_link('%link', '<button class="btn btn-secondary bg-faded"><small>← %title</small></button>'); ?></div>
    <div class='float-right w-40'><?php next_post_link('%link', '<button class="btn btn-secondary bg-faded"><small>%title →</small></button>'); ?></div>
  </div>

  <!-- Comments -->
  <?php if (comments_open() || get_comments_number())  {
    comments_template();
  }
}


theme_bottom();

?>
