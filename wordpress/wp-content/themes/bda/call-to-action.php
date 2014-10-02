<article id="cta-widget" class="masonry-item">
  <div class="inner cta">
    <h2>Click Here!</h2>
    <span>to upload your photo or video to the BROOKLYN DRAG ARCHIVE</span>
  </div>
  <div class="inner signup">
    <h2>Contribute to the Archive</h2>
    <?php echo do_shortcode('[wppb-register]'); ?>
  </div>
</article>
<script>
  // hack-ish: change form action to send user to register page for registering
  $('#adduser').attr('action', '/register/');

  // kinda bad form, sorry
  $('#cta-widget .cta').click(function() {
    <?php if (is_user_logged_in()) { ?>
      document.location.href = '/my-uploads/';
    <?php } else { ?>
      $('#cta-widget .cta').fadeOut(250);
    <?php } ?>
  });
</script>
