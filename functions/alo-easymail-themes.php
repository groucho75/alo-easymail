<?php if ( !defined('ABSPATH') ) die(); // If this file is called directly, abort.

/**
 * Dashboard related newsletter themes
 *
 * @package WordPress
 * @subpackage ALO EasyMail plugin
 */


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



/* EOF */