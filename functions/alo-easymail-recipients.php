<?php if ( !defined('ABSPATH') ) die(); // If this file is called directly, abort.

/**
 * Functions about recipients queries.
 *
 * @package WordPress
 * @subpackage ALO EasyMail plugin
 */



/**
 * Get Newsletter Recipients from post meta
 */
function alo_em_get_recipients_from_meta ( $post_id ) {
	$recipients = get_post_meta ( $post_id, "_easymail_recipients" );
	return ( !empty( $recipients[0] ) ) ? $recipients[0] : false;
}


/**
 * Get the Recipients from meta
 * @return	arr		email as values
 */
function alo_em_get_all_recipients_from_meta ( $newsletter ) {
	$recipients = alo_em_get_recipients_from_meta ( $newsletter );
	$registered = $subscribers = $subscribers_from_list = false;

	$count = array();

	if ( isset( $recipients['registered'] ) )  {
		$registered = alo_em_get_recipients_registered();
		if ( $registered ) : foreach ( $registered as $reg ) :
			if ( !in_array( $reg->user_email, $count ) )  array_push( $count, $reg->user_email );
		endforeach; endif;
	} else if ( isset( $recipients['role'] ) ) {
		global $wp_roles;
		$registered = alo_em_get_recipients_registered();
		if ( $registered ) : foreach ( $registered as $reg ) :
			$compare_roles = array_intersect( $reg->roles, $recipients['role'] );
			if ( empty($compare_roles) ) continue;
			if ( !in_array( $reg->user_email, $count ) )  array_push( $count, $reg->user_email );
		endforeach; endif;
	}

	if ( isset( $recipients['subscribers'] ) && isset( $recipients['lang'] ) )  {
		$subscribers = alo_em_get_recipients_subscribers();
		if ( $subscribers ) : foreach ( $subscribers as $sub ) :
			$sub_lang = ( !empty( $sub->lang ) ) ? $sub->lang : "UNKNOWN";
			//if ( !in_array( $sub_lang, $recipients['lang'] ) ) continue;
			if ( $sub_lang == "UNKNOWN" || !in_array( $sub_lang, alo_em_get_all_languages() ) ) { // unknown or not installed lang
				if ( !in_array( "UNKNOWN", $recipients['lang'] ) ) continue;
			} else { // installed lang
				if ( !in_array( $sub_lang, $recipients['lang'] ) ) continue;
			}
			if ( !in_array( $sub->email, $count ) )  array_push( $count, $sub->email );
		endforeach; endif;
	} else if ( isset( $recipients['list'] ) && isset( $recipients['lang'] ) ) {
		$subscribers_from_list = alo_em_get_recipients_subscribers( $recipients['list'] );
		if ( $subscribers_from_list ) : foreach ( $subscribers_from_list as $sub ) :
			$sub_lang = ( !empty( $sub->lang ) ) ? $sub->lang : "UNKNOWN";
			//if ( !in_array( $sub_lang, $recipients['lang'] ) ) continue;
			if ( $sub_lang == "UNKNOWN" || !in_array( $sub_lang, alo_em_get_all_languages() ) ) { // unknown or not installed lang
				if ( !in_array( "UNKNOWN", $recipients['lang'] ) ) continue;
			} else { // installed lang
				if ( !in_array( $sub_lang, $recipients['lang'] ) ) continue;
			}
			if ( !in_array( $sub->email, $count ) )  array_push( $count, $sub->email );
		endforeach; endif;
	}
	return $count;
}


/**
 * Count the Recipients from meta
 * @param	int
 * @param	bol		if the cached value, or count the real number
 * @return	int
 */
function alo_em_count_recipients_from_meta ( $newsletter, $not_cached=false ) {
	$recipients = alo_em_get_recipients_from_meta ( $newsletter );
	// If exists, use cached value, otherwise count
	if ( isset( $recipients['total'] ) && ! $not_cached ) {
		$total = $recipients['total'];
		unset( $recipients['estimated_total'] );
		update_post_meta ( $newsletter, "_easymail_recipients", $recipients );
	} else if ( !empty( $recipients['estimated_total'] ) && ! $not_cached ) {
		$total = $recipients['estimated_total'];
	} else {
		$total = count( alo_em_get_all_recipients_from_meta ( $newsletter ) );
		$recipients['estimated_total'] = $total;
		update_post_meta ( $newsletter, "_easymail_recipients", $recipients );
	}
	return $total;
}


/**
 * A short summary of Recipients
 * @param	arr
 */
function alo_em_recipients_short_summary ( $recipients ) {
	$output = "<ul>";
	if ( isset( $recipients['registered'] ) ) {
		$output .= "<li>" . __( 'All registered users', "alo-easymail") . "</li>";
	} else {
		if ( isset( $recipients['role'] ) ) {
			$role_str = implode( ", ", $recipients['role'] );
			$output .= "<li title=\"". esc_attr( $role_str )."\">" . count( $recipients['role'] ) ." ". __( 'User Roles' ) . alo_em_help_tooltip( $role_str ) . "</li>";
		}
	}

	if ( isset( $recipients['subscribers'] ) ) {
		$output .= "<li>" . __( 'All subscribers', "alo-easymail") . "</li>";
	} else {
		if ( isset( $recipients['list'] ) ) {
			$mailinglists = alo_em_get_mailinglists ( 'admin,public' );
			if ( $mailinglists ) {
				$list_str = "";
				foreach ( $mailinglists as $list => $val ) {
					if ( in_array ( $list, $recipients['list'] ) ) $list_str .= alo_em_translate_multilangs_array ( alo_em_get_language(), $val['name'], true ) .", ";
				}
				$list_str = trim ( $list_str, ", " );
				$output .= "<li title=\"". esc_attr( $list_str ) ."\" >" . count( $recipients['list'] ) ." ". __( 'Mailing Lists', "alo-easymail") . alo_em_help_tooltip( $list_str ) . "</li>";
			}
		}
	}
	if ( isset( $recipients['subscribers'] ) || isset( $recipients['list'] ) ) {
		if ( isset( $recipients['lang'] ) ) {
			$langs_str = str_replace( "UNKNOWN", __("Not specified / others", "alo-easymail"), implode( ", ", $recipients['lang'] ) );
			$output .= "<li title=\"". esc_attr( $langs_str )."\">" . count( $recipients['lang'] ) ." ". __( 'Languages', "alo-easymail") . alo_em_help_tooltip( $langs_str ) . "</li>";
		}
	}
	$output .= "</ul>";
	return $output;
}


/**
 * Create the Recipients cache for Newsletter
 * @return	arr	 	array value are 0
 */
function alo_em_create_cache_recipients ( $newsletter ) {
	$recipients = alo_em_get_recipients_from_meta ( $newsletter );
	//echo "<pre>". print_r ( $recipients, true ). "</pre>";
	$cache = array();
	if ( isset( $recipients['registered'] ) && $recipients['registered'] == 1 ) {
		$cache['registered'] = "0";
	} else {
		if ( isset( $recipients['role'] ) ) {
			$cache['role'] = array();
			foreach ( $recipients['role'] as $index => $id ) {
				$cache['role'][$id] = "0";
			}
		}
	}

	if ( isset( $recipients['subscribers'] ) && $recipients['subscribers'] == 1 ) {
		$cache['subscribers'] = "0";
	} else {
		if ( isset( $recipients['list'] ) ) {
			$cache['list'] = array();
			foreach ( $recipients['list'] as $index => $id ) {
				$cache['list'][$id] = "0";
			}
		}
	}
	if ( isset( $recipients['lang'] ) ) $cache['lang'] = $recipients['lang'];

	// Prepare total of recipients on cache creation
	$cache['total'] = 0;

	delete_post_meta ( $newsletter, "_easymail_cache_recipients" );
	add_post_meta ( $newsletter, "_easymail_cache_recipients", $cache );
}


/**
 * Get the Recipients cache for Newsletter
 * @return	arr
 */
function alo_em_get_cache_recipients ( $newsletter ) {
	$recipients = get_post_meta ( $newsletter, "_easymail_cache_recipients" );
	return ( !empty( $recipients[0] ) ) ? $recipients[0] : false;
}


/**
 * Save the Recipients cache for Newsletter
 */
function alo_em_save_cache_recipients ( $newsletter, $recipients ) {
	delete_post_meta ( $newsletter, "_easymail_cache_recipients" );
	add_post_meta ( $newsletter, "_easymail_cache_recipients", $recipients );
}


/**
 * Delete the Recipients cache for Newsletter
 */
function alo_em_delete_cache_recipients ( $newsletter ) {
	delete_post_meta ( $newsletter, "_easymail_cache_recipients" );
}


/**
 * Add Recipients from cache into db records.
 * @param	int		newsletter id
 * @param	int		limit: how many
 * @param	bol		if send now or add to queue
 */
function alo_em_add_recipients_from_cache_to_db ( $newsletter, $limit=10, $sendnow=false ) {
	$cache = alo_em_get_cache_recipients( $newsletter );
	$debug_response = FALSE; // set to TRUE to debug ajax responses
	if ( $debug_response ) echo "CACHE BEFORE\n<pre>". print_r ( $cache, true ). "</pre>"; // DEBUG
	if ( $cache && is_array( $cache ) ) {
		//$recipients = array();

		$start = 0;
		$now_doing = false;
		$finished = false;

		// Get the 1st required group
		if ( isset( $cache['registered'] ) )  {
			$recipients = alo_em_get_recipients_registered();
			$now_doing = "registered";
			$start = $cache['registered'];

		} else if ( isset( $cache['role'] ) ) {
			foreach ( $cache['role'] as $id => $role_start ) {

				$recipients = alo_em_get_recipients_registered();
				// Filter users according on role
				if ( is_array($recipients) ) : foreach ( $recipients as $rec_id => $rec ) :
					if ( !in_array( $id, $rec->roles ) ) unset( $recipients[$rec_id] );
				endforeach; endif;

				// If no recipients for required role, unset it and try the next
				if ( is_array($recipients) && count($recipients) > 0 ) {
					$now_doing = "role";
					$now_doing_role = $id;
					$start = $role_start;
					break;
				} else {
					$now_doing = false;
					unset( $cache['role'][$id] );
					$finished = 'role';
					$now_doing_role = $id;
				}
			}

			//if ( empty($cache['role']) ) unset( $cache['role'] );
		}

		if ( isset( $cache['subscribers'] ) && !$now_doing )  {
			$recipients = alo_em_get_recipients_subscribers();
			$now_doing = "subscribers";
			$start = $cache['subscribers'];

		} else if ( isset( $cache['list'] ) && !$now_doing ) {
			foreach ( $cache['list'] as $id => $list_start ) {
				$recipients = alo_em_get_recipients_subscribers( $id );

				$now_doing = "list";
				$now_doing_list = $id;
				$start = $list_start;
				$finished = false;

				if ( is_array($recipients) && !empty($recipients[0]) ) {
					break; // ok
				} else {
					$now_doing = false;
					$finished = 'list';
				}
			}
		}

		// If not registered round, check languages
		if ( $now_doing && $now_doing != "registered" && $now_doing != "role" && isset ( $cache['lang'] ) && is_array( $cache['lang'] ) ) {
			foreach ( $recipients as $index => $rec ) {
				$rec_lang = ( !empty( $rec->lang ) ) ? $rec->lang : "UNKNOWN"; // if subscriber has not specified lang
				if ( $rec_lang == "UNKNOWN" || !in_array( $rec_lang, alo_em_get_all_languages() ) ) { // unknown or not installed lang
					if ( !in_array( "UNKNOWN", $cache['lang'] ) ) unset ( $recipients[$index] );
				} else { // installed lang
					if ( !in_array( $rec_lang, $cache['lang'] ) ) unset ( $recipients[$index] );
				}

			}
		}

		//if ( $debug_response ) echo "RECIPIENTS\n<pre>". print_r ( $recipients, true ). "</pre>"; // DEBUG

		if ( $now_doing && $recipients ) {

			$added = 0; // to count how many added in this round

			end( $recipients );
			$end = key ( $recipients ); // the last index in recipients
			reset( $recipients );

			for ( $i = $start; $i <= $end; $i ++ ) {

				if ( $i == $end ) $finished = $now_doing; // if end reached, group finished

				if ( !isset( $recipients[$i] ) ) {
					// if ( $i == count( $recipients )-1 ) break; else continue;
					continue;
				}

				$email = ( $now_doing == "registered" || $now_doing =="role" ) ? $recipients[$i]->user_email : $recipients[$i]->email;
				if ( alo_em_get_recipient_by_email_and_newsletter( $email, $newsletter ) ) continue; // if already added, skip

				$user_id = ( email_exists( $email ) ) ? email_exists( $email ) : false;
				$args = array(
					'newsletter' => $newsletter,
					'email' => $email,
					'user_id' => $user_id
				);
				$new_id = alo_em_add_recipient( $args, true );
				if ( $new_id ) {
					$added ++;

					// Add it to total
					$cache['total'] ++;

					if ( $sendnow ) { // send only one mail (and wait the the request sleep) and exit

						$recipient = alo_em_get_recipient_by_id( $new_id );
						alo_em_send_newsletter_to ( $recipient );

						if ( alo_em_get_sleepvalue() > 0 ) usleep ( alo_em_get_sleepvalue() * 1000 );
						break;
					}

					if ( $added >= $limit ) { // if limit reached, exit
						break;
					}
				}

				// Update the offset for next
				switch ( $now_doing ) {
					case "registered":		$cache['registered'] 	= $i; 			break;
					case "role":  			$cache['role'][$now_doing_role] = $i; 	break;
					case "subscribers":		$cache['subscribers'] 	= $i; 			break;
					case "list":  			$cache['list'][$now_doing_list] = $i; 	break;
				}

				if ( $debug_response ) echo "NOW $i\n<pre>". print_r ( $cache, true ). "</pre>"; // DEBUG
			}
		}

		// If group finished, delete it from cache
		if ( $finished ) : switch ( $finished ) :
			case "registered":		unset ( $cache['registered'] ); 			break;
			case "role":  			unset ( $cache['role'][$now_doing_role] );
				//if ( ! reset($cache['role']) ) unset( $cache['role'] );
				break;
			case "subscribers":		unset ( $cache['subscribers'] ); 			break;
			case "list":  			unset ( $cache['list'][$now_doing_list] );
				//if ( ! reset( $cache['list'] ) ) unset( $cache['list'] );
				break;
		endswitch; endif;

		if ( $debug_response ) echo "NEW CACHE\n<pre>". print_r ( $cache, true ). "</pre>\nNOW DOING:".$now_doing; // DEBUG

		// If completed ALL groups (of total sent = supposed total), delete cache and mark newsletter as "sendable"
		if ( !isset( $cache['registered'] ) && !isset( $cache['subscribers'] ) && empty( $cache['list'] ) && empty( $cache['role'] )
			|| ( $cache['total'] == alo_em_count_recipients_from_meta($newsletter,true) ) ) {

			if ( count( alo_em_get_recipients_in_queue( 1, $newsletter ) ) == 0 && $sendnow ) {
				alo_em_set_newsletter_as_completed ( $newsletter );
			} else {
				alo_em_edit_newsletter_status ( $newsletter, 'sendable' );
			}

			// Store total number in meta from cache
			$meta_recipients = alo_em_get_recipients_from_meta ( $newsletter );
			$meta_recipients['total'] = $cache['total'];
			delete_post_meta ( $newsletter, "_easymail_recipients" );
			add_post_meta ( $newsletter, "_easymail_recipients", $meta_recipients );

			alo_em_delete_cache_recipients( $newsletter );
		} else {
			alo_em_save_cache_recipients ( $newsletter, $cache );
		}
	}
}


/**
 * Get single Recipient by email and newsletter
 */
function alo_em_get_recipient_by_email_and_newsletter ( $email, $newsletter ) {
	global $wpdb;

	$alo_em_cf = alo_easymail_get_custom_fields();
	$select_cf = '';
	if ( $alo_em_cf ) {
		foreach( $alo_em_cf as $key => $value ){
			$select_cf .= ', s.' . $key;
		}
	}

	$rec = $wpdb->get_row( $wpdb->prepare( "SELECT r.*, s.lang, s.unikey, s.name, s.ID AS subscriber {$select_cf}
											FROM {$wpdb->prefix}easymail_recipients AS r
    										LEFT JOIN {$wpdb->prefix}easymail_subscribers AS s ON r.email = s.email
    										WHERE r.email=%s AND r.newsletter=%d", $email, $newsletter ) );
	if ( $rec ) {
		if ( $user_id = $rec->user_id ) {
			if ( get_user_meta( $user_id, 'first_name', true ) != "" ) $rec->firstname = ucfirst( get_user_meta( $user_id, 'first_name', true ) );
		} else {
			$rec->firstname = $rec->name;
			$rec->user_id = false;
		}
	}
	return $rec;
}


/**
 * Get single Recipient by ID
 */
function alo_em_get_recipient_by_id ( $recipient ) {
	global $wpdb;
	settype($recipient, 'integer');

	$alo_em_cf = alo_easymail_get_custom_fields();
	$select_cf = '';
	if ( $alo_em_cf ) {
		foreach( $alo_em_cf as $key => $value ){
			$select_cf .= ', s.' . $key;
		}
	}

	$rec = $wpdb->get_row( $wpdb->prepare( "SELECT r.*, s.lang, s.unikey, s.name, s.ID AS subscriber {$select_cf}
											FROM {$wpdb->prefix}easymail_recipients AS r
    										LEFT JOIN {$wpdb->prefix}easymail_subscribers AS s ON r.email = s.email
    										WHERE r.ID=%d", $recipient ) );
	if ( $rec && isset( $rec->email ) ) {
		if ( $user_id = $rec->user_id ) {
			if ( get_user_meta( $user_id, 'first_name', true ) != "" ) $rec->firstname = ucfirst( get_user_meta( $user_id, 'first_name', true ) );
		} else {
			$rec->firstname = $rec->name;
			$rec->user_id = false;
		}
	}
	return $rec;
}


/**
 * Add Recipient by email and newsletter
 *@param 	arr			recipient info: email. newsletter...
 *@param 	bol			add only if subscriber is active (skip if registered user)
 *@return 	int|bol		id added of false
 */
function alo_em_add_recipient ( $args, $only_if_active=true ) {
	global $wpdb;
	$defaults = array(
		'email' => false,
		'newsletter' => false,
		'result' => '0',
		'user_id' => ''
	);
	$fields = wp_parse_args( $args, $defaults );
	$added = false;
	if ( $fields['email'] && $fields['newsletter'] ) {
		if ( !$only_if_active || !alo_em_is_subscriber( $fields['email'] ) || ( $only_if_active && alo_em_is_subscriber( $fields['email'] ) && alo_em_check_subscriber_state( $fields['email'] ) == 1 ) ) {
			$wpdb->insert ( "{$wpdb->prefix}easymail_recipients", $fields );
			$added = $wpdb->insert_id;
		}
	}
	return $added;
}


/**
 * Delete Newsletter Recipients
 */
function alo_em_delete_newsletter_recipients ( $newsletter ) {
	global $wpdb;
	$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}easymail_recipients WHERE newsletter=%d", $newsletter ) );
	$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}easymail_stats WHERE newsletter=%d", $newsletter ) );
}


/**
 * Get Newsletter Recipients from db
 * @param	bol 	if only recipients that have NOT yet received the newsletter
 */
function alo_em_get_newsletter_recipients ( $newsletter, $only_to_send=false, $offset=0, $limit=0 ) {
	global $wpdb;
	$where_to_send = ( $only_to_send ) ? "AND r.result = 0" : "";
	$limit = ( $offset || $limit ) ? " LIMIT $offset, $limit " : "";
	return $wpdb->get_results( $wpdb->prepare( "SELECT r.*, s.lang, s.unikey, s.name FROM {$wpdb->prefix}easymail_recipients AS r LEFT JOIN {$wpdb->prefix}easymail_subscribers AS s ON r.email = s.email WHERE newsletter=%d ". $where_to_send ." ORDER BY r.email ASC". $limit, $newsletter ) );
}


/**
 * Count the Newsletter Recipients from db
 * @return	int
 */
function alo_em_count_newsletter_recipients ( $newsletter, $only_to_send=false ) {
	return count( alo_em_get_newsletter_recipients ( $newsletter, $only_to_send, false, false ) );
}


/**
 * Count the Newsletter Recipients from db already sent
 * @return	int
 */
function alo_em_count_newsletter_recipients_already_sent ( $newsletter ) {
	global $wpdb;
	$sent = $wpdb->get_results( $wpdb->prepare( "SELECT ID FROM {$wpdb->prefix}easymail_recipients WHERE newsletter=%d AND result != 0 ", $newsletter ) );
	return count( $sent );
}


/**
 * Count the Newsletter Recipients from db already sent with Success
 * @return	int
 */
function alo_em_count_newsletter_recipients_already_sent_with_success ( $newsletter ) {
	global $wpdb;
	$sent = $wpdb->get_results( $wpdb->prepare( "SELECT ID FROM {$wpdb->prefix}easymail_recipients WHERE newsletter=%d AND result = '1' ", $newsletter ) );
	return count( $sent );
}


/**
 * Count the Newsletter Recipients from db already sent with Error
 * @return	int
 */
function alo_em_count_newsletter_recipients_already_sent_with_error ( $newsletter ) {
	global $wpdb;
	$sent = $wpdb->get_results( $wpdb->prepare( "SELECT ID FROM {$wpdb->prefix}easymail_recipients WHERE newsletter=%d AND result < 0", $newsletter ) );
	return count( $sent );
}


/**
 * Count the Newsletter Recipients from db already sent
 * @return	int
 */
function alo_em_newsletter_recipients_percentuage_already_sent ( $newsletter ) {
	$sent = alo_em_count_newsletter_recipients_already_sent ( $newsletter );
	$total = alo_em_count_newsletter_recipients ( $newsletter );
	$perc = ( $sent > 0 && $total > 0 ) ? number_format ( ( $sent * 100 / $total ), 1 ) : 0;
	return $perc;
}


/**
 * Get all registered users of the blog
 * return arr of object with info as in table column
 */
function alo_em_get_recipients_registered () {
	global $wpdb, $blog_id;
	$get_users = get_users();

	$default_role = get_option('default_role', 'subscriber');
	for ( $i = 0; $i < count ($get_users); $i ++ ) {
		$get_users[$i]->lang = $wpdb->get_var ( $wpdb->prepare( "SELECT lang FROM {$wpdb->prefix}easymail_subscribers WHERE email = %s", $get_users[$i]->user_email ) );
		$get_users[$i]->UID = $get_users[$i]->ID;

		// Role
		$user = new WP_User( $get_users[$i]->ID );
		if ( !empty( $user->roles ) && is_array( $user->roles ) ) {
			$get_users[$i]->roles = $user->roles;
		} else {
			$get_users[$i]->roles = array( $default_role );
		}
	}
	//echo "<pre>";print_r($get_users); echo "</pre>";
	return $get_users;
}


/**
 * Get ALL subscribers OR only by SELECTED lists
 * @lists	array	only by selected lists
 * return object with info as in table column
 */
function alo_em_get_recipients_subscribers ( $lists=false ) {
	global $wpdb;
	$where_lists = "";
	if ( $lists && !is_array($lists) ) $lists = array ( $lists );
	if ( $lists ) {
		$where_lists .= " AND (";
		foreach ( $lists as $list ) {
			$where_lists .= "lists LIKE '%|".$list."|%' OR ";
		}
		$where_lists = substr( $where_lists , 0, -3); // cut last "OR"
		$where_lists .= ")";
	}
	return $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}easymail_subscribers WHERE active='1' $where_lists" );
}


/* EOF */