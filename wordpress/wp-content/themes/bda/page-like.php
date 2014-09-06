<?php
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  if (isset($_SERVER[HTTP_REFERER])) header("Location: $_SERVER[HTTP_REFERER]");
  else header("Location: /");
  exit;
}

if (!($user_id = get_current_user_id())) exit("not logged in");
if ($post_id = $_POST['id']) {
  $liked = get_user_meta($user_id, "liked_posts", true);
  if (!$liked) $liked = array();
  $likes = get_post_meta($post_id, "num_likes", true);
  if (!$likes) $likes = 0;

  $i = array_search($post_id, $liked);

  if ($i === false) {
    // like
    $liked[] = $post_id;
    $likes++;
    echo "liked";
  }
  else {
    // unlike
    unset($liked[$i]);
    $likes--;
    echo "unliked";
  }

  if ($likes <= 0) delete_post_meta($post_id, "num_likes"); // convenient, so we can echo out num_likes and get "" if it's 0
  else update_post_meta($post_id, "num_likes", $likes);

  update_user_meta($user_id, "liked_posts", $liked);
}