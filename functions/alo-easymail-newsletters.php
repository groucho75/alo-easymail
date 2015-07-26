<?php if ( !defined('ABSPATH') ) die(); // If this file is called directly, abort.

/**
 * Functions about newsletter queries.
 *
 * @package WordPress
 * @subpackage ALO EasyMail plugin
 */



/**
 * User can edit Newsletter
 */
function alo_em_user_can_edit_newsletter ( $newsletter, $user_id=false ) {
	global $user_ID;
	if ( empty( $user_id ) ) $user_id = $user_ID;
	$user = new WP_User( $user_id );
	return $user->has_cap( 'edit_newsletter', $newsletter );
}


/**
 * Get Newsletter by id
 */
function alo_em_get_newsletter ( $newsletter ) {
	return get_post ( $newsletter );
}


/**
 * Get Newsletter Status from post meta
 */
function alo_em_get_newsletter_status ( $newsletter ) {
	return get_post_meta( $newsletter, '_easymail_status', true );
}


/**
 * Update the Newsletter Status
 * @param	int
 * @param	str
 */
function alo_em_edit_newsletter_status ( $newsletter, $status ) {
	delete_post_meta ( $newsletter, "_easymail_status" );
	add_post_meta ( $newsletter, "_easymail_status", $status );
}


/**
 * Reset/delete the Newsletter Status
 */
function alo_em_delete_newsletter_status ( $newsletter ) {
	delete_post_meta ( $newsletter, "_easymail_status" );
}


/**
 * Check if Newsletter Report of Recipients was archived
 */
function alo_em_is_newsletter_recipients_archived ( $newsletter ) {
	if( alo_em_get_newsletter_status ( $newsletter ) != "sent" ) return false;
	return ( $archive = get_post_meta ( $newsletter, "_easymail_archived_recipients" ) ) ? $archive : false;
}


/**
 * Get the Newsletter(s) using 'get_posts'
 * @param	str		$status
 * @param	int		$limit  how many newsletter
 * @return mixed
 */
function alo_em_query_newsletters ( $status="sent", $limit=1 ) {
	global $wpdb, $wp_version;
	$args = array (
		"post_type" 	=> "newsletter",
		"numberposts" 	=> $limit,
		"orderby" 		=> "post_date",
		"order" 		=> "ASC",
		"post_status" 	=> "publish"
	);
	if ( version_compare ( $wp_version, '3.1', '>=' ) ) {
		$meta_1 = array( 'key' => '_easymail_status', 'value' => $status, 'compare' => '=' );
		$args['meta_query'] = array( $meta_1 );
	} else {
		$args['meta_key'] = '_easymail_status';
		$args['meta_value'] = $status;
		$args['meta_compare'] = '=';
	}
	$newsletters = get_posts ( $args );
	return $newsletters;
}


/**
 * Count Newsletter(s) by status
 */
function alo_em_count_newsletters_by_status ( $status="sent" ) {
	return count( alo_em_query_newsletters ( $status, -1 ) );
}


/**
 * Get the Newsletter(s) on top of queue
 */
function alo_em_get_newsletters_in_queue ( $limit=1 ) {
	return alo_em_query_newsletters ( "sendable", $limit );
}


/**
 * Get the Newsletter(s) already sent
 */
function alo_em_get_newsletters_sent ( $limit=1 ) {
	return alo_em_query_newsletters ( "sent", $limit );
}


/**
 * When the newsletter has been sent, mark it as completed
 */
function alo_em_set_newsletter_as_completed ( $newsletter ) {
	global $wpdb;
	alo_em_edit_newsletter_status ( $newsletter, 'sent' );
	add_post_meta ( $newsletter, "_easymail_completed", current_time( 'mysql', 0 ) );
	$newsletter_obj = alo_em_get_newsletter ( $newsletter );
	do_action ( 'alo_easymail_newsletter_delivered', $newsletter_obj );
}



/* EOF */