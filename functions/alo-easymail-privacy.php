<?php if ( !defined('ABSPATH') ) die(); // If this file is called directly, abort.

/**
 * Functions about Privacy Page
 *
 * @package WordPress
 * @subpackage ALO EasyMail plugin
 */


/**
 * Add suggesting text for the site privacy policy
 */
function alo_em_add_privacy_policy_content() {
	if ( ! function_exists( 'wp_add_privacy_policy_content' ) ) {
		return;
	}

	$content = '<div class="wp-suggested-text">';

	$content .= '<h3>' . __( 'Newsletter', "alo-easymail") . '</h3>';
	$content .= '<p class="privacy-policy-tutorial">';
	$content .= __( 'In this subsection you should note what information is captured through newsletter subscription form and when recipients perform actions on newsletters. ', "alo-easymail");
	$content .= '</p>';
	$content .= '<p><strong class="privacy-policy-tutorial">' . __( 'Suggested text:' ) . ' </strong>: ';
	$content .= __( 'When visitors subscribe the newsletter we collect the data shown in the subscription form.', "alo-easymail");
	if ( get_option('alo_em_collect_ip_address') == "yes" ) {
		$content .= ' ' . __( 'We store also the IP address of subscribers.', "alo-easymail");
	}
	$content .= '</p>';

	$content .= '<p>';
	$content .= __( 'We try to collect some recipient actions on newsletters:', "alo-easymail");
	if ( get_option('alo_em_use_tracking_pixel') != "no" ) {
		$content .= ' ' . __( 'newsletter views', "alo-easymail") . ', ';
	}
	$content .= __( 'clicks on links.', "alo-easymail");
	$content .= '</p>';

	$content .= '<p>';
	$content .= __( 'Subscribers can edit or remove own newsletter subscription through unsubscription link provided in each newsletter.', "alo-easymail");
	$content .= ' ' . __( 'Subscribers can contact the website administrators to export or remove own subscription data.', "alo-easymail");
	$content .= '</p>';

	$content .= '<div>'; // .wp-suggested-text;

	wp_add_privacy_policy_content(
		'ALO EasyMail Newsletter',
		wp_kses_post( $content )
	);
}
add_action( 'admin_init', 'alo_em_add_privacy_policy_content' );

/* EOF */
