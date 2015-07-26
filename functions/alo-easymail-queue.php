<?php if ( !defined('ABSPATH') ) die(); // If this file is called directly, abort.

/**
 * Runctions related to sending queue
 *
 * @package WordPress
 * @subpackage ALO EasyMail plugin
 */


/**
 * Get dayrate by costant or option
 */
function alo_em_get_dayrate () {
	return ( defined( 'ALO_EM_DAYRATE' ) ) ? (int)ALO_EM_DAYRATE : (int)get_option('alo_em_dayrate');
}


/**
 * Get batchrate by costant or option
 */
function alo_em_get_batchrate () {
	return ( defined( 'ALO_EM_BATCHRATE' ) ) ? (int)ALO_EM_BATCHRATE : (int)get_option('alo_em_batchrate');
}


/**
 * Get sleepvalue by costant or option
 */
function alo_em_get_sleepvalue () {
	return ( defined( 'ALO_EM_SLEEPVALUE' ) ) ? (int)ALO_EM_SLEEPVALUE : (int)get_option('alo_em_sleepvalue');
}


/**
 * Get the first Recipients on sending queue: the oldest in recipients db table
 * checking the newsletter is not paused (opt. filtered by a newsletter id)
 *
 * @param 	int		limit: how many
 * @param 	int		newsletter id
 * @return	obj		recipients
 */
function alo_em_get_recipients_in_queue ( $limit=false, $newsletter=false ) {
	global $wpdb;
	if ( !$limit ) $limit = alo_em_get_batchrate ();
	$query_limit = ( $limit ) ? " LIMIT ".$limit : "";
	$query_newsletter = ( $newsletter ) ? " AND newsletter =". $newsletter ." " : "";

	$alo_em_cf = alo_easymail_get_custom_fields();
	$select_cf = '';
	if ( $alo_em_cf ) {
		foreach( $alo_em_cf as $key => $value ){
			$select_cf .= ', s.' . $key;
		}
	}

	$recipients = $wpdb->get_results(
		"SELECT r.*, s.lang, s.unikey, s.name, s.ID AS subscriber {$select_cf} FROM {$wpdb->prefix}easymail_recipients AS r
		LEFT JOIN {$wpdb->prefix}easymail_subscribers AS s ON r.email = s.email
		INNER JOIN {$wpdb->postmeta} AS pm ON pm.post_id = r.newsletter
		INNER JOIN {$wpdb->posts} AS p ON p.ID = r.newsletter
		WHERE pm.meta_key = '_easymail_status' AND pm.meta_value = 'sendable' AND r.result = 0 AND p.post_status = 'publish' ". $query_newsletter ."
		ORDER BY r.ID ASC" . $query_limit );
	if ( $recipients ) : foreach ( $recipients as $index => $recipient ) :
		if ( $user_id = $recipient->user_id ) {
			if ( get_user_meta( $user_id, 'first_name', true ) != "" ) {
				$recipient->firstname = ucfirst( get_user_meta( $user_id, 'first_name', true ) );
			} else {
				$recipient->firstname = $recipient->name;
			}
		} else {
			$recipient->firstname = $recipient->name;
		}

		// You can filter the $recipient object and its properties; return false to unset it.
		// Note: if you unset a recipient here, you probably have to mark it as not-sent in your function or somewhere
		// to avoid to get the same recipient again and again when you call 'alo_em_get_recipients_in_queue'
		$recipients[ $index ] = apply_filters( 'alo_easymail_recipient_in_queue', $recipient ); // Hook
		if ( ! $recipients[ $index ] ) unset( $recipients[ $index ] );

	endforeach; endif;
	return $recipients;
}



/**
 * Called by wp_cron: send the newsletter to a fraction of recipients every X minutes
 */
function alo_em_batch_sending () {
	global $wpdb;

	// search the interval between now and previous sending (or from default cron interval)
	$prev_time = ( get_option ( 'alo_em_last_cron' ) ) ? strtotime( get_option ( 'alo_em_last_cron' ) ) : current_time( 'timestamp', 0 ) - ALO_EM_INTERVAL_MIN * 60;
	$diff_time = current_time( 'timestamp', 0 ) - $prev_time;

	// so... how much recipients for this interval? // (86400 = seconds in a day)
	$day_rate = alo_em_get_dayrate();
	$tot_recs = max ( floor( ( $day_rate * $diff_time / 86400 ) ) , 1 );
	// not over the limit
	$limit_recs = min ( $tot_recs, alo_em_get_batchrate () );

	// the recipients to whom send
	//$recipients = alo_em_get_recipients_in_queue ( $limit_recs );

	// update 'last cron time' option
	update_option ( 'alo_em_last_cron', current_time( 'mysql', 0 ) );

	// if no recipients exit!
	//if ( !$recipients ) return;

	//foreach ( $recipients as $recipient ) {
	for ( $i = 1; $i <= $limit_recs; $i ++ ) {

		// Get the recipient
		$recipients = alo_em_get_recipients_in_queue ( 1 );

		// if no recipients exit the batch loop
		if ( empty($recipients[0]) ) return;

		$recipient = $recipients[0];

		if ( alo_em_get_newsletter_status ( $recipient->newsletter ) != "sendable" ) continue;

		ob_start();

		// Prepare and send the newsletter to this user!
		alo_em_send_newsletter_to ( $recipient );

		// if no more recipient of this newsletter, it has been sent
		if ( count( alo_em_get_recipients_in_queue( 1, $recipient->newsletter ) ) == 0 ) {
			alo_em_set_newsletter_as_completed ( $recipient->newsletter );
		}
		ob_end_flush();
		if ( (int)get_option('alo_em_sleepvalue') > 0 ) usleep ( (int)get_option('alo_em_sleepvalue') * 1000 );
	}
}


/* EOF */