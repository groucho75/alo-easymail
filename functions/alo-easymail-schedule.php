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
}
add_action('wp', 'alo_em_check_cron_scheduled');


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