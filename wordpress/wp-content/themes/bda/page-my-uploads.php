<?php
if (is_user_logged_in()) {
  $username = get_user_meta(get_current_user_id(), 'nickname', true);
  $token = get_youtube_token();

  get_header();
  echo '<section id="content" class="clearfix" role="main">';
  ?>

  <article class="masonry-item">
    <div class="loader"></div>
    <section class="entry-content">
      <h1>New Upload</h1>
      <div class="inner">
        <?php if ($_GET['response'] == 'bda-deleted') { ?>
          <p class="ugc-notice success">Upload successfully deleted</p>
        <?php } ?>
        <div class="ugc-inner-wrapper video-image-label"><div class="ugc-input-wrapper"><label>Image/Video (required)</label></div></div>
        <?php // hidden form for JS youtube upload (not hidden on iphones, on where there's a horrible hack) ?>
        <form style="display: none;" id="upload-yt-video" action="https://www.googleapis.com/upload/youtube/v3/videos?part=snippet&access_token=<?php echo $token; ?>" method="post" enctype="multipart/form-data">
          <p style="display: none;"><input type="hidden" name="username" value="<?php echo $username; ?>"></p>
          <p style="margin: 0;"><span class="input-label-hack">Video: </span><input type="file" accept="video/*" name="videoFile"></p>
          <p style="display: none;"><input type="submit" value="Upload File"></p>
          <p class="iphone-or">or</p>
        </form>

        <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); the_content(); endwhile; endif; ?>

      </div>
    </section>
  </article>
  <div id="debug"></div>
  
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
