<?php if ( !defined('ABSPATH') ) die(); // If this file is called directly, abort.

/**
 * Users related functions: on registration, on profile edit...
 *
 * @package WordPress
 * @subpackage ALO EasyMail plugin
 */



/**
 * On User Profile
 */
function alo_em_user_profile_optin ( $user ) {

	// get the current setting
	//if (ALO_easymail_get_optin($user->ID)=='yes'){    // deleted ALO
	if (alo_em_is_subscriber($user->user_email)){       // added ALO
		$optin_selected = " selected='selected' ";
		$optout_selected = '';
	}
	else{
		$optin_selected = '';
		$optout_selected = " selected='selected' ";
	}

	$html = "<h3>". __("Newsletter", "alo-easymail") ."</h3>\n";
	$html .= "<table class='form-table'>\n";
	$html .= "  <tr>\n";
	$optin_txt = ( alo_em_translate_option ( alo_em_get_language (), 'alo_em_custom_optin_msg', false) !="") ? alo_em_translate_option ( alo_em_get_language (), 'alo_em_custom_optin_msg', false) : __("Yes, I would like to receive the Newsletter", "alo-easymail");
	$html .= "    <th><label for='alo_em_option'>". $optin_txt ."</label></th>\n";
	$html .= "    <td>\n";
	$html .= "		<select name='alo_easymail_option' id='alo_easymail_option'>\n";
	$html .= "        <option value='yes' $optin_selected>". __("Yes", "alo-easymail")."</option>\n";
	$html .= "        <option value='no' $optout_selected>". __("No", "alo-easymail")."</option>\n";
	$html .= "      </select>\n";
	$html .= "    </td>\n";
	$html .= "  </tr>\n";
	$html .= "</table>\n";

	// add mailing lists html table
	$html .= alo_em_html_mailinglists_table_to_edit ( $user->user_email, "form-table" );


	//edit : added all the next if

	$alo_em_cf = alo_easymail_get_custom_fields();

	if(  $alo_em_cf ) :

		$html .= "<h3>". __("Newsletter fields", "alo-easymail") ."</h3>\n";
		$html .= "<table class='form-table'>\n";

		$subscriber = alo_em_get_subscriber ( $user->user_email );

		foreach( $alo_em_cf as $key => $value ){

			$html .= "  <tr>\n";
			$field_id = "alo_em_".$key; // edit-by-alo

			$html .= "    <th><label for='".$field_id."'>". __( $value['humans_name'], "alo-easymail") ."</label></th>\n"; // edit-by-alo:
			$html .= "    <td>\n";
			// edit-by-alo: added next $input block

			$prev = isset($subscriber->$key) ? format_to_edit( $subscriber->$key ) : '';
			$html .= alo_easymail_custom_field_html ( $key, $value, $field_id, $prev, true );

			$html .= "    </td>\n";
			$html .= "  </tr>\n";

		}
		$html .= "</table>\n";
	endif;

	echo $html;
}
add_action( 'show_user_profile', 'alo_em_user_profile_optin' );
add_action( 'edit_user_profile', 'alo_em_user_profile_optin' );


function alo_em_save_profile_optin($user_id) {

	if ( !current_user_can( 'edit_user', $user_id ) )
		return false;

	$user_info = get_userdata( $user_id );
	$user_email = $user_info->user_email;

	if (isset($_POST['alo_easymail_option'])) {
		if ( $_POST['alo_easymail_option'] == "yes") {

			// if changed name and lastname
			if ( isset( $_POST[ 'first_name' ] ) && $user_info->first_name != $_POST[ 'first_name' ] ) {
				$user_first_name = stripslashes( trim( $_POST[ 'first_name' ] ) );
			} else {
				$user_first_name = $user_info->first_name;
			}
			if ( isset( $_POST[ 'last_name' ] ) && $user_info->last_name != $_POST[ 'last_name' ] ) {
				$user_last_name = stripslashes( trim( $_POST[ 'last_name' ] ) );
			} else {
				$user_last_name = $user_info->last_name;
			}
			$fullname = $user_first_name." ".$user_last_name;

			// if changed email
			if ( isset( $_POST[ 'email' ] ) && is_email( $_POST[ 'email' ] ) && $user_email != $_POST[ 'email' ] ) {
				$user_email = stripslashes( trim( $_POST[ 'email' ] ) );
			}

			$fields = array();

			//edit : added all this foreach

			$alo_em_cf = alo_easymail_get_custom_fields();
			if ($alo_em_cf) {
				foreach( $alo_em_cf as $key => $value ){
					//check if custom field have been filled and match its validation rule
					if ( !empty( $_POST[ "alo_em_". $key] ) && ( !empty($value['input_validation']) && function_exists($value['input_validation']) && call_user_func($value['input_validation'], $_POST[ "alo_em_". $key]) ) ) {
						switch ( $value['input_type'] )	{
							case "checkbox":
								$fields[$key] = 1;
								break;

							default:
								$fields[$key] = sanitize_text_field( trim ( $_POST[ "alo_em_". $key] ) );
						}
					} else {
						// if custom field is empty and it's not mandatory
						if ( !$value['input_mandatory'] ) {
							switch ( $value['input_type'] )	{
								case "checkbox":
									$fields[$key] = 0;
									break;

								default:
									$fields[$key] = false;
							}
						}

					}
				}
			}

			// Is already subscriber? update or insert
			$todo_update = alo_em_is_subscriber( $user_info->user_email ) > 0 ? true: false;

			if ( $todo_update ) {
				//alo_em_update_subscriber_by_email ( $user_info->user_email, $user_email, $fullname, 1, alo_em_get_language(true) );

				//$fields['old_email'] = $user_info->user_email; //edit : added all this line
				$fields['email'] = $user_email; //edit : added all this line
				$fields['name'] = $fullname; //edit : added all this line

				if ( alo_em_update_subscriber_by_email ( $user_info->user_email, $fields, 1, alo_em_get_language(true) ) ) { //edit : orig : if ( alo_em_update_subscriber_by_email ( $user_info->user_email, $user_email, $fullname, 1, alo_em_get_language(true) ) ) {
					$subscriber = alo_em_get_subscriber ( $user_email );
					alo_em_update_subscriber_last_act( $user_email );
					do_action ( 'alo_easymail_subscriber_updated', $subscriber, $user_info->user_email );
				}
			} else {
				$fields['email'] = $user_email; //edit : added all this line
				$fields['name'] = $fullname; //edit : added all this line
				alo_em_add_subscriber( $fields, 1, alo_em_get_language(true) );//edit : orig: alo_em_add_subscriber( $user_email, $fullname, 1, alo_em_get_language(true) );
				do_action ( 'alo_easymail_new_subscriber_added', alo_em_is_subscriber( $user_email ), $user_id );
			}

			// if subscribing, save also lists
			$mailinglists = alo_em_get_mailinglists( 'public' );
			if ($mailinglists) {
				$subscriber_id = alo_em_is_subscriber( $user_email );
				foreach ( $mailinglists as $mailinglist => $val) {
					if ( isset ($_POST['alo_em_profile_lists']) && is_array ($_POST['alo_em_profile_lists']) && in_array ( $mailinglist, $_POST['alo_em_profile_lists'] ) ) {
						alo_em_add_subscriber_to_list ( $subscriber_id, $mailinglist );	  // add to list
					} else {
						alo_em_delete_subscriber_from_list ( $subscriber_id, $mailinglist ); // remove from list
					}
				}
			}
		} else {
			alo_em_delete_subscriber_by_id( alo_em_is_subscriber($user_email) );
			alo_em_add_email_in_unsubscribed ( $user_email );
		}
	}
}
add_action( 'personal_options_update', 'alo_em_save_profile_optin' );
add_action( 'edit_user_profile_update', 'alo_em_save_profile_optin' );


/**
 * Show validation errors in Profile/eEdit user screen.
 *
 * @param $errors
 * @param $update
 * @param $user
 */
function alo_em_add_profile_field_errors ( $errors, $update, $user ) {

	if ( !$update ) return;

	$alo_em_cf = alo_easymail_get_custom_fields();
	if ($alo_em_cf) {
		foreach( $alo_em_cf as $key => $value ){
			//check if custom field have been filled and match its validation rule
			if ( !empty( $_POST[ "alo_em_". $key] ) ) {

				if ( !empty($value['input_validation']) && function_exists($value['input_validation']) && call_user_func($value['input_validation'], $_POST[ "alo_em_". $key]) == false ) {
					$errors->add($key.'_error',
						alo_em___( sprintf(__("The %s field is not correct", "alo-easymail"),
								'<strong>'.__($value['humans_name'],"alo-easymail").'</strong>' .
								' (&quot;<em>'. esc_html(sanitize_text_field($_POST[ "alo_em_". $key])) .'</em>&quot;)'
							)
						)
					);
				}

			} else {
				// if custom field is empty and it's not mandatory
				if ( $value['input_mandatory'] == true ) {
					$errors->add($key.'_error',
						alo_em___( sprintf(__("The %s field is empty", "alo-easymail"), '<strong>'.__($value['humans_name'],"alo-easymail").'</strong>') )
					);
				}

			}
		}
	}
}
add_filter('user_profile_update_errors', 'alo_em_add_profile_field_errors', 10, 3);


/**
 * Show the optin/optout on Registration Form
 */
function alo_em_show_registration_optin () {
	$optin_txt = ( alo_em_translate_option ( alo_em_get_language (), 'alo_em_custom_optin_msg', false) !="") ? alo_em_translate_option ( alo_em_get_language (), 'alo_em_custom_optin_msg', false) : __("Yes, I would like to receive the Newsletter", "alo-easymail");
	echo '<p class="alo_easymail_reg_optin"><input type="checkbox" id="alo_em_opt" name="alo_em_opt" value="yes" class="input" checked="checked" style="width:auto" /> ';
	echo '<label for="alo_em_opt" >' . $optin_txt .'</label></p>';

	$mailinglists = alo_em_get_mailinglists( 'public' );
	if ( $mailinglists ) {
		$lists_msg 	= ( alo_em_translate_option ( alo_em_get_language (), 'alo_em_custom_lists_msg',false) !="") ? alo_em_translate_option ( alo_em_get_language (), 'alo_em_custom_lists_msg',false) :  __("You can also sign up for specific lists", "alo-easymail");
		echo "<p class='alo_easymail_reg_list_msg'>". $lists_msg .":</p>\n";
		foreach ( $mailinglists as $list => $val ) {
			echo "<p class='alo_easymail_reg_list'><input type='checkbox' name='alo_em_register_lists[]' id='alo_em_register_list_$list' value='$list' /> <label for='alo_em_register_list_$list'>" . alo_em_translate_multilangs_array ( alo_em_get_language(), $val['name'], true ) ."</label></p>\n";
		}
	}

	echo '<input type="hidden" id="alo_em_lang" name="alo_em_lang" value="' . esc_attr(alo_em_get_language()).'" /> ';
}
add_action('register_form','alo_em_show_registration_optin');


/**
 * Save the optin/optout on Registration Form
 */
function alo_em_save_registration_optin ( $user_id, $password="", $meta=array() )  {
	$user = get_userdata($user_id);
	if (!empty($user->first_name) && !empty($user->last_name)) {
		$name = $user->first_name.' '.$user->last_name;
	} else {
		$name = $user->display_name;
	}
	if ( isset ($_POST['alo_em_opt']) && $_POST['alo_em_opt'] == "yes" ) {
		$lang = ( isset($_POST['alo_em_lang']) && in_array ( $_POST['alo_em_lang'], alo_em_get_all_languages( false )) ) ? $_POST['alo_em_lang'] : "" ;
		$fields['email'] = $user->user_email; //edit : added all this line

		$fields['name'] = $name; //edit : added all this line

		//alo_em_add_subscriber( $fields, 1, $lang ); //edit : orig : alo_em_add_subscriber( $user->user_email, $name , 1, $lang );
		if ( alo_em_add_subscriber( $fields, 1, $lang ) == "OK" ) {
			do_action ( 'alo_easymail_new_subscriber_added', alo_em_get_subscriber( $user->user_email ), $user_id );
		}

		// if subscribing, save also lists
		$mailinglists = alo_em_get_mailinglists( 'public' );
		if ($mailinglists) {
			$subscriber_id = alo_em_is_subscriber( $user->user_email );
			foreach ( $mailinglists as $mailinglist => $val) {
				if ( isset ($_POST['alo_em_register_lists']) && is_array ($_POST['alo_em_register_lists']) && in_array ( $mailinglist, $_POST['alo_em_register_lists'] ) ) {
					alo_em_add_subscriber_to_list ( $subscriber_id, $mailinglist );	  // add to list
				}
			}
		}
	}

}
add_action( 'user_register', 'alo_em_save_registration_optin' );


/**
 * When users are deleted, maybe delete also their newsletter subscriptions.
 *
 * @param $user_id
 */
function alo_em_on_delete_user( $user_id ) {
	if ( get_option( 'alo_em_unsubscribe_when_delete_user' ) == 'yes' ) {
		$user_obj = get_userdata( $user_id );
		$email = $user_obj->user_email;

		if (  $subscriber_id = alo_em_is_subscriber( $email ) ) {
			alo_em_delete_subscriber_by_id( $subscriber_id );
		}
	}
}
add_action( 'delete_user', 'alo_em_on_delete_user' );

/* EOF */