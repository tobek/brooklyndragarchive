<?php
/**
 * Delete Event Module
 * This is used to delete a user submitted event.
 *
 * Override this template in your own theme by creating a file at
 * [your-theme]/tribe-events/community/modules/delete.php
 *
 * @package TribeCommunityEvents
 * @since  2.1
 * @author Modern Tribe Inc.
 *
 */

if ( !defined('ABSPATH') ) { die('-1'); }

$current_user = wp_get_current_user(); ?>

<div id="add-new">
	<a href="<?php echo tribe_community_events_add_event_link(); ?>" class="button"><?php _e( 'Add New', 'tribe-events-community' ); ?></a>
</div>

<div id="my-events">
	<a href="<?php echo tribe_community_events_list_events_link(); ?>" class="button"><?php _e( 'My Events', 'tribe-events-community' ); ?></a>
</div>

<div id="not-user">
	<?php echo __( 'Not', 'tribe-events-community' ) .' <i>'. $current_user->display_name .'</i>'; ?>
	<a href="<?php tribe_community_events_logout_url(); ?>"><?php _e( 'Log Out', 'tribe-events-community' ); ?></a>
</div>

<div style="clear:both"></div>

<?php $this->outputMessage(); ?>
