<?php if ( !defined('ABSPATH') ) die(); // If this file is called directly, abort.

/**
 * Functions about tracking: count views and clicks on newsletters.
 *
 * @package WordPress
 * @subpackage ALO EasyMail plugin
 */


/**
 * If recipient has been tracked (eg. if he has opened the newsletter)
 *@param	int		recipient
 *@param	str		url clicked
 *@return 	bol
 */
function alo_em_recipient_is_tracked ( $recipient, $request='' ) {
	global $wpdb;
	$trackings = alo_em_get_recipient_trackings( $recipient, $request );
	return ( $trackings ) ? true : false;
}


/**
 * Get all trackings of a recipient
 *@param	int		recipient
 *@param	str		url clicked, blank for view
 *@return 	arr		array of object
 */
function alo_em_get_recipient_trackings ( $recipient, $request='' ) {
	global $wpdb;
	return $wpdb->get_results ( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}easymail_stats WHERE recipient=%d AND request='%s'", $recipient, $request ) );
}


/**
 * Get all trackings of a recipient, excluding Views
 *@param	int		recipient
 *@param	str		url clicked, blank for view
 *@return 	arr		array of object
 */
function alo_em_get_recipient_trackings_except_views ( $recipient ) {
	global $wpdb;
	return $wpdb->get_results ( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}easymail_stats WHERE recipient=%d AND request!=''", $recipient ) );
}


/**
 * Tracking when a recipient views/click newsletter and update subscriber last activity
 *@param	int		recipient
 *@param	int		newsletter: if empty get it from recipient
 *@param	str		url clicked, blank for view
 */
function alo_em_tracking_recipient ( $recipient, $newsletter=false, $request='' ) {
	global $wpdb;
	$rec_info = alo_em_get_recipient_by_id( $recipient );
	if ( empty( $newsletter ) ) {
		$newsletter = $rec_info->newsletter;
	}
	alo_em_update_subscriber_last_act( $rec_info->email );
	return $wpdb->insert ( "{$wpdb->prefix}easymail_stats",
		array( 'recipient' => $recipient, 'newsletter' => $newsletter, 'added_on' => current_time( 'mysql', 0 ), 'request' => $request )
	);
}


/**
 * Count all trackings about a newsletter
 *@param	int		newsletter
 *@param	str		url clicked, blank for view
 *@return 	arr		array of object: each object contains recipient and number of views/clicks
 */
function alo_em_all_newsletter_trackings ( $newsletter, $request='' ) {
	global $wpdb;
	return $wpdb->get_results( $wpdb->prepare("SELECT recipient, COUNT(ID) AS numitems FROM {$wpdb->prefix}easymail_stats WHERE newsletter=%d AND request='%s' GROUP BY recipient ORDER BY numitems DESC", $newsletter, $request ));
}


/**
 * Count all trackings about a newsletter, except Views
 *@param	int		newsletter
 *@return 	arr		array of object: each object contains recipient and number of views/clicks
 */
function alo_em_all_newsletter_trackings_except_views ( $newsletter) {
	global $wpdb;
	return $wpdb->get_results( $wpdb->prepare("SELECT recipient, COUNT(ID) AS numitems FROM {$wpdb->prefix}easymail_stats WHERE newsletter=%d AND request!='' GROUP BY recipient ORDER BY numitems DESC", $newsletter ));
}


/**
 * Make a url as a trackable url
 *
 *@param	obj		recipient object
 *@param	str		url
 *@return 	str		url trackable
 */
function alo_em_make_url_trackable ( $recipient, $url ) {
	if ( ! is_object($recipient) || empty($recipient->ID) || empty($recipient->unikey) ) return $url;

	$track_vars = $recipient->ID . '|' . $recipient->unikey . '|' . $url;
	$track_vars = urlencode( base64_encode( $track_vars ) );

	return add_query_arg( 'emtrck', $track_vars, alo_em_translate_home_url ( $recipient->lang ) );
}


/**
 * Load the 1x1 pixel to track newsletter opening
 *
 * @param \WP_REST_Request
 * @return \WP_REST_Response
 */
function alo_em_rest_load_tracking_pixel( \WP_REST_Request $request ) {


	if ( get_option('alo_em_use_tracking_pixel') == "no" ) {
		echo '';
		exit;
	}

	ob_start();
	error_reporting(0);

	if ( get_option('alo_em_use_tracking_pixel') != "no" && ( $empxltrk = $request->get_param( 'empxltrk' ) ) ) {

		$get_vars = base64_decode( $empxltrk );
		$get = explode( "|", $get_vars );

		$recipient	= ( isset( $get[0] ) && is_numeric($get[0]) ) ? (int)$get[0] : false;
		$unikey		= ( isset( $get[1] ) ) ? preg_replace( '/[^a-zA-Z0-9]/i', '', $get[1]) : false;

		if ( $recipient && $unikey ) {
			$rec_info = alo_em_get_recipient_by_id( $recipient );
			if ( $rec_info && alo_em_check_subscriber_email_and_unikey ( $rec_info->email, $unikey ) ) {
				alo_em_tracking_recipient ( $recipient, $rec_info->newsletter, false );
			}
		}

	}

	@ob_end_clean();
	header("Content-Type: image/png");
	print base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABAQMAAAAl21bKAAAABGdBTUEAALGPC/xhBQAAAAZQTFRF////AAAAVcLTfgAAAAF0Uk5TAEDm2GYAAAABYktHRACIBR1IAAAACXBIWXMAAAsSAAALEgHS3X78AAAAB3RJTUUH0gQCEx05cqKA8gAAAApJREFUeJxjYAAAAAIAAUivpHEAAAAASUVORK5CYII=');

}

/* EOF */