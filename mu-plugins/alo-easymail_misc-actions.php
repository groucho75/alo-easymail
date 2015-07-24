<?php


/*******************************************************************************
 * 
 * EXAMPLE 
 *
 * Do actions when a newsletter delivery is complete
 *
 * @since: 2.0 
 *
 ******************************************************************************/

/**
 * Send a notification to author and to admin when a newsletter delivery is complete
 */ 
function custom_easymail_newsletter_is_delivered ( $newsletter ) {	
	$title = apply_filters( 'alo_easymail_newsletter_title', $newsletter->post_title, $newsletter, false );
	$content = "The newsletter **" . stripslashes ( $title ) . "**  was delivered to all recipients.";
	$content .= "\r\nTo disable this notification you have to edit: alo-easymail_custom-hooks.php";
	
  	$author = get_userdata( $newsletter->post_author );
  	wp_mail( $author->user_email, "Newsletter delivered!", $content );
  	wp_mail( get_option('admin_email'), "Newsletter delivered!", $content );
}
add_action ( 'alo_easymail_newsletter_delivered',  'custom_easymail_newsletter_is_delivered' );




/*******************************************************************************
 * 
 * EXAMPLE 
 *
 * Do actions when subscribers do something: eg. subscribe, unsubscribe,
 * edit subscription
 *
 * @since: 2.0 
 *
 ******************************************************************************/

 
/**
 * Send a notification to admin when there is a new subscriber
 * @param	obj
 * @param	int		user id optional: only if subscriber is also a registered user
 */ 
function custom_easymail_new_subscriber_is_added ( $subscriber, $user_id=false ) {
	if ( $user_id ) {
		$content = "A registered user has subscribed the newsletter:";
	} else {
		$content = "There is a new public subscriber:";
	}
	$content .= "\n\nemail: " . $subscriber->email ."\nname: ". $subscriber->name . "\nactivation: ". $subscriber->active . "\nlanguage: ". $subscriber->lang . "\n";
	if ( $user_id ) $content .= "user id: " . $user_id;
	$content .= "\r\nTo disable this notification you have to edit: alo-easymail_custom-hooks.php";
	wp_mail( get_option('admin_email'), "New subscriber", $content );
}
add_action('alo_easymail_new_subscriber_added',  'custom_easymail_new_subscriber_is_added', 10, 2 );


/**
 * Automatically add a new subscriber to a mailing list
 * @since 	2.1.3 
 * @param	obj
 * @param	int		user id optional: only if subscriber is also a registered user
 */ 
function custom_easymail_auto_add_subscriber_to_list ( $subscriber, $user_id=false ) {
	/*** Uncomment the next lines to make it works ***/
	// $list_id = 1; // put the ID of mailing list
	// alo_em_add_subscriber_to_list ( $subscriber->ID, $list_id ); 
}
add_action ( 'alo_easymail_new_subscriber_added',  'custom_easymail_auto_add_subscriber_to_list', 10, 2 );


/**
 * Do something when a subscriber updates own subscription info
 * @param	obj
 * @param	str 
 */ 
function custom_easymail_subscriber_is_updated ( $subscriber, $old_email ) {
	// do something...
}
add_action ( 'alo_easymail_subscriber_updated',  'custom_easymail_subscriber_is_updated', 10, 2);


/**
 * Do something when a subscriber unsubscribes
 * @param	str
 * @param	int		user id optional: only if subscriber is also a registered user
 */ 
function custom_easymail_subscriber_is_deleted ( $email, $user_id=false ) {
	// do something...
}
add_action('alo_easymail_subscriber_deleted',  'custom_easymail_subscriber_is_deleted', 10, 2 );


/**
 * Do something when a subscriber activates the subscription
 * (e.g. after click on activation link in email)
 * @since 	2.4.9
 * @param	str 
 */

function custom_easymail_subscriber_activated ( $email ) {
	// uncomment next lines to send a welcome message to just-activated subscribers
	/*
	$subscriber = alo_em_get_subscriber( $email );
	$subject = "Welcome on our newsletter!";
	$content = "Hi ". stripslashes( $subscriber->name ) .",\r\nwe are happy that you have activated the subscription to our newsletter.\r\n";
	$content .= "You'll receive news very soon.\r\n\r\nRegards\r\n". wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
	wp_mail( $email, $subject, $content );
	*/
}
add_action ( 'alo_easymail_subscriber_activated',  'custom_easymail_subscriber_activated' );


/* EOF */
