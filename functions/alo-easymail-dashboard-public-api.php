<?php if ( !defined('ABSPATH') ) die(); // If this file is called directly, abort.

/**
 * Public API: functions for everybody
 *
 * @package WordPress
 * @subpackage ALO EasyMail plugin
 */


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
 * @link for other args see: http://codex.wordpress.org/Template_Tags/get_posts
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


/* EOF */