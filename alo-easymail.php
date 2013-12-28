<?php
/*

Plugin Name: ALO EasyMail Newsletter
Plugin URI: http://www.eventualo.net/blog/wp-alo-easymail-newsletter/
Description: To send newsletters. Features: collect subcribers on registration or with an ajax widget, mailing lists, cron batch sending, multilanguage.
Version: 2.5.01
Author: Alessandro Massasso
Author URI: http://www.eventualo.net

*/
/*  Copyright 2013  Alessandro Massasso  (email : alo AT eventualo DOT net)
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
/*
	Modified by Wojtek Szałkiewicz (wojtek@szalkiewicz.pl)
*/

/*
 * Cron interval in minutes (default: 5)
 * If you like to modify the interval, add following line in your wp-config.php:	
 * define( "ALO_EM_INTERVAL_MIN", 8 );
 * NOTE: to apply the change you need to reactivate the plugin!
 */
if ( !defined( 'ALO_EM_INTERVAL_MIN' ) ) define( "ALO_EM_INTERVAL_MIN", 5 );

/**
 * Other stuff
 */
define( "ALO_EM_PLUGIN_DIR", basename( dirname(__FILE__) ) );
define( "ALO_EM_PLUGIN_URL", untrailingslashit( plugin_dir_url(__FILE__) ) );
define( "ALO_EM_PLUGIN_ABS", untrailingslashit( plugin_dir_path(__FILE__) ) );


if ( !defined( 'WPML_LOAD_API_SUPPORT' ) ) define ( 'WPML_LOAD_API_SUPPORT', true );	// be sure to load WPML API

/**
 * Required files
 */
global $wp_version;
if ( version_compare ( $wp_version , '3.1', '<' ) ) require_once( ABSPATH . WPINC .'/registration.php' );
require_once( 'alo-easymail_functions.php' );
require_once( 'alo-easymail-widget.php' );


/**
 * DEPRECATED: use files in /alo-easymail/mu-plugins folder
 * File including custom hooks. See plugin homepage or inside that file for more info.
 */
if ( @file_exists ( ALO_EM_PLUGIN_ABS.'/alo-easymail_custom-hooks.php' ) ) include ( ALO_EM_PLUGIN_ABS. '/alo-easymail_custom-hooks.php' );


// Update when DB tables change
define( "ALO_EM_DB_VERSION", 2020 );


/**
 * For batch sending (every tot mins)
 */
function alo_em_more_reccurences( $schedules ) {
	$schedules['alo_em_interval'] =	array(
		'interval' => 59*(ALO_EM_INTERVAL_MIN),
		'display' => 'EasyMail every ' .ALO_EM_INTERVAL_MIN. ' minutes'
	);
	
	// Set bounce schedule only if some bounce options are set
	$bounce_settings = alo_em_bounce_settings ();
	$bounce_interval = (int)$bounce_settings['bounce_interval'];
	if ( $bounce_interval > 0 && is_email($bounce_settings['bounce_email']) && !empty($bounce_settings['bounce_host']) )
	{
		$schedules['alo_em_bounce'] =	array(
			'interval' => 59 * 60 * $bounce_interval,
			'display' => 'EasyMail every ' .$bounce_interval. ' hours'
		);
	}
	return $schedules;
}
add_filter('cron_schedules', 'alo_em_more_reccurences', 300);


/**
 * To fix missing cron schedules
 */
function alo_em_check_cron_scheduled() {
    if( !wp_next_scheduled( 'alo_em_batch' ) ) {
		wp_schedule_event( time() +60, 'alo_em_interval', 'alo_em_batch' );
	}
    if( !wp_next_scheduled( 'alo_em_schedule' ) ) {
		wp_schedule_event(time(), 'twicedaily', 'alo_em_schedule');
	}
		
	// Schedule bounce events, if bounce schedule key exists
	if ( array_key_exists( 'alo_em_bounce', wp_get_schedules() ) )
	{
	   if( !wp_next_scheduled( 'alo_em_bounce_handle' ) ) {
			wp_schedule_event(time()+60, 'alo_em_bounce', 'alo_em_bounce_handle');
		}
	}
	
}
add_action('wp', 'alo_em_check_cron_scheduled');


// Schedule bounce events
add_action('alo_em_bounce_handle', 'alo_em_handle_bounces');


/**
 * On plugin activation 
 */
function alo_em_install() {
    global $wpdb, $wp_roles;
    
	if (!get_option('alo_em_template')) add_option('alo_em_template', 'Hi [USER-NAME],<br /><br />
	    I have published a new post <strong>[POST-TITLE]</strong>.<br />[POST-EXCERPT]<br />Please visit my site [SITE-LINK] to read it and leave your comment about it.<br />
        Hope to see you online!<br /><br />[SITE-LINK]');
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
	if (!get_option('alo_em_show_credit_banners')) add_option('alo_em_show_credit_banners', 'yes');
	if (!get_option('alo_em_filter_br')) add_option('alo_em_filter_br', 'no');
	if (!get_option('alo_em_filter_the_content')) add_option('alo_em_filter_the_content', 'yes');
	if (!get_option('alo_em_js_rec_list')) add_option('alo_em_js_rec_list', 'ajax_normal');
	if (!get_option('alo_em_use_themes')) add_option('alo_em_use_themes', 'yes');
	if (!get_option('alo_em_publish_newsletters')) add_option('alo_em_publish_newsletters', 'yes');
	if (!get_option('alo_em_hide_widget_users')) add_option('alo_em_hide_widget_users', 'no');
	
	alo_em_setup_predomain_texts( false );
		    	    
	if ( alo_em_db_tables_need_update() ) alo_em_install_db_tables();
	
	//-------------------------------------------------------------------------
	// Create/update the page with subscription
	
	// check if page already exists
	$my_page_id = get_option('alo_em_subsc_page');
	
	$my_page = array();
    $my_page['post_title'] = 'Newsletter';
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
	    
		if( defined( 'DB_COLLATE' ) && constant( 'DB_COLLATE' ) != '' ) {
			$collate = constant( 'DB_COLLATE' );
		} else {
			$collate = constant( 'DB_CHARSET' );
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
				) DEFAULT CHARSET=".$collate.";

				CREATE TABLE {$wpdb->prefix}easymail_recipients ( 
					ID int(11) unsigned NOT NULL auto_increment , 
					newsletter int(11) unsigned NOT NULL , 
					email varchar(100) NOT NULL , 
					result varchar(3) NOT NULL DEFAULT '0' , 
					user_id int(11) unsigned DEFAULT NULL , 
					PRIMARY KEY  (ID) 
				) DEFAULT CHARSET=".$collate.";

				CREATE TABLE {$wpdb->prefix}easymail_stats (
					ID int(11) unsigned NOT NULL auto_increment ,
					recipient int(11) unsigned NOT NULL ,
					newsletter int(11) unsigned NOT NULL ,
					added_on datetime NOT NULL ,
					request text ,
					PRIMARY KEY  (ID)
				) DEFAULT CHARSET=".$collate.";
					
				CREATE TABLE {$wpdb->prefix}easymail_unsubscribed (
					email varchar(100) NOT NULL ,  
					added_on datetime NOT NULL ,
					PRIMARY KEY  (email)
				) DEFAULT CHARSET=".$collate.";				
									
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
 * Clean the new subscription not yet activated after too much time
 */
function alo_em_clean_no_actived() {
	global $wpdb;
	// delete subscribes not yet activated after 5 days
	$limitdate = date ("Y-m-d",mktime(0,0,0,date("m"),date("d")-5,date("Y")));
    $output = $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}easymail_subscribers WHERE join_date <= '%s' AND active = '0'", $limitdate ) );
    //return $output;.
}

add_action('alo_em_schedule', 'alo_em_clean_no_actived');
add_action( 'alo_em_batch' , 'alo_em_batch_sending');


/**
 * On plugin adectivation 
 */
function alo_em_uninstall() {
	global $wpdb, $wp_roles, $wp_version;
	
    // delete scheduled cleaner
    wp_clear_scheduled_hook('alo_em_schedule');
    wp_clear_scheduled_hook('ALO_em_schedule'); // old versions
    // delete cron batch sending
    wp_clear_scheduled_hook('alo_em_batch');
    wp_clear_scheduled_hook('ALO_em_batch'); // old versions
    // delete optional bounce cron
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
 * Plugin activation whren new blog on multisite (thanks to kzyz!)
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
 * Add menu pages 
 */
function alo_em_add_admin_menu() {
  	if ( current_user_can('manage_newsletter_subscribers') )  {
  		add_submenu_page( 'edit.php?post_type=newsletter', __("Subscribers", "alo-easymail"), __("Subscribers", "alo-easymail"), 'manage_newsletter_subscribers', 'alo-easymail/alo-easymail_subscribers.php' );
  		add_action( 'load-alo-easymail/alo-easymail_subscribers.php', 'alo_em_contextual_help_tabs' );
  	}
    if ( current_user_can('manage_newsletter_options') ) {
		add_submenu_page( 'edit.php?post_type=newsletter', __("Settings"), __("Settings"), 'manage_newsletter_options', 'alo-easymail/alo-easymail_options.php' );
		add_action( 'load-alo-easymail/alo-easymail_options.php', 'alo_em_contextual_help_tabs' );
	}
	add_action( 'load-edit.php', 'alo_em_contextual_help_tabs' );
	add_action( 'load-post-new.php', 'alo_em_contextual_help_tabs' );
}

add_action('admin_menu', 'alo_em_add_admin_menu');


function alo_em_contextual_help_tabs() {
	if ( !class_exists('WP_Screen') ) return;
    $screen = get_current_screen();
	if ( !is_object($screen) ) return;
	if ( $screen->post_type != 'newsletter' ) return;
	
	// Main tab per page
	$tab = false;
	switch ( $screen->id ) {
		
		case 'alo-easymail/alo-easymail_subscribers':
			/* TODO!
			$tab = array(
				'id'      => 'alo-easymail_subscribers',
				'title'   => __("Subscribers", "alo-easymail"),
				'content' => '<p>This is the content for the tab.</p>' // TODO
				// Use 'callback' instead of 'content' for a function callback that renders the tab content.
			);
			*/
			break;
			
		case 'alo-easymail/alo-easymail_options':
			/* TODO!
			$tab = array(
				'id'      => 'alo-easymail_options',
				'title'   => __("Settings", "alo-easymail"),
				'content' => '<p>This is the content for the tab.</p>' // TODO
			);
			*/
			break;

		case 'edit-newsletter':
		case 'newsletter':
			$tab = array(
				'id'      => 'alo-easymail_newsletter',
				'title'   => __("Newsletter", "alo-easymail"),
				'content' => '<iframe width="720" height="450" src="http://www.youtube.com/embed/juglGC28T2g?rel=0" frameborder="0" allowfullscreen></iframe>'
			);
			break;
    }
		
    if ( is_array($tab) ) $screen->add_help_tab( $tab );

    // Common tab
    $screen->add_help_tab( array(
        'id'      => 'alo-easymail_links', // This should be unique for the screen.
        'title'   => __("Links"),
        'content' => '<p>'.__("Resources about EasyMail Newsletter", "alo-easymail") . ': '.
			'<a href="http://www.eventualo.net/blog/wp-alo-easymail-newsletter/" target="_blank">homepage</a> |
			<a href="http://www.eventualo.net/blog/wp-alo-easymail-newsletter-guide/" target="_blank">guide</a> |
			<a href="http://www.eventualo.net/blog/wp-alo-easymail-newsletter-faq/" target="_blank">faq</a> |
			<a href="http://www.eventualo.net/blog/easymail-newsletter-for-developers/" target="_blank">for developers</a> |
			<a href="http://www.eventualo.net/forum/" target="_blank">forum</a> |
			<a href="http://www.eventualo.net/blog/category/alo-easymail-newsletter/" target="_blank">news</a> |
			<a href="http://wordpress.org/support/plugin/alo-easymail" target="_blank" title="tag alo-easymail @ wordpress.org support forum">WP forum</a>'. '</p>'
    ) );

    // Common sidebar
	$screen->set_help_sidebar(
		"<p style='text-align:center'>". __("If you use this plugin consider the idea of donating and supporting its development", "alo-easymail") ."</p><p>".
		"<form action='https://www.paypal.com/cgi-bin/webscr' method='post' style='display:inline;margin-left: 35px'>
		<input name='cmd' value='_s-xclick' type='hidden'><input name='lc' value='EN' type='hidden'><input name='hosted_button_id' value='9E6BPXEZVQYHA' type='hidden'>
		<input src='https://www.paypal.com/en_US/i/btn/btn_donate_SM.gif' name='submit' alt='Donate via PayPal' title='Donate via PayPal' border='0' type='image' style='vertical-align: middle'>
		<img src='https://www.paypal.com/it_IT/i/scr/pixel.gif' border='0' height='1' width='1'></form>" ."</p>"
	);

	if ( $screen->id == 'alo-easymail/alo-easymail_subscribers' ) {
		$screen->add_option( 
			'per_page', 
			array(
				'label' => __("subscribers per page", "alo-easymail"), 
				'default' => 20, 
				'option' => 'edit_per_page'
			) 
		);
	}

}


/**
 * Contextual help
 */
 if ( version_compare ( $wp_version , '3.3', '<' ) )
{
	function alo_em_contextual_help() {
		global $hook_suffix;
		if (function_exists('add_contextual_help')) {
			$html = __("Resources about EasyMail Newsletter", "alo-easymail") . ': <a href="http://www.eventualo.net/blog/wp-alo-easymail-newsletter/" target="_blank">homepage</a> |
					<a href="http://www.eventualo.net/blog/wp-alo-easymail-newsletter-guide/" target="_blank">guide</a> |
					<a href="http://www.eventualo.net/blog/wp-alo-easymail-newsletter-faq/" target="_blank">faq</a> |
					<a href="http://www.eventualo.net/blog/easymail-newsletter-for-developers/" target="_blank">for developers</a> |
					<a href="http://www.eventualo.net/forum/" target="_blank">forum</a> |
					<a href="http://www.eventualo.net/blog/category/alo-easymail-newsletter/" target="_blank">news</a> |
					<a href="http://wordpress.org/support/plugin/alo-easymail" target="_blank" title="tag alo-easymail @ wordpress.org support forum">WP forum</a>';
			$html .= " | <form action='https://www.paypal.com/cgi-bin/webscr' method='post' style='display:inline'>
				<input name='cmd' value='_s-xclick' type='hidden'><input name='lc' value='EN' type='hidden'><input name='hosted_button_id' value='9E6BPXEZVQYHA' type='hidden'>
				<input src='https://www.paypal.com/en_US/i/btn/btn_donate_SM.gif' name='submit' alt='Donate via PayPal' title='Donate via PayPal' border='0' type='image' style='vertical-align: middle'>
				<img src='https://www.paypal.com/it_IT/i/scr/pixel.gif' border='0' height='1' width='1'><br></form>";
			if ( $hook_suffix == 'alo-easymail/alo-easymail_options.php' ) {
				// extra help
			}
			if ( $hook_suffix == 'alo-easymail/alo-easymail_subscribers.php' ) {
				// extra help
			}		
			add_contextual_help( $hook_suffix, $html );
		}
	}
	add_action( 'admin_head-alo-easymail/alo-easymail_options.php', 'alo_em_contextual_help' );
	add_action( 'admin_head-alo-easymail/alo-easymail_subscribers.php', 'alo_em_contextual_help' );
}


/*
 * Add some links on the plugin page
 */
function alo_em_add_plugin_links($links, $file) {
	if ( $file == plugin_basename(__FILE__) ) {
		$links[] = '<a href="http://www.eventualo.net/blog/wp-alo-easymail-newsletter-guide/" target="_blank">Guide</a>';
		$links[] = '<a href="http://www.eventualo.net/blog/wp-alo-easymail-newsletter-faq/" target="_blank">Faq</a>';
		$links[] = '<a href="http://www.eventualo.net/forum/" target="_blank">Forum</a>';
		$links[] = '<a href="http://www.eventualo.net/blog/category/alo-easymail-newsletter/" target="_blank">News</a>';
		$links[] = '<a href="http://wordpress.org/support/plugin/alo-easymail" target="_blank" title="tag alo-easymail @ wordpress.org support forum">WP forum</a>';
	}
    return $links;
} 
add_filter( 'plugin_row_meta', 'alo_em_add_plugin_links', 10, 2 );



/**
 * On plugin init
 */
function alo_em_init_method() {
	// if required, exclude the easymail page from pages' list
	if ( get_option('alo_em_show_subscripage') == "no" ) add_filter('get_pages','alo_em_exclude_page');
	// load localization files
	load_plugin_textdomain ("alo-easymail", false, "alo-easymail/languages");
	
	// Let's install custom fields, if any
	global $wpdb;

	$alo_em_cf = alo_easymail_get_custom_fields();
	if ( $alo_em_cf )
	{
		$fields = $wpdb->get_results( "SHOW COLUMNS FROM {$wpdb->prefix}easymail_subscribers" );
		$existing = array();
		foreach ( $fields as $field ) $existing[] = $field->Field;

		foreach( $alo_em_cf as $key => $value )
		{
			// Create db column if missing
			if ( !in_array( $key, $existing ) )
			{			
				$wpdb->query("ALTER TABLE {$wpdb->prefix}easymail_subscribers ADD `".$key."` ". $value['sql_attr']);

			}
			// Create index if required
			if ( $value['sql_key'] && !$wpdb->get_row("SHOW INDEX FROM {$wpdb->prefix}easymail_subscribers WHERE Column_name = '".$key."';" ) ) {
				$wpdb->query("ALTER TABLE {$wpdb->prefix}easymail_subscribers ADD INDEX ( `".$key."` )");
			}	

		}

		// Get fields again, after previpus installation
		$fields = $wpdb->get_results( "SHOW COLUMNS FROM {$wpdb->prefix}easymail_subscribers" );
		$existing = array();
		foreach ( $fields as $field ) $existing[] = $field->Field;
		
		foreach( $alo_em_cf as $key => $value )
		{
			// Create index if required
			if ( in_array( $key, $existing ) && $value['sql_key'] && !$wpdb->get_row("SHOW INDEX FROM {$wpdb->prefix}easymail_subscribers WHERE Column_name = '".$key."';" ) )
			{
				$wpdb->query("ALTER TABLE {$wpdb->prefix}easymail_subscribers ADD INDEX ( `".$key."` )");
			}
		}
	}

	// Delete obsolete values of option: if no ajax, update to normal ajax mode
	if ( get_option('alo_em_js_rec_list') != "no_ajax_onsavepost" )  update_option('alo_em_js_rec_list', "ajax_normal");
}
add_action( 'init', 'alo_em_init_method' );


/**
 * New custom post type: Newsletter
 */
function alo_em_register_newsletter_type () {

	$labels = array(
		'name' => __( 'Newsletters', "alo-easymail" ),
		'singular_name' => __( 'Newsletter', "alo-easymail" ),
		'add_new' => __( 'Add New', "alo-easymail" ),
		'add_new_item' => __( 'Add New Newsletter', "alo-easymail" ),
		'edit_item' => __( 'Edit Newsletter', "alo-easymail" ),
		'new_item' => __( 'New Newsletter', "alo-easymail" ) ,
		'view_item' => __( 'View Newsletter', "alo-easymail" ),
		'search_items' => __( 'Search Newsletters', "alo-easymail" ),
		'not_found' =>  __( 'No Newsletters found', "alo-easymail" ),
		'not_found_in_trash' => __( 'No Newsletters found in Trash', "alo-easymail" ), 
		'parent_item_colon' => __( 'Parent Newsletter', "alo-easymail" ),
		'menu_name' => __( 'Newsletters', "alo-easymail" ),
		'parent' => __( 'Parent Newsletter', "alo-easymail" ),
	);
	$args = array(
		'labels' => $labels,
		'public' => true, 
		'publicly_queryable' => true,
		'show_ui' => true, 
		'show_in_menu' => true, 
		'query_var' => true,
		'exclude_from_search' => false,
		'rewrite' => array('slug' => 'newsletters'),

		//'capability_type' => 'post', // TODO vedi sotto

		
		// http://justintadlock.com/archives/2010/07/10/meta-capabilities-for-custom-post-types
		'capability_type' => 'newsletter',
		'capabilities' => array(
			'publish_posts' 	=> 'publish_newsletters',
			'edit_posts' 		=> 'edit_newsletters',
			'edit_others_posts'	=> 'edit_others_newsletters',
			'delete_posts' 		=> 'delete_newsletters',
			'delete_others_posts'=> 'delete_others_newsletters',
			'read_private_posts'=> 'read_private_newsletters',
			// DO not assign the next 3 caps to roles: will be mapped by filter
			'edit_post' 		=> 'edit_newsletter',
			'delete_post' 		=> 'delete_newsletter',
			'read_post' 		=> 'read_newsletter',
		),
		
			
		'has_archive' => true, 
		'hierarchical' => false,
		'menu_position' => false,
		'menu_icon' => ALO_EM_PLUGIN_URL.'/images/16-email-letter.png',
		'can_export' => true,
		'supports' => array( 'title' , 'editor', 'custom-fields', 'thumbnail' )
	); 
	// If it doesn't allow newsletter publication online
	if ( get_option('alo_em_publish_newsletters') == "no" ) {
		$args['public'] = false;
		$args['publicly_queryable'] = false;
		$args['show_ui'] = true;
		$args['show_in_menu'] = true;
		$args['query_var'] = false;
		$args['exclude_from_search'] = true; // TODO read here: http://jandcgroup.com/2011/09/14/exclude-custom-post-types-from-wordpress-search-do-not-use-exclude_from_search/
	}
	$args = apply_filters ( 'alo_easymail_register_newsletter_args', $args ); 
	register_post_type( 'newsletter', $args );
}
add_action('init', 'alo_em_register_newsletter_type');


/**
 * Filtering the map_meta_cap hook to know if user can do something
 *
 * http://justintadlock.com/archives/2010/07/10/meta-capabilities-for-custom-post-types
 */
function alo_em_map_meta_cap( $caps, $cap, $user_id, $args ) {

	// If editing, deleting, or reading an item, get the post and post type object.
	if ( 'edit_newsletter' == $cap || 'delete_newsletter' == $cap || 'read_newsletter' == $cap ) {
		$post = get_post( $args[0] );
		$post_type = get_post_type_object( $post->post_type );

		// Set an empty array for the caps.
		$caps = array();
	}

	// If editing assign the required capability. 
	if ( 'edit_newsletter' == $cap ) {
		if ( $user_id == $post->post_author )
			$caps[] = $post_type->cap->edit_posts;
		else
			$caps[] = $post_type->cap->edit_others_posts;
	}

	// If deleting, assign the required capability.
	elseif ( 'delete_newsletter' == $cap ) {
		if ( $user_id == $post->post_author )
			$caps[] = $post_type->cap->delete_posts;
		else
			$caps[] = $post_type->cap->delete_others_posts;
	}
	
	// If reading a private item, assign the required capability.
	elseif ( 'read_newsletter' == $cap ) {

		if ( 'private' != $post->post_status )
			$caps[] = 'read';
		elseif ( $user_id == $post->post_author )
			$caps[] = 'read';
		else
			$caps[] = $post_type->cap->read_private_posts;
	}

	// Return the capabilities required by the user. 
	return $caps;
}
add_filter( 'map_meta_cap', 'alo_em_map_meta_cap', 10, 4 );



/**
 * Texts when a Newsletter is updated
 */
function alo_em_newsletter_updated_messages( $messages ) {
	global $post, $post_ID;
	
	if ( get_option('alo_em_publish_newsletters') == "no" ) {
		$view_url = "";
		$preview_url = "";
	} else {
		$view_url = sprintf( __(' <a href="%s">View Newsletter</a>', "alo-easymail" ), esc_url( get_permalink($post_ID) ) );
		$preview_url = sprintf( __(' <a target="_blank" href="%s">Preview Newsletter</a>', "alo-easymail"), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) );
	}
	
	$messages['newsletter'] = array(
		0 => '', // Unused. Messages start at index 1.
		1 => __('Newsletter updated.', "alo-easymail" ). $view_url,
		2 => __('Custom field updated.', "alo-easymail"),
		3 => __('Custom field deleted.', "alo-easymail"),
		4 => __('Newsletter updated.', "alo-easymail"),
		/* translators: %s: date and time of the revision */
		5 => isset($_GET['revision']) ? sprintf( __('Newsletter restored to revision from %s', "alo-easymail"), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
		6 => __('Newsletter published.', "alo-easymail") . $view_url,
		7 => __('Newsletter saved.', "alo-easymail"),
		8 => sprintf( __('Newsletter submitted. <a target="_blank" href="%s">Preview Newsletter</a>', "alo-easymail"), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
		9 => sprintf( __('Newsletter scheduled for: <strong>%1$s</strong>.', "alo-easymail"),
		// translators: Publish box date format, see http://php.net/date
		date_i18n( __( 'j M Y @ G:i', "alo-easymail" ), strtotime( $post->post_date ) ) ). $preview_url,
		10 => __('Newsletter draft updated.', "alo-easymail") . $preview_url ,
	);
	return $messages;
}
add_filter('post_updated_messages', 'alo_em_newsletter_updated_messages');


/**
 * Adds media upload in thickbox in Newsletter
 */
function alo_em_newsletter_add_media_upload_scripts() {
    if ($GLOBALS['post_type'] == 'newsletter') {
        add_thickbox();
        wp_enqueue_script('media-upload');
    }
}
add_action('admin_print_styles-post-new.php', 'alo_em_newsletter_add_media_upload_scripts');
add_action('admin_print_styles-post.php', 'alo_em_newsletter_add_media_upload_scripts');


/**
 * Dirty hack to hide "Quick edit" button // TODO: add easymail option in Quick edit view: http://shibashake.com/wordpress-theme/expand-the-wordpress-quick-edit-menu
 */
 /*
function alo_em_inti_method () {
	add_action('admin_print_styles-edit.php','alo_em_hide_quick_edit_css');
}
add_action('admin_init','alo_em_inti_method');

function alo_em_hide_quick_edit_css() {
	if ( isset( $_GET['post_type'] ) && $_GET['post_type'] == "newsletter" ) : ?>
		<style type="text/css">span.inline { display:none!important }</style>
	<?php endif;
}
*/


/**
 * Adds an type column in table
 */
function alo_em_edit_table_columns ( $columns ) {
    unset ( $columns["date"] );
   	$columns["cb"] 					= "<input type=\"checkbox\" />";
   	$columns["title"] 				= __( 'Title' ) ." / " . __( 'Subject', "alo-easymail");
   	$columns["easymail_recipients"] = __( 'Recipients', "alo-easymail" );
   	$columns["easymail_status"] 	= __( 'Newsletter status', "alo-easymail" );
   	$columns["date"] 				= __( 'Start', "alo-easymail" );
   	$columns["author"]				= __( 'Author' );   	   	   	   	   	
  	return $columns;
}
add_filter ('manage_edit-newsletter_columns', 'alo_em_edit_table_columns');


/**
 * Fills the columns of Newsletter display table
 */
function alo_em_table_column_value ( $columns ) {
	global $post, $user_ID;
	$count_recipients = alo_em_count_recipients_from_meta( $post->ID );
	$recipients = alo_em_get_recipients_from_meta( $post->ID );
	
	if ( $columns == "easymail_recipients" ) {
		if ( $count_recipients == 0 ) {
  			if ( alo_em_user_can_edit_newsletter( $post->ID ) ) echo '<a href="'. get_edit_post_link( $post->ID ) . '">';
  			echo '<img src="'. ALO_EM_PLUGIN_URL. '/images/12-exclamation.png" alt="" /> <strong class="easymail-column-no-yet-recipients-'.$user_ID.'">' . __( 'No recipients selected yet', "alo-easymail").'</strong>';
  			if ( alo_em_user_can_edit_newsletter( $post->ID ) ) echo '</a>';
  		} else {
  			if ( alo_em_user_can_edit_newsletter( $post->ID ) ) echo "<a href='#' class='easymail-toggle-short-summary' rel='{$post->ID}'>";
  			echo __( 'Total recipients', "alo-easymail") .": ";
  			echo $count_recipients;
  			
  			if ( alo_em_user_can_edit_newsletter( $post->ID ) ) {
				echo "</a><br />\n";
				echo "<div id='easymail-column-short-summary-{$post->ID}' class='easymail-column-short-summary'>\n". alo_em_recipients_short_summary ( $recipients ) ."</div>\n";
			}
  		}
	}
	
	if ( $columns == "easymail_status" ) {
  		if ( $count_recipients > 0 ) {
			echo '<img src="'. ALO_EM_PLUGIN_URL. '/images/wpspin_light.gif" style="display:none;vertical-align: middle;" id="easymail-refresh-column-status-loading-'. $post->ID.'" />';  		
  			echo "<span id=\"alo-easymail-column-status-{$post->ID}\">\n";
  			alo_em_update_column_status ( $post->ID );
  			echo "</span>\n"; 					  					
  		}
	}
}
add_action ('manage_posts_custom_column', 'alo_em_table_column_value' ); 


/**
 * Update status column after closing recipients thickbox
 */
function alo_em_ajax_column_status () {
	$newsletter = (int)$_POST['post_id'];
	if ( $newsletter ) alo_em_update_column_status( $newsletter );
	die();
}
add_action('wp_ajax_alo_easymail_update_column_status', 'alo_em_ajax_column_status');


/**
 * Pause/Play Newsletter, then update status column after closing recipients thickbox
 */
function alo_em_ajax_pauseplay_column_status () {
	$newsletter = $_POST['post_id'];
	$button = $_POST['button']; // pause or play?
	if ( $newsletter && current_user_can( "publish_newsletters" ) ) {
		if ( $button == "pause" ) {
			alo_em_edit_newsletter_status ( $newsletter, 'paused' );
		} else {
			alo_em_edit_newsletter_status ( $newsletter, 'sendable' );
		}
		alo_em_update_column_status( $newsletter );
	}
	die();
}
add_action('wp_ajax_alo_easymail_pauseplay_column_status', 'alo_em_ajax_pauseplay_column_status');


/**
 * Return data for Subscriber edit inline
 */
function alo_em_ajax_alo_easymail_subscriber_edit_inline () {
	check_ajax_referer( "alo-easymail" );
	$subscriber = $_POST['subscriber'];
	$row_index = $_POST['row_index'];	
	$inline_action = $_POST['inline_action'];
	
	if ( $subscriber && current_user_can( "manage_newsletter_subscribers" )) {
		$mailinglists = alo_em_get_mailinglists( 'admin,public' );
		$languages = alo_em_get_all_languages ( true );
		
		switch ( $inline_action ) {
			case 'save':
				// save data and return html row, red-only mode
				$subscriber_obj =  alo_em_get_subscriber_by_id( $subscriber );
				$_POST = array_map( 'strip_tags', $_POST );
				$_POST = array_map( 'stripslashes_deep', $_POST );
				
				$new_name = ( isset( $_POST['new_name'] ) ) ? trim ( $_POST['new_name'] ) : false ;
				$new_email = ( isset( $_POST['new_email'] ) && is_email( trim ( $_POST['new_email'] ) ) ) ? trim ( $_POST['new_email'] ) : false;				
				$new_active = ( isset( $_POST['new_active'] ) && is_numeric( $_POST['new_active'] ) ) ? $_POST['new_active'] : 0;
				$new_lang = ( isset( $_POST['new_lang'] ) && in_array( $_POST['new_lang'], $languages) ) ? $_POST['new_lang'] : "";
				$new_lists = ( isset( $_POST['new_lists'] ) ) ? trim( $_POST['new_lists'] ) : false;
				

				//edit : added the following foreach and its content

				$alo_em_cf = alo_easymail_get_custom_fields();

				if ($alo_em_cf) {
					foreach( $alo_em_cf as $key => $value ){
						$var_name = "new_".$key;
						//$fields[$key] = ( isset( $_POST[$var_name] ) ) ? stripslashes( trim ( $_POST[$var_name] ) ): false;

						
						//$fields[$key] = false;
						if ( isset( $_POST[$var_name] ) ) {
							switch ( $value['input_type'] )	{
								
								// particular case: checkbox value not only exist, but value 1
								case "checkbox":
									if ( $_POST[$var_name] == '1' ) {
										$fields[$key] = 1;
									} else {
										unset( $fields[$key] );
									}
									break;
									
								default:
									$fields[$key] = stripslashes( trim ( $_POST[$var_name] ) );
							}
						} else {
							$fields[$key] = false;
						}
					}
				}
				/* if ( !$new_name ) {
					echo "-error-name-is-empty";
					break;
				} */
				
				// Check if a subscriber with this email already exists
				$already = ( alo_em_is_subscriber ( $new_email ) ) ? alo_em_is_subscriber ( $new_email ) : false;
				if ( $already && $already != $subscriber && $subscriber_obj->email != $new_email ) {
					echo "-error-email-already-subscribed";
					break;					
				
				// Last check before save
				} else if ( $new_email ) {			
					//$fields['old_email'] = $subscriber_obj->email; //edit : added all this line

					$fields['email'] = $new_email; //edit : added all this line

					$fields['name'] = $new_name; //edit : added all this line

					alo_em_update_subscriber_by_email ( $subscriber_obj->email, $fields, $new_active, $new_lang, false ); //edit : orig : alo_em_update_subscriber_by_email ( $subscriber_obj->email, $new_email, $new_name, $new_active, $new_lang );
					
					$new_lists = explode ( ",", rtrim ( $new_lists, "," ) );
					if ( is_array( $mailinglists ) ) : foreach ( $mailinglists as $mailinglist => $val ) :
						if ( in_array ( $mailinglist, $new_lists ) ) {
							alo_em_add_subscriber_to_list ( $subscriber, $mailinglist );
						} else {
							alo_em_delete_subscriber_from_list ( $subscriber, $mailinglist );
						}
					endforeach; endif;
					
					echo alo_em_get_subscriber_table_row ( $subscriber, $row_index, false, $mailinglists, $languages );		
					
				} else {
					echo "-error-email-is-not-valid";
				}						
				break;

			case 'delete':
				// If required, add email in unsubscribed db table
				if ( isset($_POST['to_unsubscribe']) && $_POST['to_unsubscribe'] == 1 )
				{
					$subscriber_obj =  alo_em_get_subscriber_by_id( $subscriber );
					alo_em_add_email_in_unsubscribed ( $subscriber_obj->email );
				}
							
				// Delete the subscriber
				if ( alo_em_delete_subscriber_by_id ( $subscriber ) ) {		
					echo "-ok-deleted";					
					break;
				} else {
					echo "-1"; // error
					break;
				}		
					
				echo alo_em_get_subscriber_table_row ( $subscriber, $row_index, false, $mailinglists, $languages );		
								
			case 'cancel':
				// return html row, red-only mode
				echo alo_em_get_subscriber_table_row ( $subscriber, $row_index, false, $mailinglists, $languages );				
				break;
				
			case 'edit':
			default:
				// return html row, edit mode
				echo alo_em_get_subscriber_table_row ( $subscriber, $row_index, true, $mailinglists, $languages );					
		}
	}
	die();
}
add_action('wp_ajax_alo_easymail_subscriber_edit_inline', 'alo_em_ajax_alo_easymail_subscriber_edit_inline');


/**
 * Print html of Status column of Newsletter in display table
 */
function alo_em_update_column_status ( $newsletter ) {
	global $user_ID;
	$recipients = alo_em_get_recipients_from_meta ( $newsletter );
	if ( $recipients ) {

		// Post status
		$post_status = get_post_status( $newsletter );

		//Newsletter status		
		$status = alo_em_get_newsletter_status( $newsletter );
		
		$report_url = wp_nonce_url( ALO_EM_PLUGIN_URL . '/alo-easymail_report.php?', 'alo-easymail_report');		
		$goto_report = "<a href=\"#\" onclick=\"aloEM(this).easymailReportPopup ( '$report_url', $newsletter, '". alo_em_get_language () ."' );\" title=\"". __( 'Report', "alo-easymail") ."\">";
		$goto_report .= "<img src=\"". ALO_EM_PLUGIN_URL. "/images/16-report.png\" alt=\"\" /> ". __( 'Report', "alo-easymail") ."</a>"; 
		if ( alo_em_is_newsletter_recipients_archived ( $newsletter ) ) $goto_report .= " <em>(". __( 'archived', "alo-easymail") . ")</em>"; 

		switch ( $status ) {
		
			case "sent":
				echo "<span class='status-completed'>". __("Completed", "alo-easymail"). ": 100%</span><br />";
				$end = get_post_meta ( $newsletter, "_easymail_completed", current_time( 'mysql', 0 ) );
				if ( $end ) echo date_i18n( __( 'j M Y @ G:i', "alo-easymail" ), strtotime( $end ) ). "<br />";
				if ( alo_em_user_can_edit_newsletter( $newsletter ) ) echo $goto_report; 
				break;
				
			case "sendable":
				
				switch ( $post_status ) {
					case "publish":
						echo "<span class='status-onsending'>".__("On sending queue", "alo-easymail"). "...</span><br />";					
						echo __("Progress", "alo-easymail"). ": ". alo_em_newsletter_recipients_percentuage_already_sent( $newsletter ) . "%<br />";
						if ( alo_em_user_can_edit_newsletter( $newsletter ) && current_user_can( "publish_newsletters" ) ) {
							echo ' <img src="'. ALO_EM_PLUGIN_URL. '/images/16-refresh.png" class="easymail-refresh-column-status" alt="'. __( 'refresh', "alo-easymail"). '" title="'. __( 'refresh', "alo-easymail"). '" rel="'. $newsletter. '" />';
							echo "<a href=\"#\" onclick=\"aloEM(this).easymailPausePlay ( $newsletter, 'pause' );return false;\">";
							echo ' <img src="'. ALO_EM_PLUGIN_URL. '/images/16-pause.png" class="easymail-pause-column-status" alt="'. __( 'pause', "alo-easymail"). '" title="'. __( 'pause the sending', "alo-easymail"). '" rel="'. $newsletter. '" />';
							echo "</a>";
						}
						if ( alo_em_user_can_edit_newsletter( $newsletter ) && current_user_can( "publish_newsletters" )) echo " ". $goto_report; 
						break;
					case "pending":
						echo "<span class='status-paused'>".__("Pending Review"). "</span><br />";
						break;
					case "future":
						echo "<span class='status-paused'>".__("Scheduled"). "</span><br />";
						if ( alo_em_user_can_edit_newsletter( $newsletter ) ) {
							echo ' <img src="'. ALO_EM_PLUGIN_URL. '/images/16-refresh.png" class="easymail-refresh-column-status" alt="'. __( 'refresh', "alo-easymail"). '" title="'. __( 'refresh', "alo-easymail"). '" rel="'. $newsletter. '" />';
						}
						break;	
					case "draft":
						echo "<span class='status-paused'>".__("Draft"). "</span><br />";
						break;
					default:
						echo "<span class='status-paused'>".__("Pending"). "</span><br />";										
						break;
				} // $post_status	
				break;
				
			case "paused":
				echo "<span class='status-paused'>".__("Paused", "alo-easymail"). "!</span><br />";
				echo __("Progress", "alo-easymail"). ": ". alo_em_newsletter_recipients_percentuage_already_sent( $newsletter ) . "%<br />";
				//if ( alo_em_count_newsletter_recipients_already_sent ( $newsletter ) > 0 ) echo " <small>(".alo_em_count_newsletter_recipients_already_sent ( $newsletter ) ."/". alo_em_count_newsletter_recipients ( $newsletter ). ")</small><br />";
				if ( alo_em_user_can_edit_newsletter( $newsletter ) ) {
					echo "<a href=\"#\" onclick=\"aloEM(this).easymailPausePlay ( $newsletter, 'play' );return false;\">";
					echo ' <img src="'. ALO_EM_PLUGIN_URL. '/images/16-play.png" class="easymail-pause-column-status" alt="'. __( 'continue', "alo-easymail"). '" title="'. __( 'continue the sending', "alo-easymail"). '" rel="'. $newsletter. '" />';
					echo "</a>";
				}
				if ( alo_em_user_can_edit_newsletter( $newsletter ) ) echo " ". $goto_report; 
				break;
				
			case false:
			default:

				switch ( $post_status ) {
					case "pending":
					case "draft":
						echo "<span class='status-paused'>".__("A newsletter cannot be sent if its status is draft or pending review"). "</span><br />";
						break;
						
					default:
						if ( get_option('alo_em_js_rec_list') != "no_ajax_onsavepost" ) { // if required, no link to ajax
							//$rec_url = wp_nonce_url( ALO_EM_PLUGIN_URL . '/alo-easymail_recipients-list.php?', 'alo-easymail_recipients-list');
							if ( alo_em_user_can_edit_newsletter( $newsletter ) && current_user_can( "publish_newsletters" ) ) {
								//echo "<a href=\"#\" onclick=\"aloEM(this).easymailRecipientsGenPopup ( '$rec_url', $newsletter, '". alo_em_get_language () ."' );\">";
								echo "<a href=\"#\" class=\"easymail-reciepient-list-open\"  rel=\"".$newsletter."\">";
								echo "<img src=\"". ALO_EM_PLUGIN_URL. "/images/16-arrow-right.png\" alt=\"\" /> <strong class=\"easymail-column-status-required-list-".$user_ID."\">" . __( 'Required', "alo-easymail") .":</strong> " . __( 'Create list of recipients', "alo-easymail");
								echo "</a>";
							} else {
								echo "<span class='status-paused'>".__('Ready to be sent by an administrator', "alo-easymail"). "</span><br />";
							}
						}								
						break;
				} // $post_status	
				
		}
	}
}


/**
 * Add "views" button in edit newsletter table
 */
function alo_em_edit_table_views ( $views ) {
	$class = ( isset ( $_GET['easymail_status'] ) && $_GET['easymail_status'] == "sent" ) ? "current" : false;
	if ( alo_em_count_newsletters_by_status( 'sent' ) > 0 ) {
		// post_status=true: to avoid "All" view is the current
		$views[ "easymail_status" ] = "\t<a href=\"edit.php?post_status=true&post_type=newsletter&easymail_status=sent\"". ( ( $class ) ? " class=\"current\"" : "") . ">". __( 'Sent', "alo-easymail") . sprintf( " <span class=\"count\">(%d)</span>", alo_em_count_newsletters_by_status( 'sent' ) /*TODO*/ ) ."</a>";
	}
	return $views;
}
add_filter( 'views_edit-newsletter', 'alo_em_edit_table_views' );


/**
 * Show required newsletters in edit newsletter table
 */
function alo_em_filter_newsletter_table ( $query ) {
	global $wp_version, $pagenow;
	if ( is_admin() && $pagenow == "edit.php" && isset( $_GET['post_type'] ) && $_GET['post_type'] == "newsletter" ) {
		if ( isset ( $_GET['easymail_status'] ) && $_GET['easymail_status'] == "sent" ) {
			// query meta: http://codex.wordpress.org/Function_Reference/WP_Query#Custom_Field_Parameters
			if ( version_compare ( $wp_version , '3.1', '>=' ) ) {
				$meta_1 = array( 'key' => '_easymail_status', 'value' => 'sent', 'compare' => '=' );
				$query->set ('meta_query', array( $meta_1 ) );
			} else {
				$query->set ('meta_key', '_easymail_status' );
				$query->set ('meta_value', 'sent' );
				$query->set ('meta_compare', '=' );			
			}
		}
	}
   	return $query;
}
add_action('pre_get_posts', 'alo_em_filter_newsletter_table' );


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

			//$html .= sprintf( $value['edit_html'], $subscriber->ID, $subscriber->ID, format_to_edit( $subscriber->$key ) )."\n";
			/*switch( $value['input_type'] )
			{
				case "text":	$tpl = "<input type=\"\">";
			}
			$input = str_replace( '[id]', 'id="'.$field_id.'"', $input);
			$input = str_replace( '[name]', 'name="'.$field_id.'"', $input);
			$input = str_replace( '[value]', $subscriber ? format_to_edit( $subscriber->$key ):'', $input);
			$html .= $input ."\n";
			*/
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
					//check if custom fields have been changed
					if ( isset( $_POST[ "alo_em_". $key] ) ) {
						$fields[$key] = stripslashes( trim( $_POST[ "alo_em_". $key] ) );
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
 * Widget activation
 */
function alo_em_load_widgets() {
	register_widget( 'ALO_Easymail_Widget' );
}
add_action( 'widgets_init', 'alo_em_load_widgets' );


/**
 * Add javascript on Admin panel
 */
function alo_em_add_admin_script () {
	global $post, $pagenow;


	if ( isset($_GET['page']) && $_GET['page'] == "alo-easymail/alo-easymail_options.php") {
		wp_enqueue_script('jquery-ui-tabs');
		echo '<link rel="stylesheet" href="'.ALO_EM_PLUGIN_URL.'/inc/jquery.ui.tabs.css" type="text/css" media="print, projection, screen" />'."\n";
	}
	if ( $pagenow == "post.php" || ( isset( $_GET['post_type'] ) && $_GET['post_type'] == "newsletter" ) ) {

		//edit : added all this "$code" related lines
		$alo_em_cf = alo_easymail_get_custom_fields();
		
		$code = '<script type="text/javascript">
		<!-- <![CDATA[
		//custom fields array
		var alo_cf_array = new Array();
		';

		$i = 0;
		if ( $alo_em_cf ) {
			foreach( $alo_em_cf as $key => $value ){
				$code .= "alo_cf_array[".$i."] = '" . $key . "';\n";
				++$i;
			}
		}
		$code .= '
		// ]]> -->
		</script>';

		echo $code;
		
		wp_enqueue_script( 'json2' );
		wp_enqueue_script( 'jquery', false, array( 'json2' ) );
		wp_enqueue_script( 'thickbox' );
		wp_enqueue_script( 'alo-easymail-smartupdater', ALO_EM_PLUGIN_URL . '/inc/smartupdater.js', array('jquery'), '3.2.00' );
		//wp_enqueue_script( 'alo-easymail-backend-recipients-list', ALO_EM_PLUGIN_URL . '/inc/alo-easymail-backend-recipients-list.js' );
		wp_enqueue_style( 'alo-easymail-backend-css', ALO_EM_PLUGIN_URL.'/inc/alo-easymail-backend.css' );

		wp_enqueue_script ( 'jquery-ui-dialog' );
		wp_enqueue_script ( 'jquery-ui-resizable' );
		wp_enqueue_script ( 'jquery-ui-draggable' );
		wp_enqueue_style (  'wp-jquery-ui-dialog');

		wp_enqueue_script( 'alo-easymail-backend', ALO_EM_PLUGIN_URL . '/inc/alo-easymail-backend.js', array( 'jquery' ) );
		wp_localize_script( 'alo-easymail-backend', 'easymailJs', alo_em_localize_admin_script() );		
	}
}
add_action('admin_print_scripts', 'alo_em_add_admin_script' );

function alo_em_localize_admin_script () {
	global $post, $pagenow, $current_screen;
	$post_id = ( $post ) ? $post->ID : false;
	$screen_id = ( $current_screen ) ? $current_screen->id : false; 
    return array(
    	'ajaxurl' => admin_url( 'admin-ajax.php' ),
        'pluginPath' => ALO_EM_PLUGIN_URL."/",
        'postID' => $post_id,
        'pagenow' => $pagenow,
		'screenID' => $screen_id,
        'reportPopupTitle' => esc_js( __("Newsletter report", "alo-easymail") ),
        'subscribersPopupTitle' => esc_js( __("Newsletter subscribers creation", "alo-easymail") ),
        'themePreviewUrl' => alo_easymail_get_themes_url(),
		'nonce' => wp_create_nonce( 'alo-easymail' ),
		'errGeneric' => esc_js( __("Error during operation.", "alo-easymail") ),
		'errEmailNotValid' => esc_js( __("The e-email address is not correct", "alo-easymail") ),
		'errNameIsBlank' => esc_js( __("The name field is empty", "alo-easymail") ),
		'errEmailAlreadySubscribed'=> esc_js( __("There is already a subscriber with this e-email address", "alo-easymail") ),
		'confirmDelSubscriber'=> esc_js( __("Do you really want to DELETE this subscriber?", "alo-easymail") ),
		'confirmDelSubscriberAndUnsubscribe'=> esc_js( __("Do you really want to DELETE this subscriber?", "alo-easymail").' '. __("The email address will be added to the list of who unsubscribed", "alo-easymail") .": ". __("so you cannot add or import these email addresses using the tools in admin pages", "alo-easymail") ),
		'txtClose' => esc_js( __("close", "alo-easymail") ),
		'titleRecListModal' => esc_js( __( 'Create list of recipients', "alo-easymail") ),
		'txt_success_added' => esc_js( __( 'Recipients successfully added', "alo-easymail" ) ),
	   	'txt_success_sent' => esc_js( __( 'Newsletter successfully sent to recipients', "alo-easymail" ) ),
    );
}


/**
 * Add CSS on Admin panel
 */
function alo_em_add_admin_styles () {
	global $post, $pagenow;
	if ( $pagenow == "post.php" || ( isset( $_GET['post_type'] ) && $_GET['post_type'] == "newsletter" ) ) {
		wp_enqueue_style( 'alo-easymail-backend-css', ALO_EM_PLUGIN_URL.'/inc/alo-easymail-backend.css' );
		wp_enqueue_style( 'thickbox' );
	}
}
add_action( "admin_print_styles", 'alo_em_add_admin_styles' );


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
	/* // TODO use jquery external js!
	wp_enqueue_script( 'alo-easymail-frontend', ALO_EM_PLUGIN_URL . '/inc/alo-easymail-frontend.js' );
	wp_localize_script( 'alo-easymail-frontend', 'easymail', 
		array( 'ajaxurl' => admin_url( 'admin-ajax.php' ), 'postID' => 2 )  
	);
	*/
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
	include( ALO_EM_PLUGIN_ABS .'/alo-easymail_subscr-page.php' );
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


/**
 * Box meta: Recipients
 */
function alo_em_meta_recipients ( $post ) { 
	wp_nonce_field( ALO_EM_PLUGIN_DIR, "edit_newsletter" );
	//print_r ( alo_em_get_recipients_from_meta($post->ID) ); print_r ( alo_em_get_all_languages() );
	echo "<p " . ( ( alo_em_count_recipients_from_meta( $post->ID ) == 0 ) ? "class=\"easymail-txtwarning\"" : "" ) ." >";
	echo "<strong>" .__("Selected recipients", "alo-easymail") .": ". alo_em_count_recipients_from_meta( $post->ID ) ."</strong></p>";
	
	if ( alo_em_get_newsletter_status ( $post->ID ) == "sent" || alo_em_is_newsletter_recipients_archived ( $post->ID ) ) {
		echo "<div class=\"easymail-alert\"><p>". __("This newsletter was already sent", "alo-easymail") .".</p>";
		echo "</div>";	
		return; // exit
	}
	
	if ( alo_em_count_newsletter_recipients ( $post->ID ) > 0 ) {
		echo "<div class=\"easymail-alert\"><p>". __("The creation of the recipients list has already started", "alo-easymail") .".</p>";
		echo "<p><input type=\"checkbox\" name=\"easymail-reset-all-recipients\" id=\"easymail-reset-all-recipients\" value=\"yes\" /> ";
		echo "<strong><label for=\"easymail-reset-all-recipients\">". __("Check this flag to delete the existing list and save new recipients now", "alo-easymail") .".</label></strong></p>";
		echo "</div>";
	}
	
	$recipients = alo_em_get_recipients_from_meta ( $post->ID );
	?>
	<div class="easymail-edit-recipients easymail-edit-recipients-registered">
		<ul class="level-1st">
			<li class="list-title"><?php _e( "Users" ); ?>:</li>
			<li>
				<?php $checked = ( isset( $recipients['registered']) ) ? ' checked="checked" ' : ''; ?>
				<label for="easymail-recipients-all-regusers" class="easymail-metabox-update-count"><?php echo __("All registered users", "alo-easymail") /*. " (". count ( alo_em_get_recipients_registered () ) .")" */; ?></label>
				<input type="checkbox" name="easymail-recipients-all-regusers" id="easymail-recipients-all-regusers" value="checked" <?php echo $checked ?> class="easymail-metabox-update-count" />
			</li>

			<?php // Roles
			global $wp_roles;
			$roles = $wp_roles->get_names(); // get a list of values, containing pairs of: $role_name => $display_name
			if ( $roles ) : ?>
			<li><a href="#" class="easymail-filter-regusers-by-roles"><?php _e("Filter users according to roles", "alo-easymail"); ?>...</a></li>
			<li>
				<ul id="easymail-filter-ul-roles" class="level-2st">
					<?php
					foreach ( $roles as $key => $label ) { 
						$checked = ( isset( $recipients['role'] ) && in_array( $key, $recipients['role'] ) ) ? ' checked="checked" ' : ''; 
						?>
						<li>
							<label for="role_<?php echo $key ?>" class="easymail-metabox-update-count"><?php echo translate_user_role( $label ); ?></label>
							<input type="checkbox" name="check_role[]" class="check_role easymail-metabox-update-count" id="role_<?php echo $key ?>" value="<?php echo $key ?>" <?php echo $checked ?>  />
						</li>
					<?php } ?>
				</ul>	
			</li>
			<?php endif; // roles ?>
			
		</ul>
	</div><!-- /easymail-edit-recipients-registered -->
	
	<div class="easymail-edit-recipients easymail-edit-recipients-subscribers">
		<ul class="level-1st">
			<li class="list-title"><?php _e("Newsletter subscribers", "alo-easymail"); ?>:</li>				
			<li>
				<?php $checked = ( isset( $recipients['subscribers']) ) ? ' checked="checked" ' : ''; ?>
				<label for="easymail-recipients-all-subscribers" class="easymail-metabox-update-count"><?php echo __("All subscribers", "alo-easymail") /*. " (". count( alo_em_get_recipients_subscribers() ) .")"*/; ?></label>
				<input type="checkbox" name="easymail-recipients-all-subscribers" id="easymail-recipients-all-subscribers" value="checked" <?php echo $checked ?> class="easymail-metabox-update-count" />
			</li>
			
			<?php // if mailing lists
			$mailinglists = alo_em_get_mailinglists( 'admin,public' );
			if ( $mailinglists ) : ?>
			<li><a href="#" class="easymail-filter-subscribers-by-lists"><?php _e("Filter subscribers according to lists", "alo-easymail"); ?>...</a></li>
			<li>
				<ul id="easymail-filter-ul-lists" class="level-2st">
					<?php	
					foreach ( $mailinglists as $list => $val) { 
						if ( $val['available'] == "deleted" || $val['available'] == "hidden" ) continue; 
							$checked = ( isset( $recipients['list'] ) && in_array( $list, $recipients['list'] ) ) ? ' checked="checked" ' : ''; 
							?>
							<li>
								<label for="list_<?php echo $list ?>" class="easymail-metabox-update-count"><?php echo alo_em_translate_multilangs_array ( alo_em_get_language(), $val['name'], true ) /*. " (".  count ( alo_em_get_recipients_subscribers( $list ) ).")"*/; ?></label>
								<input type="checkbox" name="check_list[]" class="check_list easymail-metabox-update-count" id="list_<?php echo $list ?>" value="<?php echo $list ?>" <?php echo $checked ?>  />
							</li>
						<?php } ?>
				</ul>	
			</li>
			<?php endif; // $mailinglists ?>
			
			<?php // if languages
			$languages = alo_em_get_all_languages( false );
			if ( $languages ) : ?>
			<li><a href="#" class="easymail-filter-subscribers-by-languages"><?php _e("Filter subscribers according to languages", "alo-easymail"); ?>...</a></li>	
			<li>
				<ul id="easymail-filter-ul-languages" class="level-2st">			
					<?php	
					foreach ( $languages as $index => $lang) {  
						$checked = ( ( isset( $recipients['lang'] ) && in_array( $lang, $recipients['lang'] )) || !isset( $recipients['lang'] ) ) ? ' checked="checked" ' : '';
						$tot_sub_x_lang = alo_em_count_subscribers_by_lang( $lang, true );
						?>
						<li>
							<label for="check_lang_<?php echo $lang ?>" class="easymail-metabox-update-count" > <?php echo esc_html ( alo_em_get_lang_name ( $lang ) ) /* . " (". $tot_sub_x_lang .")"*/; ?></label>
							<input type="checkbox" name="check_lang[]" class="check_lang easymail-metabox-update-count" id="check_lang_<?php echo $lang ?>" value="<?php echo $lang ?>" <?php echo $checked ?> />
						</li>
					<?php }
						$checked = ( (isset($recipients['lang']) && in_array( "UNKNOWN", $recipients['lang'] )) || !isset($recipients['lang']) ) ? ' checked="checked" ' : ''; ?>
						<li>
							<label for="check_lang_unknown" class="easymail-metabox-update-count"> <?php _e("Not specified / others", "alo-easymail"); ?>
							<?php /*echo ' ('. alo_em_count_subscribers_by_lang(false, true).')';*/ ?></label>
							<input type="checkbox" name="check_lang[]" class="check_lang easymail-metabox-update-count" id="check_lang_unknown" value="UNKNOWN" <?php echo $checked ?> />
						</li>
				</ul>	
			</li>
			<?php endif; // $languages ?>			

			
		</ul>
		
	</div><!-- /easymail-edit-recipients-subscribers -->
	

	<?php
}



/**
 * Box meta: Themes
 */
function alo_em_meta_themes ( $post ) { 
	wp_nonce_field( ALO_EM_PLUGIN_DIR, "edit_newsletter" ); 
	$themes = alo_easymail_get_all_themes();	
	if ( $themes ) { 
		//echo "<pre>". print_r ( $themes, true ). "</pre>"; // DEBUG
		echo '<select name="easymail-theme-select" id="easymail-theme-select" >';	
		echo '<option value="">'. __("No", "alo-easymail") .' </option>';
		foreach( $themes as $theme => $path ) {
			$theme_selected = ( get_post_meta ( $post->ID, '_easymail_theme', true) == $theme ) ? 'selected="selected"': '';
			echo '<option value="'. $theme .'" '. $theme_selected .'>'. $theme.' </option>';
		}
		echo "</select>\n"; 
		echo "<a href='' id='easymail-theme-select-preview' >". __("View") ."</a>";
	}
}


/**
 * Save Theme
 */
function alo_em_save_newsletter_theme_meta ( $post_id ) {
	if ( isset( $_POST['easymail-theme-select'] ) && array_key_exists( $_POST['easymail-theme-select'], alo_easymail_get_all_themes() ) ) {
		update_post_meta ( $post_id, '_easymail_theme', $_POST['easymail-theme-select'] );
	} else {
		delete_post_meta ( $post_id, '_easymail_theme' );
	}
} 
add_action('alo_easymail_save_newsletter_meta_extra',  'alo_em_save_newsletter_theme_meta' );


/**
 * Box meta: Placeholders
 */
function alo_em_meta_placeholders ( $post ) { 
	wp_nonce_field( ALO_EM_PLUGIN_DIR, "edit_newsletter" );
	alo_em_tags_table ( $post->ID );
}


/**
 * Add post select in Placeholders table
 */
function alo_em_placeholders_title_easymail_post ( $post_id ) {
	$n_last_posts = (get_option('alo_em_lastposts'))? get_option('alo_em_lastposts'): 10;
	$args = array(
		'numberposts' => $n_last_posts,
		'order' => 'DESC',
		'orderby' => 'date'
		);
	$args = apply_filters ( 'alo_easymail_placeholders_title_easymail_post_vars', $args, $post_id );  // Hook
	$get_posts = get_posts($args);
	if ( $get_posts ) { 
		echo esc_html( __("Choose a post", "alo-easymail") ). ": ";
		echo '<select name="placeholder_easymail_post" id="placeholder_easymail_post" >';	
		foreach($get_posts as $post) :
		    $select_post_selected = ( get_post_meta ( $post_id, '_placeholder_easymail_post', true) == $post->ID ) ? 'selected="selected"': '';
		    echo '<option value="'.$post->ID.'" '. $select_post_selected .'>['. date_i18n( __( 'j M Y', "alo-easymail" ), strtotime( $post->post_date ) ) .'] '. get_the_title( $post->ID ).' </option>';
		endforeach;
		echo '</select><br />'; 
	} else {
		echo "<span class='easymail-txtwarning'>" . esc_html( __("There are no posts", "alo-easymail") ) . "!</span> <br />";
	}
}
add_action('alo_easymail_newsletter_placeholders_title_easymail_post',  'alo_em_placeholders_title_easymail_post' );


/**
 * Save Post select in Placeholder Box meta in Newsletter 
 */
function alo_em_save_newsletter_placeholders_easymail_post ( $post_id ) {
	if ( isset( $_POST['placeholder_easymail_post'] ) && is_numeric( $_POST['placeholder_easymail_post'] ) ) {
		update_post_meta ( $post_id, '_placeholder_easymail_post', $_POST['placeholder_easymail_post'] );
	}
} 
add_action('alo_easymail_save_newsletter_meta_extra',  'alo_em_save_newsletter_placeholders_easymail_post' );

 
/**
 * SAVE Boxes meta in Newsletter 
 */
function alo_em_save_newsletter_meta ( $post_id ) {
	
	if ( @!wp_verify_nonce( $_POST["edit_newsletter"], ALO_EM_PLUGIN_DIR )) {
		return $post_id;
	}

	if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return $post_id;

	// Check permissions
	if ( 'newsletter' == $_POST['post_type'] ) {
		if ( !current_user_can( 'edit_newsletter', $post_id ) ) return $post_id;
	}
	
	do_action ( 'alo_easymail_save_newsletter_meta_extra', $post_id );
	
	// If a previous list exists already: if requested reset, otherwise don't save
	if ( alo_em_count_newsletter_recipients( $post_id ) > 0 || alo_em_is_newsletter_recipients_archived ( $post_id ) ) {
		if ( isset( $_POST['easymail-reset-all-recipients'] ) ) {
			alo_em_delete_newsletter_recipients ( $post_id );
			alo_em_delete_newsletter_status ( $post_id );
			alo_em_delete_cache_recipients ( $post_id );
		} else {
			return $post_id; // don't save, exit
		}
	}
	
	// Save Recipients
	$recipients = array ();
	if ( isset( $_POST['easymail-recipients-all-regusers'] ) ) {
		$recipients['registered'] = "1";
	} else {
		if ( isset($_POST['check_role']) && is_array ($_POST['check_role']) ) {
			foreach ( $_POST['check_role'] as $role ) {
				$recipients['role'][] = $role;
			}
		}
	}
	
	if ( isset( $_POST['easymail-recipients-all-subscribers'] ) ) {
		$recipients['subscribers'] = "1";
	} else {
		if ( isset($_POST['check_list']) && is_array ($_POST['check_list']) ) {
			foreach ( $_POST['check_list'] as $list ) {
				$recipients['list'][] = $list;
			}
		}
	}
	if ( isset($_POST['check_lang']) && is_array ($_POST['check_lang']) ) {
		foreach ( $_POST['check_lang'] as $lang ) {
			$recipients['lang'][] = $lang;
		}
	}	
	
	// Save!
	delete_post_meta ( $post_id, "_easymail_recipients" );
	add_post_meta ( $post_id, "_easymail_recipients", $recipients );
	
	// If required, create list of recipient now, without ajax
	if ( get_option('alo_em_js_rec_list') == "no_ajax_onsavepost" && alo_em_count_recipients_from_meta( $post_id ) > 0 ) {
		alo_em_create_cache_recipients( $post_id );
		alo_em_add_recipients_from_cache_to_db( $post_id, alo_em_count_recipients_from_meta( $post_id ), false );
	}
}
add_action('save_post', 'alo_em_save_newsletter_meta');


/**
 * When a Newsletter is deleted: eg. delete recipients from db table
 */
function alo_em_newsletter_deleted ( $post_id ) {
	alo_em_delete_newsletter_recipients( $post_id );
}
add_action( 'delete_post', 'alo_em_newsletter_deleted' );


/**
 * Add a dashboard widget
 */
function alo_em_dashboard_widget_function() {
	global $wpdb;
	echo "<h4>". __("Newsletters scheduled for sending", "alo-easymail").": ". alo_em_count_newsletters_by_status( 'sendable' ) ."</h4>";
	$newsletter =  alo_em_get_newsletters_in_queue( 1 );
	if ( $newsletter ) {
		echo "<p>";
		echo '<img src="'.ALO_EM_PLUGIN_URL.'/images/16-email-forward.png" title="'.__("now sending", "alo-easymail").'" alt="" style="vertical-align:text-bottom" />';
		echo " <strong>" . stripslashes ( alo_em___( $newsletter[0]->post_title ) ) ."</strong><br />";				
		echo __("Progress", "alo-easymail") .": " . alo_em_newsletter_recipients_percentuage_already_sent( $newsletter[0]->ID ) . " %<br />" ;			
		echo "<em>".__("Added on", "alo-easymail") . " ". date_i18n( __( 'j M Y @ G:i', "alo-easymail" ), strtotime( $newsletter[0]->post_date ) ) . "  - ";
		echo __("Scheduled by", "alo-easymail") . " ". get_user_meta($newsletter[0]->post_author, 'nickname',true). "</em>";
		echo "</p>";
	} else {
		echo "<p>". __("There are no newsletters in queue", "alo-easymail") . ".</p>";
	}
	echo "<h4 style='margin-top:1.2em'>". __("Subscribers", "alo-easymail") ."</h4>";
	list ( $total, $active, $noactive ) = alo_em_count_subscribers ();
	if ($total) {
		echo "<p>". sprintf( __("There are %d subscribers: %d activated, %d not activated", "alo-easymail"), $total, $active, $noactive ) . ".</p>";
	} else {
		echo "<p>". __("No subscribers", "alo-easymail") . ".</p>";
	}
	
	if ( current_user_can('administrator') ) {
		echo "<h5 style='margin-bottom:0.4em'>". __("Updates from plugin developer", "alo-easymail") ."</h5>";
		$rss = fetch_feed( 'http://www.eventualo.net/blog/category/alo-easymail-newsletter/feed/' );
		if ( !is_wp_error( $rss ) ) {
			$maxitems = $rss->get_item_quantity( 3 ); 
			$rss_items = $rss->get_items(0, $maxitems); 
			echo "<ul style='padding-top: 0.5em'>";
			if ( $maxitems == 0 ) {
				echo '<li>No items.</li>';
			} else {
				// Loop through each feed item and display each item as a hyperlink.
				foreach ( $rss_items as $item ) : 
					$content = $item->get_content();
					$content = esc_attr( wp_html_excerpt( $content, 350 ) ) . ' [...]'; ?>
				<li>
					<a href='<?php echo $item->get_permalink(); ?>'
					title='<?php echo $content; ?>'>
					<?php echo $item->get_title(); ?></a>
					<?php echo date_i18n( __('j F Y', "alo-easymail" ), strtotime( $item->get_date() ) ); ?> 
				</li>
				<?php endforeach; 
			} 
			echo "</ul>";
		} else {
			echo '<p>';
			printf(__('<strong>RSS Error</strong>: %s'), $rss->get_error_message());
			echo '</p>';
		}
	}	
} 

function alo_em_add_dashboard_widgets() {
	if ( current_user_can ( 'manage_newsletter_subscribers' ) && current_user_can ( 'edit_others_newsletters' ) ) {
		wp_add_dashboard_widget('alo-easymail-widget', 'EasyMail Newsletter', 'alo_em_dashboard_widget_function');	
	}
} 
add_action('wp_dashboard_setup', 'alo_em_add_dashboard_widgets' );


/**
 * Show the optin/optout on Registration Form
 */
function alo_em_show_registration_optin () {
    $optin_txt = ( alo_em_translate_option ( alo_em_get_language (), 'alo_em_custom_optin_msg', false) !="") ? alo_em_translate_option ( alo_em_get_language (), 'alo_em_custom_optin_msg', false) : __("Yes, I would like to receive the Newsletter", "alo-easymail"); 
	echo '<p class="alo_easymail_reg_optin"><input type="checkbox" id="alo_em_opt" name="alo_em_opt" value="yes" class="input" checked="checked" /> ';
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
	} else {
		alo_em_add_email_in_unsubscribed ( $user->user_email );
	}
}
add_action( 'user_register', 'alo_em_save_registration_optin' );


/**
 * Edit the e-mail message
 */ 
function alo_em_handle_email ( $args ) {
	// $args['to'], $args['subject'], $args['message'], $args['headers'], $args['attachments']
	
	// Check based on $args['subject']; more attrs in $args['message']
	global $_config;
	/*
	 * 1) Activation e-mail
	 */
	if ( strpos ( "#_EASYMAIL_ACTIVATION_#", $args['subject'] ) !== false) {
		
		// Get the parameters stored as a query in $args['message'] 
		$defaults = array( 'lang' => '', 'email' => '',	'name' => '', 'unikey' => '' );
		/* // replaced 'wp_parse_args' because use urlencode and stripslashes, so affect emails with '+' chars
		$customs = wp_parse_args( $args['message'], $defaults );
		extract( $customs, EXTR_SKIP );
		*/
		$pars = array();
		$raw = explode('&', $args['message']);
		foreach ($raw as $section)
		{
			if (strpos($section, '=') !== false)
			{
				list($key, $value) = explode('=', $section);
				$pars[$key] = $value;
			}
		}		
		$customs = array_merge( $defaults, $pars );
		extract( $customs, EXTR_SKIP );
		
		//$subscriber = alo_em_get_subscriber( $email );
		
		// Subject
	   	if ( $subject_text = alo_em_translate_option ( $lang, 'alo_em_txtpre_activationmail_subj', true ) ) {
			$subject = $subject_text;
		} else {
		   	$subject = alo_em___( __("Confirm your subscription to %BLOGNAME% Newsletter", "alo-easymail" ) );
		}
		$blogname = html_entity_decode ( wp_kses_decode_entities ( get_option('blogname') ) );
		$subject = str_replace ( "%BLOGNAME%", $blogname, $subject );
		$args['subject'] = $subject;
				
		// Content
	   	if ( $content_txt = alo_em_translate_option ( $lang, 'alo_em_txtpre_activationmail_mail', true ) ) {
			$content = $content_txt;
		} else {
		   	$content = __("Hi %NAME%\nto complete your subscription to %BLOGNAME% newsletter you need to click on the following link (or paste it in the address bar of your browser):\n", "alo-easymail");
		   	$content .= "%ACTIVATIONLINK%\n\n";
		   	$content .= __("If you did not ask for this subscription ignore this message.", "alo-easymail"). "\n";
		    $content .= __("Thank you", "alo-easymail")."\n". $blogname ."\n";
		}
		/*
	 	$div_email = explode("@", $email);
		$arr_params = array ('ac' => 'activate', 'em1' => $div_email[0], 'em2' => $div_email[1], 'uk' => $unikey );
		$sub_link = add_query_arg( $arr_params, get_page_link (get_option('alo_em_subsc_page')) );
		$sub_link = alo_em_translate_url ( $sub_link, $lang );		
		*/
				
		//$div_email = explode("@", $email);
		$sub_vars = $email ."|" /*$div_email[0] . "|" . $div_email[1] . "|" */ . $unikey . "|" . $lang;
		
		//$sub_vars = $subscriber->ID . "|" . $subscriber->unikey;
		$sub_vars = urlencode( base64_encode( $sub_vars ) );
		$sub_link = add_query_arg( 'emact', $sub_vars, alo_em_translate_home_url ( $lang ) /*trailingslashit( get_home_url() )*/ );
		//$sub_link = alo_em_translate_url ( $sub_link, $lang /*$subscriber->lang */ );

	  	$content = str_replace ( "%BLOGNAME%", $blogname, $content );
	   	$content = str_replace ( "%NAME%", /* $subscriber->name */ $name, $content );
	   	$content = str_replace ( "%ACTIVATIONLINK%", $sub_link, $content ); 
	   	
		$args['message'] = $content;
	}
	return $args;
}

add_filter('wp_mail', 'alo_em_handle_email');


/**
 * Add Newsletter menu in Toolbar Admin bar (WP 3.1-3.2)
 */
if ( version_compare ( $wp_version , '3.1', '>=' ) && version_compare ( $wp_version , '3.3', '<' ) )
{
	function alo_em_add_menu_admin_bar() {
		global $wp_admin_bar;
		if ( !$wp_admin_bar ) return;
		if ( !is_admin_bar_showing() ) return;
		
		if ( current_user_can('edit_newsletters') ) {
			$wp_admin_bar->add_menu( array( 'id' => 'alo_easymail', 'title' =>__( 'Newsletters', "alo-easymail" ), 'href' => admin_url('edit.php')."?post_type=newsletter" ) );
			$wp_admin_bar->add_menu( array( 'id' => 'alo_easymail_main', 'parent' => 'alo_easymail', 'title' => __( 'Newsletters', "alo-easymail" ), 'href' => admin_url('edit.php')."?post_type=newsletter" ) );
			$wp_admin_bar->add_menu( array( 'parent' => 'alo_easymail_main', 'title' => __( 'Add New Newsletter', "alo-easymail" ), 'href' => admin_url('post-new.php')."?post_type=newsletter" ) );       
			$wp_admin_bar->add_menu( array( 'parent' => 'alo_easymail_main', 'title' => __( 'Show all', "alo-easymail" ), 'href' => admin_url('edit.php')."?post_type=newsletter" ) );   
		}
		if ( current_user_can('manage_newsletter_subscribers') ) {
			$wp_admin_bar->add_menu( array( 'parent' => 'alo_easymail', 'title' => __( 'Subscribers', "alo-easymail" ), 'href' => admin_url('edit.php')."?post_type=newsletter&page=alo-easymail/alo-easymail_subscribers.php" ) );
		}
		if ( current_user_can('manage_newsletter_options') ) {
			$wp_admin_bar->add_menu( array( 'parent' => 'alo_easymail', 'title' => __( 'Options', "alo-easymail" ), 'href' => admin_url('edit.php')."?post_type=newsletter&page=alo-easymail/alo-easymail_options.php" ) );    
		}   
	}
	add_action( 'admin_bar_menu', 'alo_em_add_menu_admin_bar' ,  70);
}
else if ( version_compare ( $wp_version , '3.3', '>=' ) )
{
	function alo_em_add_menu_toolbar( $wp_admin_bar ) {
		if ( current_user_can('edit_newsletters') ) {
			$ico = '<span class="alo-easymail-toolbar-ico">&nbsp;</span>';
			$args = array('id' => 'alo_easymail', 'title' => $ico. __( 'Newsletters', "alo-easymail" ), 'parent' => false, 'href' => admin_url('edit.php')."?post_type=newsletter" );
			$wp_admin_bar->add_node($args);
			
			$args = array('id' => 'alo_easymail-all', 'title' => __( 'Newsletters', "alo-easymail" ), 'parent' => 'alo_easymail', 'href' => admin_url('edit.php')."?post_type=newsletter" );
			$wp_admin_bar->add_node($args);

			$args = array('id' => 'alo_easymail-new', 'title' => __( 'Add New', "alo-easymail" ), 'parent' => 'alo_easymail', 'href' => admin_url('post-new.php')."?post_type=newsletter" );
			$wp_admin_bar->add_node($args);

			if ( current_user_can('manage_newsletter_subscribers') ) {
				$args = array('id' => 'alo_easymail-subscribers', 'title' => __( 'Subscribers', "alo-easymail" ), 'parent' => 'alo_easymail', 'href' => admin_url('edit.php')."?post_type=newsletter&page=alo-easymail/alo-easymail_subscribers.php" );
				$wp_admin_bar->add_node($args);
			}
			if ( current_user_can('manage_newsletter_options') ) {
				$args = array('id' => 'alo_easymail-settings', 'title' => __( 'Options', "alo-easymail" ), 'parent' => 'alo_easymail', 'href' => admin_url('edit.php')."?post_type=newsletter&page=alo-easymail/alo-easymail_options.php" );
				$wp_admin_bar->add_node($args);	
			}
						
		}  
	}
	add_action( 'admin_bar_menu', 'alo_em_add_menu_toolbar', 45 );
}



/**
 * Send a newsletter to a test email
 */
function alo_em_send_mailtest () {
	$result = "no";
	check_ajax_referer( "alo-easymail" );
	$newsletter = ( isset( $_POST['newsletter'] ) && is_numeric( $_POST['newsletter'] ) ) ? (int) $_POST['newsletter'] : false;
	$email = ( isset( $_POST['email'] ) && is_email( $_POST['email'] ) ) ? $_POST['email'] : false;
	if ( $email && $newsletter && current_user_can( "publish_newsletters" ) ) {
		
		$maybe_subscriber = (array)alo_em_get_subscriber( $email );
		if ( isset( $maybe_subscriber['ID'] ) ) {
			$maybe_subscriber['subscriber'] = $maybe_subscriber['ID'];
			unset( $maybe_subscriber['ID'] );
		} else {
			$maybe_subscriber['subscriber'] = false;
		}
		$user_id = ( email_exists( $email ) ) ? email_exists( $email ) : false;
		$recipient = (object) array_merge( $maybe_subscriber, array ( 'newsletter' => $newsletter, 'email' => $email, 'user_id' => $user_id ) );
		//$recipient = (object) array ( 'newsletter' => $newsletter, 'email' => $email );

		if ( alo_em_send_newsletter_to ( $recipient, false /*true*/ ) ) $result = "yes";
	}
	usleep( 500000 );
	die ( $result );
}
add_action('wp_ajax_easymail_send_mailtest', 'alo_em_send_mailtest');


/**
 * Alert in admin panel
 */
function alo_em_admin_notice() {
	global $pagenow;
	$page = ( isset( $_GET['page'] ) ) ? $_GET['page'] : false;
	if ( $pagenow == "edit.php" && isset( $_GET['post_type'] ) && $_GET['post_type'] == 'newsletter' && $page != 'alo-easymail/alo-easymail_subscribers.php' ) {
		/*
		if ( get_option('alo_em_timeout_alert') != "hide" ) { 
			echo '<div class="updated fade">';
			echo '<p><img src="'.ALO_EM_PLUGIN_URL.'/images/12-exclamation.png" /> '. __("To enable the plugin work better you should increase the wp_cron and php timeouts", "alo-easymail") .". ";
			echo __("For more info you can use the Help button or visit the FAQ of the site", "alo-easymail");
			echo ' <a href="http://www.eventualo.net/blog/wp-alo-easymail-newsletter-faq/#faq-3" target="_blank" title="'. __("For more info, visit the FAQ of the site.", "alo-easymail") .'">&raquo;</a></p>';
			echo "<p>(<a href='". "edit.php?post_type=newsletter&page=alo-easymail/alo-easymail_options.php" ."&amp;timeout_alert=stop' />". __('Do not show it again', 'alo-easymail') ."</a>)</p>";
			echo '</div>';
		}
		*/
				
		if ( get_option('ALO_em_debug_newsletters') != "" ) { 
			echo '<div class="updated fade">';
			echo '<p><img src="'.ALO_EM_PLUGIN_URL.'/images/12-exclamation.png" /> <strong>'. __("Debug mode is activated", "alo-easymail") ."</strong>: ";
			if ( get_option('ALO_em_debug_newsletters') == "to_author" ) 	_e("all messages will be sent to the newsletter author", "alo-easymail");
			if ( get_option('ALO_em_debug_newsletters') == "to_file" ) 		_e("all messages will be recorded into a log file", "alo-easymail");
			echo ".</p>";
			echo '</div>';
		}
	}
	if ( alo_em_db_tables_need_update() ) {
		echo '<div class="error">';
		echo '<p><img src="'.ALO_EM_PLUGIN_URL.'/images/12-exclamation.png" /> <strong><em>'. __("ALO Easymail Newsletter needs attention", "alo-easymail") ."!</em></strong><br />";
		echo __("The plugin database tables have not properly installed", "alo-easymail") .": <strong>" . __("you can try to deactivate and activate the plugin", "alo-easymail")."</strong>.";
		echo "<br /><a href=\"http://www.eventualo.net/blog/wp-alo-easymail-newsletter-faq/\" target=\"_blank\">". __("For more info, visit the FAQ of the site.", "alo-easymail")."</a>";
		echo ".</p>";
		echo '</div>';
	}
}
add_action('admin_notices', "alo_em_admin_notice");


/**
 * Manage user request made via GET vars: eg. activation link, unsubscribe link, external request
 */
function alo_em_check_get_vars () {
	global $wpdb;
	
	// From unsubscribe link
	if ( isset( $_GET['emunsub'] ) ) {
		$get_vars = base64_decode( $_GET['emunsub'] );
		$get = explode( "|", $get_vars );	
		$subscriber = alo_em_get_subscriber_by_id ( $get[0] );

		$uns_link = "";
		if ( $subscriber ) {
			$div_email = explode( "@", $subscriber->email );
		   	$arr_params = array ('ac' => 'unsubscribe', 'em1' => urlencode($div_email[0]), 'em2' => urlencode($div_email[1]), 'uk' => preg_replace( '/[^a-zA-Z0-9]/i', '', $get[1]) );
			$uns_link = add_query_arg( $arr_params, alo_em_translate_url ( get_option('alo_em_subsc_page'), $subscriber->lang ) );
		}
		wp_redirect( $uns_link );
		exit;
	}

	// From activation link
	if ( isset( $_GET['emact'] ) ) {
		$get_vars = base64_decode( $_GET['emact'] );
		$get = explode( "|", $get_vars );
		$subscriber = alo_em_get_subscriber ( $get[0] );
		
		$act_link = "";
		if ( $subscriber ) {
			$div_email = explode( "@", $subscriber->email );
			//$arr_params = array ('ac' => 'activate', 'em1' => $div_email[0], 'em2' => $div_email[1], 'uk' => $get[1] );
			$arr_params = array ('ac' => 'activate', 'em1' => urlencode($div_email[0]), 'em2' => urlencode($div_email[1]), 'uk' => preg_replace( '/[^a-zA-Z0-9]/i', '', $get[1]) );
			$act_link = add_query_arg( $arr_params, alo_em_translate_url ( get_option('alo_em_subsc_page'), $get[2] ) );
		}		
		wp_redirect( $act_link );
		exit;
	}
	
	// Called from external request (eg. cron task)
	if ( isset( $_GET['alo_easymail_doing_cron'] ) ) {
		//echo "OK let's do the batch!";
		alo_em_batch_sending ();
		exit;
	}

	// Called from a tracked link
	if ( isset( $_GET['emtrck'] ) ) {
		$get_vars = base64_decode( $_GET['emtrck'] );
		$get = explode( "|", $get_vars );

		$recipient	= ( isset( $get[0] ) && is_numeric($get[0]) ) ? (int)$get[0]: false;
		$unikey		= ( isset( $get[1] ) ) ? preg_replace( '/[^a-zA-Z0-9]/i', '', $get[1]) : false;
		$request	= ( isset( $get[2] ) ) ? esc_url_raw( $get[2] ) : false;

		if ( $recipient && $unikey && $request) {
			$rec_info = alo_em_get_recipient_by_id( $recipient );
			if ( $rec_info && alo_em_check_subscriber_email_and_unikey ( $rec_info->email, $unikey ) ) {
				alo_em_tracking_recipient ( $recipient, $rec_info->newsletter, $request );

				switch ( get_option('alo_em_campaign_vars') ) {

					case 'google':
						$campaign_args = array(
							'utm_source' 	=> 'AloEasyMail',
							'utm_medium'	=> 'email',
							'utm_campaign'	=>  $rec_info->newsletter . '-'. get_the_title( $rec_info->newsletter ),
							'utm_content'	=>  $request
						);
						$campaign_args = apply_filters ( 'alo_easymail_prepare_campaign_vars', $campaign_args, $rec_info, $request );  // Hook
						$request_w_campaign = add_query_arg ( $campaign_args, $request );
						wp_redirect( $request_w_campaign );
						exit;
						
					case 'no':
					default:
						wp_redirect( $request );
						exit;
				}
			}
		}
		exit;
	}

	// Block XSS attempt: escape/unset subscription form inputs when not in ajax (eg. if javascript disabled)
	if ( !defined('DOING_AJAX') || ! DOING_AJAX )
	{
		if ( isset($_REQUEST['alo_em_opt_name']) ) unset($_REQUEST['alo_em_opt_name']);
		if ( isset($_REQUEST['alo_em_opt_email']) ) unset($_REQUEST['alo_em_opt_email']);
		// we do not unset 'submit' because its common name, so it could be maybe used by other plugins: only a safe escape
		if ( isset($_REQUEST['submit']) ) esc_sql($_REQUEST['submit']); 
	}
}
add_action('init', 'alo_em_check_get_vars');


/**
 * If WPML is used: Try to create automatically a Newsletter subscription page 
 * for each language, when language list is changed
 */
function alo_em_create_wpml_subscrpage_translations( $settings ) {
	if ( !function_exists( 'wpml_get_active_languages' ) ) return; // if runs before WPML is completely loaded
	$langs = wpml_get_active_languages();
	
	if ( is_array( $langs ) ) {
		foreach ( $langs as $lang ) {
			// Original page ID
			$original_page_id = get_option('alo_em_subsc_page'); 
			
			// If the translated page doesn't exist, now create it
			if ( icl_object_id( $original_page_id, 'page', false, $lang['code'] ) == null ) {
				
				// Found at: http://wordpress.stackexchange.com/questions/20143/plugin-wpml-how-to-create-a-translation-of-a-post-using-the-wpml-api
				
				$post_translated_title = get_post( $original_page_id )->post_title . ' (' . $lang['code'] . ')';
				
				// All page stuff
				$my_page = array();
				$my_page['post_title'] 		= $post_translated_title;
				$my_page['post_content'] 	= '[ALO-EASYMAIL-PAGE]';
				$my_page['post_status'] 	= 'publish';
				$my_page['post_author'] 	= 1;
				$my_page['comment_status'] 	= 'closed';
				$my_page['post_type'] 		= 'page';
				
				// Insert translated post
				$post_translated_id = wp_insert_post( $my_page );

				// Get trid of original post
				$trid = wpml_get_content_trid( 'post_'.'page', $original_page_id );

				// Get default language
				$default_lang = wpml_get_default_language();

				// Associate original post and translated post
				global $wpdb;
				$wpdb->update( $wpdb->prefix.'icl_translations', array( 'trid' => $trid, 'language_code' => $lang['code'], 'source_language_code' => $default_lang ), array( 'element_id' => $post_translated_id ) );
			}
		}
	}
}
add_action('icl_save_settings', 'alo_em_create_wpml_subscrpage_translations' );


/**
 * If Duplicate Post is used: Do not duplicate EasyMail internal post meta
 */
function alo_em_when_duplicate_post( $new_post_id, $old_post_object ) {
	$exclude_meta = array( "_easymail_archived_recipients", "_easymail_completed", "_easymail_status", "_easymail_recipients" );
	foreach( $exclude_meta as $meta ) delete_post_meta ( $new_post_id, $meta );
}
add_action( "dp_duplicate_post", "alo_em_when_duplicate_post", 100, 2 );


/**
 * When someone unsubcribes, it adds email in 'unsubscribed' table
 * @param	str
 * @param	int		user id optional: only if subscriber is also a registered user
 */ 
function alo_em_add_unsubscribed_email_in_db_table ( $email, $user_id=false ) {
	global $wpdb;
	if ( ! alo_em_check_email_in_unsubscribed($email) )
	{
		alo_em_add_email_in_unsubscribed($email);
	}
}
add_action('alo_easymail_subscriber_deleted',  'alo_em_add_unsubscribed_email_in_db_table', 10, 2 );


/**
 * When someone subcribes, it delete email from 'unsubscribed' table
 * @param	str
 * @param	int		user id optional: only if subscriber is also a registered user
 */ 
function alo_em_delete_unsubscribed_email_from_db_table ( $subscriber, $user_id=false ) {
	global $wpdb;
	if ( alo_em_check_email_in_unsubscribed( $subscriber->email ) )
	{
		alo_em_delete_email_from_unsubscribed( $subscriber->email );
	}
}
add_action ( 'alo_easymail_new_subscriber_added',  'alo_em_delete_unsubscribed_email_from_db_table', 10, 2 );




/**
 * Create Our Initialization Function
 */
function alo_em_init_tinymce_buttons() {
	global $typenow;
	if ( empty($typenow) || 'newsletter' != $typenow ) return;
	if ( ! current_user_can('edit_newsletters') ) {
		return;
	}
	if ( get_user_option('rich_editing') == 'true' ) {
		add_filter( 'mce_external_plugins', 'alo_em_add_tinymce_plugin' );
		add_filter( 'mce_buttons', 'alo_em_register_tinymce_buttons' );
	}
}
add_action('admin_enqueue_scripts', 'alo_em_init_tinymce_buttons');


/**
 * Register Button
 */
function alo_em_register_tinymce_buttons( $buttons ) {
	array_push( $buttons, "|", "easymail" );
	return $buttons;
}


/**
 * Register TinyMCE Plugin
 */
function alo_em_add_tinymce_plugin( $plugin_array ) {
	$plugin_array['easymail'] = ALO_EM_PLUGIN_URL. '/inc/tinymce/editor_plugin.js';   
	return $plugin_array;
}


function alo_em_loc_tinymce_buttons() {
	global $typenow;
	if ( empty($typenow) || 'newsletter' != $typenow ) return;	
	?>
	<script type="text/javascript">
	(function() {

		alo_em_tinymce_labels = new Array();
		alo_em_tinymce_tags = new Array();
		
		<?php
		$placeholders = alo_em_newsletter_placeholders();
		if ( $placeholders ) {
			foreach( $placeholders as $key => $ph ) {
				echo 'alo_em_tinymce_labels["'. $key .'"] = " - '. esc_js( $ph['title'] ) .' -";'. "\n";
				echo 'alo_em_tinymce_tags["'. $key .'"] = new Array(';				
				if ( isset($ph['tags']) && is_array($ph['tags']) ) {
					$tag_list = '';
					foreach ( $ph['tags'] as $tag => $desc ) {
						$tag_list .= '"'. $tag .'", ';
					}
					echo rtrim( $tag_list, ', ' );
				}
				echo ');'."\n";
			}
		}
		?>
	})();
	</script>
	<?php
}
add_action( 'admin_print_footer_scripts', 'alo_em_loc_tinymce_buttons', 100 );



/**
 * Load scripts for pointers (3.3+)
 */
function alo_em_tooltip_head_scripts() {
	global $pagenow, $wp_version, $typenow;
	if ( version_compare ( $wp_version, '3.3', '<' ) ) return; // old WP, exit
	
	if ( 'newsletter' == $typenow ) {
		// Available pointers
		$add_users = get_user_setting( 'alo_em_pointer_add_users', 0 );
		$no_yet_recipients = get_user_setting( 'alo_em_pointer_no_yet_recipients', 0 );
		$required_list = get_user_setting( 'alo_em_pointer_required_list', 0 );
		
		if ( ! $add_users || ! $no_yet_recipients || ! $required_list ) {
			wp_enqueue_style( 'wp-pointer' );
			wp_enqueue_script( 'wp-pointer' );
			wp_enqueue_script( 'utils' ); // needed for setUserSetting in js
			add_action( 'admin_print_footer_scripts', 'alo_em_print_pointer_footer_scripts' );	
		}
	}
}
add_action( 'admin_enqueue_scripts', 'alo_em_tooltip_head_scripts'); 

/**
 * Print tooltip pointers (3.3+)
 */
function alo_em_print_pointer_footer_scripts() {
	global $pagenow, $typenow, $user_ID;
	$page = isset( $_GET['page'] ) ? $_GET['page'] : false;

	// In subscribers screen
	if ( $pagenow == "edit.php" && 'alo-easymail/alo-easymail_subscribers.php' == $page && ! get_user_setting( 'alo_em_pointer_add_users', 0 ) ) :
		$impexp_butt = __("Import/export subscribers", "alo-easymail");
		$pointer_content = '<h3>Easymail | '. esc_js( $impexp_butt ) .'</h3>';
		$pointer_content .= '<p>'. esc_js( sprintf( __('Maybe you would like to import subscribers from your blog registered members or an external archive (using CSV). Click the &#39;%s&#39; button', 'alo-easymail'), $impexp_butt) ) .'</p>';
?>
   <script type="text/javascript">
   //<![CDATA[
   jQuery(document).ready( function($) {
		$('#easymail-subscribers-add-button').pointer({
			content: '<?php echo $pointer_content; ?>',
			position: 'top',
			close: function() { // Once the close button is hit
				setUserSetting( 'alo_em_pointer_add_users', '1' );
			}
		  }).pointer('open');
   });
   //]]>
   </script>
<?php
	endif; // In subscribers screen

	// In newsletter list screen
	if ( $pagenow == "edit.php" && 'newsletter' == $typenow && ! get_user_setting( 'alo_em_pointer_no_yet_recipients', 0 )) :
		$pointer_content = '<h3>Easymail | '. esc_js( __( 'No recipients selected yet', "alo-easymail") ) .'</h3>';
		$pointer_content .= '<p>'. esc_js( __('Before sending the newsletter you have to select recipients.', 'alo-easymail')." " .__('Click the link to do it now.', 'alo-easymail') ) .'</p>';
?>
   <script type="text/javascript">
   //<![CDATA[
   jQuery(document).ready( function($) {
		$('.easymail-column-no-yet-recipients-<?php echo $user_ID ?>:first').pointer({
			content: '<?php echo $pointer_content; ?>',
			position: 'top',
			//close: function() { // Once the close button is hit
			open: function() { // Auto-dismiss, show only once
				setUserSetting( 'alo_em_pointer_no_yet_recipients', '1' );
			}
		  }).pointer('open');
   });
   //]]>
   </script>
<?php
	endif; // In newsletter list screen

	// In newsletter list screen
	if ( $pagenow == "edit.php" && 'newsletter' == $typenow && ! get_user_setting( 'alo_em_pointer_required_list', 0 ) ) :
		$pointer_content = '<h3>Easymail | '. esc_js( __( 'Create list of recipients', "alo-easymail") ) .'</h3>';
		$pointer_content .= '<p>'. esc_js( __('You have to prepare the list of recipients to send the newsletter to', 'alo-easymail').". " .__('Click the link to do it now.', 'alo-easymail') ) .'</p>';
?>
   <script type="text/javascript">
   //<![CDATA[
   jQuery(document).ready( function($) {
		$('.easymail-column-status-required-list-<?php echo $user_ID ?>:first').pointer({
			content: '<?php echo $pointer_content; ?>',
			position: 'top',
			//close: function() { // Once the close button is hit
			open: function() { // Auto-dismiss, show only once
				setUserSetting( 'alo_em_pointer_no_yet_recipients', '1' );
				setUserSetting( 'alo_em_pointer_required_list', '1' );
			}
		  }).pointer('open');
   });
   //]]>
   </script>
<?php
	endif; // In newsletter list screen	
	
}



/**
 * Button to preview in newsletter theme
 */

function alo_em_add_media_button() {
	global $post;

	if ( get_option('alo_em_use_themes') == 'no' ) return;
	
	if (is_object($post) && $post->post_type == 'newsletter') :
?>
<a title="Newsletter preview"  id="easymail-open-preview" class="preview button" href="#"><img id="easymail-open-preview-loading" src="<?php echo ALO_EM_PLUGIN_URL ?>/images/wpspin_light.gif" alt="" style="vertical-align: text-bottom;display: none;margin-right: 0.5em" /><?php echo __('Preview in newsletter theme', 'alo-easymail') ?></a>
<?php
	endif;
}
add_action('media_buttons', 'alo_em_add_media_button', 11);


// When click Previe btn, save the content
function alo_em_save_newsletter_content_transient () {
	global $user_ID;
	check_ajax_referer( "alo-easymail" );
	$newsletter_id = ( isset( $_POST['newsletter'] ) && is_numeric( $_POST['newsletter'] ) ) ? (int) $_POST['newsletter'] : false;
	$theme = ( isset( $_POST['theme'] ) && array_key_exists( $_POST['theme'], alo_easymail_get_all_themes() ) ) ? stripslashes(trim( $_POST['theme'] ) ) : '';

	if ( $newsletter_id )
	{
		$data = array( 'theme' => $theme );
		set_transient( 'alo_em_content_preview_'.$newsletter_id, $data, 60*3 );
		die ( '1' );
	}
	die( '-1' );
}
add_action('wp_ajax_alo_easymail_save_newsletter_content_transient', 'alo_em_save_newsletter_content_transient');


// When save newsletter, delete content in transient
function alo_em_delete_newsletter_content_transient ( $post_id ) {
	delete_transient( 'alo_em_content_preview_'.$post_id );
} 
add_action('alo_easymail_save_newsletter_meta_extra',  'alo_em_delete_newsletter_content_transient' );



/**
 * Genration of List of recipients in modal
 */

function alo_em_recipient_list_modal() {
	global $post, $pagenow, $user_email;
	if ( $pagenow == "post.php" || ( isset( $_GET['post_type'] ) && $_GET['post_type'] == "newsletter" ) ) { ?>

<div id="easymail-recipient-list-modal" data-current-id="" data-previous-id="">


	<div id='alo-easymail-bar-outer' style="display:none"><div id='alo-easymail-bar-inner'></div></div>

	<div id="alo-easymail-list-disclaimer">
		<p><?php _e("You have to prepare the list of recipients to send the newsletter to", "alo-easymail") ?>.</p>
		<p><?php _e("You can add the recipients to the sending queue (best choice) or send them the newsletter immediately (suggested only if few recipients)", "alo-easymail") ?>.</p>
		<p><em><?php _e("Warning: do not close or reload the browser window during process", "alo-easymail") ?>.</em></p>
		<br /><br />
		<p><?php _e("You can send the newsletter as test to", "alo-easymail") ?>: 
			<input type="text" id="easymail-testmail" name="easymail-testmail" size="20" value="<?php echo $user_email; ?>" />
			<button type="button" class="button easymail-navbutton easymail-send-testmail"><?php _e("Send", "alo-easymail") ?></button> 
			<img src="<?php echo ALO_EM_PLUGIN_URL?>/images/wpspin_light.gif" style="display:none;vertical-align: middle;" id="easymail-testmail-loading" />
			<img src="<?php echo ALO_EM_PLUGIN_URL?>/images/no.png" style="display:none;vertical-align: middle;"  id="easymail-testmail-no" alt="<?php _e("Yes", "alo-easymail") ?>" />
			<img src="<?php echo ALO_EM_PLUGIN_URL?>/images/yes.png" style="display:none;vertical-align: middle;" id="easymail-testmail-yes" alt="<?php _e("No", "alo-easymail") ?>" />
		</p>
	</div>

	<div id="ajaxloop-response"></div>

	<!--[if lte IE 7]>
	<div style="float: left;">
	<![endif]-->
	<div id="easymail-recipients-navbar">
		<button type="button" class="button easymail-navbutton easymail-navbutton-primary easymail-recipients-start-loop"><?php _e("Add to sending queue", "alo-easymail") ?></button> 
		
		<button type="button" class="button easymail-navbutton easymail-recipients-start-loop-and-send"><?php _e("Send now", "alo-easymail") ?></button> 
		<button type="button" class="button easymail-navbutton easymail-recipients-pause-loop" style="display:none"><?php _e("pause", "alo-easymail") ?></button> 
		<button type="button" class="button easymail-navbutton easymail-recipients-restart-loop" style="display:none"><?php _e("continue", "alo-easymail") ?></button> 

	</div>
	<!--[if lte IE 7]>
	</div>
	<![endif]-->

</div>
<?php
	}
}
add_action('admin_footer', 'alo_em_recipient_list_modal');


function alo_em_ajax_recipient_list_ajaxloop () {
	global $user_ID;
	check_ajax_referer( "alo-easymail" );

	$response = array();
	$response['error'] = '';
	
	if ( isset( $_POST['newsletter'] ) ) {
		$newsletter = (int)$_POST['newsletter'];
		if ( get_post_type( $newsletter ) != "newsletter" ) $response['error'] = esc_js( __('The required newsletter does not exist', "alo-easymail") ); 
		if ( !get_post( $newsletter ) ) $response['error'] = esc_js( __('The required newsletter does not exist', "alo-easymail")  );
		if ( !alo_em_user_can_edit_newsletter( $newsletter ) ) $response['error'] = esc_js( __('Cheatin&#8217; uh?')  );

		$post_status = get_post_status( $newsletter );
		if ( $post_status == "draft" || $post_status == "pending" )  $response['error'] = esc_js( __('A newsletter cannot be sent if its status is draft or pending review') );
	} else {
		$response['error'] = esc_js( __('Cheatin&#8217; uh?')  ); 
	}
	
	if ( $response['error'] == '' )
	{
		// If missing prepare cache 
		if ( !alo_em_get_cache_recipients( $newsletter ) ) {
			alo_em_create_cache_recipients( $newsletter );
		} else {
			// Now add a part of recipients into the db table 
			$sendnow = ( isset( $_POST['sendnow'] ) && $_POST['sendnow'] == "yes" ) ? true : false;
			$limit = apply_filters ( 'alo_easymail_ajaxloop_recipient_limit', 15 );  // Hook
			alo_em_add_recipients_from_cache_to_db( $newsletter, $limit, $sendnow );
		}
		$response['n_done'] = alo_em_count_newsletter_recipients( $newsletter );
		$response['n_tot'] =  alo_em_count_recipients_from_meta( $newsletter /*, true */ );
		$response['perc'] =  ( $response['n_done'] > 0 && $response['n_tot'] > 0 ) ? round ( $response['n_done'] * 100 / $response['n_tot'] ) : 0;
	}
	
	header( "Content-Type: application/json" );
	die( json_encode ( $response ) );
	
}
add_action('wp_ajax_alo_easymail_recipient_list_ajaxloop', 'alo_em_ajax_recipient_list_ajaxloop');


