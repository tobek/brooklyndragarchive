<?php get_header(); ?>
<section id="content" class="clearfix" role="main">

  <?php
  global $wp_query;
  if (is_tax('performer') || is_author()) {
    if (is_author()) {
      $author = (get_query_var('author_name')) ? get_user_by('slug', get_query_var('author_name')) : get_userdata(get_query_var('author'));
      $user_id = $author->ID;
      $name = $author->nickname;
      $title = '<span class="title-taxon">Uploaded by </span>' . $name;
    }
    else {
      $term = get_term_by('slug', $wp_query->query_vars['performer'], 'performer');
      $name = $term->name;
      $title = $name;
      $user_id = intval($term->description);
    }

    if ($user_id && ($user = get_userdata($user_id))) {
      $avatar = get_user_meta($user_id, 'avatar', true);
      $bio = get_user_meta($user_id, 'description', true);
      $url = $user->user_url; ?>
  <article class="performer-card format-image masonry-item">
    <div class="img-holder">
      <div class="hover-fill"></div>
      <?php if ($avatar) { ?>
        <img src="<?php echo $avatar; ?>" alt="<?php echo $name; ?>" data-large-src="<?php echo $avatar; ?>">
      <?php } ?>
      <a href="#" class="icon close"><img src="http://brooklyndragarchive.org/wordpress/wp-content/themes/bda/img/icons/close.png"></a>
    </div>
    <section class="entry-content">
      <h1><?php echo $title; ?></h1>
      <div class="meta">
        <p><?php echo $bio; ?></p>
        <p><a href='<?php echo $url; ?>' target='_blank'><?php echo $url; ?></a></p>
      </div>
    </section>
  </article>
    <?php } // end if performer has claimed page
    else { ?>
  <article class="performer-card format-image masonry-item">
    <section class="entry-content">
      <h1><?php echo $title; ?></h1>
    </section>
  </article>
    <?php } ?>
  <?php } // end if perfomer taxonomy or uploader
  if (is_tag() || is_tax('event') || is_tax('venue')) {
    if (is_tag()) {
      $title = single_tag_title('<span class="title-taxon">Tag: </span>', false);
    }
    else if (is_tax('event')) {
      $term = get_term_by('slug', $wp_query->query_vars['event'], 'event');
      $title = '<span class="title-taxon">Event: </span>' . $term->name;
    }
    else if (is_tax('venue')) {
      $term = get_term_by('slug', $wp_query->query_vars['venue'], 'venue');
      $title = '<span class="title-taxon">Venue: </span>' . $term->name;
    }
    // if (is_author()) {
    //   $author = (get_query_var('author_name')) ? get_user_by('slug', get_query_var('author_name')) : get_userdata(get_query_var('author'));
    //   $title = '<span class="title-taxon">Uploaded by </span>' . $author->nickname;
    // }
    ?>
  <article class="performer-card format-image masonry-item">
    <section class="entry-content">
      <h1><?php echo $title ?></h1>
    </section>
  </article>
  <?php }
  ?>

  <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

  <?php get_template_part( 'entry', get_post_format() ); ?>

  <?php endwhile; endif; ?>

  <?php if (is_home() && !is_user_logged_in() && !is_paged()) { // echo out register widget ?>
    <article id="register-widget" class="masonry-item">
      <div class="inner">
        <h2>Contribute to the Archive</h2>
        <?php echo do_shortcode('[wppb-register]'); ?>
      </div>
    </article>
    <script>
      // hack-ish: send user to register page for registering
      $('#adduser').attr('action', '/register/');
    </script>
  <?php } ?>

</section>

<div id="pagination">
<?php
  global $wp_query;
  echo paginate_links(array(
    'base'         => str_replace(999999999, '%#%', esc_url(get_pagenum_link(999999999))),
    'format'       => '/page/%#%',
    'total'        => $wp_query->max_num_pages,
    'current'      => max(1, get_query_var('paged')),
    'show_all'     => False,
    'end_size'     => 0,
    'mid_size'     => 0,
    'prev_next'    => True,
    'prev_text'    => '« Newer',
    'next_text'    => 'Older »'
  ));
?>
</div>

<?php get_footer(); ?>