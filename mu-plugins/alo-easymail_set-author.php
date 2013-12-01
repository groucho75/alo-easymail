<?php


/*******************************************************************************
 *
 * Set newsletter author as sender instead of default setting.
 * Add the "Author" metabox in newsletter edit screen.
 *
 * @since: 2.4.15 
 *
 ******************************************************************************/


// Set author username as newsletter sender
function custom_easymail_headers_author ( $headers, $newsletter ) {

	$user_info = get_userdata( $newsletter->post_author );

	$from_name = $user_info->user_login; // or: $user_info->user_firstname, $user_info->user_lastname...
	$mail_sender = $user_info->user_email;

	$headers = "From: ". $from_name ." <".$mail_sender.">\n";
	$headers .= "Content-Type: text/html; charset=\"" . strtolower( get_option('blog_charset') ) . "\"\n";
	
	return $headers;
}
add_filter( 'alo_easymail_newsletter_headers', 'custom_easymail_headers_author', 10, 2 );


// Add "Author" meta box in newsletter edit screen to select another user as author
add_post_type_support( 'newsletter', 'author' );



/* EOF */
