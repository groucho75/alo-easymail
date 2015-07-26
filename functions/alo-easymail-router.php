<?php if ( !defined('ABSPATH') ) die(); // If this file is called directly, abort.

/**
 * Register query vars and manage redirects.
 *
 * @package WordPress
 * @subpackage ALO EasyMail plugin
 */


/**
 * Register the allowed query vars.
 *
 * @param $vars
 * @return array
 */
function alo_em_register_query_vars( $vars ){
	$vars[] = "emunsub";
	$vars[] = "emact";
	$vars[] = "emtrck";
	$vars[] = "ac";
	$vars[] = "em1";
	$vars[] = "em2";
	$vars[] = "uk";
	$vars[] = "lang";
	return $vars;
}
add_filter( 'query_vars', 'alo_em_register_query_vars' );


/**
 * Manage user request made via GET vars: eg. activation link, unsubscribe link, external request
 */
function alo_em_check_get_vars () {
	global $wpdb;

	// From unsubscribe link
	if ( $emunsub = get_query_var('emunsub') ) {
		$get_vars = base64_decode( $emunsub );
		$get = explode( "|", $get_vars );
		$subscriber = alo_em_get_subscriber_by_id ( $get[0] );

		$uns_link = "";
		if ( $subscriber ) {
			$div_email = explode( "@", $subscriber->email );
			$arr_params = array ('ac' => 'unsubscribe', 'em1' => urlencode($div_email[0]), 'em2' => urlencode($div_email[1]), 'uk' => preg_replace( '/[^a-zA-Z0-9]/i', '', $get[1]) );
			$uns_link = add_query_arg( $arr_params, alo_em_translate_url ( get_option('alo_em_subsc_page'), $subscriber->lang ) );
		}
		wp_redirect( $uns_link );
		exit;
	}

	// From activation link
	if ( $emact = get_query_var('emact') ) {
		$get_vars = base64_decode( $emact );
		$get = explode( "|", $get_vars );
		$subscriber = alo_em_get_subscriber ( $get[0] );

		$act_link = "";
		if ( $subscriber ) {
			$div_email = explode( "@", $subscriber->email );
			$arr_params = array ('ac' => 'activate', 'em1' => urlencode($div_email[0]), 'em2' => urlencode($div_email[1]), 'uk' => preg_replace( '/[^a-zA-Z0-9]/i', '', $get[1]) );
			$act_link = add_query_arg( $arr_params, alo_em_translate_url ( get_option('alo_em_subsc_page'), $get[2] ) );
		}
		wp_redirect( $act_link );
		exit;
	}


	// Called from a tracked link
	if ( $emtrck = get_query_var('emtrck') ) {
		$get_vars = base64_decode( $emtrck );
		$get = explode( "|", $get_vars );

		$recipient	= ( isset( $get[0] ) && is_numeric($get[0]) ) ? (int)$get[0]: false;
		$unikey		= ( isset( $get[1] ) ) ? preg_replace( '/[^a-zA-Z0-9]/i', '', $get[1]) : false;
		$request	= ( isset( $get[2] ) ) ? esc_url_raw( $get[2] ) : false;

		if ( $recipient && $unikey && $request) {
			$rec_info = alo_em_get_recipient_by_id( $recipient );
			if ( $rec_info && alo_em_check_subscriber_email_and_unikey ( $rec_info->email, $unikey ) ) {
				alo_em_tracking_recipient ( $recipient, $rec_info->newsletter, $request );

				switch ( get_option('alo_em_campaign_vars') ) {

					case 'google':
						$campaign_args = array(
							'utm_source' 	=> 'AloEasyMail',
							'utm_medium'	=> 'email',
							'utm_campaign'	=>  $rec_info->newsletter . '-'. get_the_title( $rec_info->newsletter ),
							'utm_content'	=>  $request
						);
						$campaign_args = apply_filters ( 'alo_easymail_prepare_campaign_vars', $campaign_args, $rec_info, $request );  // Hook
						$request_w_campaign = add_query_arg ( $campaign_args, $request );
						wp_redirect( $request_w_campaign );
						exit;

					case 'no':
					default:
						wp_redirect( $request );
						exit;
				}
			}
		}
		exit;
	}

	// Block XSS attempt: escape/unset subscription form inputs when not in ajax (eg. if javascript disabled)
	if ( !defined('DOING_AJAX') || ! DOING_AJAX )
	{
		if ( isset($_REQUEST['alo_em_opt_name']) ) unset($_REQUEST['alo_em_opt_name']);
		if ( isset($_REQUEST['alo_em_opt_email']) ) unset($_REQUEST['alo_em_opt_email']);
		// we do not unset 'submit' because its common name, so it could be maybe used by other plugins: only a safe escape
		if ( isset($_REQUEST['submit']) ) esc_sql($_REQUEST['submit']);
	}
}
add_action('template_redirect', 'alo_em_check_get_vars');


/* EOF */