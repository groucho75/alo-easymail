<?php 
include('../../../wp-load.php');
global $wpdb;
global $user_ID;

if ( !current_user_can( "edit_newsletters" ) ) 	wp_die( __('Cheatin&#8217; uh?') );

check_admin_referer( "alo-easymail" );

$newsletter_id = ( isset( $_GET['newsletter'] ) && is_numeric( $_GET['newsletter'] ) ) ? (int) $_GET['newsletter'] : false;

if ( !alo_em_user_can_edit_newsletter( $newsletter_id ) ) wp_die( __('Cheatin&#8217; uh?') );

// first, search latest autosave
$newsletter = wp_get_post_autosave( $newsletter_id );

// if not autosave, look for saved post
if ( !$newsletter )
	$newsletter = get_post( $newsletter_id );

$content = $newsletter->post_content;

// general filters and shortcodes applied to 'the_content'?
if ( get_option('alo_em_filter_the_content') != "no" ) {
	add_filter ( 'the_content', 'do_shortcode', 11 );
	$content = apply_filters( "the_content", $content );
}

$theme_setting = get_option('alo_em_use_themes');

if ( $theme_setting == 'no' ) { // no theme
	$theme = '';
}
else if ( $theme_setting == 'yes' ) // free theme choice
{
	$transient = get_transient( 'alo_em_content_preview_'.$newsletter_id );
	if ( false === $transient ) {
		//$content = $newsletter->post_content;
		$theme = get_post_meta ( $newsletter_id, '_easymail_theme', true);
	} else {
		//$content = $transient['content'];
		$theme = $transient['theme'];
	}
}
else // fixed by setting
{
	$theme = $theme_setting;
}




if ( $theme != '' ) {
	$themes = alo_easymail_get_all_themes();
	$theme_path = ( isset( $themes[$theme] ) && file_exists( $themes[$theme] ) ) ? $themes[$theme] : false;
	if ( $theme_path ) {
		ob_start();
		require( $theme_path );
		$html = ob_get_clean();

		/*
		$html = alo_em_translate_text ( $recipient->lang, $html ); // translate the text ih html theme
		$html = str_replace('[CONTENT]', $content, $html);
		*/
		
		$info = pathinfo( $theme );
		$theme_dir =  basename( $theme, '.' . $info['extension'] );

		$html = preg_replace( '/ src\=[\'|"]'. $theme_dir.'(.+?)[\'|"]/', ' src="'. alo_easymail_get_themes_url().$theme_dir. '$1"', $html ); // <img src="..." >
		$html = preg_replace( '/url(.+?)[\s|\'|"]'. $theme_dir.'(.+?)[\s|\'|"]/', "url('". alo_easymail_get_themes_url() .$theme_dir. "$2'", $html ); // in style: url("...")
		$html = preg_replace( '/ background\=[\'|"]'. $theme_dir.'(.+?)[\'|"]/', ' background="'. alo_easymail_get_themes_url().$theme_dir. '$1"', $html ); // <table background="..." >
		
	}
	
} else {
	$html = '<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title></title>
</head>

<body>
<!-- Start newsletter content preview -->
[CONTENT]
<!-- End newsletter content preview -->
</body>

</html>';
}

$html = str_replace('<title>', '<title>['. __('Preview in newsletter theme', 'alo-easymail') .': ' . get_the_title($newsletter_id).'] ', $html);

// Test recipient
$recipient = (object) array(
	//'ID'		=> 1,
	'lang' 		=> alo_em_short_langcode ( get_locale() ),
	'name'		=> '{user-name}',
	'firstname'	=> '{user-firstname}',
	'email'		=> '{user-email}',
	'newsletter'=> $newsletter_id
);
$alo_em_cf = alo_easymail_get_custom_fields();
if ( $alo_em_cf ) {
	foreach( $alo_em_cf as $key => $value ){
		$recipient->$key = '{'.$key.'}';
	}
}

$subject = stripslashes ( alo_em_translate_text ( $recipient->lang, $newsletter->post_title, $newsletter_id, 'post_title' ) );
//$subject = apply_filters( 'alo_easymail_newsletter_title', $subject, $newsletter, $recipient );


// To avoid standard replacements by filters
$html = str_replace("[CONTENT]", $content, $html);


// Unsubscribe
$unsubfooter = alo_em_translate_option ( $recipient->lang, 'alo_em_custom_unsub_footer', true );
if ( empty( $unsubfooter ) ) $unsubfooter = __('You have received this message because you subscribed to our newsletter. If you want to unsubscribe: ', 'alo-easymail').' %UNSUBSCRIBELINK%';

$unsubfooter = str_replace ( '%UNSUBSCRIBELINK%', ' <a href="">'. '{user-unsubscribe-url}' .'</a>', $unsubfooter );
$unsubfooter = str_replace ( '%UNSUBSCRIBEURL%', '{user-unsubscribe-url}', $unsubfooter );

$html = str_replace('[USER-UNSUBSCRIBE]', $unsubfooter, $html);
$html = str_replace('[USER-UNSUBSCRIBE-URL]', '{user-unsubscribe-url}', $html);


// Read online
$viewonline_msg = alo_em_translate_option ( $recipient->lang, 'alo_em_custom_viewonline_msg', true );
if( empty( $viewonline_msg ) ) $viewonline_msg = __('To read the newsletter online you can visit this link:', 'alo-easymail') . ' %NEWSLETTERLINK%';

$viewonline_msg = str_replace( '%NEWSLETTERLINK%', ' <a href="">'. $subject .'</a>', $viewonline_msg );
$viewonline_msg = str_replace( '%NEWSLETTERURL%', '{read-online-url}', $viewonline_msg );
	
$html = str_replace('[READ-ONLINE]', $viewonline_msg, $html);
$html = str_replace('[READ-ONLINE-URL]', '{read-online-url}', $html);


// All filters
$html = apply_filters( 'alo_easymail_newsletter_content', $html, $newsletter, $recipient, false );

echo $html;
exit;
