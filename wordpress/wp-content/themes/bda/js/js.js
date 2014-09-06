var DURATION = 400;

(function($, window) {
  // on DOM load:
  $(function() {

    // for register page - we've hidden this and also don't want it enabled
    $("#send_credentials_via_email").attr("checked", false);

    if ($().tagit) {
      $(".image-upload-form .performer").tagit({
          availableTags: window.bdaperformer,
          allowSpaces: true
      });
      $(".image-upload-form .event").tagit({
          availableTags: window.bdaevent,
          allowSpaces: true
      });
      $(".image-upload-form .venue").tagit({
          availableTags: window.bdavenue,
          allowSpaces: true
      });
    }


    $('button.request-performer').click(function() {
      $('div.request-performer').slideDown();
      $('div.request-performer .before input').autocomplete({lookup: window.bdaperformer});
    });

    $('.request-performer .before button').click(function() {
      $(this).attr('disabled', true);

      $.ajax({
        type: 'POST',
        url: '/wordpress/wp-admin/admin-ajax.php',
        data: {
          'action': 'bda_send_email',
          'performer': $('div.request-performer .before input').val(),
          'message': $('div.request-performer .before textarea').val()
        },
        dataType: 'json',
        success: function(res) {
          $('.request-performer .before').slideUp();
          $('.request-performer .after').slideDown();

          if (!res.success) {
            $('.request-performer .after').html("<p>Sorry, something seems to have gone wrong! You can email us at the 'Contact us' link at the bottom of the page or try again later.</p>");
          }
        },
      });
    });


    $(".page-my-uploads .delete").on("click", function() {
      return confirm("Are you sure you want to delete this upload?");
    });
    // hack: fu_additional_html action in frontend-uploader is after submit better, i wish it was before. make it so:
    var $submitContainer = $('.ugc-input-wrapper input[type=submit]').closest('.ugc-input-wrapper');
    $submitContainer.next().remove(); // extra <br>
    $submitContainer.insertAfter($('#bda-fu-uploader-extras'));

    $("article .like").on("click", function() {
      if (window.WPNotLoggedIn) {
        alert('You must be logged in to "like" content');
        return false;
      }
      var a = this;
      // $(a).toggleClass("liked"); // a bit lazy, let's do this when we receive response
      $.post("/like/", a.href.substring(a.href.indexOf("?")+1), function(res) {
        //var numLikes = parseInt($(a).siblings(".num-likes").html());
        var numLikes = parseInt(a.title);
        if (isNaN(numLikes)) numLikes = 0;

        if (res == "not logged in") {
          window.WPNotLoggedIn = true;
          alert('You must be logged in to "like" content');
        }
        else if (res == "liked") {
          $(a).addClass("liked");
          //$(a).siblings(".num-likes").html(numLikes+1);
          a.title++;
        }
        else if (res == "unliked") {
          $(a).removeClass("liked");
          //$(a).siblings(".num-likes").html(numLikes-1);
          a.title--;
        }
      });
      return false;
    });

    window.$mContainer = $(".content-grid #content"); // masonry

    // initialize masonry
    $mContainer.masonry({
      columnWidth: 300,
      itemSelector: 'article',
      gutter: 20,
      isFitWidth: true,
      duration: DURATION
    });
    // when images loaded do layout
    $mContainer.imagesLoaded(function() {
      $mContainer.masonry('layout');
    });

    $('#content').on('click', '.hover-fill, .icon.video', function() {
      var img = $(this).siblings("img");

      // show youtube embed if one exists
      if (img.attr('data-youtube-id')) {
        var $holder = $(this).siblings('.youtube-embed-holder');
        if ($holder.html() === '') {
          $holder.html('<iframe width="940" height="720" src="//www.youtube-nocookie.com/embed/' + img.attr('data-youtube-id') + '?rel=0&showinfo=0&autohide=1&autoplay=1" frameborder="0" allowfullscreen></iframe>');
        }
        else {
          // destroy embed so it doesn't continue playing while closed.
          $holder.html('');
        }
      }
      // swap to large size image if one exists
      if (img.attr('data-large-src')) {
        img.attr('src', img.attr('data-large-src'));
        img.removeAttr('data-large-src'); // we can remain large size indefinitely
      }

      img.parents(".masonry-item").toggleClass('expanded');
      $mContainer.masonry('layout');
      // do a final re-layout after DURATION after image has finished resizing
      window.setTimeout(function() {
        $mContainer.masonry('layout');
        $('html, body').animate({'scrollTop': img.offset().top-20}, DURATION*1.25, 'swing'); // scroll into view - for some reason this seems to be coming late when adding to masonry layoutComplete listener
      }, DURATION);
    });
    $('#content').on('click', '.close', function() {
      $(this).siblings(".hover-fill").trigger("click");
      return false;
    });


    // capture and divert my-uploads submission if it's a video
    $('#ugc-media-form [type=submit]').on('click', function(e) {
      // SET LOADING ICON
      var files = $("#ugc-media-form [type=file]")[0].files;
      if (!files[0]) return true; // carry on
      var mimeType = files[0].type;
      if (mimeType.indexOf('video') !== 0) return true; // carry on

      $("#upload-yt-video [type=file]")[0].files = files;
      $("#upload-yt-video").submit(); // will be captured by ajaxForm

      e.preventDefault();
      return false;
    });
    $('#upload-yt-video').ajaxForm(function(res, status) { 
      if (status !== 'success' || !res.id) {
        alert('Sorry, there was a problem uploading the video! Please try again later, and get in touch with us if it\'s still not working.\n\n---\n\nResponse status: ' + status + '\nResponse:\n' + JSON.stringify(res));
        return;
      }

      console.log('video uploaded to youtube! response:', res);

      var username = $("#upload-yt-video [name=username]").val();

      var action = $('#upload-yt-video').attr('action');
      var token = action.substring(action.indexOf('access_token=')+13); // only works if this is the last querys tring, which it is now
      var title = username + '\'s Upload, ' + new Date().toDateString();

      var performers = $('#ugc-media-form [name=performer]').val();
      if (performers) performers = performers.split(',');
      var eventName = $('#ugc-media-form [name=event]').val();
      if (eventName) eventName = eventName.split(',');
      var venue = $('#ugc-media-form [name=venue]').val();
      if (venue) venue = venue.split(',');

      var description = $('#ugc-media-form [name=post_content]').val() + '\n\n';
      if (performers) description += "Performers: " + performers.join(', ') + '\n';
      if (eventName) description += "Event: " + eventName.join(', ') + '\n';
      if (venue) description += "Venue: " + venue.join(', ') + '\n';
      description += "Uploaded by: " + username;

      updateYouTubeVideo({
        id: res.id,
        token: token,
        title: title,
        description: description
      }, function(err, res) {
        if (err) {
          alert('Sorry, there was a problem uploading the video! Please try again later, and get in touch with us if it\'s still not working.\n\n---\n\nError while updating video attributes after upload\nError: ' + JSON.stringify(err));
          console.error(err);
        }
        else {
          console.log('video updated! response:', res);

          // now submit original frontend-uploader form, adding YT video ID and thumbnail URL
          $("#ugc-media-form [name=youtube_id]").val(res.id);
          if (res.snippet && res.snippet.thumbnails) {
            var thumbURL;
            if (res.snippet.thumbnails.high) thumbURL = res.snippet.thumbnails.high.url;
            else if (res.snippet.thumbnails.medium) thumbURL = res.snippet.thumbnails.medium.url;
            else if (res.snippet.thumbnails.default) thumbURL = res.snippet.thumbnails.default.url;
            if (thumbURL) {
              $("#ugc-media-form [name=thumb_url]").val(thumbURL);
            }
          }
          resetFormElement($("#ugc-media-form [type=file]")); // don't submit the video again
          $("#ugc-media-form").submit();
        } // end if updating video was successful
        // TODO hide loading icon
      });

    }); // end handling form ajax response for uploading youtube video

  $('.mobile-selected-menu').on('click', function() {
    $('#menu .menu').fadeToggle(200);
  });

  }); // end on DOM load
})(jQuery, window);


function updateYouTubeVideo(args, cb) {
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
    success: function(data){
      if ($.type(data) === "string") data = JSON.parse(data);
      cb(null, data);
    },
    error: function(request, err){
      cb(err);
    }
  });
}

function resetFormElement(e) {
  e.wrap('<form>').closest('form').get(0).reset();
  e.unwrap();
}