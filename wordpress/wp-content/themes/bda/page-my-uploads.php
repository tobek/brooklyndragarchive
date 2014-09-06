<?php
if (is_user_logged_in()) {
  $username = get_user_meta(get_current_user_id(), 'nickname', true);
  $token = get_youtube_token();

  get_header();
  echo '<section id="content" class="clearfix" role="main">';
  ?>

  <article class="masonry-item"><section class="entry-content"><div class="inner">
    <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); the_content(); endwhile; endif; ?>

    <?php // hidden form for JS youtube upload ?>
    <form style="display: none;" id="upload-yt-video" action="https://www.googleapis.com/upload/youtube/v3/videos?part=snippet&access_token=<?php echo $token; ?>" method="post" enctype="multipart/form-data">
      <p><input type="hidden" name="username" value="<?php echo $username; ?>"></p>
      <p><input type="file" name="videoFile"></p>
      <p><input type="submit" value="Upload File"></p>
    </form>

  </div></section></article>
  
  <?php 

  // now lets get the logged-in user's posts

  global $my_uploads;
  $my_uploads = true; // triggers delete icon and other stuff

  $user = wp_get_current_user();
  query_posts('author=' . $user->ID);

  if ( have_posts() ) : while ( have_posts() ) : the_post();
    get_template_part( 'entry', get_post_format() );
  endwhile; endif;

  echo '</section>';

  get_footer();
}
else {
  header( 'Location: /login/' );
  exit;
}