<?php


/*******************************************************************************
 * 
 * Add Category taxonomy to newsletters
 *
 * @since: 2.4.15 
 *
 ******************************************************************************/
 
function custom_easymail_add_categories ( $args ) {
	$args['taxonomies'] = array( 'category' );
	return $args;
}

add_filter ( 'alo_easymail_register_newsletter_args', 'custom_easymail_add_categories' );


/* EOF */
