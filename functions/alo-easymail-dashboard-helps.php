<?php if ( !defined('ABSPATH') ) die(); // If this file is called directly, abort.

/**
 * Dashboard helps related functions: contextual helps, pointers...
 *
 * @package WordPress
 * @subpackage ALO EasyMail plugin
 */



function alo_em_contextual_help_tabs() {
	if ( !class_exists('WP_Screen') ) return;
	$screen = get_current_screen();
	if ( !is_object($screen) ) return;
	if ( $screen->post_type != 'newsletter' ) return;

	// Common tab
	$screen->add_help_tab( array(
		'id'      => 'alo-easymail_links', // This should be unique for the screen.
		'title'   => __("Links"),
		'content' => '<p>'. __('Here you are a short screencast', 'alo-easymail') .
			': <a href="https://www.youtube.com/watch?v=juglGC28T2g" target="_blank">'.
				__('How to create and send a newsletter', 'alo-easymail').'</a></p>' .
			'<p>'.__("Resources about EasyMail Newsletter", "alo-easymail") . ': '.
			'<a href="http://www.eventualo.net/blog/wp-alo-easymail-newsletter/" target="_blank">homepage</a> |
			<a href="http://www.eventualo.net/blog/wp-alo-easymail-newsletter-guide/" target="_blank">guide</a> |
			<a href="http://www.eventualo.net/blog/wp-alo-easymail-newsletter-faq/" target="_blank">faq</a> |
			<a href="http://www.eventualo.net/blog/easymail-newsletter-for-developers/" target="_blank">for developers</a> |
			<a href="http://www.eventualo.net/blog/category/alo-easymail-newsletter/" target="_blank">news</a> |
			<a href="http://wordpress.org/support/plugin/alo-easymail" target="_blank" title="tag alo-easymail @ wordpress.org support forum">forum</a>'. '</p>'
	) );

	// Common sidebar
	$screen->set_help_sidebar(
		"<p style='text-align:center'>". __("If you use this plugin consider the idea of donating and supporting its development", "alo-easymail") ."</p><p>".
		"<form action='https://www.paypal.com/cgi-bin/webscr' method='post' style='display:inline;margin-left: 35px'>
		<input name='cmd' value='_s-xclick' type='hidden'><input name='lc' value='EN' type='hidden'><input name='hosted_button_id' value='9E6BPXEZVQYHA' type='hidden'>
		<input src='https://www.paypal.com/en_US/i/btn/btn_donate_SM.gif' name='submit' alt='Donate via PayPal' title='Donate via PayPal' border='0' type='image' style='vertical-align: middle'>
		<img src='https://www.paypal.com/it_IT/i/scr/pixel.gif' border='0' height='1' width='1'></form>" ."</p>"
	);

	if ( $screen->id == 'alo-easymail/pages/alo-easymail-admin-subscribers' ) {
		$screen->add_option(
			'per_page',
			array(
				'label' => __("subscribers per page", "alo-easymail"),
				'default' => 20,
				'option' => 'edit_per_page'
			)
		);
	}

}


/**
 * Load scripts for pointers (3.3+)
 */
function alo_em_tooltip_head_scripts() {
	global $pagenow, $wp_version, $typenow;
	if ( version_compare ( $wp_version, '3.3', '<' ) ) return; // old WP, exit

	$change_bounce_setup = get_user_setting( 'alo_em_pointer_changed_bounce_setup', 0 );

	if ( ! $change_bounce_setup || 'newsletter' == $typenow ) {
		$add_users = get_user_setting( 'alo_em_pointer_add_users', 0 );
		$no_yet_recipients = get_user_setting( 'alo_em_pointer_no_yet_recipients', 0 );
		$required_list = get_user_setting( 'alo_em_pointer_required_list', 0 );

		if ( ! $change_bounce_setup || ! $add_users || ! $no_yet_recipients || ! $required_list ) {
			wp_enqueue_style( 'wp-pointer' );
			wp_enqueue_script( 'wp-pointer' );
			wp_enqueue_script( 'utils' ); // needed for setUserSetting in js
			add_action( 'admin_print_footer_scripts', 'alo_em_print_pointer_footer_scripts' );
		}
	}
}
add_action( 'admin_enqueue_scripts', 'alo_em_tooltip_head_scripts');


/**
 * Print tooltip pointers (3.3+)
 */
function alo_em_print_pointer_footer_scripts() {
	global $pagenow, $typenow, $user_ID;
	$page = isset( $_GET['page'] ) ? $_GET['page'] : false;

	// In subscribers screen
	if ( $pagenow == "edit.php" && 'alo-easymail/pages/alo-easymail-admin-subscribers.php' == $page && ! get_user_setting( 'alo_em_pointer_add_users', 0 ) ) :
		$impexp_butt = __("Import/export subscribers", "alo-easymail");
		$pointer_content = '<h3>Easymail | '. esc_js( $impexp_butt ) .'</h3>';
		$pointer_content .= '<p>'. esc_js( sprintf( __('Maybe you would like to import subscribers from your blog registered members or an external archive (using CSV). Click the &#39;%s&#39; button', 'alo-easymail'), $impexp_butt) ) .'</p>';
		?>
		<script type="text/javascript">
			//<![CDATA[
			jQuery(document).ready( function($) {
				$('#easymail-subscribers-add-button').pointer({
					content: '<?php echo $pointer_content; ?>',
					position: 'top',
					close: function() { // Once the close button is hit
						setUserSetting( 'alo_em_pointer_add_users', '1' );
					}
				}).pointer('open');
			});
			//]]>
		</script>
		<?php
	endif; // In subscribers screen

	// In newsletter list screen
	if ( $pagenow == "edit.php" && 'newsletter' == $typenow && ! get_user_setting( 'alo_em_pointer_no_yet_recipients', 0 )) :
		$pointer_content = '<h3>Easymail | '. esc_js( __( 'No recipients selected yet', "alo-easymail") ) .'</h3>';
		$pointer_content .= '<p>'. esc_js( __('Before sending the newsletter you have to select recipients.', 'alo-easymail')." " .__('Click the link to do it now.', 'alo-easymail') ) .'</p>';
		?>
		<script type="text/javascript">
			//<![CDATA[
			jQuery(document).ready( function($) {
				$('.easymail-column-no-yet-recipients-<?php echo $user_ID ?>:first').pointer({
					content: '<?php echo $pointer_content; ?>',
					position: 'top',
					//close: function() { // Once the close button is hit
					open: function() { // Auto-dismiss, show only once
						setUserSetting( 'alo_em_pointer_no_yet_recipients', '1' );
					}
				}).pointer('open');
			});
			//]]>
		</script>
		<?php
	endif; // In newsletter list screen

	// In newsletter list screen
	if ( $pagenow == "edit.php" && 'newsletter' == $typenow && ! get_user_setting( 'alo_em_pointer_required_list', 0 ) ) :
		$pointer_content = '<h3>Easymail | '. esc_js( __( 'Create list of recipients', "alo-easymail") ) .'</h3>';
		$pointer_content .= '<p>'. esc_js( __('You have to prepare the list of recipients to send the newsletter to', 'alo-easymail').". " .__('Click the link to do it now.', 'alo-easymail') ) .'</p>';
		?>
		<script type="text/javascript">
			//<![CDATA[
			jQuery(document).ready( function($) {
				$('.easymail-column-status-required-list-<?php echo $user_ID ?>:first').pointer({
					content: '<?php echo $pointer_content; ?>',
					position: 'top',
					//close: function() { // Once the close button is hit
					open: function() { // Auto-dismiss, show only once
						setUserSetting( 'alo_em_pointer_no_yet_recipients', '1' );
						setUserSetting( 'alo_em_pointer_required_list', '1' );
					}
				}).pointer('open');
			});
			//]]>
		</script>
		<?php
	endif; // In newsletter list screen

	// In dashboard, anywhere
	if ( ! get_user_setting( 'alo_em_pointer_changed_bounce_setup', 0 ) ) :
		$pointer_content = '<h3>Easymail | '. esc_js( __( 'Only manual bounce management', "alo-easymail") ) .'</h3>';
		$pointer_content .= '<p><span style="color: red">'. esc_js( __('The cron-based bounce management has been removed', 'alo-easymail') ) .". </span>";
		$pointer_content .= esc_js( __('Visit the option page of newsletters to check bounces manually.', 'alo-easymail') ) .'</p>';
		?>
		<script type="text/javascript">
			//<![CDATA[
			jQuery(document).ready( function($) {
				$('li#menu-posts-newsletter').pointer({
					content: '<?php echo $pointer_content; ?>',
					position: 'top',
					close: function() { // Once the close button is hit
						setUserSetting( 'alo_em_pointer_changed_bounce_setup', '1' );
					}
				}).pointer('open');
			});
			//]]>
		</script>
		<?php
	endif;
}


/* EOF */