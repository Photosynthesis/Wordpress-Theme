<?php ThemeGeneral::top(); ?>


<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
  <div class='clearfix'><?php
    $thumbnail = get_the_post_thumbnail(get_the_ID(), 'post-thumbnail', array('class' => 'p-1 img-fluid'));
    if ($thumbnail != '') { ?>
      <div class="float-sm-left mr-sm-2 mb-2">
        <div class='text-center text-sm-left'>
          <div class="card d-inline-block"><a href="<?php the_permalink(); ?>">
            <?php echo $thumbnail; ?>
          </a></div>
        </div>
      </div><?php
    } ?>
    <div>
      <?php if (!is_page()) { ?>
        <h1 class='entry-title'><?php the_title(); ?></h1>
        <div>
          <small class="text-muted pb-3">
            Posted on <span class='updated published'><?php echo get_the_date('F j, Y'); ?></span>
            by <span class='author'><?php the_author_posts_link(); ?></span>
            <br class='hidden-lg-up' /><span class='hidden-md-down'> - </span>
            <a href="<?php comments_link(); ?>"><?php comments_number("0 Comments"); ?></a>
          </small>
        </div>
      <?php } ?>
    </div>
    <div class='mt-3 entry-content'><?php the_content(); ?></div>
  </div>

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


ThemeGeneral::bottom();

?>
