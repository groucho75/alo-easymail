<?php if ( !defined('ABSPATH') ) die(); // If this file is called directly, abort.

/**
 * Frontend related functions
 *
 * @package WordPress
 * @subpackage ALO EasyMail plugin
 */


/**
 * Load scripts & styles on Frontend
 */
function alo_em_load_scripts() {
	if ( get_option('alo_em_embed_css') == "yes" ) {
		if ( @file_exists ( STYLESHEETPATH.'/alo-easymail.css' ) ) {
			wp_enqueue_style ('alo-easymail', get_bloginfo('stylesheet_directory') .'/alo-easymail.css' );
		} else {
			wp_enqueue_style ('alo-easymail', ALO_EM_PLUGIN_URL.'/alo-easymail.css' );
		}
	}
}
add_action('wp_enqueue_scripts', 'alo_em_load_scripts');


/**
 * Exclude the easymail page from pages' list
 */
function alo_em_exclude_page( $pages ) {
	if ( !is_admin() ) {
		for ( $i=0; $i<count($pages); $i++ ) {
			$page = & $pages[$i];
			if ($page->ID == get_option('alo_em_subsc_page')) unset ($pages[$i]);
		}
	}
	return $pages;
}


/**
 * Manage the newsletter subscription page
 */
function alo_em_subscr_page ( $atts, $content = null ) {
	ob_start();
	include( ALO_EM_PLUGIN_ABS .'/pages/alo-easymail-subscr-page.php' );
	$contents = ob_get_contents();
	ob_end_clean();
	return $contents;
}
add_shortcode('ALO-EASYMAIL-PAGE', 'alo_em_subscr_page');


/**
 * Boxes meta in Newsletter edit/new pages
 */
function alo_em_newsletter_add_custom_box() {
	add_meta_box( "alo_easymail_newsletter_recipients", __("Recipients", "alo-easymail"), "alo_em_meta_recipients", "newsletter", "side", "high" );
	if ( get_option('alo_em_use_themes') == 'yes' || get_option('alo_em_use_themes') == '' ) add_meta_box( "alo_easymail_newsletter_themes", __("Themes", "alo-easymail"), "alo_em_meta_themes", "newsletter", "normal", "high" );
	add_meta_box( "alo_easymail_newsletter_placeholders", __("Placeholders", "alo-easymail"), "alo_em_meta_placeholders", "newsletter", "normal", "high" );
}
add_action('add_meta_boxes', 'alo_em_newsletter_add_custom_box', 8);



/*************************************************************************
 * AJAX 'SACK' FUNCTION
 *************************************************************************/

add_action('wp_head', 'alo_em_ajax_js' );


function alo_em_ajax_js()
{
	// Do not include js if required
	if ( get_option('alo_em_hide_widget_users') == "yes" && is_user_logged_in() ) return;

	// use JavaScript SACK library for Ajax
	wp_print_scripts( array( 'sack' ));

	?>
	<script type="text/javascript">
		//<![CDATA[
		<?php if ( is_user_logged_in() ) { // if logged in ?>
		function alo_em_user_form ( opt )
		{
			<?php
            $alo_em_cf = alo_easymail_get_custom_fields();

            $classfeedback = apply_filters ( 'alo_easymail_widget_error_class', 'alo_easymail_widget_error' );  // Hook
            ?>

			// updating...
			document.getElementById('alo_easymail_widget_feedback').innerHTML = '';
			document.getElementById('alo_easymail_widget_feedback').className = '<?php echo $classfeedback ?>';
			document.getElementById('alo_em_widget_loading').style.display = "inline";

			var alo_em_sack = new sack(
				"<?php echo admin_url( 'admin-ajax.php', is_ssl() ? 'admin' : 'http' ) ?>" );

			alo_em_sack.execute = 1;
			alo_em_sack.method = 'POST';
			alo_em_sack.setVar( "action", "alo_em_user_form_check" );
			alo_em_sack.setVar( "alo_easymail_option", opt );
			<?php
            $txt_generic_error = esc_js( alo_em___(__("Error during operation.", "alo-easymail")) );
            $txt_ok 		= esc_js( alo_em___(__("Successfully updated", "alo-easymail")) );
            $txt_need_sub = esc_js( alo_em___(__("Before editing other fields you have to click the subscription choice", "alo-easymail")) );
            $lang_code 	= alo_em_get_language( true );
            //edit : added all this foreach
            if( $alo_em_cf ) {
                foreach( $alo_em_cf as $key => $value ){
                    $var_name 	= "error_".$key."_empty";
                    $$var_name 	= esc_js( alo_em___( sprintf(__("The %s field is empty", "alo-easymail"), __($value['humans_name'],"alo-easymail")) ) );
                    $var_incorrect 	= "error_".$key."_incorrect";
                    $$var_incorrect 	= esc_js( alo_em___( sprintf(__("The %s field is not correct", "alo-easymail"), __($value['humans_name'],"alo-easymail")) ) );
                }
            }
            ?>
			<?php
            //edit : added all this foreach
            if( $alo_em_cf ) {
                foreach( $alo_em_cf as $key => $value ){
                	switch ( $value['input_type']) {
                		case 'checkbox':
                			echo 'alo_em_sack.setVar( "alo_em_'.$key.'", ( document.getElementById(\'alo_em_'.$key.'\').checked ? 1 : 0 ) );'."\n";

                			break;
                		default:
                    		echo 'alo_em_sack.setVar( "alo_em_'.$key.'", document.getElementById(\'alo_em_'.$key.'\').value );'."\n";
                	}
                }
            }
            ?>
			alo_em_sack.setVar( "alo_easymail_txt_generic_error", '<?php echo $txt_generic_error ?>' );
			alo_em_sack.setVar( "alo_easymail_txt_success", '<?php echo $txt_ok ?>' );
			alo_em_sack.setVar( "alo_easymail_txt_need_sub", '<?php echo $txt_need_sub ?>' );
			alo_em_sack.setVar( "alo_easymail_lang_code", '<?php echo $lang_code ?>' );
			<?php
          //edit : added all this foreach
          if( $alo_em_cf ) {
              foreach( $alo_em_cf as $key => $value ){
                    $var_name = "error_".$key."_empty";
                    $var_incorrect = "error_".$key."_incorrect";
                    if ( $value['input_mandatory'] ) echo 'alo_em_sack.setVar( "alo_em_error_'.$key.'_empty", "'.$$var_name.'");'."\n";
                    if ( !empty($value['input_validation']) ) echo 'alo_em_sack.setVar( "alo_em_error_'.$key.'_incorrect", "'.$$var_incorrect.'");'."\n";
              }
          }
            ?>
			var cbs = document.getElementById('alo_easymail_widget_form').getElementsByTagName('input');
			var length = cbs.length;
			var lists = "";
			for (var i=0; i < length; i++) {
				if (cbs[i].name == 'alo_em_form_lists' +'[]' && cbs[i].type == 'checkbox') {
					if ( cbs[i].checked ) lists += cbs[i].value + ",";
				}
			}
			alo_em_sack.setVar( "alo_em_form_lists", lists );
			alo_em_sack.setVar( "alo_em_nonce", '<?php echo wp_create_nonce('alo_em_form') ?>' );
			//alo_em_sack.onError = function() { alert('Ajax error' )};
			alo_em_sack.runAJAX();

			return true;

		}
		<?php } else {  // if not is_user_logged_in() ?>
		function alo_em_pubblic_form ()
		{

			var alo_cf_array = new Array();
			<?php
            $alo_em_cf = alo_easymail_get_custom_fields();
            $i = 0;
            if($alo_em_cf) {
                    foreach( $alo_em_cf as $key => $value ){
                    echo "alo_cf_array[".$i."] = '" . $key . "';\n";
                    ++$i;
                }
            }
            ?>
			<?php
            $error_txt_generic 		= esc_js( alo_em___(__("Error during operation.", "alo-easymail")) );
            $error_email_incorrect 	= esc_js( alo_em___(__("The e-email address is not correct", "alo-easymail")) );
            $error_name_empty 		= esc_js( alo_em___(__("The name field is empty", "alo-easymail")) );
            //edit : added all this foreach
            if( $alo_em_cf ) {
                foreach( $alo_em_cf as $key => $value ){
                    $var_name 	= "error_".$key."_empty";
                    $$var_name 	= esc_js( alo_em___( sprintf(__("The %s field is empty", "alo-easymail"), __($value['humans_name'],"alo-easymail")) ) );
                    $var_incorrect 	= "error_".$key."_incorrect";
                    $$var_incorrect 	= esc_js( alo_em___( sprintf(__("The %s field is not correct", "alo-easymail"), __($value['humans_name'],"alo-easymail")) ) );
                }
            }
            $error_email_added		= esc_js( alo_em___(__("Warning: this email address has already been subscribed, but not activated. We are now sending another activation email", "alo-easymail")) );
            $error_email_activated	= esc_js( alo_em___(__("Warning: this email address has already been subscribed", "alo-easymail")) );
            $error_on_sending			= esc_js( alo_em___(__("Error during sending: please try again", "alo-easymail")) );
            if ( get_option('alo_em_no_activation_mail') != "yes" ) {
                      $txt_ok			= esc_js( alo_em___(__("Subscription successful. You will receive an e-mail with a link. You have to click on the link to activate your subscription.", "alo-easymail")) );
            } else {
                      $txt_ok			= esc_js( alo_em___(__("Your subscription was successfully activated. You will receive the next newsletter. Thank you.", "alo-easymail")) );
            }
            $txt_subscribe			= esc_js( alo_em___(__("Subscribe", "alo-easymail")) );
            $txt_sending				= esc_js( alo_em___(__("sending...", "alo-easymail")) );
            $lang_code				= alo_em_get_language( true );
            ?>
			document.alo_easymail_widget_form.submit.value="<?php echo $txt_sending ?>";
			document.alo_easymail_widget_form.submit.disabled = true;
			document.getElementById('alo_em_widget_loading').style.display = "inline";
			document.getElementById('alo_easymail_widget_feedback').innerHTML = "";

			var alo_em_sack = new sack("<?php echo admin_url( 'admin-ajax.php', is_ssl() ? 'admin' : 'http' ) ?>" );

			alo_em_sack.execute = 1;
			alo_em_sack.method = 'POST';
			alo_em_sack.setVar( "action", "alo_em_pubblic_form_check" );
			<?php if ( get_option('alo_em_hide_name_input') != 'yes' ) : ?>
			alo_em_sack.setVar( "alo_em_opt_name", document.alo_easymail_widget_form.alo_em_opt_name.value );
			<?php endif; ?>
			alo_em_sack.setVar( "alo_em_opt_email", document.alo_easymail_widget_form.alo_em_opt_email.value );
			<?php
            //edit : added all this foreach
            if( $alo_em_cf ) {
                foreach( $alo_em_cf as $key => $value ){
                   	//echo 'alo_em_sack.setVar( "alo_em_'.$key.'", document.getElementById(\'alo_em_'.$key.'\').value );'."\n";
                	switch ( $value['input_type']) {
                		case 'checkbox':
                			echo 'alo_em_sack.setVar( "alo_em_'.$key.'", ( document.getElementById(\'alo_em_'.$key.'\').checked ? 1 : 0 ) );'."\n";

                			break;
                		default:
                    		echo 'alo_em_sack.setVar( "alo_em_'.$key.'", document.getElementById(\'alo_em_'.$key.'\').value );'."\n";
                	}
                }
            }
              ?>
			alo_em_sack.setVar( "alo_easymail_txt_generic_error", '<?php echo $error_txt_generic ?>' );
			alo_em_sack.setVar( "alo_em_error_email_incorrect", "<?php echo $error_email_incorrect ?>");
			alo_em_sack.setVar( "alo_em_error_name_empty", "<?php echo $error_name_empty ?>");
			<?php
            //edit : added all this foreach
            if( $alo_em_cf ) {
                foreach( $alo_em_cf as $key => $value ){
                      $var_name = "error_".$key."_empty";
                      $var_incorrect = "error_".$key."_incorrect";
                      if ( $value['input_mandatory'] ) echo 'alo_em_sack.setVar( "alo_em_error_'.$key.'_empty", "'.${$var_name}.'");'."\n";
                      if ( !empty($value['input_validation']) ) echo 'alo_em_sack.setVar( "alo_em_error_'.$key.'_incorrect", "'.${$var_incorrect}.'");'."\n";
                }
            }
            ?>
			alo_em_sack.setVar( "alo_em_error_email_added", "<?php echo $error_email_added ?>");
			alo_em_sack.setVar( "alo_em_error_email_activated", "<?php echo $error_email_activated ?>");
			alo_em_sack.setVar( "alo_em_error_on_sending", "<?php echo $error_on_sending ?>");
			alo_em_sack.setVar( "alo_em_txt_ok", "<?php echo $txt_ok ?>");
			alo_em_sack.setVar( "alo_em_txt_subscribe", "<?php echo $txt_subscribe ?>");
			alo_em_sack.setVar( "alo_em_lang_code", "<?php echo $lang_code ?>");

			var cbs = document.getElementById('alo_easymail_widget_form').getElementsByTagName('input');
			var length = cbs.length;
			var lists = "";
			for (var i=0; i < length; i++) {
				if (cbs[i].name == 'alo_em_form_lists' +'[]' && cbs[i].type == 'checkbox') {
					if ( cbs[i].checked ) lists += cbs[i].value + ",";
				}
			}
			alo_em_sack.setVar( "alo_em_form_lists", lists );
			alo_em_sack.setVar( "alo_em_nonce", '<?php echo wp_create_nonce('alo_em_form') ?>' );
			//alo_em_sack.onError = function() { alert('Ajax error' )};
			alo_em_sack.runAJAX();

			return true;

		}
		<?php } // end if is_user_logged_in() ?>
		//]]>
	</script>
	<?php
} // end alo_em_ajax_js

add_action('wp_ajax_alo_em_user_form_check', 'alo_em_user_form_callback');				// logged in
add_action('wp_ajax_nopriv_alo_em_pubblic_form_check', 'alo_em_pubblic_form_callback'); // pubblic, no logged in

// For logged-in users
function alo_em_user_form_callback() {
	global $wpdb, $user_ID, $user_email, $current_user;
	get_currentuserinfo();
	// Nonce error make exit now
	if ( ! wp_verify_nonce($_POST['alo_em_nonce'], 'alo_em_form') ) {
		$output = esc_js($_POST['alo_easymail_txt_generic_error']) . ".<br />";
		$classfeedback = apply_filters ( 'alo_easymail_widget_error_class', 'alo_easymail_widget_error' );  // Hook
		$feedback = "";
		$feedback .= "document.getElementById('alo_easymail_widget_feedback').innerHTML = '". $output ."';";
		$feedback .= "document.getElementById('alo_easymail_widget_feedback').className = '".$classfeedback."';";
		$feedback .= "document.getElementById('alo_em_widget_loading').style.display = 'none';";
		die($feedback);
	}
	$alo_em_cf = alo_easymail_get_custom_fields();
	$error_on_adding = false;
	if ($user_ID && isset($_POST['alo_easymail_option'])) {
		switch ( $_POST['alo_easymail_option'] ) {
			case "yes":
				$lang = ( isset($_POST['alo_easymail_lang_code']) && in_array ( $_POST['alo_easymail_lang_code'], alo_em_get_all_languages( false )) ) ? $_POST['alo_easymail_lang_code'] : "" ;
				if ( get_user_meta($user_ID, 'first_name', true) != "" || get_user_meta($user_ID, 'last_name', true) != "" ) {
					$reg_name = ucfirst(get_user_meta($user_ID, 'first_name',true))." " .ucfirst(get_user_meta($user_ID,'last_name',true));
				} else {
					$reg_name = get_user_meta($user_ID, 'nickname', true);
				}
				//alo_em_add_subscriber($user_email, $reg_name, 1, $lang );
				$fields['email'] = $user_email; //edit : added all this line
				$fields['name'] = $reg_name; //edit : added all this line
				if ( alo_em_add_subscriber( $fields, 1, $lang ) == "OK" ) { //edit : orig : if ( alo_em_add_subscriber($user_email, $reg_name, 1, $lang ) == "OK" ) {
					$subscriber = alo_em_get_subscriber ( $user_email );
					do_action ( 'alo_easymail_new_subscriber_added', $subscriber, $user_ID );
				}
				break;
			case "no":
				// alo_em_delete_subscriber_by_id( alo_em_is_subscriber($user_email) );
				if ( alo_em_delete_subscriber_by_id( alo_em_is_subscriber($user_email) ) ) do_action ( 'alo_easymail_subscriber_deleted', $user_email, $user_ID );
				break;
			case "lists":
				$subscriber_id = alo_em_is_subscriber ( $user_email );
				$mailinglists = alo_em_get_mailinglists( 'public' );
				$lists = ( isset($_POST['alo_em_form_lists'])) ? explode ( ",", trim ( $_POST['alo_em_form_lists'] , "," ) ) : array();
				if ($mailinglists && $subscriber_id) {
					foreach ( $mailinglists as $mailinglist => $val) {
						if ( in_array ( $mailinglist, $lists ) ) {
							alo_em_add_subscriber_to_list ( $subscriber_id, $mailinglist );	  // add to list
						} else {
							alo_em_delete_subscriber_from_list ( $subscriber_id, $mailinglist ); // remove from list
						}
					}
				} else if ($mailinglists) {
					$error_on_adding .= esc_js($_POST['alo_easymail_txt_need_sub']) . ".<br />";
				}
				break;

			case "cf":
				// update only if subscriber
				$subscriber_id = alo_em_is_subscriber ( $user_email );
				if ( $subscriber_id )
				{
					$lang = ( isset($_POST['alo_easymail_lang_code']) && in_array ( $_POST['alo_easymail_lang_code'], alo_em_get_all_languages( false )) ) ?  $_POST['alo_easymail_lang_code'] : "" ;
					//edit : added all this foreach
					if( $alo_em_cf ) {
						$fields = array();
						foreach( $alo_em_cf as $key => $value ){
							if ( isset($_POST['alo_em_'.$key]) ) {
								$fields[$key] 	= stripslashes( trim( $_POST['alo_em_'.$key] ));
								if ( empty( $fields[$key] ) && $value['input_mandatory'] ) {
									$error_on_adding .= esc_js($_POST['alo_em_error_'.$key.'_empty']) . ".<br />";
								} else if ( !empty($value['input_validation']) && function_exists($value['input_validation']) && call_user_func($value['input_validation'], $fields[$key])==false ) {
									$error_on_adding .= esc_js($_POST['alo_em_error_'.$key.'_incorrect']) . ".<br />";
								}
							}
						}
						alo_em_update_subscriber_by_email ( $user_email, $fields, 1, $lang );
						//die( $wpdb->last_query. print_r($_POST,true) );
					}
				} else {
					$error_on_adding .= esc_js($_POST['alo_easymail_txt_need_sub']) . ".<br />";
				}
				break;
		}
		// Compose JavaScript for return
		if ( $error_on_adding == false ) {
			$output = esc_js($_POST['alo_easymail_txt_success']);
			$classfeedback = apply_filters ( 'alo_easymail_widget_ok_class', 'alo_easymail_widget_ok' );  // Hook
		} else {
			$output = $error_on_adding;
			$classfeedback = apply_filters ( 'alo_easymail_widget_error_class', 'alo_easymail_widget_error' );  // Hook
		}
		$feedback = "";
		$feedback .= "document.getElementById('alo_easymail_widget_feedback').innerHTML = '". $output ."';";
		$feedback .= "document.getElementById('alo_easymail_widget_feedback').className = '".$classfeedback."';";
		$feedback .= "document.getElementById('alo_em_widget_loading').style.display = 'none';";
		// sanitize inputs before print
		if ( $alo_em_cf ) {
			foreach( $alo_em_cf as $key => $value ){
				$feedback .= "document.alo_easymail_widget_form.alo_em_".$key.".value ='".esc_js(sanitize_text_field($_POST['alo_em_'.$key]))."';";
			}
		}

		// if unsubscribe deselect all lists
		if ( isset($_POST['alo_easymail_option']) && $_POST['alo_easymail_option']=="no" ) {
			$feedback .= "var cbs = document.getElementById('alo_easymail_widget_form').getElementsByTagName('input');";
			$feedback .= "var length = cbs.length;";
			$feedback .= "for (var i=0; i < length; i++) {";
			$feedback .= 	"if (cbs[i].name == 'alo_em_form_lists' +'[]' && cbs[i].type == 'checkbox') { cbs[i].checked = false; }";
			$feedback .= "}";
		}

		alo_em_update_subscriber_last_act($user_email);

		// END!
		die($feedback);
	}
}

// For NOT-logged-in pubblic visitors
function alo_em_pubblic_form_callback() {
	global $wpdb, $user_ID;
	// Nonce error make exit now
	if ( ! wp_verify_nonce($_POST['alo_em_nonce'], 'alo_em_form') ) {
		$output = esc_js($_POST['alo_easymail_txt_generic_error']) . ".<br />";
		$classfeedback = apply_filters ( 'alo_easymail_widget_error_class', 'alo_easymail_widget_error' );  // Hook
		$feedback = "";
		$feedback .= "document.getElementById('alo_easymail_widget_feedback').innerHTML = '". $output ."';";
		$feedback .= "document.getElementById('alo_easymail_widget_feedback').className = '".$classfeedback."';";
		$feedback .= "document.getElementById('alo_em_widget_loading').style.display = 'none';";
		die($feedback);
	}
	$alo_em_cf = alo_easymail_get_custom_fields();
	if (isset($_POST['alo_em_opt_email'])){
		$error_on_adding = "";
		$just_added = false;
		$_POST = array_map( 'strip_tags', $_POST );
		if ( get_option('alo_em_hide_name_input') != 'yes' ) {
			$name 	= trim( $_POST['alo_em_opt_name'] );
		} else {
			$name 	= '';
		}
		$email	= trim( $_POST['alo_em_opt_email'] );
		$lang = ( isset($_POST['alo_em_lang_code']) && in_array ( $_POST['alo_em_lang_code'], alo_em_get_all_languages( false )) ) ? $_POST['alo_em_lang_code'] : "" ;
		if ( !is_email($email) ) {
			$error_on_adding .= esc_js($_POST['alo_em_error_email_incorrect']). "<br />";
		}
		if ( get_option('alo_em_hide_name_input') != 'yes' && $name == "") {
			$error_on_adding .= esc_js($_POST['alo_em_error_name_empty']) . ".<br />";
		}

		//edit : added all this foreach
		if ( $alo_em_cf ) {
			foreach( $alo_em_cf as $key => $value ){
				$fields[$key] 	= stripslashes(trim($_POST['alo_em_'.$key]));
				if ( empty( $fields[$key] ) && $value['input_mandatory'] ) {
					$error_on_adding .= esc_js($_POST['alo_em_error_'.$key.'_empty']) . ".<br />";
				} else if ( !empty($value['input_validation']) && function_exists($value['input_validation']) && call_user_func($value['input_validation'], $fields[$key])==false ) {
					$error_on_adding .= esc_js($_POST['alo_em_error_'.$key.'_incorrect']) . ".<br />";
				}
			}
		}
		if ($error_on_adding == "") { // if no error
			// try to add new subscriber (and send mail if necessary) and return TRUE if success
			$activated = ( get_option('alo_em_no_activation_mail') != "yes" ) ? 0 : 1;
			$fields['email'] = stripslashes($email); //edit : added all this line
			$fields['name'] = stripslashes($name); //edit : added all this line
			$try_to_add = alo_em_add_subscriber( $fields, $activated, $lang ); //edit : orig : $try_to_add = alo_em_add_subscriber( $email, $name, $activated, $lang );
			switch ($try_to_add) {
				case "OK":
					$just_added = true;
					$subscriber = alo_em_get_subscriber ( $email );
					do_action ( 'alo_easymail_new_subscriber_added', $subscriber, false );
					break;
				case "NO-ALREADYADDED":
					$error_on_adding = esc_js($_POST['alo_em_error_email_added']). ".<br />";
					break;
				case "NO-ALREADYACTIVATED":
					$error_on_adding = esc_js($_POST['alo_em_error_email_activated']). ".<br />";
					break;
				default: // false
					$error_on_adding = esc_js($_POST['alo_em_error_on_sending']) . ".<br />";
			}

			// if requested, add to lists
			if ( !empty($_POST['alo_em_form_lists']) ) {
				$lists = false;
				$lists = explode ( ",", trim ( $_POST['alo_em_form_lists'], "," ) );
				$subscriber = alo_em_is_subscriber ( $email );
				$mailinglists = alo_em_get_mailinglists( 'public' );
				if ( is_array($lists) ) {
					foreach ( $lists as $k => $list ) {
						if ( array_key_exists( $list, $mailinglists ) )	alo_em_add_subscriber_to_list ( $subscriber, $list );
					}
				}
			}
		}
		if ($just_added == true) {
			$output = esc_js($_POST['alo_em_txt_ok']);
			$classfeedback = apply_filters ( 'alo_easymail_widget_ok_class', 'alo_easymail_widget_ok' );  // Hook
		} else {
			$output = $error_on_adding;
			$classfeedback = apply_filters ( 'alo_easymail_widget_error_class', 'alo_easymail_widget_error' );  // Hook
		}

		// Compose JavaScript for return
		$feedback = "";

		// clean inputs before print
		// if just added, clean inputs, otherwise only sanitize them
		$alo_em_opt_name = ( $just_added ) ? '' : esc_js(sanitize_text_field($_POST['alo_em_opt_name']));
		$alo_em_opt_email = ( $just_added ) ? '' : esc_js(sanitize_text_field($_POST['alo_em_opt_email']));

		if( get_option('alo_em_hide_name_input') != 'yes' ) {
			$feedback .= "document.alo_easymail_widget_form.alo_em_opt_name.value ='".$alo_em_opt_name."';";
		}
		$feedback .= "document.alo_easymail_widget_form.alo_em_opt_email.value ='".$alo_em_opt_email."';";
		if ( $alo_em_cf ) {
			foreach( $alo_em_cf as $key => $value ){
				${'alo_em_'.$key} = ( $just_added ) ? '' : esc_js(sanitize_text_field($_POST['alo_em_'.$key]));
				$feedback .= "document.alo_easymail_widget_form.alo_em_".$key.".value ='". ${'alo_em_'.$key} ."';";
			}
		}

		$feedback .= "document.alo_easymail_widget_form.submit.disabled = false;";
		$feedback .= "document.alo_easymail_widget_form.submit.value = '". esc_js($_POST['alo_em_txt_subscribe']). "';";
		$feedback .= "document.getElementById('alo_easymail_widget_feedback').innerHTML = '$output';";
		$feedback .= "document.getElementById('alo_easymail_widget_feedback').className = '$classfeedback';";
		$feedback .= "document.getElementById('alo_em_widget_loading').style.display = 'none';";
		// END!
		die($feedback);
	}
}


/**
 * Apply filters when newsletter is read on blog
 */
function alo_em_filter_content_in_site ( $content ) {
	global $post;
	if ( !is_admin() && isset($post) && $post->post_type == 'newsletter' ) {
		$recipient = (object) array( "name" => __( "Subscriber", "alo-easymail" ), "firstname" => __( "Subscriber", "alo-easymail" ) );
		$content = apply_filters( 'alo_easymail_newsletter_content', $content, $post, $recipient, true );
	}
	return $content;
}
add_filter ( 'the_content',  'alo_em_filter_content_in_site' );


/* EOF */