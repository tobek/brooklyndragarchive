<?php

global $TERM_LIST;
$TERM_LIST = array('performer', 'venue', 'event');

add_action( 'template_redirect', 'ghetto_rewrite_rules' );
function ghetto_rewrite_rules() {
    global $wp_query;

    // error_log(print_r($wp_query, true));
    if ($wp_query->query_vars['name'] === 'yt-tok') {
      $token = get_youtube_token();
      if (!$token) {
        status_header(500);
        header('Content-Type: text/plain');
        echo 'failed to get token';
      }
      else {
        status_header(200);
        header('Content-Type: text/plain');
        echo $token;
      }
      exit;
    }
    // if ($wp_query->query_vars['name'] === 'event') {
    //   status_header(200);
    //   get_template_part('index');
    //   exit;
    // }
    else {
      return;
    }
}

add_action('init', 'bda_change_author_slug_base', 0);
function bda_change_author_slug_base() {
    global $wp_rewrite;
    $wp_rewrite->author_base = 'uploader';
}

add_action('save_post', 'bda_add_tribe_fields_to_taxon', 99);
function bda_add_tribe_fields_to_taxon($post_id) {
  global $TERM_LIST, $added_tribe_fields_already;
  if (isset($_POST['community-event'])) {
    // mysteriously this action gets called more than once
    if ($added_tribe_fields_already) return;
    $added_tribe_fields_already = true;

    $event_name = $_POST['post_title'];
    if ($_POST['venue'] && $_POST['venue']['Venue']) $venue_name = $_POST['venue']['Venue'];
    if ($_POST['organizer'] && $_POST['organizer']['Organizer']) $performer_name = $_POST['organizer']['Organizer'];

    foreach ($TERM_LIST as $term_type) {
      $term_name = ${$term_type . '_name'};
      if ($term_name) {
        // create taxon term if it doesn't exist already:
        $term = get_term_by('name', $term_name, $term_type);
        if (!$term) {
          $new_term = wp_insert_term($term_name, $term_type);
          $term = get_term_by('id', $new_term['term_id'], $term_type);
        }

        // append the term to this 'post'
        wp_set_object_terms($post_id, $term_name, $term_type, true);
      }
    } // end foreach term type
  } // end if community event
}

add_action( 'fu_additional_html', 'bda_additional_fields');
function bda_additional_fields() { ?>
  <div id="bda-fu-uploader-extras">
    <div class="ugc-input-wrapper"><label for="">Description</label><textarea class="wp-editor-area" rows="3" autocomplete="off" name="post_content" placeholder="What's the T?"></textarea></div><br>
    <div id="stage-boxes">
      <div class="ugc-input-wrapper"><label><input type="checkbox" value="" name="on-stage" id="" class="stage"> On Stage</label></div>
      <div class="ugc-input-wrapper"><label><input type="checkbox" value="" name="back-stage" id="" class="stage"> Back Stage</label></div>
      <div class="ugc-input-wrapper"><label><input type="checkbox" value="" name="off-stage" id="" class="stage"> Off Stage</label></div>
    </div>
  </div>
<?php }

add_action( 'fu_upload_result', 'bda_handle_uploads', 10, 2 );
function bda_handle_uploads($layout, $result) {
  global $TERM_LIST;
  if (!$result['post_id'] || !$result['success']) return;
  $post_id = $result['post_id'];

  $tags = array();

  set_post_format($post_id, "image");

  if ($_POST['thumb_url'] && $_POST['youtube_id']) {
    // youtube upload
    // above values get stored as postmeta so we're pretty much done
    // except this:
    $tags[] = 'video';
    
    // uh we don't have to do anything
    error_log('youtube upload');

  } // end if youtube video in post
  else if ($result['media_ids']) {
    // regular image upload 
    set_post_thumbnail($post_id, $result['media_ids'][0]);
  }
  else {
    // no image, no video, wtf
    wp_delete_post($post_id, true);
  }

  // these will be stored in taxonomy, waste of space to also store as custom post meta
  delete_post_meta($post_id, "performer"); delete_post_meta($post_id, "event"); delete_post_meta($post_id, "venue");

  foreach ($TERM_LIST as $term_type) {
    $term_name = sanitize_text_field($_POST[$term_type]);
    if ($term_name) {
      wp_set_post_terms($post_id, $term_name, $term_type, true); // will create if doesn't exist already, appending to existing terms
    }
  }

  if (isset($_POST['on-stage'])) $tags[] = 'on-stage';
  if (isset($_POST['back-stage'])) $tags[] = 'back-stage';
  if (isset($_POST['off-stage'])) $tags[] = 'off-stage';
  if ($tags) wp_set_post_tags($post_id, implode(',', $tags), true);

} // end bda_handle_uploads

add_filter('fu_before_create_post', 'bda_upload_fields');
function bda_upload_fields($post_array) {
  if (!$post_array['post_title']) $post_array['post_title'] = "(no title)";
  $post_array['post_author'] = wp_get_current_user()->ID;
  return $post_array;
}

add_filter('fu_response_map', 'bda_fu_responses');
function bda_fu_responses($map) {
  $map['bda-deleted'] = array(
    'text' => __( 'Upload successfully deleted', 'frontend-uploader' ),
    'class' => 'success',
  );
  return $map;
}

// we're hiding username and having them login by email (but not using wppb's login with email feature cause then users can't change email) but we need to have a username so let's generate a random one that's never used - just internally
add_filter('wppb_register_posted_username', 'pick_random_username');
function pick_random_username($username) {
  return $_POST['nickname'] . mt_rand(0, 100000000000); // 100 billion
}

add_filter('wppb_pre_login_url_filter', 'change_lost_pass_page');
add_filter('lostpassword_url', 'change_lost_pass_page');
function change_lost_pass_page($url) {
  return get_bloginfo('siteurl') . '/create-new-password/';
}

add_filter('wppb_register_button_name', 'register_button_value', 10, 2);
function register_button_value($button_name, $current_user) {
  return "Sign Up";
}

add_filter('register', 'use_front_end_register_url');
function use_front_end_register_url($reg_anchor) {
  return preg_replace('/<a href="[^"]*"/', '<a href="/register/"', $reg_anchor);
}

// as a result of iThemes Security plugin's currently broken Hide Login Area thing, logging out doesn't work
add_filter('logout_url', 'fix_dash_logout', 100, 2);
function fix_dash_logout( $logout_url, $redirect ) {
  return str_replace('dash', 'wp-admin/', $logout_url);
}


add_action('tribe_ce_event_submission_login_form', 'redirect_to_login');
add_action('tribe_ce_event_list_login_form', 'redirect_to_login');
function redirect_to_login() {
  wp_redirect(home_url("/login/"));
  exit;
}

// REDIRECT AFTER SUBMITTING EVENT

// First check that an event was submitted or updated.
add_filter( 'tribe_community_events_form_errors', 'tribe_gist_check_for_community_submission', 10, 1 );
function tribe_gist_check_for_community_submission( $messages ) {
  if ( is_array($messages) && !empty($messages) ) {
    $first_message = reset($messages);
    if ( $first_message['type'] == 'updated' ) {
      add_action( 'parse_request', 'tribe_gist_redirect_after_community_submission', 11, 1 );
    }
  }
  return $messages;
}
/**
 * This actually does the redirect. If an event was submitted, and we're about
 * to reload the submission page (with a message instead of a form), this will
 * redirect to the home page.
 */
function tribe_gist_redirect_after_community_submission( $wp ) {
  if ( isset($wp->query_vars[WP_Router::QUERY_VAR]) && $wp->query_vars[WP_Router::QUERY_VAR] == 'ce-add-route' && !empty($_POST) ) {
    wp_safe_redirect(home_url("/calendar/")); // Edit home_url() to whatever you page want
    exit();
  }
}

// CUSTOM USER-SUBMITTED EVENT REQURIED FIELDS
add_filter('tribe_events_community_required_fields', 'bda_event_sub_required_fields');
function bda_event_sub_required_fields() {
  return array(
    'post_title'
  );
}
add_filter('tribe_events_community_submission_error_message', 'bda_event_sub_custom_error_message');
function bda_event_sub_custom_error_message() {
  return '<p>There was a problem saving your event: the Title field is required.</p>';
}

add_filter('gettext', 'bda_event_filter_translations', 10, 3);
function bda_event_filter_translations($translation, $text, $domain) {
  if (($domain == 'tribe-events-calendar' || $domain == 'tribe-events-community') && strpos($text, 'Organizer') !== false) {
    return str_replace('Organizer', 'Host', $translation);
  }
  return $translation;
}

add_filter('tribe_organizer_label_singular', 'change_single_organizer_label' );
function change_single_organizer_label() {
  return 'Host';
}
add_filter('tribe_organizer_label_plural', 'change_plural_organizer_label' );
function change_plural_organizer_label() {
  return 'Hosts';
}

add_filter('tribe_venue_label_singular', 'change_single_venue_label' );
function change_single_venue_label() {
  return 'Location';
}
add_filter('tribe_venue_label_plural', 'change_plural_venue_label' );
function change_plural_venue_label() {
  return 'Locations';
}


function get_youtube_token() {
  if (!defined('YT_CLIENT_ID') || !defined('YT_CLIENT_SECRET') || !defined('YT_REFRESH_TOKEN')) {
    error_log('constants YT_CLIENT_ID, YT_CLIENT_SECRET, and YT_REFRESH_TOKEN must be defined (try wp-config.php)');
    return false;
  }

  $old_token = get_option('youtube_token');
  if ($old_token) {
    $response = wp_remote_get("https://www.googleapis.com/oauth2/v1/tokeninfo?access_token=$old_token");
    if ( is_wp_error( $response ) ) {
       $error_message = $response->get_error_message();
       error_log("Something went wrong checking youtube oauth token: $error_message");
       $old_token = false;
    } else if ($response['body']) {
      $body = json_decode($response['body'], true);
      if ($body['error']) {
        // probably it expired
        $old_token = false;
      }
      else {
        $time_left = $body['expires_in']; // TODO USE
        return $old_token;
      }
    }
  }

  // if we get here, old token has expired or otherwise isn't working
  $ch = curl_init();
  $fields = array(
    'grant_type' => 'refresh_token',
    'client_id' => YT_CLIENT_ID,
    'client_secret' => YT_CLIENT_SECRET,
    'refresh_token' => YT_REFRESH_TOKEN
  );

  foreach ($fields as $key => $value) {
    $fields_string .= $key . '=' . urlencode($value) . '&';
  }
  rtrim($fields_string, '&');

  curl_setopt($ch, CURLOPT_URL, 'https://accounts.google.com/o/oauth2/token');
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_POST, count($fields));
  curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
  $result = json_decode(curl_exec($ch));
  curl_close($ch);

  $token = $result->access_token;
  update_option('youtube_token', $token);
  $time_left = $result->expires_in; // TODO USE

  return $token;
}

// really horrible stupid check to detect 99% of our mobile visitors
// not for use for critical things
function is_mobile() {
  $user_agent = $_SERVER['HTTP_USER_AGENT'];
  if ($user_agent && preg_match('/iPhone|Android|BlackBerry|IEMobile/i', $user_agent)) return true;
  else return false;
}

// gets all the terms as associative array of term type => array of terms
function get_term_object($id = null) {
  global $TERM_LIST;

  if (!$id) $id = get_the_ID();
  if (!$id) return false;
  $terms = get_the_terms($id, $TERM_LIST);
  $ret = array();
  if ($terms) foreach ($terms as $term) {
    $ret[$term->taxonomy][] = array(
      "name" => $term->name,
      "slug" => $term->slug,
      "url" => "/$term->taxonomy/$term->slug/"
    );
  }
  return $ret;
}

add_filter('body_class','bda_body_class');
function bda_body_class($classes) {
  global $post;

  if ( isset( $post ) ) {
    $classes[] = $post->post_type . '-' . $post->post_name; // e.g. page-slug-name
  }

  if (tribe_is_upcoming() || tribe_is_month()) return $classes;
  else if (!is_single() && !is_page()) $classes[] = 'content-grid';
  else if (is_page() && $post->post_name == "my-uploads") $classes[] = 'content-grid';
  else $classes[] = 'single'; // will already be added for single posts, but not for pages, which we'll consider single too
  return $classes;
}

// remove hard-coded width and height attributes from thumbnail
add_filter( 'post_thumbnail_html', 'remove_img_attr' );
function remove_img_attr ($html) {
  return preg_replace('/(width|height)="\d+"\s/', "", $html);
}


add_action( 'after_setup_theme', 'bda_setup' );
function bda_setup() {
  load_theme_textdomain( 'bda', get_template_directory() . '/languages' );
  add_theme_support( 'automatic-feed-links' );
  add_theme_support( 'post-thumbnails' );
  add_theme_support( 'post-formats', array(
    'image', 'gallery', // TODO: add 'video'
  ) );
  global $content_width;
  if ( ! isset( $content_width ) ) $content_width = 640;
  register_nav_menus(
  array( 'main-menu' => __( 'Main Menu', 'bda' ) )
  );
}

add_action( 'wp_enqueue_scripts', 'bda_load_scripts' );
function bda_load_scripts() {
  // Call the google CDN version of jQuery
  wp_deregister_script('jquery');
  wp_deregister_script('jquery-form');
  wp_register_script('jquery', "//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js", false, null);
  wp_register_script('jquery-form', "//cdnjs.cloudflare.com/ajax/libs/jquery.form/3.50/jquery.form.min.js", false, null);
  wp_enqueue_script('jquery');
  wp_enqueue_script('jquery-form');

  wp_enqueue_script( 'less', "//cdnjs.cloudflare.com/ajax/libs/less.js/1.7.0/less.min.js");
  wp_enqueue_script( 'jquery-masonry-cdn', "//cdnjs.cloudflare.com/ajax/libs/masonry/3.1.5/masonry.pkgd.min.js", array('jquery'), '3.1.5', true);
  // wp_enqueue_script( 'packery', get_template_directory_uri() . "/js/packery.pkgd.min.js");
  wp_enqueue_script( 'imagesLoaded', "//cdnjs.cloudflare.com/ajax/libs/jquery.imagesloaded/3.0.4/jquery.imagesloaded.min.js", array('jquery'), '3.0.4', true);

  if (is_page('my-uploads')) {
    wp_enqueue_script( 'jquery-ui-core' );
    wp_enqueue_script( 'jquery-ui-widget' );
    wp_enqueue_script( 'jquery-ui-position' );
    wp_enqueue_script( 'jquery-ui-autocomplete' );
    wp_enqueue_script( 'tag-it', get_template_directory_uri() . "/js/tag-it.min.js", array('jquery', 'jquery-ui-core'), '1.0.0', true );

    wp_enqueue_style( 'tagit1', get_stylesheet_directory_uri(). '/css/jquery.tagit.css', array() );
    wp_enqueue_style( 'tagit2', get_stylesheet_directory_uri(). '/css/tagit.ui-zendesk.css', array() );
  }
  else if (is_page('my-profile') || tribe_is_community_edit_event_page()) {
    wp_enqueue_script( 'jquery-autocomplete', "//cdnjs.cloudflare.com/ajax/libs/jquery.devbridge-autocomplete/1.2.7/jquery.devbridge-autocomplete.min.js", array('jquery'), '1.2.7', true );
  }

  wp_enqueue_script( 'bdajs', get_template_directory_uri() . "/js/js.js", array('jquery'), '1.0.0', true );
}
add_action( 'wp_print_scripts', 'print_less_css', 0 );
function print_less_css() {
  echo '<link rel="stylesheet/less" type="text/css" href="'. get_stylesheet_directory_uri() .'/style.less">';
}

// install 'gcc, libjpeg-turbo, libjpeg-turbo-utils' and compile program and use script from http://sylvana.net/jpegcrop/exif_orientation.html and put in $PATH like /usr/bin/
add_action('wp_handle_upload', 'bda_autorotate_image', 0);
function bda_autorotate_image($result) {
  if ($result && $result['type'] === 'image/jpeg' && $result['file']) {
    exec("exifautotran " . escapeshellarg($result['file']));
  }
  return $result;
}
add_action('wppb_before_resize_avatar', 'bda_autorotate_avatar', 0);
function bda_autorotate_avatar($path) {
  if ($path) {
    exec("exifautotran " . escapeshellarg($path));
  }
}

add_action( 'wp_ajax_bda_send_email', 'ajax_send_email' );
function ajax_send_email() {
  if (is_user_logged_in()) {
    $user = wp_get_current_user();
    $performer = stripslashes(sanitize_text_field($_POST['performer']));
    $message = stripslashes(sanitize_text_field($_POST['message']));
    $their_email = $user->user_email;
    $approve_url = home_url("/approve-performer/?user_id=$user->ID&performer=" . urlencode($performer));

    $body = "<p>Performer's name: $performer<br>Account email: $their_email<br>Message: $message</p><p>You can <a href='$approve_url'>click here</a> to approve them.</p>";

    $headers[] = 'Content-type: text/html';
    $headers[] = 'Cc: goluba87@gmail.com';
    $headers[] = 'Cc: info@brooklyndragarchive.org';

    $res['success'] = wp_mail('tobyfox@gmail.com', 'Brooklyn Drag Archive - Request for performer status', $body, $headers);
    if (!$res['success']) error_log('failed to send email');
    echo json_encode($res);
  }

  die();
}

add_filter('bda_wppb_about_you', 'artists_statement');
function artists_statement($s) {
  if ($id = get_current_user_id()) {
    $data = get_userdata($id);
    $roles = $data->roles;
    if ($roles[0] === 'performer') return 'Artist\'s Statement';
  }
  return $s;
}

add_filter( 'wp_mail_from_name', function($name){
  return 'Brooklyn Drag Archive';
});
add_filter( 'wp_mail_from', function($email){
  return 'info@brooklyndragarchive.org';
});

// extend cookie expiration for logged in users
add_filter('auth_cookie_expiration', 'extend_login_cookie');
function extend_login_cookie($expirein) {
   return 2419200; // 4 weeks in seconds
}

add_action('admin_menu', 'bda_remove_post_meta_boxes');
function bda_remove_post_meta_boxes() {
  if ( !current_user_can( 'administrator' ) ) {
    // remove_meta_box( 'tagsdiv-post_tag', 'post', 'normal' );
    remove_meta_box( 'categorydiv', 'post', 'normal' );
  }
}

// Thumbnails to Admin Post View  
add_filter('manage_posts_columns', 'bda_posts_columns', 5);  
add_action('manage_posts_custom_column', 'bda_posts_custom_columns', 5, 2);  
function bda_posts_columns($defaults){
  // error_log(print_r($defaults, true));
  unset($defaults['categories']);
  unset($defaults['comments']);
  $defaults['bda_post_thumb'] = 'Thumbnail';  
  return $defaults;  
}  
function bda_posts_custom_columns($column_name, $id){  
  if($column_name === 'bda_post_thumb'){  
    echo the_post_thumbnail('thumbnail');  
  }  
}  

// Theme the TinyMCE editor
// You should create custom-editor-style.css in your theme folder
//add_editor_style('custom-editor-style.css');

// Remove the admin bar from the front end
add_filter( 'show_admin_bar', '__return_false' );

// Remove the version number of WP
remove_action('wp_head', 'wp_generator');

add_filter( 'the_title', 'bda_title' );
function bda_title( $title ) {
  global $post;
  if ($post && $post->ID === 0) return $title; // new post, specifically put here to prevent arrow in calendar events

  if ( $title == '' ) {
    return '&rarr;';
  } else {
    return $title;
  }
}

add_filter( 'wp_title', 'bda_filter_wp_title' );
function bda_filter_wp_title( $title ) {
  return $title . esc_attr( get_bloginfo( 'name' ) );
}

add_action( 'widgets_init', 'bda_widgets_init' );
function bda_widgets_init() {
  register_sidebar( array (
  'name' => __( 'Sidebar Widget Area', 'bda' ),
  'id' => 'primary-widget-area',
  'before_widget' => '<li id="%1$s" class="widget-container %2$s">',
  'after_widget' => "</li>",
  'before_title' => '<h3 class="widget-title">',
  'after_title' => '</h3>',
  ) );
}

// run once:
// add_role('performer', 'Performer', array('read' => true));
