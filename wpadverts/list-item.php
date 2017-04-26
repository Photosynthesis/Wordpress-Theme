<?php
$is_featured = get_post(get_the_ID())->menu_order;
$extra_class = $is_featured ? 'bg-info' : '';
?>
<a href='<?php the_permalink(); ?>' class='list-group-item list-group-item-action <?php echo $extra_class; ?>'><?php
  $image = adverts_get_main_image(get_the_ID());
  $image_src = esc_attr($image); ?>
  <div class='row w-100'>
    <div class='col-sm-8'><?php
      if ($image) {
        echo "<img src='{$image_src}' class='img-fluid' />";
      } ?>
    </div>
    <div class='col-sm-16'>
      <h3><?php the_title(); ?></h3>
      <div><?php echo get_post_meta(get_the_ID(), 'adverts_location', true); ?></div>
    </div>
  </div>
</a>
