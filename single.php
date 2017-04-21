<?php get_header(); ?>

<div class="row">
  <!-- Left Sidebar -->
  <div id="left-sidebar" class="<?php echo theme_left_sidebar_css_classes(); ?>">
    <?php dynamic_sidebar('main-left'); ?>
  </div>


  <!-- Content -->
  <div class="<?php echo theme_main_column_css_classes(); ?>" id="main">
    <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
      <div class='clearfix'>
        <div class="float-left mr-2 mb-2">
          <div class="card"><a href="<?php the_permalink(); ?>">
              <?php the_post_thumbnail('post-thumbnail'); ?>
          </a></div>
        </div>
        <div>
          <h1><?php the_title(); ?></h1>
        </div>
      </div>
      <p>
          <small class="text-muted pb-3">
            Posted on <?php echo get_the_date('F j, Y'); ?>
            by <?php the_author_posts_link(); ?> -
            <a href="<?php comments_link(); ?>"><?php comments_number("0 Comments"); ?></a>
          </small>
      </p>

      <div><?php the_content(); ?></div>

    </article>

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
    <?php if (comments_open() || get_comments_number()) {
      comments_template();
    } ?>

  </div>



  <!-- Right Sidebar -->
  <div id="right-sidebar" class="<?php echo theme_right_sidebar_css_classes(); ?>">
    <?php dynamic_sidebar('main-right'); ?>
  </div>
</div>

<?php get_footer(); ?>
