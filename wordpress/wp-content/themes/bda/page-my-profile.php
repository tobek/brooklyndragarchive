<?php get_header(); ?>

<section id="content" role="main">
  <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
  <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
    <section class="entry-content">
      <div class="inner">
        <h1 class="entry-title">Profile</h1>
        <?php echo do_shortcode('[wppb-edit-profile]'); ?>
        <button class="request-performer">I'm a performer</button>
        <div class="request-performer">

        <?php
        if (is_user_logged_in()) {
          $user = wp_get_current_user();
          global $wpdb;
          $performer_term_id = $wpdb->get_var($wpdb->prepare('SELECT term_id FROM wp_term_taxonomy WHERE description = %s', $user->ID));
          if ($performer_term_id) {
            // this user is a performer
            $performer_term = get_term($performer_term_id, "performer");
          }
        }

        if ($performer_term) { ?>
          <div class="before">
            <p>We know! You're <?php echo $performer_term->name; ?>. Your performer page is <a href='/performer/<?php echo $performer_term->slug; ?>/'>here</a>.</p>
            <p><a href='mailto:info@brooklyndragarchive.org'>Get in touch</a> if you have any questions!</p>
          </div>
        <?php } else { ?>
          <div class="before">
            <p>That's great! Fill out this form to be added to our <a href='/performers/'>performers list</a> and claim your profile page listing all uploads tagged with your name.</p>
            <input type="text" placeholder="Your name" />
            <textarea placeholder="Message to us (optional)"></textarea>
            <button>Submit</button>
          </div>
          <div class="after">
            <p>Thanks! We'll get back to you at the email address associated with your account.</p>
          </div>
        <?php } ?>

        </div>
      </div>
    </section>
  </article>
  <?php endwhile; endif; ?>
</section>

<?php get_footer(); ?>
