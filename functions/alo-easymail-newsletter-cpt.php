<?php if ( !defined('ABSPATH') ) die(); // If this file is called directly, abort.
/**
 * Newsletter Custom Post Type related functions, including dashboard edit screens.
 *
 * @package WordPress
 * @subpackage ALO EasyMail plugin
 */



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


		// @link http://justintadlock.com/archives/2010/07/10/meta-capabilities-for-custom-post-types
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
		'menu_icon' => 'dashicons-email',
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
 * @link http://justintadlock.com/archives/2010/07/10/meta-capabilities-for-custom-post-types
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
	//$count_recipients = 0; //alo_em_count_recipients_from_meta( $post->ID ); // todo fix
	$recipients = alo_em_get_recipients_from_meta( $post->ID );

	$status = alo_em_get_newsletter_status($post->ID);

	if ( $columns == "easymail_recipients" ) {
		if ( $status == '' && empty( $recipients['total'] ) && empty( $recipients['estimated_total'] ) ) {
			if ( alo_em_user_can_edit_newsletter( $post->ID ) ) echo '<a href="'. get_edit_post_link( $post->ID ) . '">';
			echo '<img src="'. ALO_EM_PLUGIN_URL. '/images/12-exclamation.png" alt="" /> <strong class="easymail-column-no-yet-recipients-'.$user_ID.'">' . __( 'No recipients selected yet', "alo-easymail").'</strong>';
			if ( alo_em_user_can_edit_newsletter( $post->ID ) ) echo '</a>';
		} else {
			if ( alo_em_user_can_edit_newsletter( $post->ID ) ) echo "<a href='#' class='easymail-toggle-short-summary' rel='{$post->ID}'>";
			echo __( 'Total recipients', "alo-easymail") .": ";
			echo alo_em_count_recipients_from_meta( $post->ID );

			if ( alo_em_user_can_edit_newsletter( $post->ID ) ) {
				echo "</a><br />\n";
				echo "<div id='easymail-column-short-summary-{$post->ID}' class='easymail-column-short-summary'>\n". alo_em_recipients_short_summary ( $recipients ) ."</div>\n";
			}
		}
	}

	if ( $columns == "easymail_status" ) {
		if ( ! ( $status == '' && empty( $recipients['total'] ) && empty( $recipients['estimated_total'] ) ) ) {
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

		$report_url = wp_nonce_url( ALO_EM_PLUGIN_URL . '/pages/alo-easymail-admin-report.php?', 'alo-easymail_report');
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


/**
 * When click Preview btn, save the content
 */
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


/**
 * When save newsletter, delete content in transient
 */
function alo_em_delete_newsletter_content_transient ( $post_id ) {
	delete_transient( 'alo_em_content_preview_'.$post_id );
}
add_action('alo_easymail_save_newsletter_meta_extra',  'alo_em_delete_newsletter_content_transient' );



/**
 * Generation of List of recipients in modal
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


/* EOF */