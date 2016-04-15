<?php if ( !defined('ABSPATH') ) die(); // If this file is called directly, abort.

/**
 * Functions about bounces
 *
 * @package WordPress
 * @subpackage ALO EasyMail plugin
 */



/**
 * Get bounce settings
 *
 * @return array
 */
function alo_em_bounce_settings () {
	$bounce_defaults = array(
		'bounce_email'		=> '',
		'bounce_host' 		=> '',
		'bounce_port'		=> 143,
		'bounce_protocol'	=> 'imap', // or 'pop3'
		'bounce_folder'		=> '',
		'bounce_username' 	=> '',
		'bounce_password' 	=> '',
		'bounce_flags' 		=> '', 	// optional: e.g.: /ssl/novalidate-cert
		'bounce_maxmsg'		=> 30,	// max number of msgs will be examinated per batch
		'bounce_interval'	=> '',	// auto check bounces every N hours
	);
	$bounce_saved = get_option('alo_em_bounce_settings');

	return wp_parse_args( $bounce_saved, $bounce_defaults );
}


/**
 * Add custom headers for bounce purpose in newsletters
 *
 * @return str
 */
function alo_em_add_custom_headers ( $headers, $newsletter, $recipient ) {

	if ( !empty($newsletter->ID) ) $headers .= "X-ALO-EM-Newsletter: " . $newsletter->ID . "\n";
	if ( !empty($recipient->ID) ) $headers .= "X-ALO-EM-Recipient: " . $recipient->ID . "\n";

	return $headers;
}
add_filter( 'alo_easymail_newsletter_headers', 'alo_em_add_custom_headers', 100, 3 );


/**
 * Make an IMAP connection using settings
 *
 * @param array the POST array
 * @return mix		the IMAP stream or FALSE if connection attempt fails
 */
function alo_em_bounce_connect ( $_post=array() ) {
	$bounce_settings = alo_em_bounce_settings ();

	if ( ! empty( $_post['bounce_password'] ) ) {
		$bounce_settings['bounce_password'] = sanitize_text_field( $_post['bounce_password'] );
		return @imap_open("{" . $bounce_settings['bounce_host'] .':'.$bounce_settings['bounce_port']. "/". $bounce_settings['bounce_protocol'] . $bounce_settings['bounce_flags'] . "}" . $bounce_settings['bounce_folder'], $bounce_settings['bounce_username'], $bounce_settings['bounce_password'] );
	}
	return false;
}


/**
 * Handle bounces (manually or via cron)
 *
 * The function is pluggable: you can write your better function, and pleas share it :)
 *
 * @param	str		type of final rerport: none, a text msg, an email
 */

if ( ! function_exists('alo_em_handle_bounces') ) :
function alo_em_handle_bounces ( $report=false )
{
	global $wpdb;

	$output = '';

	$bounce_settings = alo_em_bounce_settings ();

	$conn = alo_em_bounce_connect();

	if ( ! $conn ) return FALSE;

	$num_msgs = imap_num_msg($conn);

	// start bounce class
	require_once(ALO_EM_PLUGIN_ABS.'/inc/bouncehandler/bounce_driver.class.php');

	$bouncehandler = new Bouncehandler();

	// get the failures
	$email_addresses = array();
	$delete_addresses = array();

	$max_msgs = min( $num_msgs, $bounce_settings['bounce_maxmsg'] );

	if ( $report ) $output .= 'Bounces handled in: '. $bounce_settings['bounce_email'];

	for ( $n=1; $n <= $max_msgs; $n++ )
	{
		$msg_headers = imap_fetchheader($conn, $n);
		$msg_body = imap_body($conn, $n);

		$bounce = $msg_headers . $msg_body; //entire message

		$multiArray = $bouncehandler->get_the_facts($bounce);

		if (!empty($multiArray[0]['action']) && !empty($multiArray[0]['status']) && !empty($multiArray[0]['recipient']) )
		{
			if ( $report ) $output .= '<br /> - MSG #'. $n .' - Bounce response: '. $multiArray[0]['action'];

			// If delivery permanently failed, unsubscribe
			if ( $multiArray[0]['action']=='failed' )
			{
				$email = trim( $multiArray[0]['recipient'] );

				// Unsubscribe email address
				if ( $s_id = alo_em_is_subscriber( $email ) )
				{
					alo_em_delete_subscriber_by_id( $s_id );

					do_action ( 'alo_easymail_bounce_email_unsubscribed', $email ); // Hook
					if ( $report ) $output .= ' - '. $email .' UNSUBSCRIBED';
				}
			}

			// If delivery temporary or permanently failed, mark recipient as bounced
			if ( $multiArray[0]['action']=='failed' || $multiArray[0]['action']=='transient' || $multiArray[0]['action']=='autoreply' )
			{

				// TODO maybe use: $bouncehandler->x_header_search_1 = 'ALO-EM-Newsletter';


				// Look fo EasyMail custom headers: Newsletter and Recipient
				// NOTE: searching in body because IDs are inside original message included in body
				$newsletter_id = 0;
				$recipient_id = 0;
				if ( preg_match('/X-ALO-EM-Newsletter: (\d+)/i', $bounce, $matches) )
				{
					if ( !empty($matches[1]) && is_numeric( $matches[1] ) ) $newsletter_id = (int)$matches[1];
				}
				if ( preg_match('/X-ALO-EM-Recipient: (\d+)/i', $bounce, $matches) )
				{
					if ( !empty($matches[1]) && is_numeric( $matches[1] ) ) $recipient_id = (int)$matches[1];
				}

				// Mark recipient as bounced only if not a debug to author
				if ( $newsletter_id > 0 && $recipient_id > 0 && strpos($msg_headers, "( DEBUG - TO: ") === false)
				{
					$wpdb->update( "{$wpdb->prefix}easymail_recipients",
						array ( 'result' => -3 ),
						array ( 'ID' => $recipient_id, 'newsletter' => $newsletter_id, 'email' => $email )
					);
				}

				if ( $report ) $output .= ' - Recipient ID #'. $recipient_id .' marked as not delivered';

				// mark msg for deletion
				imap_delete($conn, $n);
			}

		} //if passed parsing as bounce
		else
		{
			if ( $report ) $output .= '<br /><span class="description"> - MSG #'. $n .' - Not a bounce</span>';
		}

	} //for loop


	// delete messages
	imap_expunge($conn);

	// close
	imap_close($conn);

	if ( $report ) return $output;

}
endif;


/* EOF */