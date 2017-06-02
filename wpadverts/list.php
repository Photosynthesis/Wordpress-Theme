<?php
/*
 * included by adverts/includes/shortcodes.php shortcodes_adverts_list()
 *
 * @var $loop WP_Query
 * @var $query string
 * @var $location string
 * @var $paged int
 */


if ($search_bar == "enabled") {
  $search_query = isset($_GET['query']) ? $_GET['query'] : ''; ?>
  <div class='card bg-faded mb-3'>
    <form class='pt-3 pb-3 pl-2 pr-2 clearfix' action='<?php echo esc_attr($action); ?>' method='get'>
      <div class='row'>
        <div class='col-sm-18'>
          <input type='text' class='form-control' placeholder='Keyword...' name='query' value='<?php echo $search_query; ?>'/>
        </div>
        <div class='col-sm-6'>
          <input class='float-right btn btn-primary btn-block' type='submit' value='Search' />
        </div>
      </div>
    </form>
  </div><?php
}


if ($show_results && $loop->have_posts()) { ?>
  <div class='list-group mb-3'><?php
    while ($loop->have_posts()) {
      $loop->the_post();
      include apply_filters('adverts_template_load', ADVERTS_PATH . 'templates/list-item.php');
    } ?>
  </div><?php
  wp_reset_query();

  if ($show_pagination) { ?>
    <div class='clearfix d-flex'><ul class='pagination mx-auto'><?php
      $links = paginate_links(array(
        'type' => 'array',
        'base' => $paginate_base,
        'format' => $paginate_format,
        'current' => max(1, $paged),
        'total' => $loop->max_num_pages,
        'prev_next' => false
      ));
      if (is_array($links)) {
        foreach ($links as $index => $link) {
          $class = 'page-item';
          $link = str_replace('page-numbers', 'page-link', $link);
          if (($index + 1) == max(1, $paged)) {
            $class .= ' active';
            $link = str_replace('current', 'active', $link);
          }
          echo "<li class='{$class}'>{$link}</li>\n";
        }
      }
    ?>
    </ul></div><?php
  }
} else if ($show_results) { ?>
  <div class='m-1'>There are no ads matching your search criteria.</div><?php
} ?>
