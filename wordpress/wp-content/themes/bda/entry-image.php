<?php
global $my_uploads;
$youtube_id = get_post_meta(get_the_ID(), 'youtube_id', true);
$youtube_thumb_url = get_post_meta(get_the_ID(), 'thumb_url', true);
$post_class = "masonry-item" . ($youtube_id ? ' has-video' : '');
?>

<article id="post-<?php the_ID(); ?>" <?php post_class($post_class); ?>>
  <?php if (is_single()) { ?>
    TODO
  <?php } else { ?>
    <div class="img-holder">
      <div class="hover-fill"></div>
      <?php
      if ($youtube_id && $youtube_thumb_url) { ?>
        <img src="<?php echo $youtube_thumb_url; ?>" class="masonry-item-content wp-post-image" data-youtube-id="<?php echo $youtube_id; ?>">
        <div class="youtube-embed-holder"></div>
        <span class="icon video"><img src="<?php echo get_template_directory_uri(); ?>/img/icons/video-large-white.png"></span>
      <?php }
      else {
        $large_src = wp_get_attachment_image_src(get_post_thumbnail_id(get_the_ID()), 'large');
        if ( has_post_thumbnail() ) {
          the_post_thumbnail("thumbnail", array(
            "class" => "masonry-item-content",
            "data-large-src" => $large_src[0]
          ));
        }
      }

      ?>

      <div class="details">
        <div class="description">
          <?php the_content(); ?>
        </div><div class="meta">
          <?php if (get_the_tags()) { ?>
          <span class="stage-tags">
            <?php the_tags(); ?>
          </span>
          <?php } ?>
        </div>
        <?php
          $author_name = get_the_author_meta('nickname');
          $author_url = get_author_posts_url(get_the_author_meta('ID'));
        ?>
        <span class="uploaded-by">Uploaded by <a href="<?php echo $author_url; ?>"><?php echo $author_name; ?></a></span>
      </div>

      <a href="#" class="icon close"><img src="<?php echo get_template_directory_uri(); ?>/img/icons/close.png"></a>

      <?php if ($my_uploads) { ?>

        <a href="/delete/?id=<?php the_ID(); ?>" class="icon delete"><img src="<?php echo get_template_directory_uri(); ?>/img/icons/delete.png"></a>

      <?php } else {

        if ($user_id = get_current_user_id()) { // if logged in
          $likes = get_user_meta($user_id, "liked_posts", true);
          // error_log("checking for ".get_the_ID());
          // error_log(print_r($likes,true));
          if (is_array($likes) && in_array(get_the_ID(), $likes)) $liked = "liked";
        }

        ?>
        <a href="/like/?id=<?php the_ID(); ?>" class="icon like <?php echo $liked; ?>" title="<?php echo get_post_meta(get_the_ID(), "num_likes", true); ?>">
          <img class="off" src="<?php echo get_template_directory_uri(); ?>/img/icons/heart.png">
          <img class="on" src="<?php echo get_template_directory_uri(); ?>/img/icons/heart-orange.png">
        </a>

      <?php } ?>
    </div>
    <section class="entry-content">
      <h2><?php //the_title(); ?> </h2>
      <div class="meta">
        <?php
        $terms = get_term_object();
        foreach (array('performer', 'event', 'venue') as $taxon) {
          if ($terms[$taxon]) {
            echo "<span class='taxon'>" . ucfirst($taxon) . ": </span>";
            foreach ($terms[$taxon] as $i => $term) {
              echo "<a href='$term[url]'>$term[name]</a>";
              if ($i < count($terms[$taxon]) -1) echo ", ";
            }
            echo "<br>";
          }
        }
        ?>
      </div>
    </section>
  <?php } ?>
</article>