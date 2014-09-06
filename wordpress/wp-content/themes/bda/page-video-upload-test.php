<?php
if (is_user_logged_in()) {
  $username = get_user_meta(get_current_user_id(), 'nickname', true);

  get_header();

  $token = get_youtube_token();

?>

<form id="upload-video" action="https://www.googleapis.com/upload/youtube/v3/videos?part=snippet&access_token=<?php echo $token; ?>" method="post" enctype="multipart/form-data">
  <p>Video Name: <input type="text" name="snippet[title]" size="50" /></p>
  <p>Video Description:<br/><textarea name="snippet[description]" rows="5" cols="50"></textarea></p>
  <p><input type="file" name="videoFile"></p>
  <p><input type="submit" value="Upload File"></p>
</form>

<?php

  get_footer();
?>
<script>
$(function() {
  $('form#upload-video').on('submit', function() {
    // TODO: show loading icon
  });
  $('form#upload-video').ajaxForm(function(res, status) { 
    if (status !== 'success' || !res.id) {
      alert('Sorry, there was a problem uploading the video! Please try again later, and get in touch with us if it\'s still not working.\n\n---\n\nResponse status: ' + status + '\nResponse:\n' + JSON.stringify(res));
      return;
    }

    console.log('video uploaded to youtube');

    var action = $('#upload-video').attr('action');
    var token = action.substring(action.indexOf('access_token=')+13); // only works if this is the last querys tring, which it is now
    var title = '<?php echo $username; ?>\'s Upload, ' + new Date().toDateString();
    var description = 'test description'; // TODO

    updateVideo({
      id: res.id,
      token: token,
      title: title,
      description: description
    }, function(err, res) {
      if (err) {
        alert('Sorry, there was a problem uploading the video! Please try again later, and get in touch with us if it\'s still not working.\n\n---\n\nError while updating video attributes after upload\nError: ' + JSON.stringify(err));
      }
      else {
        console.log('video updated! response:', res);
      }
      // TODO hide loading icon
    });

    if (res.snippet && res.snippet.thumbnails) {
      var thumbURL;
      if (res.snippet.thumbnails.high) thumbURL = res.snippet.thumbnails.high.url;
      else if (res.snippet.thumbnails.medium) thumbURL = res.snippet.thumbnails.medium.url;
      else if (res.snippet.thumbnails.default) thumbURL = res.snippet.thumbnails.default.url;
      if (thumbURL) {
        console.log('will use thumbnail URL ' + thumbURL);
        // TODO set thumb
      }
    }
  });
});

function updateVideo(args, cb) {
  if (!args || !args.id || !args.token) {
    console.error('missing args: must at least include id and token');
    return;
  }
  $.ajax({
    type: 'PUT',
    url: 'https://www.googleapis.com/youtube/v3/videos?part=snippet,status',
    contentType: 'application/json',
    headers: {
        Authorization: 'Bearer ' + args.token
    },
    data: JSON.stringify({
      id: args.id,
      snippet: {
        description: args.description || '',
        title: args.title || 'User Uploaded Video ' + new Date().toDateString(),
        categoryId: 24 // "entertainment"
      },
      status: {
        privacyStatus: 'unlisted'
      }
    }),
    dataType: 'text',
    processData: false,
    success: function(data){
      cb(null, data);
    },
    error: function(request, err){
      cb(err);
    }
  });
}
</script>

<?php
} // end if logged in
else {
  header( 'Location: /login/' );
  exit;
}
