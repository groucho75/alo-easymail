<?php

/*************************************************************************
 * MISC UTILITIES FUNCTIONS
 *************************************************************************/ 

/**
 * Add help image with tooltip
 */
function alo_em_help_tooltip ( $text ) {
	$text = str_replace( array("'", '"'), "", $text );
	$html = "<img src='".ALO_EM_PLUGIN_URL."/images/12-help.png' title='". esc_attr($text) ."' style='cursor:help;vertical-align:middle;margin-left:3px' alt='(?)' />";
	return $html;
}


/**
 * Compatibility with older WP version
 * get_usermeta (deprecated from 3.0)
 */
if ( !function_exists('get_user_meta') ) {
	function get_user_meta ( $user, $key, $single=false ) {
		return get_usermeta ( $user, $key );
	}
}



/**
 * Sort a multidimensional array on a array kay (found on http://php.net/manual/en/function.sort.php)
 * @array		array	the array
 * @key			str		the field to use as key to sort
 * @order		str		sort method: "ASC", "DESC"
 */

function alo_em_msort  ($array, $key, $order = "ASC") {
	$tmp = array();
	foreach($array as $akey => $array2)  {
		$tmp[$akey] = $array2[$key];
	}
    if ($order == "DESC") {
    	arsort($tmp , SORT_NUMERIC );
    } else {
    	asort($tmp , SORT_NUMERIC );
    }
	$tmp2 = array();       
 	foreach($tmp as $key => $value) {
		$tmp2[$key] = $array[$key];
	}       
	return $tmp2; 
}
        

/**
 * Remove HTML tags, including invisible text such as style and
 * script code, and embedded objects.  Add line breaks around
 * block-level tags to prevent word joining after tag removal.
 * (based on http://nadeausoftware.com/articles/2007/09/php_tip_how_strip_html_tags_web_page )
 */
function alo_em_html2plain ( $text ) {
	// transform in utf-8 if not yet
	//$text = utf8_encode($text);
	if ( function_exists( 'mb_detect_encoding' ) && mb_detect_encoding($text, "UTF-8") != "UTF-8" ) $text = utf8_encode($text);
    $text = preg_replace(
        array(
          // Remove invisible content
            '@<head[^>]*?>.*?</head>@siu',
            '@<style[^>]*?>.*?</style>@siu',
            '@<script[^>]*?.*?</script>@siu',
            '@<object[^>]*?.*?</object>@siu',
            '@<embed[^>]*?.*?</embed>@siu',
            '@<applet[^>]*?.*?</applet>@siu',
            '@<noframes[^>]*?.*?</noframes>@siu',
            '@<noscript[^>]*?.*?</noscript>@siu',
            '@<noembed[^>]*?.*?</noembed>@siu',
          // Add line breaks before and after blocks
            '@</?((address)|(blockquote)|(center)|(del))@iu',
            '@</?((div)|(h[1-9])|(ins)|(isindex)|(p)|(pre))@iu',
            '@</?((dir)|(dl)|(dt)|(dd)|(li)|(menu)|(ol)|(ul))@iu',
            '@</?((table)|(th)|(td)|(caption))@iu',
            '@</?((form)|(button)|(fieldset)|(legend)|(input))@iu',
            '@</?((label)|(select)|(optgroup)|(option)|(textarea))@iu',
            '@</?((frameset)|(frame)|(iframe))@iu',
        ),
        array(
            ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ',
            "\n\$0", "\n\$0", "\n\$0", "\n\$0", "\n\$0", "\n\$0",
            "\n\$0" 
        ),
        $text );
        
	// Next lines added by sanderbontje, patched by Thomas Heinen

	// Try to preserve links before stripping all tags
	// by rewriting '<a id="123" href="url" rel="bookmark" target="_blank" style="mystyle">link</a>' to 'link (url)'
	//$text = preg_replace('/<a(.*)href=\"([^"]+)\"(.*)>(.*)<\/a>/', " ()", $text );
	$text = preg_replace('/<a(.*)href=[\'|"]([^"]+)[\'|"](.*)>(.*)<\/a>/', "$0 ($2)", $text );

	// from <br> to \n - do this after rewriting links, so that links with <br> in them are still recognized by the regex
	$text = preg_replace('/<br(\s+)?\/?>/i', "\n", $text );

	$text = strip_tags( $text );

	// remove excessive spaces and tabs
	$text = preg_replace("/[ \t]+/", " ", $text);

	// replace quotes by their plain-text variants
	$text = preg_replace("/“/", "\"", $text);
	$text = preg_replace("/”/", "\"", $text);

	// replace dashes by =
	$text = preg_replace("/–/", "=", $text);
	$text = preg_replace("/—/", "=", $text);

	// strip blank lines (blank, with tabs or spaces)
	$text = preg_replace("/[\r\n]+[\s\t]*[\r\n]+/", "\n\n", $text );

	return $text;
}


/**
 * Show credit and banners
 *@param	bol		only donate (false) or all banners (true)
 */
function alo_em_show_credit_banners ( $all=false ) { 
	if ( get_option('alo_em_show_credit_banners') == "no" ) return; ?>
	<style type="text/css">
		.alo-banner { border:1px solid #ccc; background-color: #efefef; width:300px; height: 130px; padding: 6px; margin-right: 15px; float: left }
		.alo-banner p { font-size: 0.9em; margin: 0.5em 0 }
	</style>
	<ul style="width:100%; margin-top:20px">
		<li class="alo-banner">
			<p><em><?php _e("Please visit the plugin site for more info and feedback", "alo-easymail") ?>.
			<?php if ( function_exists('add_contextual_help') ) : ?>
			<?php _e("For more links you can use the Help button", "alo-easymail") ?>.
			<?php endif; ?>
			<br /><a href='http://www.eventualo.net/blog/wp-alo-easymail-newsletter/' target='_blank'>www.eventualo.net</a>
			</em></p>
			
			<p><em><?php _e("If you use this plugin consider the idea of donating and supporting its development", "alo-easymail") ?>:</em></p><form action='https://www.paypal.com/cgi-bin/webscr' method='post' style='display:inline'>
			<input name='cmd' value='_s-xclick' type='hidden'><input name='lc' value='EN' type='hidden'><input name='hosted_button_id' value='9E6BPXEZVQYHA' type='hidden'>
			<input src='https://www.paypal.com/en_US/i/btn/btn_donate_SM.gif' name='submit' alt='Donate via PayPal' title='Donate via PayPal' border='0' type='image'>
			<img src='https://www.paypal.com/it_IT/i/scr/pixel.gif' border='0' height='1' width='1'><br>	</form>
		</li>
		<?php if ( $all ) : ?>		
		<li class="alo-banner">
			<a href="http://themefuse.com/amember/aff/go?r=6700&i=44" title="Original WP Themes by ThemeFuse"> <img border="0" src="http://themefuse.com/banners/125x125.jpg" alt="Original WP by ThemeFuse" width="125" height="125" style="float:right;margin-left:10px" /></a>		
			<p><em>If you are interested in buying an original wp theme I would recommend <a href="http://themefuse.com/amember/aff/go?r=6700&i=44" title="Original WP Themes by ThemeFuse">ThemeFuse</a>.</em></p>
		</li>			
		<?php endif; ?>
	</ul>
<?php
}


/**
 * Return a Rate about the 1st Number on 2nd Number
 *
 * return 	int		%
 */
function alo_em_rate_on_total ( $number, $total, $float=1 ) {
	return ( $number > 0 ) ? number_format ( ( $number * 100 / $total ), $float ) : 0;
}


/*************************************************************************
 * NEWSLETTER FUNCTIONS
 *************************************************************************/ 


/**
 * User can edit Newsletter
 */
function alo_em_user_can_edit_newsletter ( $newsletter, $user_id=false ) {
	global $user_ID;
	if ( empty( $user_id ) ) $user_id = $user_ID;
	//return get_edit_post_link( $newsletter );
	//return user_can( $user_id, 'edit_post', $newsletter ); // TODO user_can c'è solo dalla 3.1
	//return current_user_can( 'edit_post', $newsletter );	
	$user = new WP_User( $user_id );
	return $user->has_cap( 'edit_newsletter', $newsletter );
}


/**
 * Get Newsletter by id
 */
function alo_em_get_newsletter ( $newsletter ) {
	return get_post ( $newsletter );
}


/**
 * Get Newsletter Status from post meta
 */
function alo_em_get_newsletter_status ( $newsletter ) {
	return get_post_meta( $newsletter, '_easymail_status', true );
}


/**
 * Update the Newsletter Status
 *@param	int	
 *@param	str
 */
function alo_em_edit_newsletter_status ( $newsletter, $status ) {
	delete_post_meta ( $newsletter, "_easymail_status" );
	add_post_meta ( $newsletter, "_easymail_status", $status );
}


/**
 * Reset/delete the Newsletter Status
 */
function alo_em_delete_newsletter_status ( $newsletter ) {
	delete_post_meta ( $newsletter, "_easymail_status" );
}


/**
 * Check if Newsletter Report of Recipients was archived
 */
function alo_em_is_newsletter_recipients_archived ( $newsletter ) {
	if( alo_em_get_newsletter_status ( $newsletter ) != "sent" ) return false;
	return ( $archive = get_post_meta ( $newsletter, "_easymail_archived_recipients" ) ) ? $archive : false;
}



/*************************************************************************
 * RECIPIENTS FUNCTIONS
 *************************************************************************/ 


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
		return $recipients['total'];
	} else {
		return count( alo_em_get_all_recipients_from_meta ( $newsletter ) );
	}
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
			}
			$output .= "<li title=\"". esc_attr( $list_str ) ."\" >" . count( $recipients['list'] ) ." ". __( 'Mailing Lists', "alo-easymail") . alo_em_help_tooltip( $list_str ) . "</li>";
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
 * Add Recipients from db into cache
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
					/*$now_doing = "list";
					$now_doing_list = $id;
					$start = $list_start;*/
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
					/*$now_doing = "list";
					$now_doing_list = $id;
					$start = $list_start;*/
					break; // ok
				} else {
					//unset( $cache['list'][$id] );
					//$now_doing = false;
					//if ( !empty($cache['list']) ) {
						$now_doing = false;
						//unset($now_doing_list);
						//unset($start);
						$finished = 'list';
					//}					
				}
			}
			//if ( empty($cache['list']) ) unset( $cache['list'] );
		}
		
		// If not registered round, check languages
		if ( $now_doing && $now_doing != "registered" && $now_doing != "role" && isset ( $cache['lang'] ) && is_array( $cache['lang'] ) ) {
			foreach ( $recipients as $index => $rec ) {			
				/*
				$search_lang = ( !empty( $rec->lang ) ) ? $rec->lang : "UNKNOWN"; // if subscriber has not specified lang
				if ( !in_array( $search_lang, $cache['lang'] ) ) unset ( $recipients[$index] );
				*/
				
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
						/*
						$recipient = alo_em_get_subscriber( $email );
						$recipient->subscriber = $recipient->ID;
						$recipient->ID = $new_id;
						$recipient->newsletter = $newsletter;
						*/
						//$recipient = (object) array ( 'newsletter' => $newsletter, 'email' => $email, 'ID' => $new_id );
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



/*************************************************************************
 * SUBSCRIPTION FUNCTIONS
 *************************************************************************/ 


/**
 * Count the n° of subscribers
 * return a array: total (active + not active), active, not active
 */
function alo_em_count_subscribers () {
    global $wpdb;
    $search = $wpdb->get_results( "SELECT active, COUNT(active) AS count FROM {$wpdb->prefix}easymail_subscribers GROUP BY active ORDER BY active ASC" );
    $total = $noactive = $active = false;
    if ($search) {
		foreach ($search as $s) {
			switch ($s->active) {
				case 0: 	$noactive = $s->count; break;
				case 1: 	$active = $s->count; break;
			}
		}
		$total = $noactive + $active;
	} 
    return array ( $total, $active, $noactive );
} 


/**
 * Check is there is already a subscriber with that email and return ID subscriber
 */
function alo_em_is_subscriber($email) {
    global $wpdb;
    $is_subscriber = $wpdb->get_var( $wpdb->prepare("SELECT ID FROM {$wpdb->prefix}easymail_subscribers WHERE email='%s' LIMIT 1", $email) );
    return (($is_subscriber)? $is_subscriber : 0); // ID in db tab subscribers
} 


/**
 * Check is there is a subscriber with this ID and return true/false
 */
function alo_em_is_subscriber_by_id ( $id ) {
    global $wpdb;
    $is_subscriber = $wpdb->get_var( $wpdb->prepare("SELECT ID FROM {$wpdb->prefix}easymail_subscribers WHERE ID=%d LIMIT 1", $id) );
    return $is_subscriber;
} 


/**
 * Check the state of a subscriber (active/not-active)
 */
function alo_em_check_subscriber_state($email) {
    global $wpdb;
    $is_activated = $wpdb->get_var( $wpdb->prepare("SELECT active FROM {$wpdb->prefix}easymail_subscribers WHERE email='%s' LIMIT 1", $email) );
    return $is_activated;
} 


/**
 * Modify the state of a subscriber (active/not-active) (BY ADMIN)
 */
function alo_em_edit_subscriber_state_by_id($id, $newstate) {
    global $wpdb;
    $output = $wpdb->update(    "{$wpdb->prefix}easymail_subscribers",
                                array ( 'active' => $newstate ),
                                array ( 'ID' => $id)
                            );
    return $output;
} 


/**
 * Modify the state of a subscriber (active/not-active) (BY SUBSCRIBER)
 */
function alo_em_edit_subscriber_state_by_email($email, $newstate="1", $unikey) {
    global $wpdb;
    $output = $wpdb->update(    "{$wpdb->prefix}easymail_subscribers",
                                array ( 'active' => $newstate ),
                                array ( 'email' => $email, 'unikey' => $unikey )
                            );
    return $output;
} 


/**
 * Add a new subscriber 
 * return bol/str:
 *		false					= generic error
 *		"OK"					= success
 *		"NO-ALREADYACTIVATED"	= not added because: email is already added and activated
 *		"NO-ALREADYADDED"		= not added because: email is already added but not activated; so send activation msg again
 */
function alo_em_add_subscriber( $fields, $newstate=0, $lang="" ) { //edit : orig : function alo_em_add_subscriber($email, $name, $newstate=0, $lang="" ) {
    global $wpdb;
 	$output = true;
 	$fields = array_map( 'strip_tags', $fields );
 	$email = $fields['email'];
 	if ( !is_admin() || ( defined('DOING_AJAX') && DOING_AJAX ) ) $fields['ip_address'] = alo_em_ip_address();
	//foreach( $fields as $key => $value ) { ${$key} = $value; } //edit : added all this line in order to transform the fields array into simple variables
    // if there is NOT a subscriber with this email address: add new subscriber and send activation email
    if (alo_em_is_subscriber($email) == false){
        $unikey = substr(md5(uniqid(rand(), true)), 0,24);    // a personal key to manage the subscription
           
        // try to send activation mail, otherwise will not add subscriber
        if ($newstate == 0) {
        	$lang_actmail = ( !empty( $lang ) ) ? $lang : alo_em_short_langcode ( get_locale() );
           	if ( !alo_em_send_activation_email($fields, $unikey, $lang_actmail) ) $output = false; // DEBUG ON LOCALHOST: comment this line to avoid error on sending mail
        }
        
        if ( $output ) {	
			$wpdb->insert ( "{$wpdb->prefix}easymail_subscribers",
           					array_merge( $fields, array( 'join_date' => get_date_from_gmt( date("Y-m-d H:i:s") ), 'active' => $newstate, 'unikey' => $unikey, 'lists' => "|", 'lang' => $lang, 'last_act' => get_date_from_gmt( date("Y-m-d H:i:s") ) ) ) //edit : orig : array( 'email' => $email, 'name' => $name, 'join_date' => get_date_from_gmt( date("Y-m-d H:i:s") ), 'active' => $newstate, 'unikey' => $unikey, 'lists' => "|", 'lang' => $lang )
			);
        	$output = "OK"; //return true; 
        }
        
    } else {
        // if there is ALREADY a subscriber with this email address, and if is NOT confirmed yet: re-send an activation email
        if ( alo_em_check_subscriber_state($email) == 0) {
            // retrieve existing unique key 
            $exist_unikey = $wpdb->get_var( $wpdb->prepare("SELECT unikey FROM {$wpdb->prefix}easymail_subscribers WHERE ID='%d' LIMIT 1", alo_em_is_subscriber($email) ) );
            
            if ( alo_em_send_activation_email($fields, $exist_unikey, $lang) ) {
                // update join date to today
                $ip_address = alo_em_ip_address();
                $output = $wpdb->update(    "{$wpdb->prefix}easymail_subscribers",
                                            array ( 'join_date' => get_date_from_gmt( date("Y-m-d H:i:s") ), 'lang' => $lang, 'last_act' => get_date_from_gmt( date("Y-m-d H:i:s") ), 'ip_address' => $ip_address ),
                                            array ( 'ID' => alo_em_is_subscriber($email) )
                                        );
             	// tell that there is already added but not active: so it has sent another activation mail.......
                $output = "NO-ALREADYADDED";
            } else {
                $output = false;
                //$output = "NO-ALREADYADDED"; // DEBUG ON LOCALHOST: comment the previous line and uncomment this one to avoid error on sending mail
            }
        } else {
	        // tell that there is already an activated subscriber.....
            $output = "NO-ALREADYACTIVATED"; 
        }
    }
    return $output;
} 


/**
 * Delete a subscriber (BY ADMIN/REGISTERED-USER)
 */
function alo_em_delete_subscriber_by_id($id) {
    global $wpdb;
    $output = $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}easymail_subscribers WHERE ID=%d LIMIT 1", $id ) );
    return $output;
} 



/**
 * Update a subscriber (BY ADMIN/REGISTERED-USER)
 */
function alo_em_update_subscriber_by_email ( $old_email, $fields, $newstate=0, $lang="", $update_lastact=true ) {
    global $wpdb;
	//foreach( $fields as $key => $value ) { ${$key} = $value; } //edit : added all this line in order to transform the fields array into simple variables
	//$old_email = $fields['old_email']; // edit-by-alo: added this line
	//unset( $fields['old_email'] ); //edit : added all this line in order to prevent "update" to break
	$fields['active'] = $newstate; //edit : added all this line
	$fields['lang'] = $lang; //edit : added all this line
	$fields['ip_address'] = alo_em_ip_address();
	if ( $update_lastact ) $fields['last_act'] = get_date_from_gmt( date("Y-m-d H:i:s") );

	// Filter custom fields
	$alo_em_cf = alo_easymail_get_custom_fields();
	if( $alo_em_cf ):
		foreach( $alo_em_cf as $key => $value ){
			switch ( $value['input_type'] )	{
				// particular case: checkbox value not only exist, but value 1
				case "checkbox":
					if ( $fields[$key] == false ) unset( $fields[$key] );
					break;
				default:
			}
		}
	endif;
	
    $output = $wpdb->update(    "{$wpdb->prefix}easymail_subscribers",
                                $fields, //edit : orig : array ( 'email' => $new_email, 'name' => $name, 'active' => $newstate, 'lang' => $lang ),
                                array ( 'email' => $old_email )
                            );         
  	return $output;
} 


/**
 * Delete a subscriber (BY SUBSCRIBER)
 */
function alo_em_delete_subscriber_by_email($email, $unikey) {
    global $wpdb;
    $output = $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}easymail_subscribers WHERE email='%s' AND unikey='%s' LIMIT 1", $email, $unikey ) );
    return $output;
} 


/**
 * Check if can access subscription page (BY SUBSCRIBER)
 */
function alo_em_can_access_subscrpage ($email, $unikey) {
    global $wpdb;
    // check if email and unikey match
    $check = alo_em_check_subscriber_email_and_unikey ( $email, $unikey );
    return $check;
} 


/**
 * Check if subscriber email and unikey match (BY SUBSCRIBER) (check EMAIL<->UNIKEY)
 */
function alo_em_check_subscriber_email_and_unikey ( $email, $unikey ) {
    global $wpdb;
    $check = $wpdb->get_var( $wpdb->prepare("SELECT ID FROM {$wpdb->prefix}easymail_subscribers WHERE email='%s' AND unikey='%s' LIMIT 1", $email, $unikey) );
    return $check;
} 


/**
 * Send email with activation link
 */
function alo_em_send_activation_email( $fields, $unikey, $lang ) { //edit : orig : function alo_em_send_activation_email($email, $name, $unikey, $lang) {
	foreach( $fields as $key => $value ) { ${$key} = $value; } //edit : added all this line in order to transform the fields array into simple variables
	$blogname = html_entity_decode ( wp_kses_decode_entities ( get_option('blogname') ) );
    // Headers
    $mail_sender = ( get_option('alo_em_sender_email') ) ? get_option('alo_em_sender_email') : "noreply@". str_replace("www.","", $_SERVER['HTTP_HOST']);
    $headers =  "";//"MIME-Version: 1.0\n";
    $headers .= "From: ". $blogname ." <".$mail_sender.">\n";
    $headers .= "Content-Type: text/plain; charset=\"". get_bloginfo('charset') . "\"\n";
    
    /*
    // Subject
    // $subject = sprintf(__("Confirm your subscription to %s Newsletter", "alo-easymail"), $blogname );
   	$subject = alo_em_translate_option ( $lang, 'alo_em_txtpre_activationmail_subj', true ); 
   	$subject = str_replace ( "%BLOGNAME%", $blogname, $subject );
    */
       	
    // Main content    
    /*
 	$div_email = explode("@", $email); // for link
    $arr_params = array ('ac' => 'activate', 'em1' => $div_email[0], 'em2' => $div_email[1], 'uk' => $unikey, 'lang' => $lang);
	$sub_link = add_query_arg( $arr_params, get_page_link (get_option('alo_em_subsc_page')) );
	//$sub_link = alo_em_translate_url ( $sub_link, $lang );
    */
    /*   
   	$content = alo_em_translate_option ( $lang, 'alo_em_txtpre_activationmail_mail', true ); 
   	$content = str_replace ( "%BLOGNAME%", $blogname, $content );
   	$content = str_replace ( "%NAME%", $name, $content );
   	$content = str_replace ( "%ACTIVATIONLINK%", $sub_link, $content );
   	*/
   	
   	$content = "lang=$lang&email=$email&name=$name&unikey=$unikey";
   	//$content = "email=$email";
   
    //echo "<br />".$headers."<br />".$subscriber->email."<br />". $subject."<br />".  $content ."<hr />" ; // DEBUG
    $sending = wp_mail( $email, /*$subject*/ "#_EASYMAIL_ACTIVATION_#", $content, $headers);  
    return $sending;
} 


/**
 * Print table with tags summay
 */
function alo_em_newsletter_placeholders() {
	global $wp_version;
	$placeholders = array (
		"easymail_post" => array (
			"title" 		=> __( "Post tags", "alo-easymail" ),
			"tags" 			=> array (
				"[POST-TITLE]" 		=> __("The link to the title of the selected post.", "alo-easymail") ." ". __("This tag works also in the <strong>subject</strong>", "alo-easymail") . ". ". __("The visit to this url will be tracked.", "alo-easymail"),
				"[POST-EXCERPT]" 	=> __("The excerpt (if any) of the post.", "alo-easymail"). ( version_compare ( $wp_version , '3.3', '>=' ) ? " ". __("If it is empty, the beginning of post content will be used.", "alo-easymail") : "" ),
				"[POST-CONTENT]"	=> __("The main content of the post.", "alo-easymail")						
			)
		),
		"easymail_subscriber" => array (
			"title" 		=> __( "Subscriber tags", "alo-easymail" ),
			"tags" 			=> array (
				"[USER-NAME]"		=> __("Name and surname of registered user.", "alo-easymail") . " (". __("For subscribers: the name used for registration", "alo-easymail") ."). ". __("This tag works also in the <strong>subject</strong>", "alo-easymail").".",
				"[USER-FIRST-NAME]"	=> __("First name of registered user.", "alo-easymail") . " (". __("For subscribers: the name used for registration", "alo-easymail") ."). ". __("This tag works also in the <strong>subject</strong>", "alo-easymail").".",
				"[USER-EMAIL]"	=> __("Email address of subscriber", "alo-easymail") . ". "
			)
		)		
	);
	
	return apply_filters ( 'alo_easymail_newsletter_placeholders_table', $placeholders ); 
}


/**
 * Print table with tags summay
 */
function alo_em_tags_table ( $post_id ) { 
	$placeholders = alo_em_newsletter_placeholders();
	
	if ( $placeholders ) :
		foreach ( $placeholders as $type => $placeholder ) : 
			if ( isset( $placeholder['tags'] )) : ?>
		
		<table class="widefat" style="margin-top:10px">
		<thead><tr><th scope="col" style="width:20%"><?php esc_html_e ( $placeholder['title'] ) ?></th>
		<th scope="col"><?php do_action ( 'alo_easymail_newsletter_placeholders_title_'.$type, $post_id ); ?></th></tr>
		</thead>
		<tbody>
		
			<?php if ( !empty( $placeholder['tags'] ) ) : foreach ( $placeholder['tags'] as $tag => $desc ) : ?>
				<tr><td><?php esc_html_e ( $tag ) ?></td><td style='font-size:80%'>
				<span class="description"><?php echo $desc ?></span></td></tr>
			<?php endforeach; endif; // $placeholder['tags'] ?>
			
		</tbody></table>
		<?php 
		endif;
		endforeach; // $placeholders
		
	endif; // if ( $placeholders ) ?>
	
<?php 
}
 

/**
 * Check the state of a subscriber (active/not-active)
 */
function alo_em_update_subscriber_last_act($email) {
    global $wpdb;
    $ip_address = alo_em_ip_address();
	$out = $wpdb->update( 	"{$wpdb->prefix}easymail_subscribers",
								array ( 'last_act' => get_date_from_gmt( date("Y-m-d H:i:s") ), 'ip_address' => $ip_address ),
								array ( 'ID' => alo_em_is_subscriber($email) )
							);
	return $out;		
}


/**
 * Get IP address: useful if you like to filter it
 */
function alo_em_ip_address() {
    $ip_address = preg_replace( '/[^0-9a-fA-F:., ]/', '',$_SERVER['REMOTE_ADDR'] );
	return apply_filters ( 'alo_easymail_ip_address', $ip_address ); 
}


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
		  echo 'alo_em_sack.setVar( "alo_em_'.$key.'", document.getElementById(\'alo_em_'.$key.'\').value );'."\n";
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
//edit : added all this for
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
  alo_em_sack.setVar( "alo_em_opt_name", document.alo_easymail_widget_form.alo_em_opt_name.value );
  alo_em_sack.setVar( "alo_em_opt_email", document.alo_easymail_widget_form.alo_em_opt_email.value );
  <?php
  //edit : added all this foreach
  if( $alo_em_cf ) {
	  foreach( $alo_em_cf as $key => $value ){
		  echo 'alo_em_sack.setVar( "alo_em_'.$key.'", document.getElementById(\'alo_em_'.$key.'\').value );'."\n";
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
    if (isset($_POST['alo_em_opt_name']) && isset($_POST['alo_em_opt_email'])){
        $error_on_adding = "";
        $just_added = false;
        $_POST = array_map( 'strip_tags', $_POST );
		$name 	= trim( $_POST['alo_em_opt_name'] );
		$email	= trim( $_POST['alo_em_opt_email'] );
		$lang = ( isset($_POST['alo_em_lang_code']) && in_array ( $_POST['alo_em_lang_code'], alo_em_get_all_languages( false )) ) ? $_POST['alo_em_lang_code'] : "" ;
        if ( !is_email($email) ) {
            $error_on_adding .= esc_js($_POST['alo_em_error_email_incorrect']). "<br />";
        }
        if ( $name == "") {
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
		
		$feedback .= "document.alo_easymail_widget_form.alo_em_opt_name.value ='".$alo_em_opt_name."';";
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



/*************************************************************************
 * NEWSLETTERS
 *************************************************************************/ 


/**
 * Get the Newsletter(s) using 'get_posts'
 *@param	str		status
 *@param	int		how many newsletter
 */
function alo_em_query_newsletters ( $status="sent", $limit=1 ) {
	global $wpdb, $wp_version;
	$args = array (
		"post_type" 	=> "newsletter", 
		"numberposts" 	=> $limit, 
		"orderby" 		=> "post_date", 
		"order" 		=> "ASC", 
		"post_status" 	=> "publish"
	);
	if ( version_compare ( $wp_version, '3.1', '>=' ) ) {
		$meta_1 = array( 'key' => '_easymail_status', 'value' => $status, 'compare' => '=' );
		$args['meta_query'] = array( $meta_1 );
	} else {
		$args['meta_key'] = '_easymail_status';
		$args['meta_value'] = $status;
		$args['meta_compare'] = '=';
	}	
	$newsletters = get_posts ( $args );
	return $newsletters;
}


/**
 * Count Newsletter(s) by status
 *@param	int		how many newsletter
 */
function alo_em_count_newsletters_by_status ( $status="sent" ) {
	return count( alo_em_query_newsletters ( $status, -1 ) );
}


/**
 * Get the Newsletter(s) on top of queue
 *@param	int		how many newsletter
 */
function alo_em_get_newsletters_in_queue ( $limit=1 ) {
	return alo_em_query_newsletters ( "sendable", $limit );
}


/**
 * Get the Newsletter(s) already sent
 *@param	int		how many newsletter
 */
function alo_em_get_newsletters_sent ( $limit=1 ) {
	return alo_em_query_newsletters ( "sent", $limit );
}



/*************************************************************************
 * BATCH SENDING
 *************************************************************************/ 


/**
 * Get dayrate by costant or option
 */
function alo_em_get_dayrate () {
	return ( defined( 'ALO_EM_DAYRATE' ) ) ? (int)ALO_EM_DAYRATE : (int)get_option('alo_em_dayrate');
}


/**
 * Get batchrate by costant or option
 */
function alo_em_get_batchrate () {
	return ( defined( 'ALO_EM_BATCHRATE' ) ) ? (int)ALO_EM_BATCHRATE : (int)get_option('alo_em_batchrate');
}


/**
 * Get sleepvalue by costant or option
 */
function alo_em_get_sleepvalue () {
	return ( defined( 'ALO_EM_SLEEPVALUE' ) ) ? (int)ALO_EM_SLEEPVALUE : (int)get_option('alo_em_sleepvalue');
}


/**
 * Add a new newsletter to batch sending
 */
 /*
function alo_em_add_new_batch ( $user_ID, $subject, $content, $recipients, $tracking, $tag ) {
	global $wpdb;
	$wpdb->insert(
                "{$wpdb->prefix}easymail_sendings", 
                array( 'start_at' => get_date_from_gmt( date("Y-m-d H:i:s") ), 'last_at' => get_date_from_gmt( date("Y-m-d H:i:s") ), 'user' => $user_ID, 'subject' => $subject, 
                'content' => $content, 'sent' => '0', 'recipients' => '', 'tracking' => $tracking, 'tag' => $tag )
            );
	$newsletter = $wpdb->insert_id;
	if ( $newsletter && is_array( $recipients ) ) alo_em_add_newsletter_recipients( $newsletter, $recipients );
    return $newsletter;
}
*/

/**
 * Add newsletter Recipients
 *
 *@param	int		newsletter id
 *@param	arr		recipients
 */
 /*
function alo_em_add_newsletter_recipients ( $newsletter, $recipients ) {
	global $wpdb;
	foreach ( $recipients as $rec ) {
		$email 		= ( isset( $rec['email'] ) ) ?		$rec['email'] 		: '';
		$lang 		= ( isset( $rec['lang'] ) ) ?		$rec['lang'] 		: '';
		$name 		= ( isset( $rec['name'] ) ) ?		$rec['name'] 		: '';
		$firstname 	= ( isset( $rec['firstname'] ) ) ? 	$rec['firstname'] 	: '';
		$unikey 	= ( isset( $rec['unikey'] ) )	? 	$rec['unikey'] 		: '';
		if ( empty( $email ) ) continue;
		$wpdb->insert(
	            "{$wpdb->prefix}easymail_recipients", 
	            	array( 'newsletter' => $newsletter, 'email' => $email,	'lang' => $lang, 'name' => $name, 'firstname' => $firstname, 'unikey' => $unikey
	             )
	        );  	
	}	
}
*/

/**
 * Get newsletter Recipients
 *
 *@param	int		newsletter id
 *@return	arr		recipients
 */
 /*
function alo_em_get_newsletter_recipients ( $newsletter ) {
	global $wpdb;
	$recipients = $wpdb->get_results( $wpdb->prepare("SELECT * FROM {$wpdb->prefix}easymail_recipients WHERE newsletter = %d ORDER BY email ASC", $newsletter ), ARRAY_A );

	// TODO se vuoto e se esiste la vecchia tabella stas cercare nel campo recipients della newsletter (per compatibilità v.<2 )
	$old_table = $wpdb->prefix . "easymail_trackings";
	if ( empty( $recipients ) && $wpdb->get_var("show tables like '$old_table'") == $old_table ) {
		$rec_field = $wpdb->get_var( $wpdb->prepare("SELECT recipients FROM {$wpdb->prefix}easymail_sendings WHERE ID =%d", $newsletter ) );
		if ( $rec_field ) $recipients = unserialize( $rec_field );
	}
	
	return $recipients;
}
*/

/**
 * Delete a sent newsletter 
 */
 /*
function alo_em_delete_newsletter ( $newsletter ) {
	global $wpdb;
	// delete newsletter
	$delete = $wpdb->query($wpdb->prepare( "DELETE FROM {$wpdb->prefix}easymail_sendings WHERE ID = %d", $newsletter ));
	// delete trackings
	$wpdb->query($wpdb->prepare( "DELETE FROM {$wpdb->prefix}easymail_trackings WHERE newsletter = %d", $newsletter ));
	do_action ( 'alo_easymail_newsletter_deleted', $newsletter );
    return $delete;
}
*/



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
 * Get the first Recipients on sending queue: the oldest in recipients db table 
 * checking the newsletter is not paused (opt. filtered by a newsletter id)
 *
 * @param 	int		limit: how many
 * @param 	int		newsletter id
 * @return	obj		recipients
 */
function alo_em_get_recipients_in_queue ( $limit=false, $newsletter=false ) {
	global $wpdb;
	if ( !$limit ) $limit = alo_em_get_batchrate ();
	$query_limit = ( $limit ) ? " LIMIT ".$limit : "";
	$query_newsletter = ( $newsletter ) ? " AND newsletter =". $newsletter ." " : "";

	$alo_em_cf = alo_easymail_get_custom_fields();
	$select_cf = '';
	if ( $alo_em_cf ) {
		foreach( $alo_em_cf as $key => $value ){
			$select_cf .= ', s.' . $key;
		}
	}
		
	$recipients = $wpdb->get_results( 
		"SELECT r.*, s.lang, s.unikey, s.name, s.ID AS subscriber {$select_cf} FROM {$wpdb->prefix}easymail_recipients AS r 
		LEFT JOIN {$wpdb->prefix}easymail_subscribers AS s ON r.email = s.email 
		INNER JOIN {$wpdb->postmeta} AS pm ON pm.post_id = r.newsletter 
		INNER JOIN {$wpdb->posts} AS p ON p.ID = r.newsletter 
		WHERE pm.meta_key = '_easymail_status' AND pm.meta_value = 'sendable' AND r.result = 0 AND p.post_status = 'publish' ". $query_newsletter ." 
		ORDER BY r.ID ASC" . $query_limit );
	if ( $recipients ) : foreach ( $recipients as $index => $recipient ) :
			if ( $user_id = $recipient->user_id ) {
				if ( get_user_meta( $user_id, 'first_name', true ) != "" ) {
					$recipient->firstname = ucfirst( get_user_meta( $user_id, 'first_name', true ) );
				} else {
					$recipient->firstname = $recipient->name;
				}
		 	} else {
		 		$recipient->firstname = $recipient->name;
		 	}
		 	
		 	// You can filter the $recipient object and its properties; return false to unset it.
		 	// Note: if you unset a recipient here, you probably have to mark it as not-sent in your function or somewhere
		 	// to avoid to get the same recipient again and again when you call 'alo_em_get_recipients_in_queue'
		 	$recipients[ $index ] = apply_filters( 'alo_easymail_recipient_in_queue', $recipient ); // Hook
		 	if ( ! $recipients[ $index ] ) unset( $recipients[ $index ] );
		 	
	endforeach; endif;
	return $recipients;
}


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
	
	/* // maybe useless in v.2...
	if ( get_option('alo_em_filter_br') != "no" ) {
		$content = wpautop( $content, 1 );
		$content = str_replace("\n", "<br />\r\n", $content);
		$content = str_replace( array("<br /><t", "<br/><t", "<br><t"), "<t", $content);
		$content = str_replace( array("<br /></t", "<br/></t", "<br></t"), "</t", $content);
	}
	*/
	
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
    switch ( $send_mode ) {
    	case "to_author":
	    		$author = get_userdata( $newsletter->post_author );
    			$debug_subject = "( DEBUG - TO: ". $recipient->email ." ) " . $subject;
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
				$mail_engine = wp_mail( $recipient->email, $subject, $content, $headers, $attachs );       					        					
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


/**
 * When the newsletter has been sent, mark it as completed
 */
function alo_em_set_newsletter_as_completed ( $newsletter ) {
	global $wpdb;
	alo_em_edit_newsletter_status ( $newsletter, 'sent' );
	add_post_meta ( $newsletter, "_easymail_completed", current_time( 'mysql', 0 ) );
	$newsletter_obj = alo_em_get_newsletter ( $newsletter );
	do_action ( 'alo_easymail_newsletter_delivered', $newsletter_obj );
}


/**
 * Called by wp_cron: send the newsletter to a fraction of recipients every X minutes
 */
function alo_em_batch_sending () {
	global $wpdb;
	
	// search the interval between now and previous sending (or from default cron interval)
	$prev_time = ( get_option ( 'alo_em_last_cron' ) ) ? strtotime( get_option ( 'alo_em_last_cron' ) ) : current_time( 'timestamp', 0 ) - ALO_EM_INTERVAL_MIN * 60;
	$diff_time = current_time( 'timestamp', 0 ) - $prev_time; 
	
	// so... how much recipients for this interval? // (86400 = seconds in a day)
	$day_rate = alo_em_get_dayrate();
	$tot_recs = max ( floor( ( $day_rate * $diff_time / 86400 ) ) , 1 ); 
	// not over the limit
	$limit_recs = min ( $tot_recs, alo_em_get_batchrate () );
			
	// the recipients to whom send
	//$recipients = alo_em_get_recipients_in_queue ( $limit_recs );
	
	// update 'last cron time' option
	update_option ( 'alo_em_last_cron', current_time( 'mysql', 0 ) );
	
	// if no recipients exit!
	//if ( !$recipients ) return;
	
	//foreach ( $recipients as $recipient ) {
	for ( $i = 1; $i <= $limit_recs; $i ++ ) {

		// Get the recipient
		$recipients = alo_em_get_recipients_in_queue ( 1 );

		// if no recipients exit the batch loop
		if ( empty($recipients[0]) ) return;

		$recipient = $recipients[0];
				
		if ( alo_em_get_newsletter_status ( $recipient->newsletter ) != "sendable" ) continue;

		ob_start();
		
		// Prepare and send the newsletter to this user!
		alo_em_send_newsletter_to ( $recipient );
	
		// if no more recipient of this newsletter, it has been sent
		if ( count( alo_em_get_recipients_in_queue( 1, $recipient->newsletter ) ) == 0 ) {
			alo_em_set_newsletter_as_completed ( $recipient->newsletter );
		}
		ob_end_flush();
		if ( (int)get_option('alo_em_sleepvalue') > 0 ) usleep ( (int)get_option('alo_em_sleepvalue') * 1000 );
	}		
}



/**
 * alo newsletter custom email hooks
 */
function alo_em_zirkuss_custom_easymail_placeholders( $placeholders ) {
	$warning_readonline = ( get_option('alo_em_publish_newsletters') == "no" ) ? " <strong>".__( 'This tag now does not work because the online publication of newsletters is disabled', 'alo-easymail' ).": ". __( 'you can set it up in settings', 'alo-easymail' )."</strong>" : "";
	
	$placeholders['easymail_subscriber']['tags']['[USER-UNSUBSCRIBE]'] = __ ( 'Text and URL to unsubscribe.', 'alo-easymail' ) . " (". __( 'You can customise this text in settings', 'alo-easymail' ) .".)";
	$placeholders['easymail_subscriber']['tags']['[USER-UNSUBSCRIBE-URL]'] = __ ( 'URL to unsubscribe.', 'alo-easymail' );
	
	$placeholders['easymail_newsletter']['title'] = __( "Newsletter tags", "alo-easymail" );
	$placeholders['easymail_newsletter']['tags']['[READ-ONLINE]'] = __ ( 'Text and URL to the online version.', 'alo-easymail' ) . " (". __( 'You can customise this text in settings', 'alo-easymail' ) ."). " . __('The visit to this url will be tracked.', 'alo-easymail'). " ".$warning_readonline;
	$placeholders['easymail_newsletter']['tags']['[READ-ONLINE-URL]'] = __ ( 'URL to the online version.', 'alo-easymail' ). " ". __('The visit to this url will be tracked.', 'alo-easymail'). " ". $warning_readonline;
	$placeholders['easymail_newsletter']['tags']['[TITLE]'] = __ ( 'Title of the newsletter.', 'alo-easymail' );
	$placeholders['easymail_newsletter']['tags']['[DATE]'] = __ ( 'Date of the newsletter.', 'alo-easymail' );
	if ( current_theme_supports( 'post-thumbnails' ) ) $placeholders['easymail_newsletter']['tags']['[THUMB]'] = __ ( 'Post Thumbnail of newsletter', 'alo-easymail' );
	$placeholders['easymail_newsletter']['tags']['[GALLERY]'] = __ ( 'Image gallery of newsletter', 'alo-easymail' );

	$placeholders['easymail_site']['title'] = __( "Site tags", "alo-easymail" );
	$placeholders['easymail_site']['tags']['[SITE-LINK]'] = __("The link to the site", "alo-easymail") .". ".__('The visit to this url will be tracked.', 'alo-easymail');
	$placeholders['easymail_site']['tags']['[SITE-URL]'] = __ ( 'URL to the site.', 'alo-easymail' ).". ".__('The visit to this url will be tracked.', 'alo-easymail');
	$placeholders['easymail_site']['tags']['[SITE-NAME]'] = __('Site Title');
	$placeholders['easymail_site']['tags']['[SITE-DESCRIPTION]'] = __('Tagline');

	if ( current_theme_supports( 'post-thumbnails' ) ) $placeholders['easymail_post']['tags']['[POST-THUMB]'] = __("Post Thumbnail", "alo-easymail");
	$placeholders['easymail_post']['tags']['[POST-GALLERY]'] = __("The image gallery of the post", "alo-easymail")	;
					
	return $placeholders;
}
add_filter ( 'alo_easymail_newsletter_placeholders_table', 'alo_em_zirkuss_custom_easymail_placeholders', 5 );


/**
 * alo newsletter content
 */
function alo_em_zirkuss_newsletter_content( $content, $newsletter, $recipient, $stop_recursive_the_content = false ) 
{  
	if ( !is_object( $recipient ) ) $recipient = new stdClass();
	if ( empty( $recipient->lang ) ) $recipient->lang = alo_em_short_langcode ( get_locale() );

	// title
	$subject = stripslashes ( alo_em_translate_text ( $recipient->lang, $newsletter->post_title, $newsletter->ID, 'post_title' ) );
	$subject = apply_filters( 'alo_easymail_newsletter_title', $subject, $newsletter, $recipient );
		
	// use the email theme only when emailing the
	// newsletter. otherwise use the default
	// wordpress theme to display the newsletter.
	if ( isset( $recipient->ID ) ) 
	{	
		// If newsletter publication online available, create the message to read the newsletter online
		if ( get_option('alo_em_publish_newsletters') == "no" ) {
			$viewonline_url = $viewonline_msg = $trackable_viewonline_url = "";
		} else {
			
			$viewonline_msg = alo_em_translate_option ( $recipient->lang, 'alo_em_custom_viewonline_msg', true );

			$viewonline_url = alo_em_translate_url ( $recipient->newsletter /*get_permalink( $recipient->newsletter )*/, $recipient->lang );
			$trackable_viewonline_url = alo_em_make_url_trackable ( $recipient, $viewonline_url );
			
		   	if( empty( $viewonline_msg ) )
		   	{
				$viewonline_msg = __('To read the newsletter online you can visit this link:', 'alo-easymail') . ' %NEWSLETTERLINK%';
			}
		
			$viewonline_msg = str_replace( '%NEWSLETTERLINK%', ' <a href="'.$trackable_viewonline_url/*$viewonline_url*/.'">'. $subject /*$viewonline_url*/ .'</a>', $viewonline_msg );
			$viewonline_msg = str_replace( '%NEWSLETTERURL%', $trackable_viewonline_url /*$viewonline_url*/, $viewonline_msg );
		}
		
		$unsubfooter = $uns_link = $tracking_view = ""; // default empty
		
		if ( isset( $recipient->unikey ) ) { // if subscriber
			$uns_vars = $recipient->subscriber . '|' . $recipient->unikey;
			$uns_vars = urlencode( base64_encode( $uns_vars ) );
			$uns_link = add_query_arg( 'emunsub', $uns_vars, alo_em_translate_home_url ( $recipient->lang ) /*trailingslashit( get_home_url() )*/ );
			//$uns_link = alo_em_translate_url ( $uns_link, $recipient->lang );
		
			$unsubfooter = alo_em_translate_option ( $recipient->lang, 'alo_em_custom_unsub_footer', true );
		
		   	if ( empty( $unsubfooter ) )
		   	{
				$unsubfooter = __('You have received this message because you subscribed to our newsletter. If you want to unsubscribe: ', 'alo-easymail').' %UNSUBSCRIBELINK%';
			}
		
			$unsubfooter = str_replace ( '%UNSUBSCRIBELINK%', ' <a href="'.$uns_link.'">'. $uns_link/*__('visit this link', 'alo-easymail')*/ .'</a>', $unsubfooter );
			$unsubfooter = str_replace ( '%UNSUBSCRIBEURL%', $uns_link, $unsubfooter );

			// Tracking code
			$track_vars = $recipient->ID . '|' . $recipient->unikey;
		    $track_vars = urlencode( base64_encode( $track_vars ) );    
			$tracking_view = '<img src="'. ALO_EM_PLUGIN_URL .'/tr.php?v='. $track_vars .'" width="1" height="1" border="0" alt="" >';
		}
		
		// Content default if not theme found
		$html = $content;
		
		// Get the theme file
		$default_theme = get_option('alo_em_use_themes');
		if ( $default_theme != 'no' ) {
			if ( $default_theme == "yes" ) { // Free choice
				$theme = get_post_meta ( $newsletter->ID, '_easymail_theme', true );
			} else { // Force theme by option
				$theme = $default_theme;
			}
			if ( $theme != "" && array_key_exists( $theme, alo_easymail_get_all_themes() ) ) {
				$themes = alo_easymail_get_all_themes();
				$theme_path = ( isset( $themes[$theme] ) && file_exists( $themes[$theme] ) ) ? $themes[$theme] : false;
				if ( $theme_path ) {
					//$html = file_get_contents( $theme_path ); // replaced by eqhes, for php themes
					ob_start();
					require( $theme_path );
					$html = ob_get_clean();
					
					$html = alo_em_translate_text ( $recipient->lang, $html ); // translate the text ih html theme
					$html = str_replace('[CONTENT]', $content, $html);
					$info = pathinfo( $theme );
					$theme_dir =  basename( $theme, '.' . $info['extension'] );
					//$html = str_replace( $theme_dir, alo_easymail_get_themes_url().$theme_dir, $html );
					$html = preg_replace( '/ src\=[\'|"]'. $theme_dir.'(.+?)[\'|"]/', ' src="'. alo_easymail_get_themes_url().$theme_dir. '$1"', $html ); // <img src="..." >
					$html = preg_replace( '/url(.+?)[\s|\'|"]'. $theme_dir.'(.+?)[\s|\'|"]/', "url('". alo_easymail_get_themes_url() .$theme_dir. "$2'", $html ); // in style: url("...")
					$html = preg_replace( '/ background\=[\'|"]'. $theme_dir.'(.+?)[\'|"]/', ' background="'. alo_easymail_get_themes_url().$theme_dir. '$1"', $html ); // <table background="..." >
				}
			} 
		}
	}
	else
	{
		$viewonline_msg = $viewonline_url = $trackable_viewonline_url = ""; // nonsense: probably it's being read online...
		$unsubfooter = $uns_link = $tracking_view = ""; // unuseful
		
		// Get the content
		$html = $content;
	}
	

	// Create the image gallery
	$args = array( 'post_type' => 'attachment', 'post_mime_type' => array( 'image/jpeg', 'image/jpg', 'image/png', 'image/gif' ), 'numberposts' => -1, 'post_status' => null, 'post_parent' => $newsletter->ID, 'orderby' => 'menu_order', 'order' => 'ASC' ); 
	$attachments = get_posts( $args );
	$gallery = '';
	
	if ( $attachments ) {
		$size = ( $size = get_post_meta ( $newsletter->ID, '_placeholder_newsletter_imgsize', true ) ) ? $size : 'thumbnail';
				
		foreach( $attachments as $index => $attachment ) {
			$src = wp_get_attachment_image_src( $attachment->ID, $size );
			$gallery .= '<img class="alo-easymail-gallery-newsletter" src="' . $src[0] . '" width="' . $src[1] . '" height="' . $src[2] . '" border="0" alt="" />'."\n";
		}
		
		$gallery = apply_filters( 'alo_easymail_placeholder_newsletter_gallery', $gallery,  $attachments, $size, $newsletter->ID );
	}
	
	// post thumbnail
	$thumb = "";
	if ( current_theme_supports( 'post-thumbnails' ) ) {
		if ( has_post_thumbnail( $newsletter->ID ) ) {
			$size = ( $size = get_post_meta ( $newsletter->ID, '_placeholder_newsletter_imgsize', true ) ) ? $size : 'thumbnail';
			$thumb = get_the_post_thumbnail( $newsletter->ID, $size, array( 'class'	=> "alo-easymail-thumb-newsletter" ) );
			$thumb = apply_filters( 'alo_easymail_placeholder_newsletter_thumb', $thumb,  $size, $newsletter->ID );
		} 
	}
	
	// post thumb and gallery
	$post_id = get_post_meta ( $newsletter->ID, '_placeholder_easymail_post', true );
	$post_thumb = $post_gallery = "";
	if ( $post_id ) {
		
		// Create the post gallery
		$args = array( 'post_type' => 'attachment', 'post_mime_type' => array( 'image/jpeg', 'image/jpg', 'image/png', 'image/gif' ), 'numberposts' => -1, 'post_status' => null, 'post_parent' => $post_id, 'orderby' => 'menu_order', 'order' => 'ASC' ); 
		$attachments = get_posts( $args );
	
		if ( $attachments ) {
			$size = ( $size = get_post_meta ( $post_id, '_placeholder_post_imgsize', true ) ) ? $size : 'thumbnail';
							
			foreach( $attachments as $index => $attachment ) {
				$src = wp_get_attachment_image_src( $post_id, $size );
				$post_gallery .= '<img class="alo-easymail-gallery-post" src="' . $src[0] . '" width="' . $src[1] . '" height="' . $src[2] . '" border="0" alt="" />'."\n";
			}
		
			$post_gallery = apply_filters( 'alo_easymail_placeholder_post_gallery', $post_gallery,  $attachments, $size, $post_id );
		}
	
		// post thumbnail
		if ( current_theme_supports( 'post-thumbnails' ) ) {
			if ( has_post_thumbnail( $post_id ) ) {
				$size = ( $size = get_post_meta ( $post_id, '_placeholder_post_imgsize', true ) ) ? $size : 'thumbnail';
				$post_thumb = get_the_post_thumbnail( $post_id, $size, array( 'class'	=> "alo-easymail-thumb-post" ) );
				$post_thumb = apply_filters( 'alo_easymail_placeholder_post_thumb', $post_thumb,  $size, $post_id );
			} 
		}
	}
	
	// site
	$site_url = alo_em_translate_home_url ( $recipient->lang ); //get_option ('siteurl');
	$trackable_site_url = alo_em_make_url_trackable ( $recipient, $site_url );
	
	$blogname = esc_html( get_option('blogname') );
	$blogdescription = esc_html( get_option('blogdescription') );
	
	// newsletter
	$date = date_i18n( get_option('date_format')/*__( 'j / n / Y', "alo-easymail" )*/, strtotime( $newsletter->post_date ) );
	
	// content	   
	//$body = $content; 
	
	/*
	if ( get_option('alo_em_filter_the_content') != "no" ) {
		add_filter ( 'the_content', 'do_shortcode', 11 );
		$body = apply_filters( "the_content", $body );
	}
	*/
	
	// replace all tags
	$html = str_replace('[READ-ONLINE]', $viewonline_msg, $html);
	$html = str_replace('[READ-ONLINE-URL]', $trackable_viewonline_url, $html);
	$html = str_replace('[USER-UNSUBSCRIBE]', $unsubfooter, $html);
	$html = str_replace('[USER-UNSUBSCRIBE-URL]', $uns_link, $html);
	$html = str_replace('[TITLE]', $subject, $html);
	$html = str_replace('[THUMB]', $thumb, $html);
	$html = str_replace('[GALLERY]', $gallery, $html);	
	$html = str_replace('[SITE-URL]', $trackable_site_url, $html);
	$html = str_replace('[SITE-NAME]', $blogname, $html);
	$html = str_replace('[SITE-DESCRIPTION]', $blogdescription, $html);	
	$html = str_replace('[DATE]', $date, $html);
	$html = str_replace('[POST-THUMB]', $post_thumb, $html);
	$html = str_replace('[POST-GALLERY]', $post_gallery, $html);		
	
	// Insert tracking img before </body> if tag exists, otherwise at the end
	if ( strpos( $html, "</body") !== false ) {
		$html = str_replace( "</body", $tracking_view ."\n</body" , $html);
	} else {
		$html .= $tracking_view;
	}
	
	return $html;	
}
add_filter ( 'alo_easymail_newsletter_content',  'alo_em_zirkuss_newsletter_content', 9, 4 );


/**
 * Add Img size in newsletter select in placeholders table
 *
 */
function alo_em_placeholders_title_newsletter_imgsize ( $post_id ) {
	echo __("Select the image size", "alo-easymail"). ": ";	
	echo '<select name="placeholder_newsletter_imgsize" id="placeholder_newsletter_imgsize" >';
	$sizes = array( 'thumbnail', 'medium', 'large' );
	foreach ( $sizes as $size ) {
	    $select_gallery_size = ( get_post_meta ( $post_id, '_placeholder_newsletter_imgsize', true) == $size ) ? 'selected="selected"': '';
	    echo '<option value="'. $size .'" '. $select_gallery_size .'>'. $size . '</option>';
	}
	echo '</select>'; 
}
add_action('alo_easymail_newsletter_placeholders_title_easymail_newsletter', 'alo_em_placeholders_title_newsletter_imgsize', 12 );


/**
 * Add Img size in newsletter select in placeholders table
 *
 */
function alo_em_placeholders_title_post_imgsize ( $post_id ) {
	echo __("Select the image size", "alo-easymail"). ": ";	
	echo '<select name="placeholder_post_imgsize" id="placeholder_post_imgsize" >';
	$sizes = array( 'thumbnail', 'medium', 'large' );
	foreach ( $sizes as $size ) {
	    $select_gallery_size = ( get_post_meta ( $post_id, '_placeholder_post_imgsize', true) == $size ) ? 'selected="selected"': '';
	    echo '<option value="'. $size .'" '. $select_gallery_size .'>'. $size . '</option>';
	}
	echo '</select>'; 
}
add_action('alo_easymail_newsletter_placeholders_title_easymail_post', 'alo_em_placeholders_title_post_imgsize', 12 );


/**
 * Save gallery size when the newsletter is saved
 */
function alo_em_save_placeholder_gallery ( $post_id ) {
	if ( isset( $_POST['placeholder_newsletter_imgsize'] ) ) {
		update_post_meta ( $post_id, '_placeholder_newsletter_imgsize', $_POST['placeholder_newsletter_imgsize'] );
	}
	if ( isset( $_POST['placeholder_post_imgsize'] ) ) {
		update_post_meta ( $post_id, '_placeholder_post_imgsize', $_POST['placeholder_post_imgsize'] );
	}	
} 
add_action('alo_easymail_save_newsletter_meta_extra', 'alo_em_save_placeholder_gallery' );


/**
 * Filter Newsletter Title when sending
 */
function alo_em_filter_title( $subject, $newsletter, $recipient ) {
	if ( !is_object( $recipient ) ) $recipient = new stdClass();
	if ( empty( $recipient->lang ) ) $recipient->lang = alo_em_short_langcode ( get_locale() );
	$post_id = get_post_meta ( $newsletter->ID, '_placeholder_easymail_post', true );
	$obj_post = ( $post_id ) ? get_post( $post_id ) : false;
	if ( $obj_post ) {
		$post_title = stripslashes ( alo_em_translate_text ( $recipient->lang, $obj_post->post_title, $post_id, 'post_title' ) );
	    $subject = str_replace('[POST-TITLE]', $post_title, $subject);
	} else {
	    $subject = str_replace('[POST-TITLE]', "", $subject);
	}
    if ( isset( $recipient ) && is_object( $recipient ) ) {
		if ( isset( $recipient->name ) ) {
		    $subject = str_replace("[USER-NAME]", stripslashes ( $recipient->name ), $subject );
		} else {
		    $subject = str_replace("[USER-NAME]", "", $subject );
		}
		if ( isset( $recipient->firstname ) ) {
		    $subject = str_replace("[USER-FIRST-NAME]", stripslashes ( $recipient->firstname ), $subject );
		} else {
		    $subject = str_replace("[USER-FIRST-NAME]", "", $subject );
		}
    }	
	return $subject;
}
add_filter ( 'alo_easymail_newsletter_title',  'alo_em_filter_title', 10, 3 );


/**
 * Filter Newsletter Title when in title bar in site
 */
function alo_em_filter_title_bar( $subject ) {
	global $post;
	if ( get_post_type( $post->ID ) == 'newsletter' ) {
		$post_id = get_post_meta ( $post->ID, '_placeholder_easymail_post', true );
		$obj_post = ( $post_id ) ? get_post( $post_id ) : false;
		if ( $obj_post ) {
			$post_title = stripslashes ( alo_em_translate_text ( alo_em_get_language (), $obj_post->post_title, $post_id, 'post_title' ) );
			$subject = str_replace('[POST-TITLE]', $post_title, $subject);
		} else {
			$subject = str_replace('[POST-TITLE]', "", $subject);
		}
	}
	return $subject;
}
add_filter ( 'single_post_title',  'alo_em_filter_title_bar' );


/**
 * Filter Newsletter Title when viewed in site
 */
function alo_em_filter_title_in_site ( $subject ) {
	global $post, $pagenow;
	// in frontend and in 'edit.php' screen in backend
	if ( isset( $post ) && is_object( $post ) && ( !is_admin() || $pagenow == 'edit.php' ) ) {
		$post_id = get_post_meta ( $post->ID, '_placeholder_easymail_post', true );
		$obj_post = ( $post_id ) ? get_post( $post_id ) : false;
		if ( $obj_post ) {
			$post_title = stripslashes ( alo_em_translate_text ( false, $obj_post->post_title, $post_id, 'post_title' ) );
			$subject = str_replace('[POST-TITLE]', $post_title, $subject);
		} else {
			$subject = str_replace('[POST-TITLE]', "", $subject);
		}
	}
	return $subject;
}
add_filter ( 'the_title',  'alo_em_filter_title_in_site' );


/**
 * Filter Newsletter Content when sending
 */
function alo_em_filter_content ( $content, $newsletter, $recipient, $stop_recursive_the_content=false ) {
	global $wp_version;
	if ( !is_object( $recipient ) ) $recipient = new stdClass();
	if ( empty( $recipient->lang ) ) $recipient->lang = alo_em_short_langcode ( get_locale() );
	$post_id = get_post_meta ( $newsletter->ID, '_placeholder_easymail_post', true );
	$obj_post = ( $post_id ) ? get_post( $post_id ) : false;

	if ( $obj_post ) {
		$post_title = stripslashes ( alo_em_translate_text ( $recipient->lang, $obj_post->post_title, $post_id, 'post_title' ) );
		
		$post_link = alo_em_translate_url( $obj_post->ID, $recipient->lang );
		$trackable_post_link = alo_em_make_url_trackable ( $recipient, $post_link );

	    $content = str_replace("[POST-TITLE]", "<a href='". $trackable_post_link /*esc_url ( alo_em_translate_url( $obj_post->ID, $recipient->lang ) )*/. "'>". $post_title ."</a>", $content);      
	} else {
	    $content = str_replace("[POST-TITLE]", "", $content);
	}
	
	if ( $obj_post ) {
		$postcontent =  stripslashes ( alo_em_translate_text ( $recipient->lang, $obj_post->post_content, $post_id, 'post_content' ) );
		if ( get_option('alo_em_filter_the_content') != "no" && !$stop_recursive_the_content ) $postcontent = apply_filters('the_content', $postcontent);
	    $content = str_replace("[POST-CONTENT]", $postcontent, $content);

		// Get post excerpt: if not, uses trimmed post content (WP 3.3+)
	    if ( !empty($obj_post->post_excerpt)) {
			$post_excerpt = stripslashes ( alo_em_translate_text ( $recipient->lang, $obj_post->post_excerpt, $post_id, 'post_excerpt' ) );
			$content = str_replace("[POST-EXCERPT]", $post_excerpt, $content);
		} else {
			if ( version_compare ( $wp_version, '3.3', '>=' ) ) {
				$content = str_replace("[POST-EXCERPT]", wp_trim_words( $postcontent, 50, ' [...]' ), $content);
			} else {
				$content = str_replace("[POST-EXCERPT]", "", $content);
			}
		}
	} else {
	    $content = str_replace("[POST-CONTENT]", "", $content);
	    $content = str_replace("[POST-EXCERPT]", "", $content);
	}
	/*
	if ( $obj_post && !empty($obj_post->post_excerpt)) {
		$post_excerpt = stripslashes ( alo_em_translate_text ( $recipient->lang, $obj_post->post_excerpt, $post_id, 'post_excerpt' ) );
	    $content = str_replace("[POST-EXCERPT]", $post_excerpt, $content);       
	} else {
	    $content = str_replace("[POST-EXCERPT]", "", $content);
	}
	*/
    if ( $recipient ) {	
		if ( isset( $recipient->name ) ) {
		    $content = str_replace("[USER-NAME]", stripslashes ( $recipient->name ), $content);     
		} else {
		    $content = str_replace("[USER-NAME]", "", $content);
		}            
		if ( isset( $recipient->firstname ) ) {
		    $content = str_replace("[USER-FIRST-NAME]", stripslashes ( $recipient->firstname ), $content);       
		} else {
		    $content = str_replace("[USER-FIRST-NAME]", "", $content);
		}
		if ( isset( $recipient->email ) ) {
		    $content = str_replace("[USER-EMAIL]", stripslashes ( $recipient->email ), $content);       
		} else {
		    $content = str_replace("[USER-EMAIL]", "", $content);
		}        			
    }

	$home_url = alo_em_translate_home_url ( $recipient->lang );
	$trackable_home_url = alo_em_make_url_trackable ( $recipient, $home_url );
    
    $content = str_replace("[SITE-LINK]", "<a href='". $trackable_home_url /*esc_url ( alo_em_translate_home_url ( $recipient->lang ) )*/ ."'>". esc_html( get_option('blogname') )."</a>", $content);  
    
	return $content;	
}
add_filter ( 'alo_easymail_newsletter_content',  'alo_em_filter_content', 10, 4 );


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


/**
 * Add [CUSTOM-LINK] placeholder
 */
function alo_em_customlink_placeholder ( $placeholders ) {
	$placeholders["easymail_customlink"] = array (
		"title" 		=> __("Custom links", "alo-easymail"),
		"tags" 			=> array (
			"[CUSTOM-LINK]"	=> 	__("This placeholder produces a link (html &lt;a&gt; tag) and has the following parameters", "alo-easymail"). ":". "<ul style='margin-left: 2em;font-size: 90%'>".
								"<li><code style='font-style:normal;font-weight: bold'>". "href". "</code> ".
									__("the ID of a post or a full web address", "alo-easymail"). " (". __("mandatory", "alo-easymail") . ")</li>".
								"<li><code style='font-style:normal;'>". "title". "</code> ".
									__("the text of the link", "alo-easymail").". ". __("Default", "alo-easymail") .": " . __("the title of the post (if &#39;href&#39; is a post ID) or the &#39;href&#39; itself", "alo-easymail") . "</li>".
								"<li><code style='font-style:normal;'>". "tracking". "</code> ".
									__("the click on the link by the recipient will be tracked (1) or not (0)", "alo-easymail"). ". " .__("Default", "alo-easymail") . ": 1 (". __("Yes", "alo-easymail") . ")</li>".
								"<li><code style='font-style:normal;'>". "class". "</code> ".
									__("the class tag attribute", "alo-easymail"). ". " .__("Default", "alo-easymail") .": &#39;alo-easymail-link&#39;" . "</li>".
								"<li><code style='font-style:normal;'>". "style". "</code> ".
									__("the style tag attribute", "alo-easymail"). "</li>".																		
								"</ul>" .
								__("Sample:", "alo-easymail") . " ". __("a link to blog post with ID 1, with custom css style, without tracking", "alo-easymail") . ": <br />" .
									"<code style='font-style:normal;'>". "[CUSTOM-LINK href=1 style=\"color: #f00\" tracking=0]". "</code>" . "<br />" .
								__("Sample:", "alo-easymail") . " ". __("a link to Wordress.org, with custom title", "alo-easymail") . ": <br />" .
									"<code style='font-style:normal;'>". "[CUSTOM-LINK href=\"http://www.wordpress.org\" title=\"visit WordPress site\"]". "</code>" 							
		)
	);
	return $placeholders;
}
add_filter ( 'alo_easymail_newsletter_placeholders_table', 'alo_em_customlink_placeholder' );


function custom_easymail_placeholders_title_easymail_customlink ( $post_id ) {
	echo __("You can insert customised links to blog posts or external web addresses", "alo-easymail"). '.';	
}
add_action('alo_easymail_newsletter_placeholders_title_easymail_customlink', 'custom_easymail_placeholders_title_easymail_customlink' );


function alo_em_placeholders_replace_customlink_tag ( $content, $newsletter, $recipient, $stop_recursive_the_content=false ) {  
	if ( !is_object( $recipient ) ) $recipient = new stdClass();
	if ( empty( $recipient->lang ) ) $recipient->lang = alo_em_short_langcode ( get_locale() );

	if ( preg_match_all('/\[CUSTOM-LINK(.*)\]/i', $content, $matches, PREG_SET_ORDER)) {

		if ( is_array($matches) ) : foreach($matches as $match) :

			// Complete palceholder
			$found = $match[0];

			// Placeholder attributes
			$atts =  shortcode_parse_atts( trim($match[1]) );

			$params = shortcode_atts( array(
				'href' 		=> '',
				'title' 	=> '',
				'tracking' 	=> 1,
				'class'		=> 'alo-easymail-link',
				'style'		=> '',
			), $atts );
			
			if ( empty($params['href']) ) continue; // skip if 'href' is empty
			
			// Numeric = post ID
			if ( is_numeric( $params['href'] ) )
			{
				if ( $obj_post = get_post( $params['href'] ) )
				{
					$title = !empty($params['title']) ? stripslashes ( $params['title'] ) : stripslashes ( alo_em_translate_text ( $recipient->lang, $obj_post->post_title, $obj_post->ID, 'post_title' ) );
					$link = alo_em_translate_url( $obj_post->ID, $recipient->lang );
				}								
			}
			else
			// Otherwise = url
			{
				$title = !empty($params['title']) ? stripslashes ( $params['title'] ) : esc_url( $params['href'] );
				$link = esc_url( $params['href'] );
			}

			if ( $params['tracking'] == 1 )
			{			
				$link = alo_em_make_url_trackable ( $recipient, $link );
			}

			$content = str_replace( $found, '<a href="'. $link . '" class="'. esc_attr($params['class']) . '" style="'. esc_attr($params['style']) . '">'. $title .'</a>', $content );
			
		endforeach; endif;
	}
	
	return $content;
}
add_filter ( 'alo_easymail_newsletter_content',  'alo_em_placeholders_replace_customlink_tag', 10, 4 );


/**
 * Add placeholders of custom fields
 */

function alo_em_cf_placeholders ( $placeholders ) {

	$alo_em_cf = alo_easymail_get_custom_fields();

	if( $alo_em_cf ) {
		foreach( $alo_em_cf as $key => $value ){
			$placeholders['easymail_subscriber']['tags']['[USER-'.strtoupper($key).']'] = __('Subscriber custom field',"alo-easymail").': '. __($value['humans_name'],"alo-easymail");
		}
	}

	return $placeholders;
}
add_filter ( 'alo_easymail_newsletter_placeholders_table', 'alo_em_cf_placeholders' );


function alo_em_cf_placeholders_replace_tags ( $content, $newsletter, $recipient, $stop_recursive_the_content=false ) {
	if ( !is_object( $recipient ) ) $recipient = new stdClass();
	if ( empty( $recipient->lang ) ) $recipient->lang = alo_em_short_langcode ( get_locale() );

	//$content = str_replace("[CUST_ANREDE]", $recipient->cust_anrede, $content);

	$alo_em_cf = alo_easymail_get_custom_fields();

	if( $alo_em_cf ) {
		foreach( $alo_em_cf as $key => $value ){
			$content = str_replace('[USER-'.strtoupper($key).']', alo_easymail_custom_field_html ( $key, $value, $key, $recipient->{$key}, false ), $content);
		}
	}
		
	return $content;
}
add_filter ( 'alo_easymail_newsletter_content',  'alo_em_cf_placeholders_replace_tags', 10, 4 );



/*************************************************************************
 * MAILING LISTS & RECIPIENTS FUNCTIONS
 *************************************************************************/ 


/**
 * Get Subscriber by e-mail
 */
function alo_em_get_subscriber ( $email ) {
	global $wpdb;
	return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}easymail_subscribers WHERE email = '%s'", $email ) );
}


/**
 * Get Subscriber by ID
 */
function alo_em_get_subscriber_by_id ( $ID ) {
	global $wpdb;
	settype($ID, 'integer'); 
	return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}easymail_subscribers WHERE ID = %d", $ID ) );
}


/**
 * Get all registered users of the blog 
 * return arr of object with info as in table column
 */
function alo_em_get_recipients_registered () {
	global $wpdb, $blog_id;    
    if ( function_exists( 'get_users' ) ) { // For WP >= 3.1
    	$get_users = get_users();
   	} else { // For WP < 3.1
   		$get_users = get_users_of_blog();
   	}
   	
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


/**
 * Count subscribers reading the selected language
 * param	lang		if false return no langs or no longer available langs
 * param	active		if only activated subscribers or all subscribers
 * return int
 */
function alo_em_count_subscribers_by_lang ( $lang=false, $only_activated=false ) {
	global $wpdb;
	if ( $lang ) {
		$str_lang = "lang='$lang'";
	} else {
		// search with no selected langs or old langs now not requested
		$langs = alo_em_get_all_languages();
		$str_lang = "lang IS NULL OR lang NOT IN (";
		if ( is_array($langs) ) { 
			foreach ( $langs as $k => $l ) {
				$str_lang .= "'$l',";
			}
		}
		$str_lang = rtrim ($str_lang, ",");
		$str_lang .= ")" ;
	}
	$str_activated = ( $only_activated ) ? " AND active = '1'" : "";
	return $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}easymail_subscribers WHERE $str_lang $str_activated" );
}


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


/**
 * Check if email already exists in Unsubscribed table
 * 
 *@param	str		email
 *@return 	bol
 */
function alo_em_check_email_in_unsubscribed ( $email) {
	global $wpdb;
	$exists = $wpdb->get_var( $wpdb->prepare("SELECT email FROM {$wpdb->prefix}easymail_unsubscribed WHERE email='%s'", $email) );
	return ( $exists ) ? true : false;
}


/**
 * Get date when email unsubscribed
 * 
 *@param	str		email
 *@return 	date
 */
function alo_em_when_email_unsubscribed ( $email) {
	global $wpdb;
	return $wpdb->get_var( $wpdb->prepare("SELECT added_on FROM {$wpdb->prefix}easymail_unsubscribed WHERE email='%s'", $email) );
}


/**
 * Add email in Unsubscribed table
 * 
 *@param	str		email
 */
function alo_em_add_email_in_unsubscribed ( $email) {
	global $wpdb;
	if ( !alo_em_check_email_in_unsubscribed( $email ) )
	{
		$wpdb->insert ( "{$wpdb->prefix}easymail_unsubscribed",
					array( 'email' => $email,  'added_on' => current_time( 'mysql', 0 ) )
		);
	}
}


/**
 * Delete email in Unsubscribed table
 * 
 *@param	str		email
 */
function alo_em_delete_email_from_unsubscribed ( $email) {
	global $wpdb;
	$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}easymail_unsubscribed WHERE email = '%s'", $email ) );
}




/*************************************************************************
 * TRACKING FUNCTIONS
 *************************************************************************/ 

/**
 * If recipient has been tracked (eg. if he has opened the newsletter)
 *@param	int		recipient
 *@param	str		url clicked
 *@return 	bol
 */
function alo_em_recipient_is_tracked ( $recipient, $request='' ) {
	global $wpdb;
	$trackings = alo_em_get_recipient_trackings( $recipient, $request );
	return ( $trackings ) ? true : false;
}


/**
 * Get all trackings of a recipient
 *@param	int		recipient
 *@param	str		url clicked, blank for view
 *@return 	arr		array of object
 */
function alo_em_get_recipient_trackings ( $recipient, $request='' ) {
	global $wpdb;
	return $wpdb->get_results ( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}easymail_stats WHERE recipient=%d AND request='%s'", $recipient, $request ) );
}


/**
 * Get all trackings of a recipient, excluding Views
 *@param	int		recipient
 *@param	str		url clicked, blank for view
 *@return 	arr		array of object
 */
function alo_em_get_recipient_trackings_except_views ( $recipient ) {
	global $wpdb;
	return $wpdb->get_results ( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}easymail_stats WHERE recipient=%d AND request!=''", $recipient ) );
}


/**
 * Tracking when a recipient views/click newsletter and update subscriber last activity
 *@param	int		recipient
 *@param	int		newsletter: if empty get it from recipient 
 *@param	str		url clicked, blank for view
 */
function alo_em_tracking_recipient ( $recipient, $newsletter=false, $request='' ) {
    global $wpdb;
    $rec_info = alo_em_get_recipient_by_id( $recipient );
    if ( empty( $newsletter ) ) {
    	$newsletter = $rec_info->newsletter;
    }
    alo_em_update_subscriber_last_act( $rec_info->email );
	return $wpdb->insert ( "{$wpdb->prefix}easymail_stats",
           					array( 'recipient' => $recipient, 'newsletter' => $newsletter, 'added_on' => current_time( 'mysql', 0 ), 'request' => $request )
	);
} 


/**
 * Count all trackings about a newsletter
 *@param	int		newsletter
 *@param	str		url clicked, blank for view 
 *@return 	arr		array of object: each object contains recipient and number of views/clicks
 */
function alo_em_all_newsletter_trackings ( $newsletter, $request='' ) {
	global $wpdb;
	return $wpdb->get_results( $wpdb->prepare("SELECT recipient, COUNT(ID) AS numitems FROM {$wpdb->prefix}easymail_stats WHERE newsletter=%d AND request='%s' GROUP BY recipient ORDER BY numitems DESC", $newsletter, $request ));
}


/**
 * Count all trackings about a newsletter, except Views
 *@param	int		newsletter
 *@return 	arr		array of object: each object contains recipient and number of views/clicks
 */
function alo_em_all_newsletter_trackings_except_views ( $newsletter) {
	global $wpdb;
	return $wpdb->get_results( $wpdb->prepare("SELECT recipient, COUNT(ID) AS numitems FROM {$wpdb->prefix}easymail_stats WHERE newsletter=%d AND request!='' GROUP BY recipient ORDER BY numitems DESC", $newsletter ));
}


/**
 * Make a url as a trackable url
 *
 *@param	obj		recipient object
 *@param	str		url
 *@return 	str		url trackable
 */
function alo_em_make_url_trackable ( $recipient, $url ) {
	if ( ! is_object($recipient) || empty($recipient->ID) || empty($recipient->unikey) ) return $url;
	
	$track_vars = $recipient->ID . '|' . $recipient->unikey . '|' . $url;
    $track_vars = urlencode( base64_encode( $track_vars ) );
		
	return add_query_arg( 'emtrck', $track_vars, alo_em_translate_home_url ( $recipient->lang ) );
}



/*************************************************************************
 * MULTILANGUAGE
 *************************************************************************/ 


/**
 * Check if there is a multiplanguage enabled plugin 
 * return the name of plugin, or false
 */
function alo_em_multilang_enabled_plugin () {
	// Choice by custom filters
	$plugin_by_filter = apply_filters ( 'alo_easymail_multilang_enabled_plugin', false ); // Hook
	if ( $plugin_by_filter ) return $plugin_by_filter;
	
	// 1st choice: qTranslate
	global $q_config;
	if( function_exists( 'qtrans_init') && isset($q_config) ) return "qTrans";
	
	// 2nd choice: using WPML
	if( defined('ICL_SITEPRESS_VERSION') ) return "WPML";
	
	// TODO other choices...
	
	// no plugin: return false
	return false;
}


/**
 * Return a text after applying a multilanguage filter 
 */
function alo_em___ ( $text ) {
	// 1st choice: using qTranslate
	if( alo_em_multilang_enabled_plugin() == "qTrans" && function_exists( 'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage') ) {
		return qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage ( $text );
	}
	
	// Choice by custom filters
	$text = apply_filters ( 'alo_easymail_multilang_alo_em___', $text ); // Hook	

	// last case: return without translating
	return $text ;
}

/**
 * Echo a text after applying a multilanguage filter (based on 'alo_em___')
 */
function alo_em__e ( $text ) {
	echo alo_em___ ( $text );
}



/**
 * Return a text after applying a multilanguage filter 
 *
 * 2nd param:			useful for qTrans: get the part of text of selected lang, e.g.: "<!--:en-->english text<!--:-->"
 * 3rd and 4th params: 	useful for WPML: to get the $prop (title, content. excerpt...) of the $post
 */
function alo_em_translate_text ( $lang, $text, $post=false, $prop="post_title" ) {
	// if blank lang or not installed on blog, get default lang
	if ( empty($lang) || !in_array ( $lang, alo_em_get_all_languages( false )) ) $lang = alo_em_short_langcode ( get_locale() );
	
	// 1st choice: if using qTranslate, get the part of text of selected lang
	if( alo_em_multilang_enabled_plugin() == "qTrans" && function_exists( 'qtrans_use') ) {
		return qtrans_use ( $lang, $text, false);
	}

	// 2nd choice: using WPML
	if( alo_em_multilang_enabled_plugin() == "WPML" && is_numeric( $post ) && function_exists( 'icl_object_id' ) ) {
		$transl_post = icl_object_id( $post, get_post_type( $post ), true, $lang );
		$transl_post_obj = get_post( $transl_post );
		$transl_post_text = $transl_post_obj->{$prop};
		if ( $transl_post_text ) return $transl_post_text;
	}		

	// last case: return as is
	return $text ;
}


/**
 * Return a text of the requested lang from a saved option or default option
 * param	fallback	if requested lang not exists and fallback true returns a lang default
 */
function alo_em_translate_option ( $lang, $key , $fallback=true ) {
	$default_lang = alo_em_short_langcode ( get_locale() ); // default lang
	$fallback_lang = "en"; // latest default...
	$text_1 = $text_2 = $text_3 = false;

	// from default option if exists
	if ( get_option( $key."_default" ) ) {
		$get = get_option( $key."_default" );
		if ( is_array($get) ) {
			foreach ( $get as $k => $v ) {
				if ( $k == $lang )			$text_1 = $v;	// the requested lang
				if ( $k == $default_lang )	$text_2 = $v;	// the default lang
				if ( $k == $fallback_lang ) $text_3 = $v;	// the fallback lang
			}
		}
	}
		
	// from option
	if ( get_option( $key ) ) {
		$get = get_option( $key );
		if ( is_array($get) ) {
			foreach ( $get as $k => $v ) {
				if ( !empty($v) ) { // if not empty
					if ( $k == $lang )			$text_1 = $v;	// the requested lang
					if ( $k == $default_lang )	$text_2 = $v;	// the default lang
					if ( $k == $fallback_lang ) $text_3 = $v;	// the fallback lang
				}
			}
		}
	}
	
	if ( $text_1 ) return $text_1;
	if ( $text_2 && $fallback ) return $text_2;	
	if ( $text_3 && $fallback ) return $text_3;
	return false;
}


/**
 * Return a text of the requested lang from an array with same text in several langs ( "en" => "hi", "es" => "hola"...)
 * param	fallback	if requested lang not exists and fallback true returns a lang default
 */
function alo_em_translate_multilangs_array ( $lang, $array, $fallback=true ) {
	if ( !is_array($array) ) return $array; // if not array, return the text
	
	$default_lang = alo_em_short_langcode ( get_locale() ); // default lang
	$fallback_lang = "en"; // latest default...
	$text_1 = $text_2 = $text_3 = false;
	
	foreach ( $array as $k => $v ) {
		if ( $k == $lang ) 			$text_1 = $v;	// the requested lang
		if ( $k == $default_lang ) 	$text_2 = $v;	// the default lang
		if ( $k == $fallback_lang ) $text_3 = $v;	// the fallback lang
	}
	
	if ( $text_1 ) return $text_1;
	if ( $text_2 && $fallback ) return $text_2;	
	if ( $text_3 && $fallback ) return $text_3;
	return false;
}


/** 
 * Return the url localised for the requested lang 
 *
 * param	$post	int		the post/page ID
 * param	$lang	str		two-letter language codes, e.g.: "it"
 */
function alo_em_translate_url ( $post, $lang ) {
	// if blank lang or not installed on blog, get default lang
	if ( empty($lang) || !in_array ( $lang, alo_em_get_all_languages( false )) ) $lang = alo_em_short_langcode ( get_locale() );
	
	// Choice by custom filters
	$url_by_filter = apply_filters ( 'alo_easymail_multilang_translate_url', false, $post, $lang ); // Hook
	if ( $url_by_filter ) return $url_by_filter;
	
	// 1st choice: using qTranslate
	if( alo_em_multilang_enabled_plugin() == "qTrans" ) {
		return add_query_arg( "lang", $lang, get_permalink( $post ) );
	}

	// 2nd choice: using WPML
	if( alo_em_multilang_enabled_plugin() == "WPML" && function_exists( 'icl_object_id' ) ) {
		$translated_post = icl_object_id( $post, get_post_type( $post ), true, $lang );
		//return add_query_arg( "lang", $lang, $url );
		return add_query_arg( "lang", $lang, get_permalink( $translated_post ) );
	}	
	
	// last case: return th url with a "lang" var... maybe it could be useful...
	return add_query_arg( "lang", $lang, get_permalink( $post ) );
}


/** 
 * Return the homepage url localised for the requested lang 
 *
 * param	$lang	str		two-letter language codes, e.g.: "it"
 */
function alo_em_translate_home_url ( $lang ) {
	// if blank lang or not installed on blog, get default lang
	if ( empty($lang) || !in_array ( $lang, alo_em_get_all_languages( false )) ) $lang = alo_em_short_langcode ( get_locale() );
	
	// Choice by custom filters
	$url_by_filter = apply_filters ( 'alo_easymail_multilang_translate_home_url', false, $lang ); // Hook
	if ( $url_by_filter ) return $url_by_filter;
	
	// 1st choice: using qTranslate
	if( alo_em_multilang_enabled_plugin() == "qTrans" ) {
		return add_query_arg( "lang", $lang, trailingslashit( get_home_url() ) );
	}

	// 2nd choice: using WPML
	if( alo_em_multilang_enabled_plugin() == "WPML" && function_exists( 'icl_get_home_url' ) ) {
		return icl_get_home_url();
	}	
	
	// last case: return th url with a "lang" var... maybe it could be useful...
	return add_query_arg( "lang", $lang, trailingslashit( get_home_url() ) );
}


/** 
 * Return the ID of subscription page: e.g. useful for WPML, or other purposes
 */
function alo_em_get_subscrpage_id ( $lang=false ) {

	// Choice by custom filters
	$page_by_filter = apply_filters ( 'alo_easymail_multilang_get_subscrpage_id', false, $lang ); // Hook
	if ( $page_by_filter ) return $page_by_filter;
	
	// 1st choice: using qTranslate
	if( alo_em_multilang_enabled_plugin() == "qTrans" ) {
		return get_option('alo_em_subsc_page');
	}

	// 2nd choice: using WPML
	if( alo_em_multilang_enabled_plugin() == "WPML" && function_exists( 'icl_object_id' ) ) {
		$original = get_option('alo_em_subsc_page');
		return icl_object_id( $original, 'page', true, $lang );
	}	
	
	// last case: return th same ID
	return get_option('alo_em_subsc_page');
}


/**
 * Return the current language 
 *
 * param	bol		try lang detection form browser (eg. useful for subscription if multilang plugin not installed)
 */
function alo_em_get_language ( $detect_from_browser=false ) {
	// Choice by custom filters
	$lang_by_filter = apply_filters ( 'alo_easymail_multilang_get_language', false, $detect_from_browser ); // Hook
	if ( $lang_by_filter ) return strtolower( $lang_by_filter );
	
	// 1st choice: using qTranslate
	if( alo_em_multilang_enabled_plugin() == "qTrans" && function_exists( 'qtrans_getLanguage') ) {
		return strtolower( qtrans_getLanguage() );
	}

	// 2nd choice: using WPML
	if( alo_em_multilang_enabled_plugin() == "WPML" && defined('ICL_LANGUAGE_CODE') ) {
		return strtolower( ICL_LANGUAGE_CODE );
	}
		
	// Last choice: get from browser only if requested and the lang .mo is available on blog
	if ( $detect_from_browser ) {
		if ( !empty($_SERVER['HTTP_ACCEPT_LANGUAGE']) ) $lang = alo_em_short_langcode ( $_SERVER['HTTP_ACCEPT_LANGUAGE'] );
		if ( !empty($lang) && in_array($lang, alo_em_get_all_languages(false)) ) {
			return $lang;
		} else {
			return "";
		}
	} else {	
		// otherwise return default blog language
		return alo_em_short_langcode ( get_locale() );
	}
}

/**
 * Return 2 chars lowercase lang code (eg. from "it_IT" to "it")
 */
function alo_em_short_langcode ( $lang ) {
	return strtolower ( substr( $lang, 0, 2) );
}

/**
 * Return the long name of language
 */
function alo_em_get_lang_name ( $lang_code ) {
	global $q_config;
	$lang_code = alo_em_short_langcode( $lang_code );
	if ( alo_em_multilang_enabled_plugin() == "qTrans" && isset($q_config) ) { // qTranslate
		$name = $q_config['language_name'][$lang_code];
	} else { // default
		$longname = alo_em_format_code_lang ( $lang_code );
		$splitname = explode ( ";", $longname );
		$name = $splitname[0];
	}
	return $name;
}


/**
 * Return the lang flag
 * param 	fallback	if there is not the image, return the lang code ('code') or lang name ('name') or nothing
 */
function alo_em_get_lang_flag ( $lang_code, $fallback=false ) {
	global $q_config;
	if ( empty($lang_code) ) return; 
	$flag = false;
	$lang_code =  alo_em_short_langcode ( $lang_code );
	if ( alo_em_multilang_enabled_plugin() == "qTrans" && isset($q_config) ) { // qTranslate
		if ( $lang_code == "en" && !file_exists ( trailingslashit(WP_CONTENT_DIR).$q_config['flag_location']. $lang_code .".png" ) ) {
			$img_code = "gb";
		} else {
			$img_code = $lang_code;
		}
		$flag = "<img src='". trailingslashit(WP_CONTENT_URL).$q_config['flag_location']. $img_code .".png' alt='".$q_config['language_name'][$lang_code]."' title='".$q_config['language_name'][$lang_code]."' alt='' />" ;
	} else { // default
		if ( $fallback == "code" ) $flag = $lang_code;
		if ( $fallback == "name" ) $flag = alo_em_get_lang_name ( $lang_code );
	}
	return $flag;
}


/**
 * Return an array with availables languages
 * param 	by_users	if true and no other translation plugins get all langs chosen by users, if not only langs installed on blog
 */
function alo_em_get_all_languages ( $fallback_by_users=false ) {
	global $wp_version, $alo_em_all_languages;

	if(empty($alo_em_all_languages)){
	
		// Choice by custom filters
		$langs_by_filter = apply_filters ( 'alo_easymail_multilang_get_all_languages', false, $fallback_by_users ); // Hook
		if ( !empty( $langs_by_filter ) && is_array( $langs_by_filter ) ) $alo_em_all_languages = $langs_by_filter;
	
		// Case 1: using qTranslate
		elseif( alo_em_multilang_enabled_plugin() == "qTrans" && function_exists( 'qtrans_getSortedLanguages') ) {
			$alo_em_all_languages = qtrans_getSortedLanguages();
		}

		// Case 2: using WPML
		elseif( alo_em_multilang_enabled_plugin() == "WPML" && function_exists( 'icl_get_languages') ) {
			$languages = icl_get_languages('skip_missing=0&orderby=code');
			if ( is_array( $languages ) ) $alo_em_all_languages = array_keys( $languages );
		}	
	
		// Case: search for setting
		elseif ( get_option( 'alo_em_langs_list' ) != "" ) {
			$languages = explode ( ",", get_option( 'alo_em_langs_list' ) );
	
			// If languages, add locale lang (if not yet) and return
			if ( !empty ($languages[0]) ) {
				$default = alo_em_short_langcode ( get_locale() );
				if ( !in_array( $default, $languages ) ) $languages[] = $default;
				$alo_em_all_languages = $languages;
			}
		} 
		/* // Disabled to avoid auto-loading languages...
		else {
			// Case: wp default detection
			$languages = array();
			// WP_CONTENT_DIR. '/languages/' instead of WP_LANG_DIR: if qtranslate previously installed and then de-activated, the WP_LANG_DIR will remain 'wp-includes/languages/'
			foreach( (array)glob( WP_CONTENT_DIR. '/languages/*.mo' ) as $lang_file ) {
				$lang_file = basename($lang_file, '.mo');
				if ( 0 !== strpos( $lang_file, 'continents-cities' ) && 0 !== strpos( $lang_file, 'ms-' ) )
					$languages[] = alo_em_short_langcode( $lang_file );
			}
		}
		*/
	
		// Last case: return all langs chosen by users or default
		elseif ( $fallback_by_users ) {
			$alo_em_all_languages = alo_em_get_all_languages_by_users();
		} else {
			$alo_em_all_languages = array( alo_em_short_langcode ( get_locale() ) );
		}
	}

	return $alo_em_all_languages;
}


/**
 * Return an array with all languages chosen by users
 */
function alo_em_get_all_languages_by_users () {
	global $wpdb;
	$langs = $wpdb->get_results( "SELECT lang FROM {$wpdb->prefix}easymail_subscribers GROUP BY lang" , ARRAY_N );
	if ( $langs ) {
		$output = array();
		foreach ( $langs as $key => $val ) {
			if ( !empty($val[0]) ) $output[] = $val[0];
		}
		return $output;
	} else {
		return array( alo_em_short_langcode ( get_locale() ) );
	}
}



/**
 * Return the long name of language
 */
function alo_em_format_code_lang( $code = '' ) {
	$code = strtolower( substr( $code, 0, 2 ) );
	$lang_codes = array(
		'aa' => 'Afar', 'ab' => 'Abkhazian', 'af' => 'Afrikaans', 'ak' => 'Akan', 'sq' => 'Albanian', 'am' => 'Amharic', 'ar' => 'Arabic', 'an' => 'Aragonese', 'hy' => 'Armenian', 'as' => 'Assamese', 'av' => 'Avaric', 'ae' => 'Avestan', 'ay' => 'Aymara', 'az' => 'Azerbaijani', 'ba' => 'Bashkir', 'bm' => 'Bambara', 'eu' => 'Basque', 'be' => 'Belarusian', 'bn' => 'Bengali',
		'bh' => 'Bihari', 'bi' => 'Bislama', 'bs' => 'Bosnian', 'br' => 'Breton', 'bg' => 'Bulgarian', 'my' => 'Burmese', 'ca' => 'Catalan; Valencian', 'ch' => 'Chamorro', 'ce' => 'Chechen', 'zh' => 'Chinese', 'cu' => 'Church Slavic; Old Slavonic; Church Slavonic; Old Bulgarian; Old Church Slavonic', 'cv' => 'Chuvash', 'kw' => 'Cornish', 'co' => 'Corsican', 'cr' => 'Cree',
		'cs' => 'Czech', 'da' => 'Danish', 'dv' => 'Divehi; Dhivehi; Maldivian', 'nl' => 'Dutch; Flemish', 'dz' => 'Dzongkha', 'en' => 'English', 'eo' => 'Esperanto', 'et' => 'Estonian', 'ee' => 'Ewe', 'fo' => 'Faroese', 'fj' => 'Fijjian', 'fi' => 'Finnish', 'fr' => 'French', 'fy' => 'Western Frisian', 'ff' => 'Fulah', 'ka' => 'Georgian', 'de' => 'German', 'gd' => 'Gaelic; Scottish Gaelic',
		'ga' => 'Irish', 'gl' => 'Galician', 'gv' => 'Manx', 'el' => 'Greek, Modern', 'gn' => 'Guarani', 'gu' => 'Gujarati', 'ht' => 'Haitian; Haitian Creole', 'ha' => 'Hausa', 'he' => 'Hebrew', 'hz' => 'Herero', 'hi' => 'Hindi', 'ho' => 'Hiri Motu', 'hu' => 'Hungarian', 'ig' => 'Igbo', 'is' => 'Icelandic', 'io' => 'Ido', 'ii' => 'Sichuan Yi', 'iu' => 'Inuktitut', 'ie' => 'Interlingue',
		'ia' => 'Interlingua (International Auxiliary Language Association)', 'id' => 'Indonesian', 'ik' => 'Inupiaq', 'it' => 'Italian', 'jv' => 'Javanese', 'ja' => 'Japanese', 'kl' => 'Kalaallisut; Greenlandic', 'kn' => 'Kannada', 'ks' => 'Kashmiri', 'kr' => 'Kanuri', 'kk' => 'Kazakh', 'km' => 'Central Khmer', 'ki' => 'Kikuyu; Gikuyu', 'rw' => 'Kinyarwanda', 'ky' => 'Kirghiz; Kyrgyz',
		'kv' => 'Komi', 'kg' => 'Kongo', 'ko' => 'Korean', 'kj' => 'Kuanyama; Kwanyama', 'ku' => 'Kurdish', 'lo' => 'Lao', 'la' => 'Latin', 'lv' => 'Latvian', 'li' => 'Limburgan; Limburger; Limburgish', 'ln' => 'Lingala', 'lt' => 'Lithuanian', 'lb' => 'Luxembourgish; Letzeburgesch', 'lu' => 'Luba-Katanga', 'lg' => 'Ganda', 'mk' => 'Macedonian', 'mh' => 'Marshallese', 'ml' => 'Malayalam',
		'mi' => 'Maori', 'mr' => 'Marathi', 'ms' => 'Malay', 'mg' => 'Malagasy', 'mt' => 'Maltese', 'mo' => 'Moldavian', 'mn' => 'Mongolian', 'na' => 'Nauru', 'nv' => 'Navajo; Navaho', 'nr' => 'Ndebele, South; South Ndebele', 'nd' => 'Ndebele, North; North Ndebele', 'ng' => 'Ndonga', 'ne' => 'Nepali', 'nn' => 'Norwegian Nynorsk; Nynorsk, Norwegian', 'nb' => 'Bokmål, Norwegian, Norwegian Bokmål',
		'no' => 'Norwegian', 'ny' => 'Chichewa; Chewa; Nyanja', 'oc' => 'Occitan, Provençal', 'oj' => 'Ojibwa', 'or' => 'Oriya', 'om' => 'Oromo', 'os' => 'Ossetian; Ossetic', 'pa' => 'Panjabi; Punjabi', 'fa' => 'Persian', 'pi' => 'Pali', 'pl' => 'Polish', 'pt' => 'Portuguese', 'ps' => 'Pushto', 'qu' => 'Quechua', 'rm' => 'Romansh', 'ro' => 'Romanian', 'rn' => 'Rundi', 'ru' => 'Russian',
		'sg' => 'Sango', 'sa' => 'Sanskrit', 'sr' => 'Serbian', 'hr' => 'Croatian', 'si' => 'Sinhala; Sinhalese', 'sk' => 'Slovak', 'sl' => 'Slovenian', 'se' => 'Northern Sami', 'sm' => 'Samoan', 'sn' => 'Shona', 'sd' => 'Sindhi', 'so' => 'Somali', 'st' => 'Sotho, Southern', 'es' => 'Spanish; Castilian', 'sc' => 'Sardinian', 'ss' => 'Swati', 'su' => 'Sundanese', 'sw' => 'Swahili',
		'sv' => 'Swedish', 'ty' => 'Tahitian', 'ta' => 'Tamil', 'tt' => 'Tatar', 'te' => 'Telugu', 'tg' => 'Tajik', 'tl' => 'Tagalog', 'th' => 'Thai', 'bo' => 'Tibetan', 'ti' => 'Tigrinya', 'to' => 'Tonga (Tonga Islands)', 'tn' => 'Tswana', 'ts' => 'Tsonga', 'tk' => 'Turkmen', 'tr' => 'Turkish', 'tw' => 'Twi', 'ug' => 'Uighur; Uyghur', 'uk' => 'Ukrainian', 'ur' => 'Urdu', 'uz' => 'Uzbek',
		've' => 'Venda', 'vi' => 'Vietnamese', 'vo' => 'Volapük', 'cy' => 'Welsh','wa' => 'Walloon','wo' => 'Wolof', 'xh' => 'Xhosa', 'yi' => 'Yiddish', 'yo' => 'Yoruba', 'za' => 'Zhuang; Chuang', 'zu' => 'Zulu' );
	//$lang_codes = apply_filters( 'lang_codes', $lang_codes, $code );
	return strtr( $code, $lang_codes );
}


/**
 * Create options (if not exist yet) with array of pre-domain text in all languages
 * param 	reset_defaults		if yes create defaults (useful also if new langs installed)
 */
 
function alo_em_setup_predomain_texts( $reset_defaults = false ) {
	//Required pre-domain text
	require_once( 'languages/alo-easymail-predomain.php');
	
	global $alo_em_textpre;
	foreach ( $alo_em_textpre as $key => $sub ) {
		// add/update only if not exists or forced
		if ( !get_option($key.'_default') || $reset_defaults ) {
			update_option ( $key.'_default', $sub );
		}
	}
}

/**
 * Assign a subscriber to a language	
 */
function alo_em_assign_subscriber_to_lang ( $subscriber, $lang ) {
	global $wpdb;
	$wpdb->update(    "{$wpdb->prefix}easymail_subscribers",
		            array ( 'lang' => $lang ),
		            array ( 'ID' => $subscriber )
		        );
}


/**
 * Polylang integration
 **/
function alo_em_polylang_set_plugin( $multilang_plugin ){

	if ( defined('POLYLANG_VERSION') )
		$multilang_plugin = 'polylang';
	return $multilang_plugin;
}
add_filter ( 'alo_easymail_multilang_enabled_plugin', 'alo_em_polylang_set_plugin' );

function alo_em_polylang_get_language( $lang, $detect_from_browser ){

	if ( function_exists('pll_current_language') )
		$lang = pll_current_language('slug');
	return $lang;
}
add_filter ( 'alo_easymail_multilang_get_language', 'alo_em_polylang_get_language', 10, 2 );

function alo_em_polylang_get_all_languages( $langs, $fallback_by_users  ){

	if ( function_exists('pll_the_languages') )
	{
		global $polylang;
		if (isset($polylang))
		{
			$pl_languages = $polylang->get_languages_list();
			if ( is_array($pl_languages) ) foreach( $pl_languages as $i =>$pl_lang )
				$langs[] = $pl_lang->slug;
		}
	}
	return $langs;
}
add_filter ( 'alo_easymail_multilang_get_all_languages', 'alo_em_polylang_get_all_languages', 10, 2 );

function alo_em_polylang_translate_url( $filtered_url, $post, $lang ){

	if ( function_exists('pll_get_post') )
	{
		if ( $translated_id = pll_get_post( $post, $lang ) )
		{
			$filtered_url = get_permalink( $translated_id );
		}
	}
	return $filtered_url;
}
add_filter ( 'alo_easymail_multilang_translate_url', 'alo_em_polylang_translate_url', 10, 3 );

function alo_em_polylang_get_subscrpage_id( $translated_id, $lang ){

	if ( function_exists('pll_get_post') )
	{
		$original = get_option('alo_em_subsc_page');
		$translated_id = pll_get_post( $original, $lang );
	}
	return $translated_id;
}
add_filter ( 'alo_easymail_multilang_get_subscrpage_id', 'alo_em_polylang_get_subscrpage_id', 10, 2 );



/*************************************************************************
 * FUNCTIONS FOR FRONTEND 
 *************************************************************************/ 


/**
 * Get the selected Newsletters using 'get_posts'
 *
 * Include this code in your template file:
 * <?php if ( function_exists('alo_easymail_get_newsletters') ) alo_easymail_get_newsletters(); ?>
 * @param	arr		there is the custom arg "newsletter_status" (values: sent, sendable , paused); for other args see: http://codex.wordpress.org/Template_Tags/get_posts
 */
 
function alo_easymail_get_newsletters ( $args=false ) {
	global $wp_version;
	if ( !is_array( $args ) ) $args = array();
	$args["post_type"] = "newsletter";
	$status = ( isset( $args["newsletter_status"] ) && in_array( $args["newsletter_status"], array( 'sent', 'sendable', 'paused' ) ) ) ? $args["newsletter_status"] : 'sent';
	if ( version_compare ( $wp_version, '3.1', '>=' ) ) {
		$meta_1 = array( 'key' => '_easymail_status', 'value' => $status, 'compare' => '=' );
		$args['meta_query'] = array( $meta_1 );
	} else {
		$args['meta_key'] = '_easymail_status';
		$args['meta_value'] = $status;
		$args['meta_compare'] = '=';
	}	
	return get_posts ( $args );
}


/**
 * Get the selected Newsletters using a Shortcode
 *
 * Using 'alo_easymail_get_newsletters' to get posts.
 * Put [ALO-EASYMAIL-ARCHIVE] in a page or post
 * @param	arr		there are 3 custom args: 	"newsletter_status" (values: sent, sendable , paused), 
 *												"ul_class", 
 * 												"li_format" (values: 'title_date', 'date_title', 'title')
 *					for other args see: http://codex.wordpress.org/Template_Tags/get_posts 
 */
function alo_easymail_print_archive ( $atts=false, $content="" ) {
	global $post;
	$defaults = array( 'ul_class' => 'easymail-newsletter-archive', 'li_format' => 'title_date' );
	$args = wp_parse_args( $atts, $defaults );
	$newsletters = alo_easymail_get_newsletters( $args );
	if ( $newsletters ) { 
		$output = "<ul class='". $args['ul_class'] ."'>";
		foreach( $newsletters as $post ) : setup_postdata( $post ); 
			switch ( $args['li_format'] ) : 
				case "date_title":
					$output .= "<li><span>". get_the_date() ."</span> <a href='". alo_em_translate_url( $post->ID, alo_em_get_language() ) /*get_permalink()*/ . "'>" . get_the_title( $post->ID ) ."</a></li>";
					break;
				case "title":
					$output .= "<li><a href='". alo_em_translate_url( $post->ID, alo_em_get_language() ) ."'>". get_the_title( $post->ID ) ."</a></li>";
					break;					
				case "title_date":
				default:						
					$output .= "<li><a href='". alo_em_translate_url( $post->ID, alo_em_get_language() ) . "'>". get_the_title( $post->ID ) ."</a> <span>". get_the_date() ."</span></li>";
		endswitch;
		endforeach; 
		$output .= "</ul>";
		wp_reset_postdata();
		return $output;
	}
}
add_shortcode('ALO-EASYMAIL-ARCHIVE', 'alo_easymail_print_archive');


/*************************************************************************
 * THEMES
 *************************************************************************/ 


/**
 * Get all available themes
 *
 * First search in 'wp-content/themes/{active-theme}/alo-easymail-themes' folder; 
 * if not exists search in 'wp-content/plugins/alo-easymail/alo-easymail-themes'
 */
 
function alo_easymail_get_all_themes () {
	if ( @file_exists( WP_CONTENT_DIR.'/alo-easymail-themes/' ) ) {
		$dir = WP_CONTENT_DIR . '/alo-easymail-themes/';
	} else if ( @file_exists ( trailingslashit( get_stylesheet_directory() ) .'alo-easymail-themes/' ) ) {
		$dir = trailingslashit( get_stylesheet_directory() ) .'alo-easymail-themes/';
	} else {
		$dir = ALO_EM_PLUGIN_ABS."/alo-easymail-themes/";
	}
	// $themes = glob( $dir. "*.{htm,html}", GLOB_BRACE ); // GLOB_BRACE not supported by some servers
	$_htm 	= ( is_array( glob( $dir. "*.htm") ) ) ? glob( $dir. "*.htm") : array();
	$_html 	= ( is_array( glob( $dir. "*.html") ) ) ? glob( $dir. "*.html") : array();
	$_php 	= ( is_array( glob( $dir. "*.php") ) ) ? glob( $dir. "*.php") : array();			
	$themes = array_merge( $_htm, $_html, $_php );	
	$return = array();
	if( $themes && count( $themes ) > 0 ) {
		sort( $themes );
		foreach( $themes as $theme ) {
			$namefile = basename( $theme );
			if ( $namefile == "index.php" ) continue;
			$return[ $namefile ] = $theme;
		}
	}
	return $return;
}


/**
 * Get url of themes (eg. for preview or for image url in themes)
 *
 * First search in 'wp-content/themes/{active-theme}/alo-easymail-themes' folder; 
 * if not exists search in 'wp-content/plugins/alo-easymail/alo-easymail-themes'
 */
 
function alo_easymail_get_themes_url () {
	if ( @file_exists( WP_CONTENT_DIR.'/alo-easymail-themes/' ) ) {
		$url = content_url( '/alo-easymail-themes/' );
	} else if ( @file_exists ( trailingslashit( get_stylesheet_directory() ) .'alo-easymail-themes/' ) ) {
		$url = trailingslashit( get_stylesheet_directory_uri() ) .'alo-easymail-themes/';
	} else {
		$url = ALO_EM_PLUGIN_URL."/alo-easymail-themes/";
	}
	return ( ! is_ssl() ) ? str_replace('https://', 'http://', $url) : $url;
}


/*************************************************************************
 * SUBSCRIBERS SCREEN 
 *************************************************************************/ 


/**
 * Html row of a Subscriber in subscriber table
 */
 
function alo_em_get_subscriber_table_row ( $subscriber_id, $row_index=0, $edit=false, $all_lists=false, $all_langs=false ) {
		if ( empty( $subscriber_id ) ) return false;
		$subscriber = alo_em_get_subscriber_by_id( $subscriber_id );
		$html = "";
		//$html .= "<tr id=\"subscriber-row-{$subscriber_id}\" class=\"subscriber-row\">\n";
		
		$html .= "<th scope=\"row\" class=\"subscriber-row-index\">". $row_index . "</th>\n";
        $html .= "<td style=\"vertical-align: middle;\">";
		$html .= "<input type=\"checkbox\" name=\"subscribers[]\" id=\"subscribers_". $subscriber_id . "\" value=\"". $subscriber_id. "\" />\n";
	    $html .= "</td>\n";

		if ( get_option('show_avatars') )
		{
			$html .= "<td>" . get_avatar($subscriber->email, 30). "&nbsp;</td>";
		}
	
		$html .= "<td class=\"subscriber-email\">";
		if ( $edit ) {
			$html .= "<input type=\"text\" id=\"subscriber-". $subscriber_id ."-email-new\" name=\"subscriber-". $subscriber_id ."-email-new\" class=\"subscriber-email-new\" value=\"". format_to_edit( $subscriber->email ) . "\" />\n";
		} else {
			$html .= esc_html($subscriber->email);
		}
		$html .= "&nbsp;</td>\n";

		$html .= "<td class=\"subscriber-name\">";
		if ( $edit ) {
			$html .= "<input type=\"text\" id=\"subscriber-". $subscriber_id ."-name-new\" name=\"subscriber-". $subscriber_id ."-name-new\" class=\"subscriber-name-new\" value=\"". format_to_edit( $subscriber->name ). "\" />\n";
		} else {
			$html .= esc_html($subscriber->name);
		}
		$html .= "&nbsp;</td>\n";
		
		//edit : added the following foreach and its content
		$alo_em_cf = alo_easymail_get_custom_fields();
		if ($alo_em_cf) {
			foreach( $alo_em_cf as $key => $value ){
				
				$field_id = "subscriber-".$subscriber_id."-".$key."-new"; // edit-by-alo: added
				
				$html .= "<td class=\"subscriber-".$key."-new\">"; // edit-by-alo
				
				if ( $edit ) {
					$var_value = "";
					if( ! empty( $subscriber->$key ) ){
						$var_value = $subscriber->$key;
					}
					// edit-by-alo: added
					//$html .= sprintf( $value['edit_html'], $subscriber_id, $subscriber_id, format_to_edit( $var_value ) );
					$html .= alo_easymail_custom_field_html ( $key, $value, $field_id, $var_value, true );
					
				} else {
					$var_value = "";

					// particular case: empty is a negative checkbox
					if( empty( $subscriber->$key ) && $value['input_type'] == 'checkbox' ) {
						$html .= alo_easymail_custom_field_html ( $key, $value, $field_id, $var_value, false );
					} else if( ! empty( $subscriber->$key ) ){
						$var_value = $subscriber->$key;
						$html .= alo_easymail_custom_field_html ( $key, $value, $field_id, $var_value, false );
					} else {
						$html .= "";
					}
				}
				$html .= "&nbsp;</td>\n";
			}
		}
				
		$html .= "<td>";

		$user_id = email_exists($subscriber->email);
		if ( !$user_id ) {
			$user_id = apply_filters ( 'alo_easymail_get_userid_by_subscriber', false, $subscriber );  // Hook
		}
		if ( $user_id ) {
			$user_info = get_userdata( $user_id );		
			
			if ( get_current_user_id() == $user_id ) {
				$profile_link = 'profile.php';
			} else {
				$profile_link = esc_url( add_query_arg( 'wp_http_referer', urlencode( stripslashes( $_SERVER['REQUEST_URI'] ) ), "user-edit.php?user_id={$user_id}" ) );
			}
			$html .= "<a href=\"". $profile_link . "\" title=\"". esc_attr( __("View user profile", "alo-easymail") ) ."\">{$user_info->user_login}</a>";
		}
		$html .= "&nbsp;</td>\n";
			
		$html .= "<td class=\"subscriber-joindate\">\n";
		$join_date_datetime = date_i18n( __( "d/m/Y \h.H:i", "alo-easymail" ), strtotime( $subscriber->join_date ) );
		
		$join_time_diff  = sprintf( __( "%s ago", "alo-easymail" ), human_time_diff( strtotime( $subscriber->join_date ), current_time('timestamp') ) );
		//$html .= $join_time_diff ." <img src=\"".ALO_EM_PLUGIN_URL."/images/12-clock.png\" class=\"clock\" title=\"". esc_attr($join_date_datetime) ."\" alt=\"". $join_date_datetime ."\" />\n";
		$html .= "<abbr title=\"". esc_attr($join_date_datetime) ."\" />". $join_time_diff ."</abbr>\n";
		$html .= "</td>\n";

		$html .= "<td class=\"subscriber-lastact\">\n";
		$last_act = !empty($subscriber->last_act) ? $subscriber->last_act : $subscriber->join_date;
		$last_act_datetime = date_i18n( __( "d/m/Y \h.H:i", "alo-easymail" ), strtotime( $last_act ) );
		$last_act_diff = sprintf( __( "%s ago", "alo-easymail" ), human_time_diff( strtotime( $last_act ), current_time('timestamp') ) );
		//$last_ip_addr = ' @ IP: '. ( !empty($subscriber->ip_address) ? $subscriber->ip_address : '?' );
		//$html .= $last_act_diff ." <img src=\"".ALO_EM_PLUGIN_URL."/images/12-clock.png\" class=\"clock\" title=\"". esc_attr($last_act_datetime . $last_ip_addr) ."\" alt=\"(". $last_act_datetime .")\" />\n";
		$html .= "<abbr title=\"". esc_attr($last_act_datetime) ."\" />". $last_act_diff ."</abbr>\n";
		if ( !empty($subscriber->ip_address) ) {
			$last_ip_addr = preg_replace( '/[^0-9a-fA-F:., ]/', '', $subscriber->ip_address );
			$html .= "<br /><a href=\"http://www.whatismyipaddress.com/ip/$last_ip_addr\" title=\"". esc_attr( $last_ip_addr .' @ whatismyipaddress.com') ."\" target=\"_blank\" class=\"ip-address\"/>IP ". $last_ip_addr ."</abbr>\n";
		}
		$html .= "</td>\n";
				
		$html .= "<td class=\"subscriber-active\">\n";
		if ( $edit ) {
			$active_checked = ($subscriber->active == 1) ? " checked=\"checked\" ": "";
			$html .= "<input type=\"checkbox\" id=\"subscriber-". $subscriber_id ."-active-new\" name=\"subscriber-". $subscriber_id ."-active-new\" class=\"subscriber-active-new\" $active_checked />\n";
		} else {
			$html .= "<img src=\"".ALO_EM_PLUGIN_URL."/images/".( ($subscriber->active == 1) ? "yes.png":"no.png" ) ."\" />\n";
		}
		$html .= "</td>\n";
		
        
       	$html .= "<td class=\"subscriber-lists\">\n";	
		$user_lists = alo_em_get_user_mailinglists ( $subscriber_id );
		if ( $edit && is_array( $all_lists ) ) {
			foreach ( $all_lists as $list => $val ) {
				$checked = ( is_array( $user_lists ) && in_array( $list, $user_lists ) ) ? " checked=\"checked\" " : "";
				$html .= "<input type=\"checkbox\" name=\"subscriber-". $subscriber_id ."-lists-new[]\" class=\"subscriber-lists-new subscriber-". $subscriber_id ."-lists-new\" id=\"subscriber-". $subscriber_id ."-lists-new_". $list ."\" value=\"". $list ."\" $checked /><label for=\"subscriber-". $subscriber_id ."-lists-new_". $list ."\">". alo_em_translate_multilangs_array ( alo_em_get_language(), $val['name'], true )."</label><br />\n";
			} 

		} else {
    		if ( $user_lists && is_array ( $user_lists ) && $all_lists ) {
    			$html .= "<ul class=\"userlists\">\n";     			
    			foreach ( $user_lists as $user_list ) {
	    			$html .= "<li>" . alo_em_translate_multilangs_array ( alo_em_get_language(), $all_lists[$user_list]["name"], true ) . "</li>\n";
	    		}
	    		$html .= "</ul>\n";
    		}
		}
		$html .= "&nbsp;</td>\n";

		$html .= "<td class=\"subscriber-lang\">\n";
		if ( $edit && is_array( $all_langs ) && !empty( $all_langs[0] ) ) {
			$html .= "<select id=\"subscriber-". $subscriber_id ."-lang-new\" name=\"subscriber-". $subscriber_id ."-lang-new\">\n";
			$html .= "<option value=\"\"></option>\n";
			foreach ( $all_langs as $key => $val ) {
				$selected = ( $subscriber->lang == $val ) ? " selected=\"selected\" " : "";
				$lang_name = esc_html ( alo_em_get_lang_name ( $val ) );
				$html .= "<option value=\"".$val."\" ".$selected.">". $lang_name ."</option>\n";
			} 
			$html .= "</select>\n";		
		} else {
			$html .= ( $subscriber->lang ) ? alo_em_get_lang_flag( $subscriber->lang, 'name') : "";
		}		
		$html .= "&nbsp;</td>\n";
		        
				
		$html .= "<td class=\"subscriber-actions\">\n"; // Actions   	
		$html .= "<img src=\"". ALO_EM_PLUGIN_URL. "/images/wpspin_light.gif\" style=\"display:none;vertical-align: middle;\" id=\"easymail-subscriber-". $subscriber_id ."-actions-loading\" />\n"; 			
		if ( $edit ) {
			$html .= " <a href=\"\" title=\"". esc_attr( __("Cancel", "alo-easymail") )."\" class=\"easymail-subscriber-edit-inline-cancel\" id=\"easymail-subscriber-edit-inline-cancel_{$subscriber_id}\" rel=\"{$subscriber_id}\">";
			$html .= "<img src=\"".ALO_EM_PLUGIN_URL."/images/no.png\" /></a>\n";

			$html .= " <a href=\"\" title=\"". esc_attr( __("Save", "alo-easymail") )."\" class=\"easymail-subscriber-edit-inline-save\" id=\"easymail-subscriber-edit-inline-save_{$subscriber_id}\" rel=\"{$subscriber_id}\">";
			$html .= "<img src=\"".ALO_EM_PLUGIN_URL."/images/yes.png\" /></a>\n";
			
		} else {		
    		$html .= "<a href=\"\" title=\"". esc_attr( __("Quick edit", "alo-easymail") )."\" class=\"easymail-subscriber-edit-inline\" id=\"easymail-subscriber-edit-inline_{$subscriber_id}\" rel=\"{$subscriber_id}\">";
		    $html .= "<img src=\"".ALO_EM_PLUGIN_URL. "/images/16-edit.png\" alt=\"". esc_attr( __("Quick edit", "alo-easymail") )."\" /></a>";    
			
			$html .= " <a href=\"\" title=\"". esc_attr( __("Delete subscriber", "alo-easymail") )."\" class=\"easymail-subscriber-delete\" id=\"easymail-subscriber-delete_{$subscriber_id}\" rel=\"{$subscriber_id}\">";
			$html .= "<img src=\"".ALO_EM_PLUGIN_URL."/images/trash.png\" alt=\"". esc_attr( __("Delete subscriber", "alo-easymail") )."\" /></a>";

			$html .= " <a href=\"\" title=\"". esc_attr( __("Delete subscriber and add the email to the list of who unsubscribed", "alo-easymail") )."\" class=\"easymail-subscriber-delete  and-unsubscribe\" id=\"easymail-subscriber-delete-and-unsubscribe_{$subscriber_id}\" rel=\"{$subscriber_id}\">";
			$html .= "<img src=\"".ALO_EM_PLUGIN_URL."/images/trash_del.png\" alt=\"". esc_attr( __("Delete subscriber and add the email to the list of who unsubscribed", "alo-easymail") )."\" /></a>";							
		}	
		$html .= "</td>\n";		
	return $html;
}


/*************************************************************************
 * CUSTOM FIELDS
 *************************************************************************/ 


/**
 * Prepare and return the custom field array built by filters
 *
 * @return arr|false
 */
 
function alo_easymail_get_custom_fields () {
	$fields = false;
	$fields = apply_filters ( 'alo_easymail_newsletter_set_custom_fields', $fields );
	if ( !empty($fields) && is_array($fields) )
	{
		foreach ( $fields as $key => $value )
		{
			// Defaults for each field
			$defaults = array(
				'humans_name' 	=> $key,
				'sql_attr' 		=> "VARCHAR(100) DEFAULT NULL",
				'sql_key'		=> false,
				'input_type' 	=> "text",
				'input_options'	=> false,
				'input_mandatory'=>false,
				'input_validation'=> false,
				'input_attr'	=> ""
			);
			$fields[$key] = wp_parse_args( $value, $defaults );
		}
	}
	else
	{
		$fields = false;
	}
	return $fields;
}



/**
 * Get the edit html of a custom field
 *
 * @param	str		the field key
 * @param	arr		the field array
 * @param	arr		the name of html element
 * @param	str		the preset value
 * @param	bol		edit or view
 * @param	str		js when input is blured (or changed)
 * @return	html
 */
 
function alo_easymail_custom_field_html ( $key, $field, $input_name="", $value="", $edit=false, $js_onblur="" ) {
	if ( empty($key) ||  empty($field) ) return "";
	$field_id = empty($input_name) ? "alo_em_".$key : $input_name;
	$input = "";
	if (isset($field['input_type']) )
	{
		switch ( $field['input_type'] )
		{
			case 'select':
				if ( $edit )
				{
					$input .= "<select id=\"$field_id\" name=\"$field_id\" class=\"input-select\" {$field['input_attr']} onchange=\"$js_onblur\">\n";
					if ( isset($field['input_options']) && is_array($field['input_options']) )
					{
						foreach ( $field['input_options'] as $k => $v )
						{
							$selected = $value == $k ? "selected=\"selected\"" : "";
							$input .= "<option value=\"$k\" $selected>".esc_html(__($v, "alo-easymail"))."</option>\n";
						}
					}
					$input .= "</select>\n";
				}
				else
				{
					$input .= isset($field['input_options'][$value]) ? esc_html($field['input_options'][$value]) : esc_html($value);
				}
				break;

			/*
			case 'checkbox':
				if ( $edit )
				{
					$checked = $value == 1 ? "checked=\"checked\"" : "";
					$input .= "<input type=\"checkbox\" id=\"$field_id\" name=\"$field_id\" class=\"input-checkbox\" value=\"1\" $checked {$field['input_attr']} onblur=\"$js_onblur\" />\n";
				}
				else
				{
					$input .= "<img src=\"".ALO_EM_PLUGIN_URL."/images/".( ($value == '1') ? "yes.png":"no_grey.png" ) ."\"";
					$input .= " alt=\"".( ($value == '1') ? __("yes", "alo-easymail"):__("no", "alo-easymail") ) ."\" />\n";
				}				
				break;
			*/
										
			case 'textarea':
				if ( $edit )
				{
					$input .= "<textarea id=\"$field_id\" name=\"$field_id\" class=\"input-textarea\" {$field['input_attr']} onblur=\"$js_onblur\">".format_to_edit($value)."</textarea>\n";
				}
				else
				{
					$input .= esc_html($value);
				}
				break;
				
			default:
			case 'text':
				if ( $edit )
				{
					$input .= "<input type=\"text\" id=\"$field_id\" name=\"$field_id\" class=\"input-text\" value=\"".esc_attr($value)."\" {$field['input_attr']} onblur=\"$js_onblur\" onkeydown=\"if(window.event) {keynum = event.keyCode;} else if(event.which) {keynum = event.which;}; if (keynum==13) { $js_onblur; return false;}\" />\n";
				}
				else
				{
					$input .= esc_html($value);
				}
				break;

		}
	}
	return $input;
}


/**
 * Get a checkbox list of WP roles, useful in options
 *
 * @param	str		name field
 * @param	arr		the caps to search in roles
 * @param	str		more attributes
 * @return	html
 *
 * echo alo_em_role_checkboxes( 'roles', 'publish_posts' );
 */
 
function alo_em_role_checkboxes ( $name='roles', $search_caps=array(), $attrs='' ) {
	
	settype( $search_caps, 'array' );
	
	if ( empty($search_caps[0]) ) return '';
	
	$get_editable_roles = get_editable_roles();

	$html = '';
	
	foreach ($get_editable_roles as $role => $val )
	{
		// Search the req caps in role caps
		$has_caps = array_intersect( array_keys($val['capabilities']), $search_caps );
		// Compare the reordered arrays: must be identical
		sort( $search_caps );
		sort( $has_caps );
		$checked = ( $search_caps == $has_caps )? 'checked="checked"' : '';

		// Admin always checked
		$disabled = ( $role == 'administrator' ) ? 'disabled="disabled"' : '';
		
		$html .= '<input type="checkbox" name="'.$name.'[]" id="'.$name.'-'.$role.'" value="'.$role.'" '.$checked .' '.$attrs.' '. $disabled.' /> ';
		$html .= '<label for="'.$name.'-'.$role.'">'.$val['name'].'</label><br />';
	}
	//$html .='<pre>'. print_r( $get_editable_roles, true ) .'</pre>'; // debug		
	return $html;
}


/*************************************************************************
 * BOUNCES
 *************************************************************************/ 

/**
 * Get bounce settings
 * 
 * @return array
 */
function alo_em_bounce_settings () {
	$bounce_defaults = array(
		'bounce_email'		=> '',
		'bounce_host' 		=> '',
		'bounce_port'		=> 143,
		'bounce_protocol'	=> 'imap', // or 'pop3'
		'bounce_folder'		=> '',
		'bounce_username' 	=> '',
		'bounce_password' 	=> '',
		'bounce_flags' 		=> '', 	// optional: e.g.: /ssl/novalidate-cert
		'bounce_maxmsg'		=> 30,	// max number of msgs will be examinated per batch
		'bounce_interval'	=> 6,	// auto check bounces every N hours
	);
	$bounce_saved = get_option('alo_em_bounce_settings');
	
	return wp_parse_args( $bounce_saved, $bounce_defaults );
}


/**
 * Add custom headers for bounce purpose in newsletters
 * 
 * @return str
 */
function alo_em_add_custom_headers ( $headers, $newsletter, $recipient ) {
	
	if ( !empty($newsletter->ID) ) $headers .= "X-ALO-EM-Newsletter: " . $newsletter->ID . "\n";
	if ( !empty($recipient->ID) ) $headers .= "X-ALO-EM-Recipient: " . $recipient->ID . "\n";

	return $headers;
}
add_filter( 'alo_easymail_newsletter_headers', 'alo_em_add_custom_headers', 100, 3 );


/**
 * Make an IMAP connection using settings
 * 
 * @return mix		the IMAP stream or FALSE if connection attempt fails
 */
function alo_em_bounce_connect () {
	$bounce_settings = alo_em_bounce_settings ();
	return @imap_open("{" . $bounce_settings['bounce_host'] .':'.$bounce_settings['bounce_port']. "/". $bounce_settings['bounce_protocol'] . $bounce_settings['bounce_flags'] . "}" . $bounce_settings['bounce_folder'], $bounce_settings['bounce_username'], $bounce_settings['bounce_password'] );
}
	

/**
 * Handle bounces (manually or via cron)
 * 
 * The function is pluggable: you can write your better function, and pleas share it :)
 * 
 * @param	str		type of final rerport: none, a text msg, an email
 */

if ( ! function_exists('alo_em_handle_bounces') )
{
	function alo_em_handle_bounces ( $report=false ) 
	{
		global $wpdb;
		
		$output = '';
		
		$bounce_settings = alo_em_bounce_settings ();
		
		$conn = alo_em_bounce_connect();

		if ( ! $conn ) return FALSE;
		
		$num_msgs = imap_num_msg($conn);

		// start bounce class
		require_once('inc/bouncehandler/bounce_driver.class.php');
		
		$bouncehandler = new Bouncehandler();

		// get the failures
		$email_addresses = array();
		$delete_addresses = array();
		
		$max_msgs = min( $num_msgs, $bounce_settings['bounce_maxmsg'] );
		
		if ( $report ) $output .= 'Bounces handled in: '. $bounce_settings['bounce_email'];
		
		for ( $n=1; $n <= $max_msgs; $n++ ) 
		{
			$msg_headers = imap_fetchheader($conn, $n);
			$msg_body = imap_body($conn, $n);
			
			$bounce = $msg_headers . $msg_body; //entire message
			
			$multiArray = $bouncehandler->get_the_facts($bounce);
			
			if (!empty($multiArray[0]['action']) && !empty($multiArray[0]['status']) && !empty($multiArray[0]['recipient']) ) 
			{
				if ( $report ) $output .= '<br /> - MSG #'. $n .' - Bounce response: '. $multiArray[0]['action'];
				
				// If delivery permanently failed, unsubscribe
				if ( $multiArray[0]['action']=='failed' ) 
				{
					$email = trim( $multiArray[0]['recipient'] );

					// Unsubscribe email address
					if ( $s_id = alo_em_is_subscriber( $email ) )
					{
						alo_em_delete_subscriber_by_id( $s_id );	

						do_action ( 'alo_easymail_bounce_email_unsubscribed', $email ); // Hook
						if ( $report ) $output .= ' - '. $email .' UNSUBSCRIBED';			
					}
				}
				
				// If delivery temporary or permanently failed, mark recipient as bounced
				if ( $multiArray[0]['action']=='failed' || $multiArray[0]['action']=='transient' || $multiArray[0]['action']=='autoreply' ) 	
				{
					
					// TODO maybe use: $bouncehandler->x_header_search_1 = 'ALO-EM-Newsletter';
					
					
					// Look fo EasyMail custom headers: Newsletter and Recipient
					// NOTE: searching in body because IDs are inside original message included in body
					$newsletter_id = 0;
					$recipient_id = 0;
					if ( preg_match('/X-ALO-EM-Newsletter: (\d+)/i', $bounce, $matches) )
					{ 
						if ( !empty($matches[1]) && is_numeric( $matches[1] ) ) $newsletter_id = (int)$matches[1];
					}
					if ( preg_match('/X-ALO-EM-Recipient: (\d+)/i', $bounce, $matches) )
					{ 
						if ( !empty($matches[1]) && is_numeric( $matches[1] ) ) $recipient_id = (int)$matches[1];
					}
					
					// Mark recipient as bounced only if not a debug to author
					if ( $newsletter_id > 0 && $recipient_id > 0 && strpos($msg_headers, "( DEBUG - TO: ") === false) 
					{					
						$wpdb->update( "{$wpdb->prefix}easymail_recipients",
							array ( 'result' => -3 ),
							array ( 'ID' => $recipient_id, 'newsletter' => $newsletter_id, 'email' => $email )
						);
					}
					
					if ( $report ) $output .= ' - Recipient ID #'. $recipient_id .' marked as not delivered';
					
					// mark msg for deletion
					imap_delete($conn, $n);
				}
								
			} //if passed parsing as bounce
			else
			{
				if ( $report ) $output .= '<br /><span class="description"> - MSG #'. $n .' - Not a bounce</span>';
			}
			
		} //for loop
		

		// delete messages
		imap_expunge($conn);

		// close
		imap_close($conn);
		
		if ( $report ) return $output;
		
	}

} // ( ! function_exists('alo_em_handle_bounces') )


/* EOF */
