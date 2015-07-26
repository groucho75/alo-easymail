<?php if ( !defined('ABSPATH') ) die(); // If this file is called directly, abort.

/**
 * Functions about mailing lists
 *
 * @package WordPress
 * @subpackage ALO EasyMail plugin
 */



/**
 * Get the mailing lists (as array)
 * @types  str		list types requested (a string with comma: eg. 'hidden,admin,public')
 */
function alo_em_get_mailinglists ( $types = false ) {
	$get = get_option('alo_em_mailinglists');
	if ( $types == false ) {
		$types = array ( 'hidden', 'admin', 'public' ); // default types
	} else {
		$types = explode (",", $types);
	}
	if ( empty($get) ) {
		return false;
	} else {
		$mailinglists = maybe_unserialize($get);
		$mailinglists = alo_em_msort ($mailinglists,'order', 'ASC');//($mailinglists,'order', false);
		foreach ( $mailinglists as $list => $val) { // don't return unrequested types
			if ( !in_array( $val['available'], $types ) ) unset ($mailinglists[$list]);
		}
		return (array)$mailinglists;
	}
}


/**
 * Save the mailing lists
 * @lists  array
 */
function alo_em_save_mailinglists ( $lists ) {
	if ( !is_array ($lists) ) return false;
	$arraylists = $lists; // maybe_serialize( $lists );
	update_option ( 'alo_em_mailinglists', $arraylists );
	return true;
}


/**
 * Add a mailing list subscription to a subscriber (and save in db the new list)
 * @subscriber
 * @list
 */
function alo_em_add_subscriber_to_list ( $subscriber, $list ) {
	global $wpdb;
	settype($list, 'integer');
	$user_lists = alo_em_get_user_mailinglists ( $subscriber );
	if ( $user_lists && in_array($list, $user_lists) ) return; // if already, exit
	$user_lists[] = $list; // add the list
	asort ( $user_lists ); // order id from min to max, 1->9
	$updated_lists = implode ( "|", $user_lists );
	$updated_lists = "|".$updated_lists."|";
	return $wpdb->update( "{$wpdb->prefix}easymail_subscribers", array ( 'lists' => $updated_lists ), array ( 'ID' => $subscriber ) );
}


/**
 * Delete subscriber from mailing list
 * @subscriber
 * @list
 */
function alo_em_delete_subscriber_from_list ( $subscriber, $list ) {
	global $wpdb;
	settype($list, 'integer');
	return $wpdb->query( "UPDATE {$wpdb->prefix}easymail_subscribers SET lists = REPLACE(lists, '|".$list."|', '|') WHERE ID=" . $subscriber );
}


/**
 * Delete ALL subscribers from mailing list(s)
 * @lists	array of lists ID
 */
function alo_em_delete_all_subscribers_from_lists ( $lists ) {
	global $wpdb;
	if ( !is_array($lists) ) $lists = array ( $lists );
	foreach ( $lists as $list ) {
		$wpdb->query( "UPDATE {$wpdb->prefix}easymail_subscribers SET lists = REPLACE(lists, '|".$list."|', '|')" );
	}
	return true;
}


/**
 * Get the user mailing lists
 * @array_lists		array of lists ID
 */
function alo_em_get_user_mailinglists ( $subscr_id ) {
	global $wpdb;
	$lists = $wpdb->get_var ( $wpdb->prepare( "SELECT lists FROM {$wpdb->prefix}easymail_subscribers WHERE ID = %d", $subscr_id ) );
	if ( $lists	) {
		$array_lists = explode ( "|", trim ($lists, "|" ) );
		if ( is_array($array_lists) && $array_lists[0] != false  ) {
			asort ( $array_lists ); // order id from min to max, 1->9
			return (array)$array_lists;
		} else {
			return false;
		}
	} else {
		return false;
	}
}


/**
 * Creates a html table with checkbox lists to edit own subscription
 * @user_email		str		subscriber email
 * @cssclass		str		the class css for the html table
 */
function alo_em_html_mailinglists_table_to_edit ( $user_email, $cssclass="" ) {
	$html = "";
	$lists_msg 	= ( alo_em_translate_option ( alo_em_get_language (), 'alo_em_custom_lists_msg',false) !="") ? alo_em_translate_option ( alo_em_get_language (), 'alo_em_custom_lists_msg',false) :  __("You can also sign up for specific lists", "alo-easymail");
	$mailinglists = alo_em_get_mailinglists( 'public' );
	if ( $mailinglists ) {
		$subscriber_id = alo_em_is_subscriber( $user_email );
		$user_lists = alo_em_get_user_mailinglists ( $subscriber_id );
		$html .= "<table ". (($cssclass!="")? " class='$cssclass' " : "") ."><tbody>\n";
		$html .= "<tr><th ". (($cssclass=="")? " style='width:50%' ":"") .">". $lists_msg	.":</th>\n";
		$html .= "<td>\n";
		foreach ( $mailinglists as $list => $val ) {
			$checked = ( $user_lists && in_array ( $list, $user_lists )) ? "checked='checked'" : "";
			$html .= "<input type='checkbox' name='alo_em_profile_lists[]' id='alo_em_profile_list_$list' value='$list' $checked /><label for='alo_em_profile_list_$list' value='$list'>" . alo_em_translate_multilangs_array ( alo_em_get_language(), $val['name'], true ) ."</label><br />\n"; //edit : added the "label" element for better accessibility
		}
		$html .= "</td></tr>\n";
		$html .= "</tbody></table>\n";
	}
	return $html;
}



/* EOF */