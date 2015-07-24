<?php

/*******************************************************************************
 * 
 * Latest posts placeholder: [LATEST-POSTS]
 *
 * The following set of functions adds a new placeholder that includes the latest 
 * published posts inside newsletter
 *
 * @since: 2.0
 *
 ******************************************************************************/


/**
 * Add placeholder to table in new/edit newsletter screen
 *
 */
function custom_easymail_placeholders ( $placeholders ) {
	$placeholders["custom_latest"] = array (
		"title" 		=> __("Latest posts", "alo-easymail"),
		"tags" 			=> array (
			"[LATEST-POSTS]"		=> __("A list with the latest published posts", "alo-easymail").". ".__("The visit to this url will be tracked.", "alo-easymail")
		)
	);
	return $placeholders;
}
add_filter ( 'alo_easymail_newsletter_placeholders_table', 'custom_easymail_placeholders' );


/**
 * Add selects in placeholders table
 * 
 * Note that the hook name is based upon the name of placeholder given in previous function as index:
 * alo_easymail_newsletter_placeholders_title_{your_placeholder}
 * If placeholder is 'my_archive' the hook will be:
 * alo_easymail_newsletter_placeholders_title_my_archive
 *
 */
function custom_easymail_placeholders_title_custom_latest ( $post_id ) {
	echo __("Select how many posts", "alo-easymail"). ": ";	
	echo '<select name="placeholder_custom_latest" id="placeholder_custom_latest" >';
	for ( $i = 3; $i <= 10; $i++ ) {
	    $select_custom_latest = ( get_post_meta ( $post_id, '_placeholder_custom_latest', true) == $i ) ? 'selected="selected"': '';
	    echo '<option value="'.$i.'" '. $select_custom_latest .'>'. $i. '</option>';
	}
	echo '</select><br />';

	$cat_args = array(
		'show_option_all' 	=> 	esc_html( '(no, all categories)' ),
		'name' 				=>	'placeholder_custom_latest_cat'
	);
	if ( $select_custom_latest_cat = get_post_meta ( $post_id, '_placeholder_custom_latest_cat', true ) ) {
		$cat_args['selected'] =  (int)$select_custom_latest_cat;
	} 
	echo __("Filter by category", "alo-easymail"). ": ";	
	wp_dropdown_categories( $cat_args );	
}
add_action('alo_easymail_newsletter_placeholders_title_custom_latest', 'custom_easymail_placeholders_title_custom_latest' );


/**
 * Save latest post number when the newsletter is saved
 */
function custom_save_placeholder_custom_latest ( $post_id ) {
	if ( isset( $_POST['placeholder_custom_latest'] ) && is_numeric( $_POST['placeholder_custom_latest'] ) ) {
		update_post_meta ( $post_id, '_placeholder_custom_latest', $_POST['placeholder_custom_latest'] );
	}
	if ( isset( $_POST['placeholder_custom_latest_cat'] ) && is_numeric( $_POST['placeholder_custom_latest_cat'] ) ) {
		update_post_meta ( $post_id, '_placeholder_custom_latest_cat', $_POST['placeholder_custom_latest_cat'] );
	}	
} 
add_action('alo_easymail_save_newsletter_meta_extra', 'custom_save_placeholder_custom_latest' );


/**
 * Replace the placeholder when the newsletter is sending 
 * @param	str		the newsletter text
 * @param	obj		newsletter object, with all post values
 * @param	obj		recipient object, with following properties: ID (int), newsletter (int: recipient ID), email (str), result (int: 1 if successfully sent or 0 if not), lang (str: 2 chars), unikey (str), name (str: subscriber name), user_id (int/false: user ID if registered user exists), subscriber (int: subscriber ID), firstname (str: firstname if registered user exists, otherwise subscriber name)
 * @param	bol    	if apply "the_content" filters: useful to avoid recursive and infinite loop
 */ 
function custom_easymail_placeholders_get_latest ( $content, $newsletter, $recipient, $stop_recursive_the_content=false ) {  
	if ( !is_object( $recipient ) ) $recipient = new stdClass();
	if ( empty( $recipient->lang ) ) $recipient->lang = alo_em_short_langcode ( get_locale() );
	$limit = get_post_meta ( $newsletter->ID, '_placeholder_custom_latest', true );
	$categ = get_post_meta ( $newsletter->ID, '_placeholder_custom_latest_cat', true );
	$latest = "";
	if ( $limit ) {
		$args = array( 'numberposts' => $limit, 'order' => 'DESC', 'orderby' => 'date' );
		if ( (int)$categ > 0 ) $args['category'] = $categ;
		$myposts = get_posts( $args );
		if ( $myposts ) :
			$latest .= "<ul>\r\n";
			foreach( $myposts as $post ) :	// setup_postdata( $post );
				$post_title = stripslashes ( alo_em_translate_text ( $recipient->lang, $post->post_title, $post->ID, 'post_title' ) );

				$post_link = alo_em_translate_url( $post->ID, $recipient->lang );
				$trackable_post_link = alo_em_make_url_trackable ( $recipient, $post_link );
				
	   			$latest .= "<li><a href='". $trackable_post_link . "'>". $post_title ."</a></li>\r\n"; 
			endforeach; 
			$latest .= "</ul>\r\n";
		endif;	     
	} 
	$content = str_replace("[LATEST-POSTS]", $latest, $content);
   
	return $content;	
}
add_filter ( 'alo_easymail_newsletter_content',  'custom_easymail_placeholders_get_latest', 10, 4 );


/* EOF */
