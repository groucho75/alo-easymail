<?php if ( !defined('ABSPATH') ) die(); // If this file is called directly, abort.

/**
 * Dashboard related functions: enqueue files, plugin dashboard menu, plugin links...
 *
 * @package WordPress
 * @subpackage ALO EasyMail plugin
 */



/**
 * Add javascript on Admin panel
 */
function alo_em_add_admin_script () {
	global $post, $pagenow;


	if ( isset($_GET['page']) && $_GET['page'] == "alo-easymail/pages/alo-easymail-admin-options.php") {
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
 * Add menu pages
 */
function alo_em_add_admin_menu() {
	if ( current_user_can('manage_newsletter_subscribers') )  {
		add_submenu_page( 'edit.php?post_type=newsletter', __("Subscribers", "alo-easymail"), __("Subscribers", "alo-easymail"), 'manage_newsletter_subscribers', 'alo-easymail/pages/alo-easymail-admin-subscribers.php' );
		add_action( 'load-alo-easymail/pages/alo-easymail-admin-subscribers.php', 'alo_em_contextual_help_tabs' );
	}
	if ( current_user_can('manage_newsletter_options') ) {
		add_submenu_page( 'edit.php?post_type=newsletter', __("Settings"), __("Settings"), 'manage_newsletter_options', 'alo-easymail/pages/alo-easymail-admin-options.php' );
		add_action( 'load-alo-easymail/pages/alo-easymail-admin-options.php', 'alo_em_contextual_help_tabs' );
	}
	add_action( 'load-edit.php', 'alo_em_contextual_help_tabs' );
	add_action( 'load-post-new.php', 'alo_em_contextual_help_tabs' );
}
add_action('admin_menu', 'alo_em_add_admin_menu');


/*
 * Add some links on the plugin page
 */
function alo_em_add_plugin_links($links, $file) {
	if ( $file == 'alo-easymail/alo-easymail.php' ) {
		$links[] = '<a href="http://www.eventualo.net/blog/wp-alo-easymail-newsletter-guide/" target="_blank">Guide</a>';
		$links[] = '<a href="http://www.eventualo.net/blog/wp-alo-easymail-newsletter-faq/" target="_blank">Faq</a>';
		$links[] = '<a href="http://www.eventualo.net/blog/category/alo-easymail-newsletter/" target="_blank">News</a>';
		$links[] = '<a href="http://wordpress.org/support/plugin/alo-easymail" target="_blank" title="tag alo-easymail @ wordpress.org support forum">Forum</a>';
	}
	return $links;
}
add_filter( 'plugin_row_meta', 'alo_em_add_plugin_links', 10, 2 );


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

				$fields = array(); // to be saved
				//edit : added the following foreach and its content

				$alo_em_cf = alo_easymail_get_custom_fields();

				if ($alo_em_cf) {
					foreach( $alo_em_cf as $key => $value ){
						$var_name = "new_".$key;
						//$fields[$key] = ( isset( $_POST[$var_name] ) ) ? stripslashes( trim ( $_POST[$var_name] ) ): false;


						//$fields[$key] = false;
						if ( !empty( $_POST[$var_name] ) ) {

							switch ( $value['input_type'] )	{
								case "checkbox":
									$fields[$key] = 1;
									break;

								default:
									$fields[$key] = sanitize_text_field( trim ( $_POST[$var_name] ) );
							}
						} else {
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
 * Add Newsletter menu in Toolbar Admin bar (WP 3.1-3.2)
 */
function alo_em_add_menu_toolbar( $wp_admin_bar ) {
	if ( current_user_can('edit_newsletters') ) {
		$ico = '<span class="ab-icon dashicons dashicons-email"> </span>';
		$args = array('id' => 'alo_easymail', 'title' => $ico. __( 'Newsletters', "alo-easymail" ), 'parent' => false, 'href' => admin_url('edit.php')."?post_type=newsletter" );
		$wp_admin_bar->add_node($args);

		$args = array('id' => 'alo_easymail-all', 'title' => __( 'Newsletters', "alo-easymail" ), 'parent' => 'alo_easymail', 'href' => admin_url('edit.php')."?post_type=newsletter" );
		$wp_admin_bar->add_node($args);

		$args = array('id' => 'alo_easymail-new', 'title' => __( 'Add New', "alo-easymail" ), 'parent' => 'alo_easymail', 'href' => admin_url('post-new.php')."?post_type=newsletter" );
		$wp_admin_bar->add_node($args);

		if ( current_user_can('manage_newsletter_subscribers') ) {
			$args = array('id' => 'alo_easymail-subscribers', 'title' => __( 'Subscribers', "alo-easymail" ), 'parent' => 'alo_easymail', 'href' => admin_url('edit.php')."?post_type=newsletter&page=alo-easymail/pages/alo-easymail-admin-subscribers.php" );
			$wp_admin_bar->add_node($args);
		}
		if ( current_user_can('manage_newsletter_options') ) {
			$args = array('id' => 'alo_easymail-settings', 'title' => __( "Settings" ), 'parent' => 'alo_easymail', 'href' => admin_url('edit.php')."?post_type=newsletter&page=alo-easymail/pages/alo-easymail-admin-options.php" );
			$wp_admin_bar->add_node($args);
		}

	}
}
add_action( 'admin_bar_menu', 'alo_em_add_menu_toolbar', 45 );


/**
 * Alert in admin panel
 */
function alo_em_admin_notice() {
	global $pagenow;
	$page = ( isset( $_GET['page'] ) ) ? $_GET['page'] : false;
	if ( $pagenow == "edit.php" && isset( $_GET['post_type'] ) && $_GET['post_type'] == 'newsletter' && $page != 'alo-easymail/pages/alo-easymail-admin-subscribers.php' ) {

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
 * Html row of a Subscriber in subscriber table
 */
function alo_em_get_subscriber_table_row ( $subscriber_id, $row_index=0, $edit=false, $all_lists=false, $all_langs=false ) {
	if ( empty( $subscriber_id ) ) return false;
	$subscriber = alo_em_get_subscriber_by_id( $subscriber_id );
	$html = "";
	//$html .= "<tr id=\"subscriber-row-{$subscriber_id}\" class=\"subscriber-row\">\n";

	$html .= "<th scope=\"row\" class=\"subscriber-row-index row-important-column\">". $row_index . "</th>\n";
	$html .= "<td class=\"row-important-column\" style=\"vertical-align: middle;\">";
	$html .= "<input type=\"checkbox\" name=\"subscribers[]\" id=\"subscribers_". $subscriber_id . "\" value=\"". $subscriber_id. "\" />\n";
	$html .= "</td>\n";

	if ( get_option('show_avatars') )
	{
		$html .= "<td>" . get_avatar($subscriber->email, 30). "&nbsp;</td>";
	}

	$html .= "<td class=\"subscriber-email row-important-column\">";
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
	$html .= "<abbr title=\"". esc_attr($last_act_datetime) ."\" />". $last_act_diff ."</abbr>\n";
	if ( get_option('alo_em_collect_ip_address') == 'yes' && !empty($subscriber->ip_address) ) {
		$html .= "<br /><a href=\"http://www.whatismyipaddress.com/ip/{$subscriber->ip_address}\" title=\"". esc_attr( $subscriber->ip_address .' @ whatismyipaddress.com') ."\" target=\"_blank\" class=\"ip-address\"/>IP ". $subscriber->ip_address ."</abbr>\n";
	}
	$html .= "</td>\n";

	$html .= "<td class=\"subscriber-active row-important-column\">\n";
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


	$html .= "<td class=\"subscriber-actions row-important-column\">\n"; // Actions
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


/* EOF */