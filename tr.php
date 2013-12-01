<?php 

include('../../../wp-load.php');
global $wpdb;

/*
Feature inspired by phplist: http://www.phplist.com/
Many thanks to those are working on it!

eg. link in email:
'<img src="{...path_to_easymail_dir...}tr.php?v={base-64-vars}" width="1" height="1" border="0" alt="">';
*/

ob_start();
error_reporting(0);

if ( isset( $_REQUEST['v'] ) ) {

	$get_vars = base64_decode( $_REQUEST['v'] );
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

?>
