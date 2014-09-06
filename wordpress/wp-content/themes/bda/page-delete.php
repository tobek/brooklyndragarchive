<?php
if ($_GET['id'] && is_user_logged_in()) {
  $user = wp_get_current_user();
  $post_id = $_GET['id'];

  if (get_post_field("post_author", $post_id) == $user->ID) {
    wp_delete_post($post_id, true);
    header( 'Location: /my-uploads/?response=bda-deleted' );
    exit;
  }
}
header( 'Location: /' );
exit;
