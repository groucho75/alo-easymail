<?php if ( !defined('ABSPATH') ) die(); // If this file is called directly, abort.

/**
 * Functions about subscribers queries.
 *
 * @package WordPress
 * @subpackage ALO EasyMail plugin
 */


/**
 * Count the n° of subscribers
 * return a array: total (active + not active), active, not active
 */
function alo_em_count_subscribers () {
	global $wpdb;
	$search = $wpdb->get_results( "SELECT active, COUNT(active) AS count FROM {$wpdb->prefix}easymail_subscribers GROUP BY active ORDER BY active ASC" );
	$total = $noactive = $active = false;
	if ($search) {
		foreach ($search as $s) {
			switch ($s->active) {
				case 0: 	$noactive = $s->count; break;
				case 1: 	$active = $s->count; break;
			}
		}
		$total = $noactive + $active;
	}
	return array ( $total, $active, $noactive );
}


/**
 * Check is there is already a subscriber with that email and return ID subscriber
 */
function alo_em_is_subscriber($email) {
	global $wpdb;
	$is_subscriber = $wpdb->get_var( $wpdb->prepare("SELECT ID FROM {$wpdb->prefix}easymail_subscribers WHERE email='%s' LIMIT 1", $email) );
	return (($is_subscriber)? $is_subscriber : 0); // ID in db tab subscribers
}


/**
 * Check is there is a subscriber with this ID and return true/false
 */
function alo_em_is_subscriber_by_id ( $id ) {
	global $wpdb;
	$is_subscriber = $wpdb->get_var( $wpdb->prepare("SELECT ID FROM {$wpdb->prefix}easymail_subscribers WHERE ID=%d LIMIT 1", $id) );
	return $is_subscriber;
}


/**
 * Check the state of a subscriber (active/not-active)
 */
function alo_em_check_subscriber_state($email) {
	global $wpdb;
	$is_activated = $wpdb->get_var( $wpdb->prepare("SELECT active FROM {$wpdb->prefix}easymail_subscribers WHERE email='%s' LIMIT 1", $email) );
	return $is_activated;
}


/**
 * Modify the state of a subscriber (active/not-active) (BY ADMIN)
 */
function alo_em_edit_subscriber_state_by_id($id, $newstate) {
	global $wpdb;
	$output = $wpdb->update(    "{$wpdb->prefix}easymail_subscribers",
		array ( 'active' => $newstate ),
		array ( 'ID' => $id)
	);
	return $output;
}


/**
 * Modify the state of a subscriber (active/not-active) (BY SUBSCRIBER)
 */
function alo_em_edit_subscriber_state_by_email($email, $newstate="1", $unikey) {
	global $wpdb;
	$output = $wpdb->update(    "{$wpdb->prefix}easymail_subscribers",
		array ( 'active' => $newstate ),
		array ( 'email' => $email, 'unikey' => $unikey )
	);
	return $output;
}


/**
 * Add a new subscriber
 * return bol/str:
 *		false					= generic error
 *		"OK"					= success
 *		"NO-ALREADYACTIVATED"	= not added because: email is already added and activated
 *		"NO-ALREADYADDED"		= not added because: email is already added but not activated; so send activation msg again
 */
function alo_em_add_subscriber( $fields, $newstate=0, $lang="" ) { //edit : orig : function alo_em_add_subscriber($email, $name, $newstate=0, $lang="" ) {
	global $wpdb;
	$output = true;
	$fields = array_map( 'strip_tags', $fields );
	$email = $fields['email'];
	if ( !is_admin() || ( defined('DOING_AJAX') && DOING_AJAX ) ) $fields['ip_address'] = alo_em_ip_address();
	// if there is NOT a subscriber with this email address: add new subscriber and send activation email
	if (alo_em_is_subscriber($email) == false){
		$unikey = substr(md5(uniqid(rand(), true)), 0,24);    // a personal key to manage the subscription

		// try to send activation mail, otherwise will not add subscriber
		if ($newstate == 0) {
			$lang_actmail = ( !empty( $lang ) ) ? $lang : alo_em_short_langcode ( get_locale() );
			if ( !alo_em_send_activation_email($fields, $unikey, $lang_actmail) ) $output = false; // DEBUG ON LOCALHOST: comment this line to avoid error on sending mail
		}

		if ( $output ) {
			$wpdb->insert ( "{$wpdb->prefix}easymail_subscribers",
				array_merge( $fields, array( 'join_date' => get_date_from_gmt( date("Y-m-d H:i:s") ), 'active' => $newstate, 'unikey' => $unikey, 'lists' => "|", 'lang' => $lang, 'last_act' => get_date_from_gmt( date("Y-m-d H:i:s") ) ) ) //edit : orig : array( 'email' => $email, 'name' => $name, 'join_date' => get_date_from_gmt( date("Y-m-d H:i:s") ), 'active' => $newstate, 'unikey' => $unikey, 'lists' => "|", 'lang' => $lang )
			);
			$output = "OK"; //return true;
		}

	} else {
		// if there is ALREADY a subscriber with this email address, and if is NOT confirmed yet: re-send an activation email
		if ( alo_em_check_subscriber_state($email) == 0) {
			// retrieve existing unique key
			$exist_unikey = $wpdb->get_var( $wpdb->prepare("SELECT unikey FROM {$wpdb->prefix}easymail_subscribers WHERE ID='%d' LIMIT 1", alo_em_is_subscriber($email) ) );

			if ( alo_em_send_activation_email($fields, $exist_unikey, $lang) ) {
				// update join date to today
				$ip_address = alo_em_ip_address();
				$output = $wpdb->update(    "{$wpdb->prefix}easymail_subscribers",
					array ( 'join_date' => get_date_from_gmt( date("Y-m-d H:i:s") ), 'lang' => $lang, 'last_act' => get_date_from_gmt( date("Y-m-d H:i:s") ), 'ip_address' => $ip_address ),
					array ( 'ID' => alo_em_is_subscriber($email) )
				);
				// tell that there is already added but not active: so it has sent another activation mail.......
				$output = "NO-ALREADYADDED";
			} else {
				$output = false;
				//$output = "NO-ALREADYADDED"; // DEBUG ON LOCALHOST: comment the previous line and uncomment this one to avoid error on sending mail
			}
		} else {
			// tell that there is already an activated subscriber.....
			$output = "NO-ALREADYACTIVATED";
		}
	}
	return $output;
}


/**
 * Delete a subscriber (BY ADMIN/REGISTERED-USER)
 */
function alo_em_delete_subscriber_by_id($id) {
	global $wpdb;
	$output = $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}easymail_subscribers WHERE ID=%d LIMIT 1", $id ) );
	return $output;
}



/**
 * Update a subscriber (BY ADMIN/REGISTERED-USER)
 */
function alo_em_update_subscriber_by_email ( $old_email, $fields, $newstate=0, $lang="", $update_lastact=true ) {
	global $wpdb;
	$fields['active'] = $newstate; //edit : added all this line
	$fields['lang'] = $lang; //edit : added all this line
	$fields['ip_address'] = alo_em_ip_address();
	if ( $update_lastact ) $fields['last_act'] = get_date_from_gmt( date("Y-m-d H:i:s") );

	$output = $wpdb->update(    "{$wpdb->prefix}easymail_subscribers",
		$fields, //edit : orig : array ( 'email' => $new_email, 'name' => $name, 'active' => $newstate, 'lang' => $lang ),
		array ( 'email' => $old_email )
	);
	return $output;
}


/**
 * Delete a subscriber (BY SUBSCRIBER)
 */
function alo_em_delete_subscriber_by_email($email, $unikey) {
	global $wpdb;
	$output = $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}easymail_subscribers WHERE email='%s' AND unikey='%s' LIMIT 1", $email, $unikey ) );
	return $output;
}


/**
 * Check if can access subscription page (BY SUBSCRIBER)
 */
function alo_em_can_access_subscrpage ($email, $unikey) {
	global $wpdb;
	// check if email and unikey match
	$check = alo_em_check_subscriber_email_and_unikey ( $email, $unikey );
	return $check;
}


/**
 * Check if subscriber email and unikey match (BY SUBSCRIBER) (check EMAIL<->UNIKEY)
 */
function alo_em_check_subscriber_email_and_unikey ( $email, $unikey ) {
	global $wpdb;
	$check = $wpdb->get_var( $wpdb->prepare("SELECT ID FROM {$wpdb->prefix}easymail_subscribers WHERE email='%s' AND unikey='%s' LIMIT 1", $email, $unikey) );
	return $check;
}



/**
 * Check the state of a subscriber (active/not-active)
 */
function alo_em_update_subscriber_last_act($email) {
	global $wpdb;
	$ip_address = alo_em_ip_address();
	$out = $wpdb->update( 	"{$wpdb->prefix}easymail_subscribers",
		array ( 'last_act' => get_date_from_gmt( date("Y-m-d H:i:s") ), 'ip_address' => $ip_address ),
		array ( 'ID' => alo_em_is_subscriber($email) )
	);
	return $out;
}


/**
 * Get Subscriber by e-mail
 */
function alo_em_get_subscriber ( $email ) {
	global $wpdb;
	return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}easymail_subscribers WHERE email = '%s'", $email ) );
}


/**
 * Get Subscriber by ID
 */
function alo_em_get_subscriber_by_id ( $ID ) {
	global $wpdb;
	settype($ID, 'integer');
	return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}easymail_subscribers WHERE ID = %d", $ID ) );
}


/**
 * Check if email already exists in Unsubscribed table
 *
 *@param	str		email
 *@return 	bol
 */
function alo_em_check_email_in_unsubscribed ( $email) {
	global $wpdb;
	$exists = $wpdb->get_var( $wpdb->prepare("SELECT email FROM {$wpdb->prefix}easymail_unsubscribed WHERE email='%s'", $email) );
	return ( $exists ) ? true : false;
}


/**
 * Get date when email unsubscribed
 *
 *@param	str		email
 *@return 	date
 */
function alo_em_when_email_unsubscribed ( $email) {
	global $wpdb;
	return $wpdb->get_var( $wpdb->prepare("SELECT added_on FROM {$wpdb->prefix}easymail_unsubscribed WHERE email='%s'", $email) );
}


/**
 * Add email in Unsubscribed table
 *
 *@param	str		email
 */
function alo_em_add_email_in_unsubscribed ( $email) {
	global $wpdb;
	if ( !alo_em_check_email_in_unsubscribed( $email ) )
	{
		$wpdb->insert ( "{$wpdb->prefix}easymail_unsubscribed",
			array( 'email' => $email,  'added_on' => current_time( 'mysql', 0 ) )
		);
	}
}


/**
 * Delete email in Unsubscribed table
 *
 *@param	str		email
 */
function alo_em_delete_email_from_unsubscribed ( $email) {
	global $wpdb;
	$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}easymail_unsubscribed WHERE email = '%s'", $email ) );
}


/**
 * When someone unsubcribes, it adds email in 'unsubscribed' table
 * @param	str
 * @param	int		user id optional: only if subscriber is also a registered user
 */
function alo_em_add_unsubscribed_email_in_db_table ( $email, $user_id=false ) {
	global $wpdb;
	if ( ! alo_em_check_email_in_unsubscribed($email) )
	{
		alo_em_add_email_in_unsubscribed($email);
	}
}
add_action('alo_easymail_subscriber_deleted',  'alo_em_add_unsubscribed_email_in_db_table', 10, 2 );


/**
 * When someone subcribes, it delete email from 'unsubscribed' table
 * @param	str
 * @param	int		user id optional: only if subscriber is also a registered user
 */
function alo_em_delete_unsubscribed_email_from_db_table ( $subscriber, $user_id=false ) {
	global $wpdb;
	if ( alo_em_check_email_in_unsubscribed( $subscriber->email ) )
	{
		alo_em_delete_email_from_unsubscribed( $subscriber->email );
	}
}
add_action ( 'alo_easymail_new_subscriber_added',  'alo_em_delete_unsubscribed_email_from_db_table', 10, 2 );


/* EOF */