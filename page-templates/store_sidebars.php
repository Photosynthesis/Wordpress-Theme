<?php
/*
Template Name: Store Sidebars
 */

theme_top('wc'); ?>


<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
    <div class='clearfix'><?php
      if (has_post_thumbnail()) {
        $thumbnail = get_the_post_thumbnail();
        if ($thumbnail != '') { ?>
          <div class="float-left mr-2 mb-2">
            <div class="card"><a href="<?php the_permalink(); ?>">
              <?php echo $thumbnail; ?>
            </a></div>
          </div><?php
        }
        } ?>
      <div><h1><?php the_title(); ?></h1></div>
    </div>

  <div class='mt-3'><?php the_content(); ?></div>

</article>


<?php theme_bottom('wc'); ?>
