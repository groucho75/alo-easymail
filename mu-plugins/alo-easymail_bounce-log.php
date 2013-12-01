<?php


/*******************************************************************************
 *
 * Write in a log the emails unsubscribed because bounced.
 *
 * @since: 2.5
 *
 ******************************************************************************/


function custom_easymail_bounce_email_unsubscribed( $email ) {
	
	$log = @fopen( WP_CONTENT_DIR . "/newsletter_bounces.log", 'a+' );
	$log_message = 	date_i18n( __( 'j M Y @ G:i' ) ) .": Email address ". $email . " unsubscribed because bounced.\n";
	fwrite ( $log, $log_message ) ;
	fclose ( $log );		
}
add_action('alo_easymail_bounce_email_unsubscribed', 'custom_easymail_bounce_email_unsubscribed');



/* EOF */
