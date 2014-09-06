<?php

/**
 1: Validate stuff
 2: Change user role to performer
 3: Associate term with user (store user ID in term's description)
 4: Email user
 */

$user_id = intval(sanitize_text_field($_GET['user_id']));
$performer = stripslashes(sanitize_text_field($_GET['performer']));

if (! is_user_logged_in() || ! current_user_can('edit_posts')) {
  echo "You must be logged in as an admin or editor to do this.";
  exit;
}
else if (! $user_id || ! $performer) {
  echo "Where's the user id and performer name?";
  exit;
}

$user = get_userdata($user_id);
if (! $user) {
  echo "Invalid user ID $user_id";
  exit;
}

$term = get_term_by('name', $performer, 'performer');
if (! $term) {
  echo "<p>Created new performer: $performer<p>";
  $new_term = wp_insert_term($performer, 'performer');
  $term = get_term_by('id', $new_term['term_id'], 'performer');
}

// #2
$user->set_role('performer');

// #3
wp_update_term($term->term_id, 'performer', array('description' => $user_id));

// #4

$performer_url = home_url("/performer/$term->slug/");
$profile_url = home_url("/my-profile/");

$headers[] = 'Content-type: text/html';
$headers[] = 'Cc: tobyfox@gmail.com';

$body = "<p>Hi $performer,</p><p>Your account has now been linked to uploads tagged with your name. You can view your performer page <a href='$performer_url'>here</a>, and upload your own image and some information about yourself on your profile page <a href='$profile_url'>here</a>. Enjoy!</p><p>Brooklyn Drag Archive<br>info@brooklyndragarchive.org</p>";

wp_mail($user->user_email, 'Brooklyn Drag Archive - You\'ve been approved as a performer!', $body, $headers);

echo "<p>Success! View their performer page <a href='$performer_url'>here</a>.</p>";
exit;
