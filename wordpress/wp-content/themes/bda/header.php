<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>

  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

  <title><?php wp_title( ' | ', true, 'right' ); ?></title>

  <meta name="HandheldFriendly" content="True">
  <meta name="MobileOptimized" content="480">
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

  <?php // TODO icons & favicons (for more: http://www.jonathantneal.com/blog/understand-the-favicon/) ?>
  <!-- <link rel="apple-touch-icon" href="<?php echo get_template_directory_uri(); ?>/library/images/apple-icon-touch.png"> -->
  <link rel="icon" href="<?php echo get_template_directory_uri(); ?>/favicon.gif">
  <!--[if IE]>
    <link rel="shortcut icon" href="<?php echo get_template_directory_uri(); ?>/favicon.gif">
  <![endif]-->
  <?php // or, set /favicon.ico for IE10 win ?>
  <meta name="msapplication-TileColor" content="#f01d4f">
  <meta name="msapplication-TileImage" content="<?php echo get_template_directory_uri(); ?>/library/images/win8-tile-icon.png">

  <?php
    global $wp_query;
    if (is_home()) $canonical = get_home_url();
    else if (is_tax() || is_tag() || is_category()) $canonical = get_term_link($wp_query->queried_object);
    else if (is_author()) {
      $author = (get_query_var('author_name')) ? get_user_by('slug', get_query_var('author_name')) : get_userdata(get_query_var('author'));
      $canonical = get_author_posts_url($author->ID);
    }
    else if (is_single() && $wp_query->posts && $wp_query->posts[0]) {
      $canonical = get_permalink($wp_query->posts[0]->ID);
    }

    if ($canonical) {?>
  <link rel="canonical" href="<?php echo $canonical; ?>" />
    <?php } ?>

  <?php wp_head(); ?>

  <?php // TODO Google Analytics Here ?>

</head>

<body <?php body_class(); ?>>

<div id="header-wrapper">
  <header id="header" class="page-wrapper clearfix" role="banner">
    <section id="branding">
      <div id="site-title">
      <?php if ( ! is_singular() ) { echo '<h1>'; } ?>
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>" title="<?php esc_attr_e( get_bloginfo( 'name' ), 'bda' ); ?>" rel="home">
          <img src="/img/logo.gif" alt="<?php echo esc_html( get_bloginfo( 'name' ) ); ?>" />
        </a>
      <?php if ( ! is_singular() ) { echo '</h1>'; } ?>
      </div>
    </section>

    <nav id="menu" role="navigation">
      <?php
        if (is_page()) $menu_title = get_the_title();
        else if (is_home()) $menu_title = 'Home';
        else $menu_title = 'Menu';
      ?>
      <ul class="mobile-selected-menu"><li class="menu-item"><img src="<?php echo get_template_directory_uri(); ?>/img/icons/menu.png" class="menu-icon"><a href="javascript:void(0)"><?php echo $menu_title; ?></a></li></ul>
      <?php wp_nav_menu( array( 'theme_location' => 'main-menu' ) ); ?>
    </nav>
  </header>
</div>

<div class="page-wrapper">

<div id="container">