<?php
/**
 * This resource has loaded as an embed pixel image at the bottom
 * of newsletter to tracking the recipient view.
 *
 * @package WordPress
 * @subpackage ALO EasyMail plugin
 *
 * @deprecated: Now we use REST endopoint
 * @see alo_em_register_rest_tracking_pixel()
 */

define('WP_USE_THEMES', false);
include('../../../wp-load.php');

global $wpdb;

/*
Feature inspired by phplist: http://www.phplist.com/
Many thanks to those are working on it!

eg. link in email:
'<img src="{...path_to_easymail_dir...}tr.php?v={base-64-vars}" width="1" height="1" border="0" alt="">';
*/

if ( get_option('alo_em_use_tracking_pixel') == 'no' ) exit;

ob_start();
error_reporting(0);

if ( isset( $_GET['v'] ) ) {

	$get_vars = base64_decode( $_GET['v'] );
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

//echo $wpdb->last_query;

// print 1 pixel png image
@ob_end_clean();
header("Content-Type: image/png");
print base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABAQMAAAAl21bKAAAABGdBTUEAALGPC/xhBQAAAAZQTFRF////AAAAVcLTfgAAAAF0Uk5TAEDm2GYAAAABYktHRACIBR1IAAAACXBIWXMAAAsSAAALEgHS3X78AAAAB3RJTUUH0gQCEx05cqKA8gAAAApJREFUeJxjYAAAAAIAAUivpHEAAAAASUVORK5CYII=');
