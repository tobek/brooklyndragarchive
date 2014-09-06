<?php get_header(); ?>

<section id="content" role="main">
  <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
  <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
    <?php
    if ( has_post_thumbnail() ) {
      $large_src = wp_get_attachment_image_src(get_post_thumbnail_id(get_the_ID()), 'large');
      echo "<section class='entry-img' style='background-image: url(\"$large_src[0]\")'></section>";
    }
    ?>
    <section class="entry-content">
      <div class="inner">
        <h1 class="entry-title"><?php the_title(); ?></h1> <?php // edit_post_link(); ?>
        <?php the_content(); ?>
      </div>
    </section>
  </article>
  <?php endwhile; endif; ?>
</section>

<?php get_footer(); ?>