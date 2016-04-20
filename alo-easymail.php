<?php
/*
Plugin Name: ALO EasyMail Newsletter
Plugin URI: http://www.eventualo.net/blog/wp-alo-easymail-newsletter/
Description: To send newsletters. Features: collect subscribers on registration or with an ajax widget, mailing lists, cron batch sending, multilingual.
Version: 2.9.0
Author: Alessandro Massasso
Author URI: http://www.eventualo.net
Text Domain: alo-easymail
Domain Path: /languages
@contributor Wojtek Szałkiewicz (wojtek@szalkiewicz.pl)
*/
/*  Copyright 2015  Alessandro Massasso  (email : alo AT eventualo DOT net)
    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.
    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

// If this file is called directly, abort.
if ( !defined('ABSPATH') ) die();


/**
 * Cron interval in minutes (default: 5)
 * If you like to modify the interval, add following line in your wp-config.php:	
 * define( "ALO_EM_INTERVAL_MIN", 8 );
 * NOTE: to apply the change you need to reactivate the plugin!
 */
if ( !defined( 'ALO_EM_INTERVAL_MIN' ) ) define( "ALO_EM_INTERVAL_MIN", 5 );


/**
 * Update when DB tables change
 */
define( "ALO_EM_DB_VERSION", 2020 );


/**
 * Other stuff
 */
define( "ALO_EM_PLUGIN_DIR", basename( dirname(__FILE__) ) );
define( "ALO_EM_PLUGIN_URL", untrailingslashit( plugin_dir_url(__FILE__) ) );
define( "ALO_EM_PLUGIN_ABS", untrailingslashit( plugin_dir_path(__FILE__) ) );


/**
 * Be sure to load WPML API
 */
function alo_em_load_wpml_api_support() {
	if ( !defined( 'WPML_LOAD_API_SUPPORT' ) ) define ( 'WPML_LOAD_API_SUPPORT', true );
}
add_action('wpml_loaded', 'alo_em_load_wpml_api_support');


/**
 * Required files
 */
require_once( ALO_EM_PLUGIN_ABS.'/functions/alo-easymail-helpers.php' );
require_once( ALO_EM_PLUGIN_ABS.'/functions/alo-easymail-newsletters.php' );
require_once( ALO_EM_PLUGIN_ABS.'/functions/alo-easymail-recipients.php' );
require_once( ALO_EM_PLUGIN_ABS.'/functions/alo-easymail-subscribers.php' );
require_once( ALO_EM_PLUGIN_ABS.'/functions/alo-easymail-placeholders.php' );
require_once( ALO_EM_PLUGIN_ABS.'/functions/alo-easymail-lists.php' );
require_once( ALO_EM_PLUGIN_ABS.'/functions/alo-easymail-tracking.php' );
require_once( ALO_EM_PLUGIN_ABS.'/functions/alo-easymail-multilingual.php' );
require_once( ALO_EM_PLUGIN_ABS.'/functions/alo-easymail-bounces.php' );
require_once( ALO_EM_PLUGIN_ABS.'/functions/alo-easymail-custom-fields.php' );
require_once( ALO_EM_PLUGIN_ABS.'/functions/alo-easymail-dashboard-public-api.php' );
require_once( ALO_EM_PLUGIN_ABS.'/functions/alo-easymail-themes.php' );
require_once( ALO_EM_PLUGIN_ABS.'/functions/alo-easymail-queue.php' );
require_once( ALO_EM_PLUGIN_ABS.'/functions/alo-easymail-widget.php' );
require_once( ALO_EM_PLUGIN_ABS.'/functions/alo-easymail-schedule.php' );
require_once( ALO_EM_PLUGIN_ABS.'/functions/alo-easymail-newsletter-cpt.php' );
require_once( ALO_EM_PLUGIN_ABS.'/functions/alo-easymail-frontend.php' );
require_once( ALO_EM_PLUGIN_ABS.'/functions/alo-easymail-users.php' );
require_once( ALO_EM_PLUGIN_ABS.'/functions/alo-easymail-mailer.php' );
require_once( ALO_EM_PLUGIN_ABS.'/functions/alo-easymail-router.php' );

if ( is_admin() ) {
	require_once(ALO_EM_PLUGIN_ABS . '/functions/alo-easymail-dashboard.php');
	require_once(ALO_EM_PLUGIN_ABS . '/functions/alo-easymail-dashboard-helps.php');
	require_once(ALO_EM_PLUGIN_ABS . '/functions/alo-easymail-dashboard-tinymce.php');
}


/**
 * File including custom hooks. See plugin homepage or inside that file for more info.
 *
 * @deprecated: Use files in /alo-easymail/mu-plugins folder
 */
if ( @file_exists ( ALO_EM_PLUGIN_ABS.'/alo-easymail_custom-hooks.php' ) ) {
	include ( ALO_EM_PLUGIN_ABS. '/alo-easymail_custom-hooks.php' );
}

/**
 * Set plugin i18n text domain.
 */
function alo_em_load_textdomain() {
	load_plugin_textdomain ("alo-easymail", false, dirname( plugin_basename( __FILE__ ) ) . '/languages');
}
add_action( 'plugins_loaded', 'alo_em_load_textdomain' );


/**
 * On plugin activation 
 */
function alo_em_install() {
    global $wpdb, $wp_roles;

	alo_em_load_textdomain();

	if (!get_option('alo_em_list')) add_option('alo_em_list', '');
    if (!get_option('alo_em_lastposts')) add_option('alo_em_lastposts', 10);
    if (!get_option('alo_em_dayrate')) add_option('alo_em_dayrate', 2000);
    if (!get_option('alo_em_batchrate')) add_option('alo_em_batchrate', 25);
    if (!get_option('alo_em_sleepvalue')) add_option('alo_em_sleepvalue', 0);
	if (!get_option('alo_em_sender_email')) {
		$admin_email = get_option('admin_email');
	    add_option('alo_em_sender_email', $admin_email);
	}
	if (!get_option('alo_em_sender_name')) {
		$sender_name = get_option('blogname');
	    add_option('alo_em_sender_name', $sender_name );
	}
		
	update_option('alo_em_import_alert', "show" );
	update_option('alo_em_timeout_alert', "show" );
	if (!get_option('alo_em_delete_on_uninstall')) add_option('alo_em_delete_on_uninstall', 'no');
	if (!get_option('alo_em_show_subscripage')) add_option('alo_em_show_subscripage', 'no');
	if (!get_option('alo_em_embed_css')) add_option('alo_em_embed_css', 'no');
	if (!get_option('alo_em_no_activation_mail')) add_option('alo_em_no_activation_mail', 'no');
	if (!get_option('alo_em_show_credit_banners')) add_option('alo_em_show_credit_banners', 'no');
	if (!get_option('alo_em_filter_br')) add_option('alo_em_filter_br', 'no');
	if (!get_option('alo_em_filter_the_content')) add_option('alo_em_filter_the_content', 'yes');
	if (!get_option('alo_em_use_tracking_pixel')) add_option('alo_em_use_tracking_pixel', 'yes');
	if (!get_option('alo_em_js_rec_list')) add_option('alo_em_js_rec_list', 'ajax_normal');
	if (!get_option('alo_em_use_themes')) add_option('alo_em_use_themes', 'yes');
	if (!get_option('alo_em_publish_newsletters')) add_option('alo_em_publish_newsletters', 'yes');
	if (!get_option('alo_em_hide_widget_users')) add_option('alo_em_hide_widget_users', 'no');
	if (!get_option('alo_em_unsubscribe_when_delete_user')) add_option('alo_em_unsubscribe_when_delete_user', 'no');
	if (!get_option('alo_em_hide_name_input')) add_option('alo_em_hide_name_input', 'no');
	if (!get_option('alo_em_collect_ip_address')) add_option('alo_em_collect_ip_address', 'no');

	if ( alo_em_db_tables_need_update() ) alo_em_install_db_tables();
	
	//-------------------------------------------------------------------------
	// Create/update the page with subscription
	
	// check if page already exists
	$my_page_id = get_option('alo_em_subsc_page');
	
	$my_page = array();
    $my_page['post_title'] = __( 'Newsletter', "alo-easymail" );
    $my_page['post_content'] = '[ALO-EASYMAIL-PAGE]';
    $my_page['post_status'] = 'publish';
    $my_page['post_author'] = 1;
    $my_page['comment_status'] = 'closed';
    $my_page['post_type'] = 'page';
    
    if ( !$my_page_id ) { // insert the post into the database
        $my_page_id = wp_insert_post( $my_page );
        update_option('alo_em_subsc_page', $my_page_id);
    }
    
    // add scheduled cleaner
    if( !wp_next_scheduled( 'alo_em_schedule' ) ) wp_schedule_event(time(), 'twicedaily', 'alo_em_schedule');
    // add scheduled cron batch
    if( !wp_next_scheduled( 'alo_em_batch' ) ) wp_schedule_event( time() +60, 'alo_em_interval', 'alo_em_batch' );
    
    // default permissions
	$wp_roles->add_cap( 'administrator', 'manage_newsletter_options');
	$wp_roles->add_cap( 'administrator', 'manage_newsletter_subscribers');		
	$wp_roles->add_cap( 'administrator', 'publish_newsletters');
	$wp_roles->add_cap( 'administrator', 'edit_newsletters');
	$wp_roles->add_cap( 'administrator', 'edit_others_newsletters');
	$wp_roles->add_cap( 'administrator', 'delete_newsletters');
	$wp_roles->add_cap( 'administrator', 'delete_others_newsletters');
	$wp_roles->add_cap( 'administrator', 'read_private_newsletters');

	$wp_roles->add_cap( 'editor', 'publish_newsletters');
	$wp_roles->add_cap( 'editor', 'edit_newsletters');
	$wp_roles->add_cap( 'editor', 'edit_others_newsletters');
	$wp_roles->add_cap( 'editor', 'delete_newsletters');
	$wp_roles->add_cap( 'editor', 'delete_others_newsletters');
}


/**
 * Since 3.1 the register_activation_hook is not called when a plugin
 * is updated, so to run the above code on automatic upgrade you need
 * to check the plugin db version on another hook.
 */
function alo_em_check_db_when_loaded() {
    if ( alo_em_db_tables_need_update() ) alo_em_install_db_tables();
}
add_action('plugins_loaded', 'alo_em_check_db_when_loaded');


/**
 * Install/update database tables
 */
function alo_em_install_db_tables() {
	global $wpdb;
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	
    //-------------------------------------------------------------------------
	
    if ( alo_em_db_tables_need_update()  ) {

	    $charset_collate = '';
	    if ( ! empty( $wpdb->charset ) ) {
		    $charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset}";
	    }
	    if ( ! empty( $wpdb->collate ) ) {
		    $charset_collate .= " COLLATE {$wpdb->collate}";
	    }

	    // Create the table structure
	    $sql = "CREATE TABLE {$wpdb->prefix}easymail_subscribers ( 
				    ID int(11) unsigned NOT NULL auto_increment , 
				    email varchar(100) NOT NULL , 
				    name varchar(100) NOT NULL , 
				    join_date datetime NOT NULL , 
				    active INT( 1 ) NOT NULL DEFAULT '0' , 
				    unikey varchar(24) NOT NULL , 
				    lists varchar(255) DEFAULT '|' , 
				    lang varchar(5) DEFAULT NULL , 
				    last_act datetime NULL ,
				    ip_address varchar(50) NULL , 
				    PRIMARY KEY  (ID) 
				) $charset_collate;

				CREATE TABLE {$wpdb->prefix}easymail_recipients ( 
					ID int(11) unsigned NOT NULL auto_increment , 
					newsletter int(11) unsigned NOT NULL , 
					email varchar(100) NOT NULL , 
					result varchar(3) NOT NULL DEFAULT '0' , 
					user_id int(11) unsigned DEFAULT NULL , 
					PRIMARY KEY  (ID) 
				) $charset_collate;

				CREATE TABLE {$wpdb->prefix}easymail_stats (
					ID int(11) unsigned NOT NULL auto_increment ,
					recipient int(11) unsigned NOT NULL ,
					newsletter int(11) unsigned NOT NULL ,
					added_on datetime NOT NULL ,
					request text ,
					PRIMARY KEY  (ID)
				) $charset_collate;
					
				CREATE TABLE {$wpdb->prefix}easymail_unsubscribed (
					email varchar(100) NOT NULL ,  
					added_on datetime NOT NULL ,
					PRIMARY KEY  (email)
				) $charset_collate;				
									
			    ";  
				
	    dbDelta($sql);
	    
		// Update the old "lists" field if upgrading from v. 1.x
		$installed_db = get_option('alo_em_db_version');
		if ( $installed_db < 2012 ) {
			$wpdb->query( "UPDATE ". $wpdb->prefix."easymail_subscribers SET lists = REPLACE( lists, '_', '|');" );
			$wpdb->query( "UPDATE {$wpdb->options} SET option_name = REPLACE( option_name, 'ALO_em_', 'alo_em_');" );
		}	
		// v.2016: Add table indexes    
		if ( $installed_db < 2016 ) {
			$wpdb->query("ALTER TABLE {$wpdb->prefix}easymail_recipients ADD INDEX ( `newsletter` ), ADD INDEX ( `email` )");
			$wpdb->query("ALTER TABLE {$wpdb->prefix}easymail_stats ADD INDEX ( `newsletter` ), ADD INDEX ( `recipient` )");
		}		
		// Add 'email' index only if not exists (it exists in plugin versions older than 2.3)
		if ( !$wpdb->get_row("SHOW INDEX FROM {$wpdb->prefix}easymail_subscribers WHERE Non_unique = 0 AND Column_name = 'email';" ) ) {
			$wpdb->query("ALTER TABLE {$wpdb->prefix}easymail_subscribers ADD UNIQUE ( `email` )");
		}
		// v.2017: Modify Request column, an index in new 'unsubscribed' table
		if ( $installed_db < 2017 ) {
			$wpdb->query("ALTER TABLE {$wpdb->prefix}easymail_stats CHANGE `request` `request` text");
		}
		// v.2019: the new 'last_act' column if empty has the same value of the 'join_date'
		if ( $installed_db < 2019 ) {
			$wpdb->query("UPDATE ". $wpdb->prefix."easymail_subscribers SET last_act = join_date WHERE last_act IS NULL;");
		}
		
		
	    update_option( "alo_em_db_version", ALO_EM_DB_VERSION );
    }
}


/**
 * Check if plugin tables are already properly installed
 */
function alo_em_db_tables_need_update() {
	global $wpdb;
	
	$installed_db = get_option('alo_em_db_version');
	
	$missing_table = false; // Check if tables not yet installed
	$tables = array ( $wpdb->prefix."easymail_subscribers", $wpdb->prefix."easymail_recipients", $wpdb->prefix."easymail_stats", $wpdb->prefix."easymail_unsubscribed" );
	foreach ( $tables as $table_name ) {
		if ( $wpdb->get_var("show tables like '$table_name'") != $table_name ) $missing_table = true;
	}
	return ( $missing_table || ALO_EM_DB_VERSION != $installed_db ) ? true : false;
}


/**
 * Manage plugin activation: on multisite and on standard (thanks to kzyz!)
 */
function alo_em_activate() {
	global $wpdb;

	if ( function_exists( 'is_multisite' ) && is_multisite() ) {
		// check if it is a network activation - if so, run the activation function for each blog id
		if ( isset( $_GET['networkwide'] ) && ( $_GET['networkwide'] == 1 ) ) {
			$old_blog = $wpdb -> blogid;
			// Get all blog ids
			$blogids = $wpdb -> get_col( $wpdb -> prepare( "SELECT blog_id FROM {$wpdb -> blogs}" ) );
			foreach ( $blogids as $blog_id ) {
				switch_to_blog( $blog_id );
				alo_em_install();
			}
			switch_to_blog( $old_blog );
		return;
		}
	}
	alo_em_install();
}
register_activation_hook(__FILE__,'alo_em_activate');




/**
 * On plugin deactivation
 */
function alo_em_uninstall() {
	global $wpdb, $wp_roles, $wp_version;
	
    // delete scheduled cleaner
    wp_clear_scheduled_hook('alo_em_schedule');
    wp_clear_scheduled_hook('ALO_em_schedule'); // old versions
    // delete cron batch sending
    wp_clear_scheduled_hook('alo_em_batch');
    wp_clear_scheduled_hook('ALO_em_batch'); // old versions
    // delete deprecated bounce cron
    wp_clear_scheduled_hook('alo_em_bounce_handle');
    
    // if required delete all plugin data (options, db tables, page)
   	if ( get_option('alo_em_delete_on_uninstall') == "yes" ) {
   		$tables = array ( "easymail_recipients", "easymail_subscribers", "easymail_stats", "easymail_sendings", "easymail_trackings", "easymail_unsubscribed" );
   		foreach ( $tables as $tab ) {
   			$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}$tab");
   		}

		// delete option from db
		$wpdb->query( "DELETE FROM {$wpdb->prefix}options WHERE option_name LIKE 'alo_em_%'" );

	    // delete subscription page
		if ( version_compare ( $wp_version , '2.9', '>=' ) ) {
			wp_delete_post( get_option('alo_em_subsc_page'), true ); // skip trash, from wp 2.9
		} else {
			wp_delete_post( get_option('alo_em_subsc_page') );
		}
		// and the option with page id
		delete_option ('alo_em_subsc_page');
		
		// reset cap
		$roles = $wp_roles->get_names(); // get a list of values, containing pairs of: $role_name => $display_name
		foreach ( $roles as $rolename => $key) {
			$wp_roles->remove_cap( $rolename, 'manage_newsletter_options');
			$wp_roles->remove_cap( $rolename, 'manage_newsletter_subscribers');		

			$wp_roles->remove_cap( $rolename, 'publish_newsletters');
			$wp_roles->remove_cap( $rolename, 'edit_newsletters');
			$wp_roles->remove_cap( $rolename, 'edit_others_newsletters');
			$wp_roles->remove_cap( $rolename, 'delete_newsletters');
			$wp_roles->remove_cap( $rolename, 'delete_others_newsletters');
			$wp_roles->remove_cap( $rolename, 'read_private_newsletters');

			// deprecated old caps to be removed
			$wp_roles->remove_cap( $rolename, 'manage_easymail_options');
			$wp_roles->remove_cap( $rolename, 'manage_easymail_subscribers');
		}		
	}
	
}


/**
 * Manage plugin de-activation: on multisite and on standard (thanks to kzyz!) 
 */
function alo_em_deactivate() {
	global $wpdb;

	if ( function_exists( 'is_multisite' ) && is_multisite() ) {
		// check if it is a network activation - if so, run the activation function for each blog id
		if ( isset( $_GET['networkwide'] ) && ( $_GET['networkwide'] == 1 ) ) {
			$old_blog = $wpdb->blogid;
			// Get all blog ids
			$blogids = $wpdb -> get_col( $wpdb -> prepare( "SELECT blog_id FROM {$wpdb -> blogs}" ) );
			foreach ( $blogids as $blog_id ) {
				switch_to_blog( $blog_id );
				alo_em_uninstall();
			}
			switch_to_blog( $old_blog );
			return;
		}
	}
	alo_em_uninstall();
}
register_deactivation_hook( __FILE__, 'alo_em_deactivate' );


/**
 * Plugin activation when new blog on multisite (thanks to kzyz!)
 */
function alo_em_new_blog( $blog_id ) {
	global $wpdb;

	if ( is_plugin_active_for_network( ALO_EM_PLUGIN_DIR. '/'. basename( __FILE__ ) ) ) {
		$old_blog = $wpdb -> blogid;
		switch_to_blog( $blog_id );
		alo_em_install();
		switch_to_blog( $old_blog );
	}
}
add_action( 'wpmu_new_blog', 'alo_em_new_blog' ); 


/**
 * On plugin init
 */
function alo_em_init_method() {
	// if required, exclude the easymail page from pages' list
	if ( get_option('alo_em_show_subscripage') == "no" ) add_filter('get_pages','alo_em_exclude_page');

	// Delete obsolete values of option: if no ajax, update to normal ajax mode
	if ( get_option('alo_em_js_rec_list') != "no_ajax_onsavepost" )  update_option('alo_em_js_rec_list', "ajax_normal");

	// Delete deprecated bounce cron and option
	if( wp_next_scheduled( 'alo_em_bounce_handle' ) ) {
		wp_clear_scheduled_hook('alo_em_bounce_handle');
	}
	if ( $bounce_settings = get_option('alo_em_bounce_settings') ) {
		if ( isset( $bounce_settings['bounce_password'] ) || isset( $bounce_settings['bounce_interval'] ) ) {
			unset( $bounce_settings['bounce_password'], $bounce_settings['bounce_interval'] );
			update_option('alo_em_bounce_settings', $bounce_settings);
			set_user_setting( 'alo_em_pointer_changed_bounce_setup', 0 );
		}
	}
}
add_action( 'init', 'alo_em_init_method' );


/**
 * If Duplicate Post is used: Do not duplicate EasyMail internal post meta
 */
function alo_em_when_duplicate_post( $new_post_id, $old_post_object ) {
	$exclude_meta = array( "_easymail_archived_recipients", "_easymail_completed", "_easymail_status", "_easymail_recipients" );
	foreach( $exclude_meta as $meta ) delete_post_meta ( $new_post_id, $meta );
}
add_action( "dp_duplicate_post", "alo_em_when_duplicate_post", 100, 2 );





/* EOF */
