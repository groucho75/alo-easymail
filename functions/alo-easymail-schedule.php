<?php if ( !defined('ABSPATH') ) die(); // If this file is called directly, abort.

/**
 * Cron schedule related functions
 *
 * @package WordPress
 * @subpackage ALO EasyMail plugin
 */


/**
 * For batch sending (every tot mins)
 */
function alo_em_more_reccurences( $schedules ) {
	$schedules['alo_em_interval'] =	array(
		'interval' => 59*(ALO_EM_INTERVAL_MIN),
		'display' => 'EasyMail every ' .ALO_EM_INTERVAL_MIN. ' minutes'
	);

	// Set bounce schedule only if some bounce options are set
	$bounce_settings = alo_em_bounce_settings ();
	$bounce_interval = (int)$bounce_settings['bounce_interval'];
	if ( $bounce_interval > 0 && is_email($bounce_settings['bounce_email']) && !empty($bounce_settings['bounce_host']) )
	{
		$schedules['alo_em_bounce'] =	array(
			'interval' => 59 * 60 * $bounce_interval,
			'display' => 'EasyMail every ' .$bounce_interval. ' hours'
		);
	}
	return $schedules;
}
add_filter('cron_schedules', 'alo_em_more_reccurences', 300);


/**
 * To fix missing cron schedules
 */
function alo_em_check_cron_scheduled() {
	if( !wp_next_scheduled( 'alo_em_batch' ) ) {
		wp_schedule_event( time() +60, 'alo_em_interval', 'alo_em_batch' );
	}
	if( !wp_next_scheduled( 'alo_em_schedule' ) ) {
		wp_schedule_event(time(), 'twicedaily', 'alo_em_schedule');
	}

	// Schedule bounce events, if bounce schedule key exists
	if ( array_key_exists( 'alo_em_bounce', wp_get_schedules() ) )
	{
		if( !wp_next_scheduled( 'alo_em_bounce_handle' ) ) {
			wp_schedule_event(time()+60, 'alo_em_bounce', 'alo_em_bounce_handle');
		}
	}

}
add_action('wp', 'alo_em_check_cron_scheduled');


// Schedule bounce events
add_action('alo_em_bounce_handle', 'alo_em_handle_bounces');



/**
 * Clean the new subscription not yet activated after too much time
 */
function alo_em_clean_no_actived() {
	global $wpdb;
	// delete subscribes not yet activated after 5 days, you can filter the number
	$default = 5;
	$days = apply_filters ( 'alo_easymail_clean_no_activated_after', $default ); // Hook
	if ( !is_numeric($days) ) {
		$days = $default;
	}
	$limitdate = date ("Y-m-d",mktime(0,0,0,date("m"),date("d")-$days,date("Y")));
	$output = $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}easymail_subscribers WHERE join_date <= %s AND active = '0'", $limitdate ) );
}

add_action('alo_em_schedule', 'alo_em_clean_no_actived');
add_action( 'alo_em_batch' , 'alo_em_batch_sending');



/* EOF */