<?php ThemeGeneral::top(); ?>

<?php ThemeGeneral::image_banner(); ?>

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
    <div class='mt-3 entry-content'><?php the_content(); ?></div>
    <div class='mt-4'><?php
      $posts_query = new WP_Query(array(
        'category_name' => 'communities-articles',
        'post_type' => 'post',
        'posts_per_page' => 15,
      ));
      if ($posts_query->have_posts()) {
        while ($posts_query->have_posts()) {
          $posts_query->the_post();
          get_template_part('content', 'archive');
        }
      } ?>
    </div>
  </div>

</article>


<?php ThemeGeneral::bottom(); ?>
