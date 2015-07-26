<?php if ( !defined('ABSPATH') ) die(); // If this file is called directly, abort.

/**
 * Email sending related functions
 *
 * @package WordPress
 * @subpackage ALO EasyMail plugin
 */



/**
 * Send a newsletter to a test email
 */
function alo_em_send_mailtest () {
	$result = "no";
	check_ajax_referer( "alo-easymail" );
	$newsletter = ( isset( $_POST['newsletter'] ) && is_numeric( $_POST['newsletter'] ) ) ? (int) $_POST['newsletter'] : false;
	$email = ( isset( $_POST['email'] ) && is_email( $_POST['email'] ) ) ? $_POST['email'] : false;
	if ( $email && $newsletter && current_user_can( "publish_newsletters" ) ) {

		$maybe_subscriber = (array)alo_em_get_subscriber( $email );
		if ( isset( $maybe_subscriber['ID'] ) ) {
			$maybe_subscriber['subscriber'] = $maybe_subscriber['ID'];
			unset( $maybe_subscriber['ID'] );
		} else {
			$maybe_subscriber['subscriber'] = false;
		}
		$user_id = ( email_exists( $email ) ) ? email_exists( $email ) : false;
		$recipient = (object) array_merge( $maybe_subscriber, array ( 'newsletter' => $newsletter, 'email' => $email, 'user_id' => $user_id ) );
		//$recipient = (object) array ( 'newsletter' => $newsletter, 'email' => $email );

		if ( alo_em_send_newsletter_to ( $recipient, false ) ) $result = "yes";
	}
	usleep( 500000 );
	die ( $result );
}
add_action('wp_ajax_easymail_send_mailtest', 'alo_em_send_mailtest');



/**
 * Send email with activation link
 */
function alo_em_send_activation_email( $fields, $unikey, $lang ) {
	extract($fields);
	$blogname = html_entity_decode ( wp_kses_decode_entities ( get_option('blogname') ) );
	// Headers
	$mail_sender = ( get_option('alo_em_sender_email') ) ? get_option('alo_em_sender_email') : "noreply@". str_replace("www.","", $_SERVER['HTTP_HOST']);
	$headers =  "";//"MIME-Version: 1.0\n";
	$headers .= "From: ". $blogname ." <".$mail_sender.">\n";
	$headers .= "Content-Type: text/plain; charset=\"". get_bloginfo('charset') . "\"\n";

	$content = "lang=$lang&email=$email&name=$name&unikey=$unikey";

	//echo "<br />".$headers."<br />".$subscriber->email."<br />". $subject."<br />".  $content ."<hr />" ; // DEBUG

	if ( !empty($name) ) {
		$recipient_address = html_entity_decode ( wp_kses_decode_entities ($name) ) .' <'. $email.'>';
	} else {
		$recipient_address = $email;
	}

	$sending = wp_mail( $recipient_address, "#_EASYMAIL_ACTIVATION_#", $content, $headers);
	return $sending;
}


/**
 * Edit the activation e-mail message
 */
function alo_em_handle_email ( $args ) {
	// $args['to'], $args['subject'], $args['message'], $args['headers'], $args['attachments']

	// Check based on $args['subject']; more attrs in $args['message']
	global $_config;
	/*
	 * 1) Activation e-mail
	 */
	if ( strpos ( "#_EASYMAIL_ACTIVATION_#", $args['subject'] ) !== false) {

		// Get the parameters stored as a query in $args['message']
		$defaults = array( 'lang' => '', 'email' => '',	'name' => '', 'unikey' => '' );
		/* // replaced 'wp_parse_args' because use urlencode and stripslashes, so affect emails with '+' chars
		$customs = wp_parse_args( $args['message'], $defaults );
		extract( $customs, EXTR_SKIP );
		*/
		$pars = array();
		$raw = explode('&', $args['message']);
		foreach ($raw as $section)
		{
			if (strpos($section, '=') !== false)
			{
				list($key, $value) = explode('=', $section);
				$pars[$key] = $value;
			}
		}
		$customs = array_merge( $defaults, $pars );
		extract( $customs, EXTR_SKIP );

		// Subject
		if ( $subject_text = alo_em_translate_option ( $lang, 'alo_em_txtpre_activationmail_subj', true ) ) {
			$subject = $subject_text;
		} else {
			$subject = alo_em___( __("Confirm your subscription to %BLOGNAME% Newsletter", "alo-easymail" ) );
		}
		$blogname = html_entity_decode ( wp_kses_decode_entities ( get_option('blogname') ) );
		$subject = str_replace ( "%BLOGNAME%", $blogname, $subject );
		$args['subject'] = $subject;

		// Content
		if ( $content_txt = alo_em_translate_option ( $lang, 'alo_em_txtpre_activationmail_mail', true ) ) {
			$content = $content_txt;
		} else {
			$content = __("Hi %NAME%\nto complete your subscription to %BLOGNAME% newsletter you need to click on the following link (or paste it in the address bar of your browser):\n", "alo-easymail");
			$content .= "%ACTIVATIONLINK%\n\n";
			$content .= __("If you did not ask for this subscription ignore this message.", "alo-easymail"). "\n";
			$content .= __("Thank you", "alo-easymail")."\n". $blogname ."\n";
		}

		$sub_vars = $email ."|" /*$div_email[0] . "|" . $div_email[1] . "|" */ . $unikey . "|" . $lang;

		//$sub_vars = $subscriber->ID . "|" . $subscriber->unikey;
		$sub_vars = urlencode( base64_encode( $sub_vars ) );
		$sub_link = add_query_arg( 'emact', $sub_vars, alo_em_translate_home_url ( $lang ) );

		$content = str_replace ( "%BLOGNAME%", $blogname, $content );
		$content = str_replace ( "%NAME%", /* $subscriber->name */ $name, $content );
		$content = str_replace ( "%ACTIVATIONLINK%", $sub_link, $content );

		$args['message'] = $content;
	}
	return $args;
}

add_filter('wp_mail', 'alo_em_handle_email');


/**
 * Wrap text and create alt text content before sending newsletter
 */
function alo_em_alt_mail_body( $phpmailer ) {
	$phpmailer->WordWrap = 50;
	//if( $phpmailer->ContentType == 'text/html' && $phpmailer->AltBody == '') {
	if( $phpmailer->ContentType == 'text/html') { // added by sanderbontje
		$plain_text = alo_em_html2plain ( $phpmailer->Body );
		// To avoid empty alt text that does not make newsletter leave out!
		if ( $plain_text == "" ) {
			$plain_text .= __( 'This newsletter is available only in html', 'alo-easymail' ).".\n";
			$plain_text .= __( 'The link to the site', 'alo-easymail' ).": ";
			$plain_text .= get_option ('siteurl');
		}
		$phpmailer->AltBody = $plain_text;

		// Return-Path if bounce settings
		$bounce_settings = alo_em_bounce_settings();
		if ( is_email($bounce_settings['bounce_email']) )
		{
			$phpmailer->Sender = $bounce_settings['bounce_email'];
		}
	}
}
add_action( 'phpmailer_init', 'alo_em_alt_mail_body' );


/**
 * Send the Newsletter to Recipient
 * @param	arr		a recipient object: email, newsletter, ID (opt), lang (opt), name (opt), unikey (opt), subsriber (opt)
 * @param	bol		if true forse to send, ignore debug setting
 * @return	bol
 */
function alo_em_send_newsletter_to ( $recip, $force_send=false ) {
	global $wpdb;
	$defaults = array(
		'email' => false,
		'newsletter' => false,
		'ID' => false,	// if false, it's a test sending
		'lang' => alo_em_get_language (),
		'name' => false,
		'firstname' => false,
		'subscriber' => false,
		'unikey' => false,
		'user_id' => false
	);
	$args = wp_parse_args( (array)$recip, $defaults );
	$recipient = (object)$args;

	if ( !is_email( $recipient->email ) ) {
		$wpdb->update( "{$wpdb->prefix}easymail_recipients",
			array ( 'result' => -2 ),
			array ( 'ID' => $recipient->ID )
		);
		return;
	}

	// Get newsletter details
	$newsletter = alo_em_get_newsletter( $recipient->newsletter );

	$subject = stripslashes ( alo_em_translate_text ( $recipient->lang, $newsletter->post_title, $newsletter->ID, 'post_title' ) );
	$subject = apply_filters( 'alo_easymail_newsletter_title', $subject, $newsletter, $recipient );

	$content = alo_em_translate_text( $recipient->lang, $newsletter->post_content, $newsletter->ID, 'post_content' );

	// general filters and shortcodes applied to 'the_content'?
	if ( get_option('alo_em_filter_the_content') != "no" ) {
		add_filter ( 'the_content', 'do_shortcode', 11 );
		$content = apply_filters( "the_content", $content );
	}

	// easymail standard and custom filters
	$content = apply_filters( 'alo_easymail_newsletter_content', $content, $newsletter, $recipient, false );


	$mail_sender = ( get_option('alo_em_sender_email') ) ? get_option('alo_em_sender_email') : "noreply@". str_replace("www.","", $_SERVER['HTTP_HOST']);
	$from_name = html_entity_decode ( wp_kses_decode_entities ( get_option('alo_em_sender_name') ) );

	$headers = "From: ". $from_name ." <".$mail_sender.">\n";
	$headers .= "Content-Type: text/html; charset=\"" . strtolower( get_option('blog_charset') ) . "\"\n";

	// Custom newsletter headers
	$headers = apply_filters( 'alo_easymail_newsletter_headers', $headers, $newsletter, $recipient );

	// Custom newsletter attachs
	$attachs = apply_filters( 'alo_easymail_newsletter_attachments', array(), $newsletter );

	// ---- Send MAIL (or DEBUG) ----
	$send_mode = ( $force_send ) ? "" : get_option('alo_em_debug_newsletters');

	if ( !empty($recipient->name) ) {
		$recipient_address = html_entity_decode ( wp_kses_decode_entities ($recipient->name) ) .' <'. $recipient->email.'>';
	} else {
		$recipient_address = $recipient->email;
	}

	switch ( $send_mode ) {
		case "to_author":
			$author = get_userdata( $newsletter->post_author );
			$debug_subject = "( DEBUG - TO: ". $recipient_address ." ) " . $subject;
			$mail_engine = wp_mail( $author->user_email, $debug_subject, $content, $headers, $attachs );
			break;
		case "to_file":
			$log = fopen( WP_CONTENT_DIR . "/user_{$newsletter->post_author}_newsletter_{$newsletter->ID}.log", 'a+' );
			$log_message = 	"\n------------------------------ ". date_i18n( __( 'j M Y @ G:i' ) ) ." ------------------------------\n\n";
			$log_message .=	"HEADERS:\n". $headers ."\n";
			$log_message .=	"TO:\t\t\t". $recipient->email ."\n";
			$log_message .=	"SUBJECT:\t". $subject ."\n\n";
			$log_message .=	"CONTENT:\n". $content ."\n\n";
			if ( !empty($attachs) ) $log_message .=	"ATTACHMENTS:\n". ( is_array($attachs) ? print_r($attachs,true) : $attachs ) ."\n\n";
			$mail_engine = ( fwrite ( $log, $log_message ) ) ? true : false;
			fclose ( $log );
			break;
		default:  // no debug: send it!
			$mail_engine = wp_mail( $recipient_address, $subject, $content, $headers, $attachs );
	}

	$sent = ( $mail_engine ) ? "1" : "-1";

	// If recipient is in db (eg. ID exists) update db
	if ( $recipient->ID ) {
		$wpdb->update(    "{$wpdb->prefix}easymail_recipients",
			array ( 'result' => $sent ),
			array ( 'ID' => $recipient->ID )
		);
	}
	return ( $mail_engine ) ? true : false;
}


/* EOF */