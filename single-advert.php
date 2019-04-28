<?php

wp_enqueue_script('wpadverts-mal-google-api');

ThemeGeneral::top();
$post_id = get_the_ID();
?>

<h2><?php the_title(); ?></h2>


<!-- Image Slider -->
<?php
$children = get_children(array('post_parent' => $post_id));
$thumb_id = get_post_thumbnail_id($post_id);
$images = array();

if (!empty($children)) {
  if (isset($children[$thumb_id])) {
    $images[$thumb_id] = $children[$thumb_id];
    unset($children[$thumb_id]);
  }

  $images += $children;
  $images = adverts_sort_images($images, $post_id); ?>
  <div id='advert-carousel' class='carousel slide' data-ride='carousel'>
    <div class='carousel-inner<?php echo sizeof($children) > 1 ? ' bg-primary' : ''; ?>' role='listbox'><?php
      $first_image = true;
      foreach ($images as $image_post) {
        $class ='carousel-item';
        if ($first_image === true) {
          $class .= ' active';
          $first_image = false;
        }
        $image = wp_get_attachment_image_src($image_post->ID, 'large');
        if (isset($image[0])) {
          $image_url = esc_attr($image[0]);
          echo "<div class='{$class}'><img class='d-block mx-auto' src='{$image_url}' /></div>";
        }
      } ?>
    </div><?php
    if (sizeof($images) > 1) { ?>
      <a class="carousel-control-prev" href="#advert-carousel" role="button" data-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="sr-only">Previous</span>
      </a>
      <a class="carousel-control-next" href="#advert-carousel" role="button" data-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="sr-only">Next</span>
      </a><?php
    } ?>
  </div><?php
} ?>


<!-- Author & Info -->
<div class='media mt-3'>
  <?php echo get_avatar(get_post_meta($post_id, 'adverts_email', true), 48, '', '', array('class' => 'd-flex mr-3')); ?>
  <div class='media-body'>
    <?php echo apply_filters("adverts_tpl_single_posted_by", sprintf(__("by <strong>%s</strong>", "adverts"), get_post_meta($post_id, 'adverts_person', true)), $post_id) ?><br/>
  </div>
</div>


<!-- Category / Location -->
<dl class="row mt-3">
  <dt class='col-6'><?php _e("Category", "adverts") ?></dt>
  <dd class='col-18'><?php
    $advert_category = get_the_terms($post_id, 'advert_category');
    foreach ($advert_category as $c) { ?>
      <a href="<?php esc_attr_e(get_term_link($c)); ?>"><?php echo join(" / ", advert_category_path($c)); ?></a><?php
    } ?>
  </dd>

  <?php
  if (get_post_meta($post_id, 'adverts_location', true)) { ?>
    <dt class='col-6'><?php _e("Location", "adverts") ?></dt>
    <dd class='col-18'>
      <?php echo apply_filters("adverts_tpl_single_location", esc_html(get_post_meta($post_id, "adverts_location", true)), $post_id); ?>
    </dd><?php
  } ?>
</dl>
<?php do_action( "adverts_tpl_single_details", $post_id ) ?>


<!-- Message / Phone -->
<button id='adverts-send-message-button' class='btn btn-warning mr-2'>Send Message</button>
<?php
$phone_number = get_post_meta($post_id, 'adverts_phone', true);
if ($phone_number) {
  echo "<button id='adverts-phone-number-button' class='btn btn-secondary'>Phone <a href='tel:{$phone_number}'>{$phone_number}</a></button>";
}

do_action("adverts_tpl_single_bottom", $post_id); ?>


<!-- Content -->
<div><?php the_content(); ?></div>


<?php ThemeGeneral::bottom(); ?>
