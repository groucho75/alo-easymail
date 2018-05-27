<?php
/**
 * Frontend page where subscribers manage their subscription
 *
 * @package WordPress
 * @subpackage ALO EasyMail plugin
 */

global $wpdb;

// example:
// http://{blog_url}/?page_id=4&ac=unsubscribe&em1=email_account&em2=domain.ltd&uk={uniquekey}

$allowed_actions = array('activate', 'unsubscribe', 'do_unsubscribe', 'do_editlists');

// Email
$em1 = get_query_var('em1');
$em2 = get_query_var('em2');
$concat_email = $em1 . "@" . $em2; 
$email  = ( is_email($concat_email) ) ? $concat_email : false;

$unikey = ( get_query_var('uk') ) ? preg_replace( '/[^a-zA-Z0-9]/i', '', get_query_var('uk'))  : false;
$action = ( get_query_var('ac') && in_array( get_query_var('ac'), $allowed_actions) ) ? get_query_var('ac') : false;

$classfeedback_ok  = apply_filters ( 'alo_easymail_widget_ok_class', 'alo_easymail_widget_ok' );  // Hook
$classfeedback_err = apply_filters ( 'alo_easymail_widget_error_class', 'alo_easymail_widget_error' );  // Hook

// If there is not an activation/unsubscribe request
if ( !$action || !$email || alo_em_can_access_subscrpage ($email, $unikey) == false ) : // if cannot
	// if there is action show error msg
	if(get_query_var('ac')) echo "<p class=\"". $classfeedback_err ."\">".__("Error during operation.", "alo-easymail") ."</p>";
	
	$optin_txt = ( alo_em_translate_option ( alo_em_get_language (), 'alo_em_custom_optin_msg', false) !="") ? alo_em_translate_option ( alo_em_get_language (), 'alo_em_custom_optin_msg', false) : __("Yes, I would like to receive the Newsletter", "alo-easymail"); 
    echo "<div id='alo_easymail_page'>";
	echo alo_em_show_widget_form();
	echo "</div>";
	
else: // if can go on
 

// Activate
if ($email && $action == 'activate') {
    if (alo_em_edit_subscriber_state_by_email($email, "1", $unikey) === FALSE) {
        echo "<p class=\"". $classfeedback_err ."\">".__("Error during activation. Please check the activation link.", "alo-easymail")."</p>";
    } else {
        echo "<p class=\"". $classfeedback_ok ."\">".__("Your subscription was successfully activated. You will receive the next newsletter. Thank you.", "alo-easymail")."</p>";
        do_action ( 'alo_easymail_subscriber_updated', $email, $email );
        do_action ( 'alo_easymail_subscriber_activated', $email );

		alo_em_update_subscriber_last_act($email);
    }
}
    
// Request unsubscribe/modify subsription (step #1)
if ($email && $action == 'unsubscribe') {
	$mailinglists = alo_em_get_mailinglists( 'public' );
	if ($mailinglists) { // only if there are public lists
		echo '<form method="post" action="'. get_permalink() .'" class="alo_easymail_manage_subscriptions">';
		echo "<p>".__("To modify your subscription to mailing lists use this form", "alo-easymail") . ":</p>";
		echo '<div class="alo_easymail_lists_table">';
		echo alo_em_html_mailinglists_table_to_edit ( $email, "" );
		echo '</div>';

		//edit : added all the next if
		$alo_em_cf = alo_easymail_get_custom_fields();
		if( $alo_em_cf ):
			echo "<p>".__("You can modify your subscription details", "alo-easymail") . ":</p>";
			echo "<table>";
			foreach( $alo_em_cf as $key => $value ){
				echo "  <tr>\n";
				$field_id = "alo_em_".$key; // edit-by-alo
				echo "    <td><label for='".$field_id."'>". __( $value['humans_name'], "alo-easymail") ."</label></td>\n";
				echo "    <td>\n";
				echo alo_easymail_custom_field_html ( $key, $value, $field_id, "", true ) ."\n";
				echo "    </td>\n";
				echo "  </tr>\n";
			}
			echo "</table>";
		endif;

		wp_nonce_field('alo_em_subscpage','alo_em_nonce');
	   	echo '<input type="hidden" name="ac" value="do_editlists" />';
		echo '<input type="hidden" name="em1" value="'. esc_attr($em1). '" />';
		echo '<input type="hidden" name="em2" value="'. esc_attr($em2) .'" />';
		echo '<input type="hidden" name="uk" value="'. $unikey .'" />';
		echo '<input type="submit" name="submit" value="'. esc_attr( __('Edit', "alo-easymail") ). '" />';
		echo '</form>'; 
    }
    
    echo '<form method="post" action="'. get_permalink() .'" class="alo_easymail_unsubscribe_form">';
	$to_unsubscribe_txt = alo_em_translate_option ( alo_em_get_language (), 'alo_em_custom_to_unsubscribe_msg', false );
	if ( empty( $to_unsubscribe_txt ) ) $to_unsubscribe_txt = __("To unsubscribe the newsletter for good click this button", "alo-easymail");
	echo "<p>". $to_unsubscribe_txt . "</p>";
    wp_nonce_field('alo_em_subscpage','alo_em_nonce');
 	echo '<input type="hidden" name="ac" value="do_unsubscribe" />';
    echo '<input type="hidden" name="em1" value="'. esc_attr($em1) . '" />';
    echo '<input type="hidden" name="em2" value="'. esc_attr($em2) .'" />';
    echo '<input type="hidden" name="uk" value="'. $unikey .'" />';
    echo '<input type="submit" name="submit" value="'. esc_attr( __('Unsubscribe me', 'alo-easymail') ). '" />';
    echo '</form>'; 
}

// Confirm unsubscribe and do it! (step #2a)
if ($email && $action == 'do_unsubscribe' && isset($_POST['submit']) && wp_verify_nonce($_POST['alo_em_nonce'],'alo_em_subscpage') ) {
    if (alo_em_delete_subscriber_by_email($email, $unikey)) {
		$done_unsubscribe_txt = alo_em_translate_option ( alo_em_get_language (), 'alo_em_custom_done_unsubscribe_msg', false );
		if ( empty( $done_unsubscribe_txt ) ) $done_unsubscribe_txt = __("Your subscription was successfully deleted. Bye bye.", "alo-easymail");
        echo "<p class=\"". $classfeedback_ok ."\">". $done_unsubscribe_txt ."</p>";
        do_action ( 'alo_easymail_subscriber_deleted', $email, false );
    } else {
        echo "<p class=\"". $classfeedback_err ."\">".__("Error during unsubscription.", "alo-easymail")." ". __("Try again.", "alo-easymail"). "</p>";
        echo "<p class=\"". $classfeedback_err ."\">".__("If it fails again you can contact the administrator", "alo-easymail").": <a href='mailto:".get_option('admin_email')."?Subject=Unsubscribe'>".get_option('admin_email')."</a></p>";
    }
}

// Modify lists subscription and save it! (step #2b)
if ($email && $action == 'do_editlists' && isset($_POST['submit']) && wp_verify_nonce($_POST['alo_em_nonce'],'alo_em_subscpage') ) {
	$mailinglists = alo_em_get_mailinglists( 'public' );
	$subscriber_id = alo_em_is_subscriber( $email );
	if ($mailinglists) {
		foreach ( $mailinglists as $mailinglist => $val) {					
			if ( isset ($_POST['alo_em_profile_lists']) && is_array ($_POST['alo_em_profile_lists']) && in_array ( $mailinglist, $_POST['alo_em_profile_lists'] ) ) {
				alo_em_add_subscriber_to_list ( $subscriber_id, $mailinglist );	  // add to list
			} else {
				alo_em_delete_subscriber_from_list ( $subscriber_id, $mailinglist ); // remove from list
			}
		}
	}

	//edit : added all this foreach
	$alo_em_cf = alo_easymail_get_custom_fields();
	if ($alo_em_cf) {
		$fields = array();
		foreach( $alo_em_cf as $key => $value ){
			//check if custom fields have been changed
			if ( isset( $_POST[ "alo_em_". $key] ) ) {
				$fields[$key] = $_POST[ "alo_em_". $key];
			}
		}		
		alo_em_update_subscriber_by_email ( $email, $fields, 1, alo_em_get_language(true) ); 
	}
	$subscriber = alo_em_get_subscriber ( $email );
    do_action ( 'alo_easymail_subscriber_updated', $subscriber, $email );

    alo_em_update_subscriber_last_act($email);
    
	echo "<p class=\"". $classfeedback_ok ."\">" . __("Your subscription to mailing lists successfully updated", "alo-easymail") . ".</p>";
}


endif; //  end CHECK IF CAN ACCESS