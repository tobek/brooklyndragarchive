<?php get_header(); ?>

<section id="content" role="main" class="performers">
  <?php
  $performers = get_terms("performer");
  if ( !empty( $performers ) && !is_wp_error( $performers ) ){
     echo "<ul>";
     foreach ( $performers as $performer ) {
       echo "<li><a href='/performer/$performer->slug/'>$performer->name</a></li>";
     }
     echo "</ul>";
  }
  ?>
</section>

<?php get_footer(); ?>