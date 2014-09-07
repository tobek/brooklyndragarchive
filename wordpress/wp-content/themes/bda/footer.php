<div class="clear"></div>
</div>
<footer id="footer" role="contentinfo">
<!--   <p class="social">
    <a href="#" class="social-link"><img src="<?php echo get_template_directory_uri(); ?>/img/social/twt.png" class="social-img"></a>
    <a href="#" class="social-link"><img src="<?php echo get_template_directory_uri(); ?>/img/social/fb.png" class="social-img"></a>
    <a href="#" class="social-link"><img src="<?php echo get_template_directory_uri(); ?>/img/social/gplus.png" class="social-img"></a>
    <a href="#" class="social-link"><img src="<?php echo get_template_directory_uri(); ?>/img/social/vimeo.png" class="social-img"></a>
  </p> -->
  <p>
    <?php echo sprintf( __( '%1$s %2$s, %3$s.', 'bda' ), '&copy;', date( 'Y' ), esc_html( get_bloginfo( 'name' ) ) ); ?>
    <a href="mailto:info@brooklyndragarchive.org">Contact us</a>.
  </p>
</footer>
</div>

<?php
// for upload auto-complete, print these out
// TODO cache this somehow or load it differently
global $my_uploads, $TERM_LIST;
if ($my_uploads || is_page('my-profile') || tribe_is_community_edit_event_page()) {
  echo "<script>\n";
  foreach ($TERM_LIST as $term_type) {
    $all = get_terms($term_type, array('hide_empty' => false));
    $all = array_map(function ($term) { return $term->name; }, $all);
    $json = html_entity_decode(str_replace(array("'", '\u0022'), array("\'", '\\\\u0022'), json_encode($all, JSON_HEX_QUOT)));
    echo "window.bda$term_type = JSON.parse('". $json ."');\n";
  }
  echo "</script>\n";
}
?>

<?php wp_footer(); ?>
</body>
</html>