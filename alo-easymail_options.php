<?php
//auth_redirect();
if ( !current_user_can('manage_newsletter_options') ) 	wp_die(__('Cheatin&#8217; uh?'));


// Base link
$link_base = "edit.php?post_type=newsletter&page=alo-easymail/alo-easymail_options.php";
	
global $wp_version, $wpdb, $user_ID, $wp_roles;


// Check IMAP extension is installed
$imap_installed = ( function_exists('imap_open') ) ? true : false;


// delete welcome setting alert
if ( isset($_REQUEST['timeout_alert']) && $_REQUEST['timeout_alert'] == "stop" ) {
	update_option( 'alo_em_timeout_alert', "hide" ); 
}

// If updating languages list
if ( isset($_POST['langs_list']) && current_user_can('manage_options') ) {
	$new_langs = explode ( ",", stripslashes( trim($_POST['langs_list'])) );
	if ( !empty($new_langs[0]) ) {
		for ( $i=0; $i < count($new_langs); $i++ ) {
			if ( strlen( trim($new_langs[$i]) ) < 2 ) unset( $new_langs[$i] );
			$new_langs[$i] = alo_em_short_langcode ( trim($new_langs[$i]) );
		}
		$str_langs = implode ( ',', $new_langs );
		$str_langs = rtrim ( $str_langs, "," );
		update_option('alo_em_langs_list', $str_langs );
	}
}

// All available languages
$languages = alo_em_get_all_languages( false );
// Text fields for multilangual customization
$text_fields = array ( "optin_msg", "optout_msg", "lists_msg", "preform_msg", "disclaimer_msg" );


// If require to check bounce connection or manually check bounces, first submit form and save options
if ( isset( $_POST['test_bounce_connection'] ) || isset( $_POST['check_bounces_now'] ) ) $_POST['submit'] = true;	


if ( isset($_POST['submit']) ) {
	flush_rewrite_rules( false ); // reset for newsletter permalink 

	// -------- Options permitted to all ('manage_newsletter_options')
	// Tab TEXTS
	if ( isset($_REQUEST['task']) && $_REQUEST['task'] == "tab_texts" ) {
		$activamail_subj = array();
		$activamail_mail = array();
		$optin_msg	= array();
		$optout_msg	= array();	
		$lists_msg	= array();
		$disclaimer_msg	= array();
		$unsub_footer = array();
		$preform_msg = array();
		$viewonline_msg = array();
		foreach ( $languages as $key => $lang ) {
			if (isset($_POST['activamail_subj_'.$lang]) && trim( $_POST['activamail_subj_'.$lang] ) != "" ) $activamail_subj[$lang] = stripslashes(trim($_POST['activamail_subj_'.$lang]));
			if (isset($_POST['activamail_mail_'.$lang]) && trim( $_POST['activamail_mail_'.$lang] ) != "" ) $activamail_mail[$lang] = stripslashes(trim($_POST['activamail_mail_'.$lang]));
			if (isset($_POST['optin_msg_'.$lang]) )		$optin_msg[$lang] = stripslashes(trim($_POST['optin_msg_'.$lang]));
			if (isset($_POST['optout_msg_'.$lang]) )	$optout_msg[$lang] = stripslashes(trim($_POST['optout_msg_'.$lang]));
			if (isset($_POST['lists_msg_'.$lang]) )		$lists_msg[$lang] = stripslashes(trim($_POST['lists_msg_'.$lang]));
			if (isset($_POST['disclaimer_msg_'.$lang]) ) $disclaimer_msg[$lang] = stripslashes(trim($_POST['disclaimer_msg_'.$lang]));
			if (isset($_POST['unsub_footer_'.$lang]) )	$unsub_footer[$lang] = stripslashes(trim($_POST['unsub_footer_'.$lang]));
			if (isset($_POST['preform_msg_'.$lang]) )	$preform_msg[$lang] = stripslashes(trim($_POST['preform_msg_'.$lang]));
			if (isset($_POST['viewonline_msg_'.$lang]) ) $viewonline_msg[$lang] = stripslashes(trim($_POST['viewonline_msg_'.$lang]));
		}
		if ( count ($activamail_subj) ) update_option('alo_em_txtpre_activationmail_subj', $activamail_subj );
		if ( count ($activamail_mail) ) update_option('alo_em_txtpre_activationmail_mail', $activamail_mail );
		if ( count ($optin_msg) ) 		update_option('alo_em_custom_optin_msg', $optin_msg );
		if ( count ($optout_msg) ) 		update_option('alo_em_custom_optout_msg', $optout_msg );
		if ( count ($lists_msg) ) 		update_option('alo_em_custom_lists_msg', $lists_msg );		
		if ( count ($disclaimer_msg) ) 		update_option('alo_em_custom_disclaimer_msg', $disclaimer_msg );		
		if ( count ($unsub_footer) ) 	update_option('alo_em_custom_unsub_footer', $unsub_footer );
		if ( count ($preform_msg) ) 	update_option('alo_em_custom_preform_msg', $preform_msg );
		if ( count ($viewonline_msg) ) 	update_option('alo_em_custom_viewonline_msg', $viewonline_msg );
	
	}
	// --------
	
	// -------- Options permitted ONLY to ADMIN ('manage_options')
	if ( current_user_can('manage_options') ) {
		// Tab GENERAL
		if ( isset($_REQUEST['task']) && $_REQUEST['task'] == "tab_general" ) {
			
			if(isset($_POST['subsc_page']) && (int)$_POST['subsc_page'] ) update_option('alo_em_subsc_page', trim($_POST['subsc_page']));
		
			if ( isset($_POST['show_subscripage']) ) {
				update_option('alo_em_show_subscripage', "yes");
			} else {
				update_option('alo_em_show_subscripage', "no") ;
			}
			if ( isset($_POST['embed_css']) ) {
				update_option('alo_em_embed_css', "yes");
			} else {
				update_option('alo_em_embed_css', "no") ;
			}
			if ( isset($_POST['credit_banners']) ) {
				update_option('alo_em_show_credit_banners', "yes");
			} else {
				update_option('alo_em_show_credit_banners', "no") ;
			}
			if ( isset($_POST['no_activation_mail']) ) {
				update_option('alo_em_no_activation_mail', "yes");
			} else {
				update_option('alo_em_no_activation_mail', "no") ;
			}				
			if ( isset($_POST['delete_on_uninstall']) && isset($_POST['delete_on_uninstall_2']) ) {
				update_option('alo_em_delete_on_uninstall', "yes");
			} else {
				update_option('alo_em_delete_on_uninstall', "no") ;
			}
			if ( isset($_POST['publish_newsletters']) ) {
				update_option('alo_em_publish_newsletters', "yes");
			} else {
				update_option('alo_em_publish_newsletters', "no") ;
			}
			if ( isset($_POST['hide_widget_users']) ) {
				update_option('alo_em_hide_widget_users', "yes");
			} else {
				update_option('alo_em_hide_widget_users', "no") ;
			}
			
			
		} // end Tab GENERAL

		// Tab NEWSLETTER
		if ( isset($_REQUEST['task']) && $_REQUEST['task'] == "tab_newsletter" ) {
		
			if(isset($_POST['sender_email'])) update_option('alo_em_sender_email', trim($_POST['sender_email']));
			if(isset($_POST['sender_name'])) update_option('alo_em_sender_name', stripslashes( trim($_POST['sender_name'])) );
			if(isset($_POST['lastposts']) && (int)$_POST['lastposts'] > 0) update_option('alo_em_lastposts', trim($_POST['lastposts']));	
		
			if(isset($_POST['debug_newsletters']) && in_array( $_POST['debug_newsletters'], array("","to_author","to_file") ) )
				update_option('alo_em_debug_newsletters', $_POST['debug_newsletters']);
				
			if ( isset($_POST['filter_the_content']) ) {
				update_option('alo_em_filter_the_content', "yes");
			} else {
				update_option('alo_em_filter_the_content', "no") ;
			}
			if ( isset($_POST['js_rec_list']) ) {
				update_option('alo_em_js_rec_list', "yes");
			} else {
				update_option('alo_em_js_rec_list', "no") ;
			}			
			if(isset($_POST['js_rec_list']) && in_array( $_POST['js_rec_list'], array("ajax_normal","no_ajax_onsavepost") ) )
				update_option('alo_em_js_rec_list', $_POST['js_rec_list']);

			if(isset($_POST['campaign_vars']) && in_array( $_POST['campaign_vars'], array("no","google") ) )
				update_option('alo_em_campaign_vars', $_POST['campaign_vars']);
			
			$theme_options = array_merge ( array('yes'=>'1','no'=>'1'), alo_easymail_get_all_themes() );
			if ( isset($_POST['use_themes']) && array_key_exists( $_POST['use_themes'], $theme_options ) )
				update_option('alo_em_use_themes', $_POST['use_themes']);
			
		} // end Tab NEWSLETTER
		
		// Tab BATCH SENDING
		if ( isset($_REQUEST['task']) && $_REQUEST['task'] == "tab_batch" ) {
			if(isset($_POST['dayrate']) && (int)$_POST['dayrate'] >= 100 && (int)$_POST['dayrate'] <= 10000 ) update_option('alo_em_dayrate', trim((int)$_POST['dayrate']));
			if(isset($_POST['batchrate']) && (int)$_POST['batchrate'] >= 5 && (int)$_POST['batchrate'] <= 200 ) update_option('alo_em_batchrate', trim((int)$_POST['batchrate']));
			if(isset($_POST['sleepvalue']) && (int)$_POST['sleepvalue'] <= 5000 ) update_option('alo_em_sleepvalue', trim((int)$_POST['sleepvalue']));
		} // end Tab BATCH SENDING

		// Tab PERMISSIONS
		if ( isset($_REQUEST['task']) && $_REQUEST['task'] == "tab_permissions" ) {

			// Get role objects: $role_administrator, $role_editor, ecc.
			$roles = array_keys( get_editable_roles() );
			foreach ( $roles as $key )
			{
				if ( $key == 'administrator' ) continue; // skip admin
				${'role_'.$key} = get_role( $key );
			}

			// Option => capabilities
			$map_caps = array(
				'can_edit_own_newsletters' 		=> array('edit_newsletters','delete_newsletters'),
				'can_edit_other_newsletters' 	=> array('edit_others_newsletters','delete_others_newsletters'),
				'can_send_newsletters'			=> array('publish_newsletters'),
				'can_manage_subscribers'		=> array('manage_newsletter_subscribers'),
				'can_manage_options'			=> array('manage_newsletter_options')
			);

			foreach ( $map_caps as $option => $caps )
			{
				$role_options = ( isset($_POST[$option]) ) ? (array)$_POST[$option] : false;
					
				foreach ( $roles as $key )
				{
					if ( $key == 'administrator' ) continue; // skip admin
					
					if ( $role_options && in_array( $key, $role_options ) )
					{
						foreach ( $caps as $cap ) 	${'role_'.$key}->add_cap( $cap );
					}
					else
					{
						foreach ( $caps as $cap ) 	${'role_'.$key}->remove_cap( $cap );
					}
				}
			
			}
			
		} // end Tab PERMISSIONS

		// Tab BOUNCES
		if ( isset($_REQUEST['task']) && $_REQUEST['task'] == "tab_bounces" ) {
		
			$bounce_keys = array(
				"bounce_email",
				"bounce_host",
				"bounce_port",
				"bounce_protocol",
				"bounce_folder",
				"bounce_username",
				"bounce_password",
				"bounce_flags",
				"bounce_interval",
				"bounce_maxmsg",
			);
			
			$new_bounce_settings = array();
			foreach( $bounce_keys AS $bounce_key )
			{
				if ( !empty($_POST[$bounce_key]) ) 
				{
					$new_bounce_settings[$bounce_key] = stripslashes(trim(strip_tags($_POST[$bounce_key])));
				}
			}
			update_option('alo_em_bounce_settings', $new_bounce_settings);
			
		} // end Tab BOUNCES
				
	} 
	// --------
    echo '<div id="message" class="updated fade"><p>'. __("Updated", "alo-easymail") .'</p></div>';
} // end if Submit

?>

<script type="text/javascript">
	var $em = jQuery.noConflict();
	$em(document).ready(function(){
		$em('#easymail_slider').tabs({ fx: { opacity: 'toggle', duration:'fast' }  });
		$em('#activamail_container').tabs();
		<?php 
		foreach ( $text_fields as $text_field ) {
			echo '$em(\'#'.$text_field.'_container\').tabs();'."\n";
		} ?>
		$em('#listname_container').tabs();
		$em('#unsub_footer_container').tabs();	
		$em('#viewonline_msg_container').tabs();	
	});
</script>

<style type="text/css">
	.text-alert { background-color:#FFFFE0;	-moz-border-radius:3px 3px 3px 3px;	border: 1px solid #E6DB55; padding:0 0.6em;	}
	.text-alert p {	padding:0; margin:0.5em 0; }	
</style>
		
<!--<div class="wrap">-->


<div id="easymail_slider" class="wrap">
<div class="icon32" id="icon-options-general"><br></div>
<h2>Alo EasyMail Newsletter Options</h2>

<ul id="easymail_options_tabs">
	<?php if ( current_user_can('manage_options') ) echo '<li><a href="#general">' . __("General", "alo-easymail") .'</a></li>'; ?>
	<?php if ( current_user_can('manage_options') ) echo '<li><a href="#newsletter">' . __("Newsletter", "alo-easymail") .'</a></li>'; ?>
	<li><a href="#texts"><?php _e("Texts", "alo-easymail") ?></a></li>
	<?php if ( current_user_can('manage_options') ) echo '<li><a href="#batchsending">' . __("Batch sending", "alo-easymail") .'</a></li>'; ?>
	<?php if ( current_user_can('manage_options') ) echo '<li><a href="#permissions">' . __("Permissions", "alo-easymail") .'</a></li>'; ?>
	<li><a href="#mailinglists"><?php _e("Mailing Lists", "alo-easymail") ?></a></li>
	<?php if ( current_user_can('manage_options') ) echo '<li><a href="#bounces">' . __("Bounces", "alo-easymail") .'</a></li>'; ?>	
</ul>


<!-- --------------------------------------------
GENERAL
--------------------------------------------  -->

<?php if ( current_user_can('manage_options') ) : /* only admin can */ ?>

<div id="general">

<form action="#general" method="post">
<h2><?php _e("General", "alo-easymail") ?></h2>

<table class="form-table"><tbody>

<?php 
if ( get_option('alo_em_subsc_page') ) {
	$selected_subscripage = get_option('alo_em_subsc_page');
} else {
	$selected_subscripage = "";
}
?>
<tr valign="top">
<th scope="row"><?php _e("Subscription page", "alo-easymail") ?>:</th>
<td>
<?php
$args = array(
	'numberposts' => -1,
	'post_type' => 'page',
	'order' => 'ASC',
	'orderby' => 'title'
); 
$get_pages = get_posts($args);
if ( count($get_pages) ) {
	echo "<select name='subsc_page' id='subsc_page'>";
	echo "<option value=''> </option>";
	foreach($get_pages as $page) :
		echo "<option value='".$page->ID."' ". ( ($page->ID == $selected_subscripage)? " selected='selected'": "") .">#". $page->ID ." ". get_the_title ($page->ID) ." </option>";
	endforeach;
	echo "</select>\n";
}
?>
<br /><span class="description"><?php _e("This should be the page that includes the [ALO-EASYMAIL-PAGE] shortcode. By default, this page is titled &#39;Newsletter&#39;", "alo-easymail") ?>.</span></td>
</tr>


<?php 
if ( get_option('alo_em_show_subscripage') == "yes" ) {
	$checked_show_subscripage = 'checked="checked"';
} else {
	$checked_show_subscripage = "";
}
//$subcripage_link = "<a href='" . get_permalink(get_option('alo_em_subsc_page')) . "'>" . get_the_title (get_option('alo_em_subsc_page')) . "</a>";
?>
<tr valign="top">
<th scope="row"><?php _e("Show subscription page", "alo-easymail") ?>:</th>
<td><input type="checkbox" name="show_subscripage" id="show_subscripage" value="yes" <?php echo $checked_show_subscripage ?> /> <span class="description"><?php _e("If yes, the subscription page appears in menu or widget that list all blog pages", "alo-easymail") ?>.</span></td>
</tr>

<?php 
if ( get_option('alo_em_embed_css') == "yes" ) {
	$checked_embed_css = 'checked="checked"';
} else {
	$checked_embed_css = "";
}
?>
<tr valign="top">
<th scope="row"><?php _e("Embed CSS file", "alo-easymail") ?>:</th>
<td><input type="checkbox" name="embed_css" id="embed_css" value="yes" <?php echo $checked_embed_css ?> /> <span class="description"><?php _e("If yes, the plugin loads the CSS styles from a file in its directory", "alo-easymail") ?>. <?php _e("Tip: copy &#39;alo-easymail.css&#39; to your theme directory and edit it there. Useful to prevent the loss of styles when you upgrade the plugin", "alo-easymail") ?>.</span></td>
</tr>

<?php 
if ( get_option('alo_em_no_activation_mail') == "yes" ) {
	$checked_embed_css = 'checked="checked"';
} else {
	$checked_embed_css = "";
}
?>
<tr valign="top">
<th scope="row"><?php _e("Disable activation e-mail", "alo-easymail") ?>:</th>
<td><input type="checkbox" name="no_activation_mail" id="no_activation_mail" value="yes" <?php echo $checked_embed_css ?> /> <span class="description"><?php _e("If yes, a new subscriber is automatically activated without confirmation e-mail", "alo-easymail") ?>.</span></td>
</tr>


<?php 
if ( get_option('alo_em_hide_widget_users') == "yes" ) {
	$checked_hide_widget_users = 'checked="checked"';
} else {
	$checked_hide_widget_users = "";
}
?>
<tr valign="top">
<th scope="row"><?php _e("Hide widget from users", "alo-easymail") ?>:</th>
<td><input type="checkbox" name="hide_widget_users" id="hide_widget_users" value="yes" <?php echo $checked_hide_widget_users ?> /> <span class="description"><?php _e("If yes, the widget will be not shown to registered users", "alo-easymail") ?>. <?php _e("They can always edit newsletter subscription in profile page", "alo-easymail") ?>.</span></td>
</tr>


<?php 
if ( get_option('alo_em_publish_newsletters') == "yes" ) {
	$checked_publish_newsletters = 'checked="checked"';
} else {
	$checked_publish_newsletters = "";
}
?>
<tr valign="top">
<th scope="row"><?php _e("Publish newsletters online", "alo-easymail") ?>:</th>
<td><input type="checkbox" name="publish_newsletters" id="publish_newsletters" value="yes" <?php echo $checked_publish_newsletters ?> /> <span class="description"><?php _e("If yes, newsletters automatically are published into your blog so they can be read online", "alo-easymail") ?>. <?php _e("If no, newsletters are not available online", "alo-easymail") ?>.</span></td>
</tr>


<?php 
if ( get_option('alo_em_show_credit_banners') == "yes" ) {
	$checked_credit_banners = 'checked="checked"';
} else {
	$checked_credit_banners = "";
}
?>
<tr valign="top">
<th scope="row"><?php _e("Show credit banners in back-end", "alo-easymail") ?>:</th>
<td><input type="checkbox" name="credit_banners" id="credit_banners" value="yes" <?php echo $checked_credit_banners ?> /> <span class="description"><?php _e("You are free to hide the credits, but in that case it's a common practice to make a small donatation via Paypal to the plugin author", "alo-easymail") ?>.</span></td>
</tr>

<?php 
if ( get_option('alo_em_delete_on_uninstall') == "yes" ) {
	$checked_delete_on_uninstall = 'checked="checked"';
} else {
	$checked_delete_on_uninstall = "";
}
?>
<tr valign="top">
<th scope="row"><?php _e("Delete all plugin data on deactivation", "alo-easymail") ?>:</th>
<td><span class="description"><?php _e("On plugin deactivation, all plugin options, preferences and database tables (including all newsletters and subscribers data) will be definitely deleted", "alo-easymail");?>. <?php _e("If you need these data make sure you do a database backup before plugin deactivation", "alo-easymail");?>.</span><br />
<input type="checkbox" name="delete_on_uninstall" id="delete_on_uninstall" value="yes" <?php echo $checked_delete_on_uninstall ?> /><label for="delete_on_uninstall"> <?php _e("Delete all plugin data on deactivation", "alo-easymail") ?></label><br />
<input type="checkbox" name="delete_on_uninstall_2" id="delete_on_uninstall_2" value="yes" <?php echo $checked_delete_on_uninstall ?> /><label for="delete_on_uninstall_2"> <?php _e("Yes, I understand", "alo-easymail") ?>. <?php _e("Delete all plugin data on deactivation", "alo-easymail") ?></label>
</td>
</tr>

</tbody> </table>

<p class="submit">
<input type="hidden" name="user_ID" value="<?php echo (int) $user_ID ?>" />
<input type="hidden" name="task" value="tab_general" /> <?php // reset task ?>
<!--<span id="autosave"></span>-->
<input type="submit" name="submit" value="<?php _e('Update', 'alo-easymail') ?>" class="button-primary" />
</p>
</form>

</div> <!-- end general -->

<?php endif; /* only admin can */ ?>



<!-- --------------------------------------------
NEWSLETTER
--------------------------------------------  -->

<?php if ( current_user_can('manage_options') ) : /* only admin can */ ?>

<div id="newsletter">

<form action="#newsletter" method="post">
<h2><?php _e("Newsletter", "alo-easymail") ?></h2>

<table class="form-table"><tbody>
<tr valign="top">
<th scope="row"><label for="lastposts"><?php _e("Number of last posts to display", "alo-easymail") ?>:</label></th>
<td><input type="text" name="lastposts" value="<?php echo get_option('alo_em_lastposts') ?>" id="lastposts" size="2" maxlength="2" />
<span class="description"><?php _e("Number of recent posts to show in the dropdown list of the newsletter sending form", "alo-easymail");?></span></td>
</tr>

<tr valign="top">
<th scope="row"><label for="sender_email"><?php _e("Sender's email address", "alo-easymail") ?>:</label></th>
<td><input type="text" name="sender_email" value="<?php echo get_option('alo_em_sender_email') ?>" id="sender_email" size="30" maxlength="100" /></td>
</tr>

<tr valign="top">
<th scope="row"><label for="sender_name"><?php _e("Sender's name", "alo-easymail") ?>:</label></th>
<td><input type="text" name="sender_name" value="<?php esc_attr_e( get_option('alo_em_sender_name') ) ?>" id="sender_name" size="30" maxlength="100" /></td>
</tr>


<?php
/*
// maybe useless in v.2...
if ( get_option('alo_em_filter_br') != "no" ) {
	$checked_filter_br = 'checked="checked"';
} else {
	$checked_filter_br = "";
}
*/
if ( get_option('alo_em_filter_the_content') != "no" ) {
	$checked_filter_the_content = 'checked="checked"';
} else {
	$checked_filter_the_content = "";
}
?>
<tr valign="top">
<th scope="row"><?php _e("Filters to the newsletter text", "alo-easymail") ?>:</th>
<td>
<input type="checkbox" name="filter_the_content" id="filter_the_content" value="yes" <?php echo $checked_filter_the_content ?> /><span class="description"> <?php esc_html_e(__("Apply 'the_content' filters and shortcodes to newsletter content", "alo-easymail")) ?></span>
</td>
</tr>


<?php
if ( get_option('alo_em_campaign_vars') ) {
	$selected_campaign_vars = get_option('alo_em_campaign_vars');
} else {
	$selected_campaign_vars = "no";
}
?>
<tr valign="top">
<th scope="row"><?php _e("Appends campaign variables to links", "alo-easymail") ?>:</th>
<td>
<?php $campaign_vars_list = array (
	"no"		=>	__("no", "alo-easymail"),
	"google" 	=> 	__("Google Analytics", "alo-easymail") . ' (<small style="font-style: italic"><a href="http://www.google.com/analytics/" target="_blank">'. __("you need an account", "alo-easymail") .'</a></small>)'
);
foreach( $campaign_vars_list as $key => $label ) :
	echo '<input type="radio" name="campaign_vars" value="'.$key.'" id="campaign_vars_'.$key.'" '. ( ( $key == $selected_campaign_vars )? 'checked="checked"': "") .' />';
	echo ' <label for="campaign_vars_'.$key.'">'. $label .'</label><br />';
endforeach; ?>
<span class="description"><?php _e("The plugin appends campagin variables to links", "alo-easymail") ?>.
<?php echo __("E.g.", "alo-easymail").' '. __("Google Analytics", "alo-easymail") .': </span><br /><code><small>'. '...&utm_source=AloEasyMail&utm_medium=email&utm_campaign={newsletter-id-and-title}&utm_content={requested-url}</small></code>'; ?>
</td>
</tr>


<?php  
if ( get_option('alo_em_use_themes') ) {
	$selected_use_themes = get_option('alo_em_use_themes');
} else {
	$selected_use_themes = "yes"; // deafaut: use theme
}
?>
<tr valign="top">
<th scope="row"><?php _e("Use themes", "alo-easymail") ?>:</th>
<td>
<select name='use_themes' id='use_themes'>
	<option value='no' <?php echo ( ( 'no' == $selected_use_themes )? " selected='selected'": "") ?> ><?php _e("no", "alo-easymail") ?></option>
	<option value='yes' <?php echo ( ( 'yes' == $selected_use_themes )? " selected='selected'": "") ?>><?php echo __("yes", "alo-easymail") . ", " .__(" free choice for authors", "alo-easymail") ?></option>
	<?php 
	$values_use_themes = alo_easymail_get_all_themes();
	foreach( $values_use_themes as $key => $label ) :
		echo "<option value='$key' ". ( ( $key == $selected_use_themes )? " selected='selected'": "") .">". __("yes", "alo-easymail") ." ". __("but always use", "alo-easymail") . ": " . esc_html( $key ). "</option>";
	endforeach; ?>
</select>
<br /><span class="description"><?php _e("Tip: copy &#39;alo-easymail-themes&#39; folder to your theme directory and edit your themes there. Useful to prevent the loss of themes when you upgrade the plugin", "alo-easymail") ?>
</span></td>
</tr>


<?php  
if ( get_option('alo_em_js_rec_list') ) {
	$selected_js_rec_list = get_option('alo_em_js_rec_list');
} else {
	$selected_js_rec_list = "";
}
?>
<tr valign="top">
<th scope="row"><?php _e("Method of creation of the recipient list", "alo-easymail") ?>:</th>
<td>
<select name='js_rec_list' id='js_rec_list'>
	<?php $values_js_rec_list = array ( 
		"ajax_normal" 		=> __("ajax", "alo-easymail") . " (" . __("Default", "alo-easymail") .")",
		"no_ajax_onsavepost"=> __("no ajax", "alo-easymail"). ": " . __("creation when newsletter is saved", "alo-easymail")
	);
	foreach( $values_js_rec_list as $key => $label ) :
		echo "<option value='$key' ". ( ( $key == $selected_js_rec_list )? " selected='selected'": "") .">". esc_html( $label ) ."</option>";
	endforeach; ?>
</select>
<br /><span class="description"><?php _e("If the standard and cool method does not work for you, you can try another option", "alo-easymail") ?>.
<?php _e("Otherwise, you can create the list directly when the newsletter is saved, without use of ajax", "alo-easymail") ?>:
<?php _e("this is the quickest and safest mode, but it could not work if case of several thousands of recipients", "alo-easymail") ?>.
</span></td>
</tr>


<?php  
if ( get_option('alo_em_debug_newsletters') ) {
	$selected_debug_newsletters = get_option('alo_em_debug_newsletters');
} else {
	$selected_debug_newsletters = "";
}
?>
<tr valign="top">
<th scope="row"><?php _e("Debug newsletters", "alo-easymail") ?>:</th>
<td>
<select name='debug_newsletters' id='debug_newsletters'>
	<option value=''><?php _e("no", "alo-easymail") ?></option>
	<?php $values_debug_newsletters = array ( "to_author" => __("send all emails to the author", "alo-easymail"), "to_file" => __("put all emails into a log file", "alo-easymail") );
	foreach( $values_debug_newsletters as $key => $label ) :
		echo "<option value='$key' ". ( ( $key == $selected_debug_newsletters )? " selected='selected'": "") .">". esc_html( $label ). "</option>";
	endforeach; ?>
</select>
<br /><span class="description"><?php _e("If you choose a debug mode the newsletters won&#39;t be sent to the selected recipients", "alo-easymail") ?>:<br />
<ul style="margin-left:20px;font-size:90%">
<li><code><?php _e("send all emails to the author", "alo-easymail") ?></code>: <?php _e("all messages will be sent to the newsletter author", "alo-easymail") ?>.</li>
<li><code><?php _e("put all emails into a log file", "alo-easymail") ?></code>: <?php _e("all messages will be recorded into a log file", "alo-easymail") ?> 
(<?php printf( __("called %s and saved in %s", "alo-easymail"), "&quot;user_{AUTHOR-ID}_newsletter_{NEWSLETTER-ID}.log&quot;", "&quot;".WP_CONTENT_DIR."&quot;" ) ?>): <?php _e("the log file is accessible on your server and contains personal information so you have to delete it as soon as possible!", "alo-easymail") ?></li>
</ul>
</span></td>
</tr>

</tbody> </table>

<p class="submit">
<input type="hidden" name="user_ID" value="<?php echo (int) $user_ID ?>" />
<input type="hidden" name="task" value="tab_newsletter" /> <?php // reset task ?>
<!--<span id="autosave"></span>-->
<input type="submit" name="submit" value="<?php _e('Update', 'alo-easymail') ?>" class="button-primary" />
</p>
</form>

</div> <!-- end general -->

<?php endif; /* only admin can */ ?>


<!-- --------------------------------------------
TEXTS
--------------------------------------------  -->

<div id="texts">

<form action="#texts" method="post">
<h2><?php _e("Texts", "alo-easymail") ?></h2>

<table class="form-table"><tbody>

<?php
if ( alo_em_multilang_enabled_plugin() == false ) {
	echo '<tr valign="top">';
	echo '<td colspan="2">';
		echo '<div class="text-alert">';
		echo '<p>'. __('No multilanguage plugin is enabled, so you will only see texts in the main language of the site', 'alo-easymail') .'.</p>';
		echo '<p>'. __('Recommended plugins, fully compatible with EasyMail, for a complete multilingual functionality', 'alo-easymail') .': ';
		echo '<a href="http://wpml.org/" target="_blank">WPML</a>, ';
		echo '<a href="http://wordpress.org/plugins/qtranslate/" target="_blank">qTranslate</a>, ';
		echo '<a href="http://wordpress.org/plugins/polylang/" target="_blank">Polylang</a>';	
		echo '.</p>';
		//echo '<p>'. sprintf( __('Type the texts in all available languages (they are found in %s)', 'alo-easymail'), '<em>'.WP_LANG_DIR.'</em>' ) .".</p>";
		echo '<p>'. __('If you like here you can list the languages available', 'alo-easymail') .':<br />';
		$langs_list = ( get_option( 'alo_em_langs_list' ) != "" ) ? get_option( 'alo_em_langs_list' ) : "";
		echo '<input type="text" name="langs_list" value="' . $langs_list .'"  />';
		echo '<input type="submit" name="submit" value="'. __('Update', 'alo-easymail') .'" class="button" /> ';
		echo '<span class="description">'. __('List of two-letter language codes separated by commas', 'alo-easymail'). ' ('. sprintf( '<a href="http://en.wikipedia.org/wiki/List_of_ISO_639-1_codes" target="_blank">%s</a>', __('iso 639-1 codes', 'alo-easymail') ) . '). '. __('Sample:', 'alo-easymail') .' en,de,it</span>';
		echo '</p>';
		echo '<p>'. __("The plugin looks for the subscriber&#39;s language in the browser setting and sends the e-mail accordingly", 'alo-easymail') . '.</p>';
		echo '<p>'. __('If you are not using a multilanguage site ignore this piece of information', 'alo-easymail') .'.</p>';
		
		echo '</div>';
	echo '</td></tr>';
}
?>


<tr valign="top">
<th scope="row">
<h4><?php _e("Widget/Page Texts", "alo-easymail") ?></h4>
</th><td></td>
</tr>


<?php 
// Texts fields

foreach ( $text_fields as $text_field ) : ?>
	
	<tr valign="top">
	<th scope="row">
	<?php 
	switch ($text_field) {
		case "optin_msg": 	_e("Optin message", "alo-easymail"); break;
		case "optout_msg": 	_e("Optout message", "alo-easymail"); break;
		case "lists_msg": 	_e("Invite to join mailing lists", "alo-easymail"); break;				
		case "disclaimer_msg": 	_e("Policy claim", "alo-easymail"); break;	
		case "preform_msg": 	_e("Top claim", "alo-easymail"); break;	
	}
	?>:
	</th>
	<td><span class="description"><?php _e("Leave blank to use default text", "alo-easymail") ?>:</span>
	<?php 
	switch ($text_field) {
		case "optin_msg": 	_e("Yes, I would like to receive the Newsletter", "alo-easymail"); break;
		case "optout_msg": 	_e("No, please do not email me", "alo-easymail"); break;
		case "lists_msg": 	_e("You can also sign up for specific lists", "alo-easymail"); break;
		case "disclaimer_msg": 
			echo "(". __("empty", "alo-easymail"). ") ";
			echo '<br /><span class="description">'. __("If filled in it will appear at the bottom of widget/page. Useful to show/link more info about privacy", "alo-easymail"). '.</span>';  
			break;			
		case "preform_msg": 
			echo "(". __("empty", "alo-easymail"). ") ";
			echo '<br /><span class="description">'. __("If filled in it will appear at the top of widget/page. Useful to invite to subscribe", "alo-easymail"). '.</span>';  
			break;							
	}
	?>
	<div id="<?php echo $text_field ?>_container">
	
	<?php
	$custom_texts 	= get_option( 'alo_em_custom_'. $text_field );
	
	// Set tabs and fields
	if ( $languages ) {
		$lang_li = array();
		$lang_div = array();	
		foreach ( $languages as $key => $lang ) {
			$lang_li[$lang] = '<li><a href="#'.$text_field.'_div_'.$lang.'"><strong>' . alo_em_get_lang_name( $lang ) .'</strong></a></li>';
			$lang_div[$lang] = '<div id ="'.$text_field.'_div_'.$lang.'">';
			$lang_text = ( !empty( $custom_texts[$lang] ) ) ? esc_attr($custom_texts[$lang]) : "";
			switch ( $text_field ) {
				case 'disclaimer_msg': // textarea: more space
					$lang_div[$lang] .= '<textarea id="'.$text_field.'_'.$lang.'" name="'.$text_field.'_'.$lang.'" cols="100" rows="10" style="width:100%">' . $lang_text .'</textarea>';
					break;
				default:	// input
					$lang_div[$lang] .= '<input type="text" name="'.$text_field.'_'.$lang.'" value="' . $lang_text .'" id="'.$text_field.'_'.$lang.'" maxlength="100" style="width:100%" />';
			}
			$lang_div[$lang] .= '</div>';
		}
	}
	?>
	<ul id="<?php echo $text_field?>_tabs">
	<?php echo implode ( "\n\n", $lang_li); ?>
	</ul>

<?php echo implode ( "\n\n", $lang_div);?>
</div>

</td>
</tr>

<?php endforeach; // text_fields
?>



<tr valign="top">
<th scope="row">
<h4><?php _e("Communications", "alo-easymail") ?></h4>
</th><td></td>
</tr>

<tr valign="top">
<th scope="row"><?php _e("Activation e-mail", "alo-easymail") ?>:</th>
<td><span class="description"><?php _e("Leave blank to use default text", "alo-easymail") ?>.</span>
<div id="activamail_container">
	<?php 
	$subjects = get_option( 'alo_em_txtpre_activationmail_subj' );
	$mails = get_option( 'alo_em_txtpre_activationmail_mail' );
	// Set tabs and fields
	if ( $languages ) {
		$lang_li = array();
		$lang_div = array();	
		foreach ( $languages as $key => $lang ) {
			$lang_li[$lang] = '<li><a href="#activamail_div_'.$lang.'">'. /*alo_em_get_lang_flag($lang, false) .*/ ' <strong>' . alo_em_get_lang_name( $lang ) .'</strong>';
			$lang_li[$lang] .= '</a></li>';
			$lang_div[$lang] = '<div id ="activamail_div_'.$lang.'"><span class="description">'. __("Subject", "alo-easymail") .'</span><br />';
			$lang_subj = ( !empty($subjects[$lang]) ) ? esc_attr($subjects[$lang]) : "";
			$lang_div[$lang] .= '<input type="text" name="activamail_subj_'.$lang.'" value="' . $lang_subj .'" id="activamail_subj_'.$lang.'" maxlength="100" style="width:100%;margin-bottom:8px" /><br />';
			$lang_mail = ( !empty($mails[$lang]) ) ? esc_html($mails[$lang]) : "";
			$lang_div[$lang] .= '<span class="description">'.__("Main body", "alo-easymail").'</span><br /><textarea name="activamail_mail_'.$lang.'" rows="6" style="width:100%" />' . $lang_mail .'</textarea>';			
			$lang_div[$lang] .= '</div>';
		}
	}
	?>
	<ul id="activamail_tabs">
	<?php echo implode ( "\n\n", $lang_li); ?>
	</ul>

<?php echo implode ( "\n\n", $lang_div);?>
</div>

<p><?php _e("You can use the following tags", "alo-easymail");?>:</p>
<ul style="margin-left:20px">
	<li><code>%BLOGNAME%</code>: <?php _e("the blog name", "alo-easymail");?></li>
	<li><code>%NAME%</code>: <?php _e("the subscriber name", "alo-easymail");?></li>
	<li><code>%ACTIVATIONLINK%</code>: <?php _e("the url that the new subscriber must click/visit to confirm the subscription", "alo-easymail");?></li>
</ul>	
</td>
</tr>

<tr valign="top">
<th scope="row"><?php _e("Unsubscription disclaimer", "alo-easymail") ?>:<br />[USER-UNSUBSCRIBE]</th>
<td><span class="description"><?php _e("Leave blank to use default text", "alo-easymail") ?>:</span><br />
<?php
echo "&lt;p&gt;&lt;em&gt;". __("You have received this message because you subscribed to our newsletter. If you want to unsubscribe: ", "alo-easymail")." ";
echo __("visit this link", "alo-easymail") ."&lt;br /&gt; %UNSUBSCRIBELINK%";
echo "&lt;/em&gt;&lt;/p&gt;";
?>
<div id="unsub_footer_container">
	<?php 
	$custom_texts = get_option( 'alo_em_custom_unsub_footer' );
	if ( $languages ) {
		$lang_li = array();
		$lang_div = array();	
		foreach ( $languages as $key => $lang ) {
			$lang_li[$lang] = '<li><a href="#unsub_footer_div_'.$lang.'"><strong>' . alo_em_get_lang_name( $lang ) .'</strong></a></li>';
			$lang_div[$lang] = '<div id ="unsub_footer_div_'.$lang.'">';
			$lang_text = ( !empty( $custom_texts[$lang] ) ) ? esc_html($custom_texts[$lang]) : "";
			$lang_div[$lang] .= '<textarea name="unsub_footer_'.$lang.'" rows="3" style="width:100%" />' . $lang_text .'</textarea>';
			$lang_div[$lang] .= '</div>';
		}
	}
	?>
	<ul id="unsub_footer_tabs">
	<?php echo implode ( "\n\n", $lang_li); ?>
	</ul>

<?php echo implode ( "\n\n", $lang_div);?>
</div>

<p><?php _e("You can use the following tags", "alo-easymail");?>:</p>
<ul style="margin-left:20px">
	<li><code>%BLOGNAME%</code>: <?php _e("the blog name", "alo-easymail");?></li>
	<li><code>%UNSUBSCRIBELINK%</code>: <?php _e("the html link that the new subscriber must click/visit to unsubscribe the newsletter", "alo-easymail");?></li>
	<li><code>%UNSUBSCRIBEURL%</code>: <?php _e("the plain url that the new subscriber must click/visit to unsubscribe the newsletter", "alo-easymail");?>.
	<?php echo __("It's a plain url, you have to compose the link", "alo-easymail").': ';
	echo '<code>'. esc_html( "<a href=\"%UNSUBSCRIBEURL%\">...</a>" ) .'</code>';?></li>
</ul>	
</td>
</tr>


<tr valign="top">
<th scope="row"><?php _e("Read newsletter online", "alo-easymail") ?>:<br />[READ-ONLINE]</th>
<td><span class="description"><?php _e("Leave blank to use default text", "alo-easymail") ?>:</span><br />
<?php
echo "&lt;p&gt;&lt;em&gt;". __("To read the newsletter online you can visit this link:", "alo-easymail") ." %NEWSLETTERLINK% &lt;/em&gt;&lt;/p&gt;";
?>
<div id="viewonline_msg_container">
	<?php 
	$custom_texts = get_option( 'alo_em_custom_viewonline_msg' );
	if ( $languages ) {
		$lang_li = array();
		$lang_div = array();	
		foreach ( $languages as $key => $lang ) {
			$lang_li[$lang] = '<li><a href="#viewonline_msg_div_'.$lang.'"><strong>' . alo_em_get_lang_name( $lang ) .'</strong></a></li>';
			$lang_div[$lang] = '<div id ="viewonline_msg_div_'.$lang.'">';
			$lang_text = ( !empty( $custom_texts[$lang] ) ) ? esc_html($custom_texts[$lang]) : "";
			$lang_div[$lang] .= '<textarea name="viewonline_msg_'.$lang.'" rows="3" style="width:100%" />' . $lang_text .'</textarea>';
			$lang_div[$lang] .= '</div>';
		}
	}
	?>
	<ul id="viewonline_msg_tabs">
	<?php echo implode ( "\n\n", $lang_li); ?>
	</ul>

<?php echo implode ( "\n\n", $lang_div);?>
</div>

<p><?php _e("You can use the following tags", "alo-easymail");?>:</p>
<ul style="margin-left:20px">
	<li><code>%NEWSLETTERLINK%</code>: <?php _e("html link to the newsletter web url", "alo-easymail");?></li>
	<li><code>%NEWSLETTERURL%</code>: <?php _e("plain url to the newsletter web url", "alo-easymail");?>.
	<?php echo __("It's a plain url, you have to compose the link", "alo-easymail").': ';
	echo '<code>'. esc_html( "<a href=\"%NEWSLETTERURL%\">...</a>") .'</code>';?></li>
</ul>	
</td>
</tr>


</tbody> </table>
    
<p class="submit">
<input type="hidden" name="user_ID" value="<?php echo (int) $user_ID ?>" />
<input type="hidden" name="task" value="tab_texts" /> <?php // reset task ?>
<!--<span id="autosave"></span>-->
<input type="submit" name="submit" value="<?php _e('Update', 'alo-easymail') ?>" class="button-primary" />
</p>
</form>

</div> <!-- end Texts -->


<!-- --------------------------------------------
BATCH SENDING
--------------------------------------------  -->

<?php if ( current_user_can('manage_options') ) : /* only admin can */ ?>

<div id="batchsending">

<form action="#batchsending" method="post">
<h2><?php _e("Batch sending", "alo-easymail") ?></h2>


<table class="form-table"><tbody>

<?php
if ( defined( 'ALO_EM_DAYRATE' ) || defined( 'ALO_EM_BATCHRATE' ) || defined( 'ALO_EM_SLEEPVALUE' ) ) {
	echo '<tr valign="top">';
	echo '<td colspan="2">';
		echo '<div class="text-alert">';
		echo '<p>'. sprintf( __('Some parameters are already setted up in %s, so the values below could be ignored', 'alo-easymail'), '<em>wp-config.php</em>')  .'.</p>';
		echo '</div>';
	echo '</td></tr>';
}

// Try to trigger WP cron
$response = wp_remote_get( site_url( 'wp-cron.php' ) );
if ( ! is_wp_error( $response ) && $response['response']['code'] != '200' ) {
	echo '<tr valign="top">';
	echo '<td colspan="2">';
		echo '<div class="text-alert"><p><img src="'.ALO_EM_PLUGIN_URL.'/images/12-exclamation.png" /> <strong>' . __('WP-Cron is not working properly', 'alo-easymail') .':</strong> ';
		echo sprintf( __( '%s is returning a %s response which could mean cron jobs aren\'t getting fired properly', 'alo-easymail' ), '<code>wp-cron.php</code>', '<code>' . $response['response']['code'] .': ' . $response['response']['message'] . '</code>' ). '.<br />';
		echo __('The file seems to be not accessible: is your blog behind some kind of authentication, maintenance plugin, .htpasswd protection?', 'alo-easymail');
		echo ' <a href="http://www.eventualo.net/blog/wp-alo-easymail-newsletter-faq/#faq-3" target="_blank">'. __('For more info, visit the FAQ of the site.', 'alo-easymail').'</a> ';
		echo '<br /><em>('. __('If you are using an external cron job ignore this piece of information', 'alo-easymail').').</em>';
		echo '</p></div>';
	echo '</td></tr>';	
}


// Next batch sending scheduled in WP cron
$next_cron = wp_next_scheduled( 'alo_em_batch' );
if( !$next_cron ) {
	// we try to scheduled it now
	wp_schedule_event( time() +60, 'alo_em_interval', 'alo_em_batch' ); 
	$next_cron = wp_next_scheduled( 'alo_em_batch' );
}
echo '<tr valign="top">';
echo '<td colspan="2">';
	if ( $next_cron ) {
		echo '<div class="easymail-alert" style="background-color:#99FF66"><img src="'.ALO_EM_PLUGIN_URL.'/images/yes.png" style="vertical-align: text-bottom;" /> <em>' . sprintf( __('The cron is scheduled to launch a batch every %s minutes', 'alo-easymail'), ALO_EM_INTERVAL_MIN ).'. '. __('Next possible sending', 'alo-easymail') .': '. date_i18n( __( 'j M Y @ G:i', 'alo-easymail' ), $next_cron + 3600 * get_option('gmt_offset', 0 ) ) . '</em></div>';
	} else {
		echo '<div class="text-alert"><p><img src="'.ALO_EM_PLUGIN_URL.'/images/12-exclamation.png" /> <strong>' . __('There is not any next scheduled sending in WP-Cron', 'alo-easymail') .':</strong> ';
		echo __('you can try to deactivate and activate the plugin', 'alo-easymail'). '. ';
		echo ' <a href="http://www.eventualo.net/blog/wp-alo-easymail-newsletter-faq/#faq-3" target="_blank">'. __('For more info, visit the FAQ of the site.', 'alo-easymail').'</a> ';
		echo '<br /><em>('. __('If you are using an external cron job ignore this piece of information', 'alo-easymail').').</em>';
		echo '</p></div>';
	}
echo '</td></tr>';
		
?>

<tr valign="top">
<th scope="row"><label for="dayrate"><?php _e("Maximum number of emails that can be sent in a 24-hr period", "alo-easymail") ?>:</label></th>
<td><input type="text" name="dayrate" value="<?php echo get_option('alo_em_dayrate') ?>" id="dayrate" size="5" maxlength="5" />
<span class="description">(100 - 10000)</span></td>
</tr>

<tr valign="top">
<th scope="row"><label for="batchrate">
	<?php _e("Maximum number of emails that can be sent per batch", "alo-easymail"); ?>
	<?php echo " (". sprintf( __('every %s minutes', "alo-easymail"), ALO_EM_INTERVAL_MIN ) ."):"; ?>
	</label></th>
<td><input type="text" name="batchrate" value="<?php echo get_option('alo_em_batchrate') ?>" id="batchrate" size="5" maxlength="3" />
<span class="description">(5 - 200) <?php _e("Recommended", "alo-easymail") ?>: &le; 30.</span></td>
</tr>

<tr valign="top">
<th scope="row"><label for="sleepvalue"><?php _e("Interval between emails in a single batch, in milliseconds", "alo-easymail") ?>:</label></th>
<td><input type="text" name="sleepvalue" value="<?php echo (int)get_option('alo_em_sleepvalue') ?>" id="sleepvalue" size="5" maxlength="4" />
<span class="description">(0 - 5000) <?php _e("Default", "alo-easymail") ?>: 0.<br /><?php _e("Usually you do not have to modify this value", "alo-easymail") ?>.<br /><?php _e("It is useful if your provider allows a maximum number of emails that can be sent per second or minute", "alo-easymail") ?>.<br /><?php _e("The higher this value, the lower the number of emails sent for each batch", "alo-easymail") ?>. </span></td>
</tr>

</tbody> </table>

<div style="width: 700px;background-color:#ddd;margin-top:15px;padding:10px 20px 15px 20px">

	<h4 style="text-align: center"><?php _e("What about the batch sending based on WP cron system?", "alo-easymail") ?></h4>
	<p style="font-size:80%;">
		<?php echo '<strong>'. esc_html( __("Let&#39;s speak clearly: the WP cron system is a pseudo-cron and it's not accurate by definition", "alo-easymail") ). '</strong>' ?>.
	</p>		
	<p style="font-size:80%;">
		<?php esc_html_e( sprintf(__("After a newsletter is scheduled for sending, the plugin *will try* to send it to a small number of recipients every %s minutes, until all recipients have been included", "alo-easymail"), ALO_EM_INTERVAL_MIN )) ?>.
	</p>
	<p style="font-size:80%;">
		<?php esc_html_e( sprintf(__("The WP cron makes a sending when someone hits your blog after at least %s minutes from the previous sending. If you receive one visit every %s minutes you will have one sending every %s minutes", "alo-easymail"), ALO_EM_INTERVAL_MIN, ALO_EM_INTERVAL_MIN*6, ALO_EM_INTERVAL_MIN*6 ) ) ?>.
		<?php esc_html_e( __("The number of recipients for each sending is calculated on the cut-off of emails you can send per day and on the time interval from the previous sending", "alo-easymail") ) ?>.				
	</p>
	<p style="font-size:80%;">
		<?php echo '<strong>'. esc_html( __("If your blog gets a few visitors a day or you like to have an accurate batch sending, you have to set up an external cron job", "alo-easymail") ). '</strong>' ?>.
	</p>
	<p style="font-size:80%;">
		<?php esc_html_e( __("You can ask your provider to setup a GET cron job that periodically hits the WP cron on your blog, e.g.", "alo-easymail")) ?>:<br />
		<code><?php echo '*/'.ALO_EM_INTERVAL_MIN.' * * * * GET ' . site_url( 'wp-cron.php?doing_wp_cron' ) .' > /dev/null' ?></code>
	</p>		
	
	<h4 style="text-align: center;margin-top:20px"><?php _e("Important advice to calculate the best limit", "alo-easymail") ?></h4>
	<ol style="font-size:80%;">
		<li><?php esc_html_e( __("Ask your provider the cut-off of emails you can send per day. Multiplying the hourly limit by 24 is not the right way to calculate it: very often the resulting number is much higher than the actual cut-off.", "alo-easymail")) ?></li>
		<li><?php esc_html_e( __("Subtract from this cut-off the number of emails you want to send from your blog (e.g. registration procedures, activation and unsubscribing of EasyMail, notices from other plugins etc.).", "alo-easymail")) ?></li>
		<li><?php esc_html_e( __("If in doubt, just choose a number definitely lower than the cut-off: you'll have more chances to have your mail delivered, and less chances to end up in a blacklist...", "alo-easymail")) ?></li>
	</ol>
	
	<p style="font-size:80%;"><em><?php _e("For more links you can use the Help button", "alo-easymail") ?></em></p>			
</div>

<p class="submit">
<input type="hidden" name="user_ID" value="<?php echo (int) $user_ID ?>" />
<input type="hidden" name="task" value="tab_batch" /> <?php // reset task ?>
<!--<span id="autosave"></span>-->
<input type="submit" name="submit" value="<?php _e('Update', 'alo-easymail') ?>" class="button-primary" />
</p>
</form>

</div> <!-- end Batch sending -->

<?php endif; /* only admin can */ ?>

<!-- --------------------------------------------
PERMISSIONS
--------------------------------------------  -->

<?php if ( current_user_can('manage_options') ) : /* only admin can */ ?>

<div id="permissions">

<form action="#permissions" method="post">
<h2><?php _e("Permissions", "alo-easymail") ?></h2>

<table class="form-table"><tbody>

<tr valign="top">
<th scope="row"><?php _e("Can create and edit own newsletters", "alo-easymail") ?>:</th>
<td>
<div style="float: left;display: inline-block">
<?php 
echo alo_em_role_checkboxes ( 'can_edit_own_newsletters', array('edit_newsletters','delete_newsletters') );
?>
</div>
<span class="description"  style="float: left;margin-left: 2em"> <?php _e("The user with this capability can manage own newsletters (view the report, delete)", "alo-easymail") ?>.
<br /><?php _e("This user cannot publish and send newsletters", "alo-easymail") ?>.
</span>
</td>
</tr>

<tr valign="top">
<th scope="row"><?php _e("Can edit newsletters of other users", "alo-easymail") ?>:</th>
<td>
<div style="float: left;display: inline-block">
<?php 
echo alo_em_role_checkboxes ( 'can_edit_other_newsletters', array('edit_others_newsletters','delete_others_newsletters') );
?>
</div>
<span class="description"  style="float: left;margin-left: 2em"> <?php _e("The user with this capability can manage newsletters of all users (view the report, delete)", "alo-easymail") ?>.
<br /><?php _e("This user cannot publish and send newsletters", "alo-easymail") ?>.
</span>
</td>
</tr>

<tr valign="top">
<th scope="row"><?php _e("Can publish and send newsletters", "alo-easymail") ?>:</th>
<td>
<div style="float: left;display: inline-block">
<?php 
echo alo_em_role_checkboxes ( 'can_send_newsletters', array('publish_newsletters') );
?>
</div>
<span class="description"  style="float: left;margin-left: 2em"> <?php _e("The user with this capability can send and publish newsletters", "alo-easymail") ?>.
</span>
</td>
</tr>

<tr valign="top">
<th scope="row"><?php _e("Can manage newsletter subscribers", "alo-easymail") ?>:</th>
<td>
<div style="float: left;display: inline-block">
<?php 
echo alo_em_role_checkboxes ( 'can_manage_subscribers', array('manage_newsletter_subscribers') );
?>
</div>
<span class="description"  style="float: left;margin-left: 2em"> <?php _e("The user with this capability can manage subscribers (add, delete, assign to mailing lists...)", "alo-easymail") ?>.
</span>
</td>
</tr>

<tr valign="top">
<th scope="row"><?php _e("Can manage newsletter options", "alo-easymail") ?>:</th>
<td>
<div style="float: left;display: inline-block">
<?php 
echo alo_em_role_checkboxes ( 'can_manage_options', array('manage_newsletter_options') );
?>
</div>
<span class="description"  style="float: left;margin-left: 2em"> <?php _e("The user with this capability can set up these setting sections", "alo-easymail") ?>: 
<?php _e("Texts", "alo-easymail") ?>, 
<?php _e("Mailing Lists", "alo-easymail") ?>.<br />
<?php _e("Other sections can be modified only by administrators", "alo-easymail") ?>.
</span>
</td>
</tr>


</tbody> </table>

<p class="submit">
<input type="hidden" name="user_ID" value="<?php echo (int) $user_ID ?>" />
<input type="hidden" name="task" value="tab_permissions" /> <?php // reset task ?>
<input type="submit" name="submit" value="<?php _e('Update', 'alo-easymail') ?>"  class="button-primary" />
</p>
</form>

</div> <!-- end permissions -->

<?php endif; /* only admin can */ ?>

<!-- --------------------------------------------
MAILING LISTS 
--------------------------------------------  -->
<div id="mailinglists">

<h2><?php _e("Mailing Lists", "alo-easymail"); ?></h2>

<?php //echo "<pre style='font-size:80%'>"; print_r( $_REQUEST ); echo "</pre>"; // DEBUG ?>

<?php 
// If exists, get the id list to work on	
if ( isset( $_REQUEST['list_id'] ) ) {
	$list_id = stripslashes ( $wpdb->escape ( $_REQUEST['list_id'] ) );
	if ( !is_numeric ( $list_id ) ) $list_id = false;
} else {
	$list_id = false;
}
	
// Updating Request...
if ( isset( $_REQUEST['task'] ) ) {
	switch ( $_REQUEST['task'] ) {
		case "edit_list":	// EDIT an existing Mailing list
			if ( $list_id ) {
				$mailinglists = alo_em_get_mailinglists ( 'hidden,admin,public' );
				$list_name = $mailinglists [$list_id]["name"];
				$list_available = $mailinglists [$list_id]["available"];	
				$list_order = $mailinglists [$list_id]["order"];		
			} else {
				echo '<div id="message" class="error"><p>'. __("Error during operation.", "alo-easymail") .'</p></div>';
			}				
			break;
		case "save_list":	// SAVE a mailing list (add or update)
			if ( isset($_REQUEST['submit_list']) ) {
				//$list_name = stripslashes( trim( $_POST['elp_list_name'] ) );
				
				// List name	
				$list_name	= array();
				foreach ( $languages as $key => $lang ) {
					if (isset($_POST['listname_'.$lang]) )	$list_name[$lang] = stripslashes(trim($_POST['listname_'.$lang]));
				}
				
				$list_available = stripslashes( trim( $_POST['elp_list_available'] ) );
				$list_order = stripslashes( trim( $_POST['elp_list_order'] ) );
				if ( $list_name && $list_available && is_numeric($list_order) ) {
					$mailinglists = alo_em_get_mailinglists ( 'hidden,admin,public' );
					if ( $list_id )  { // update
						$mailinglists [$list_id] = array ( "name" => $list_name, "available" => $list_available, "order" => $list_order );
					} else { // or add a new
						if ( empty($mailinglists) ) { // if 1st list, skip index 0
							$mailinglists [] = array ( "name" => "not-used", "available" => "deleted", "order" => "");
						}	
						$mailinglists [] = array ( "name" => $list_name, "available" => $list_available, "order" => $list_order);
					}
					if ( alo_em_save_mailinglists ( $mailinglists ) ) {
						unset ( $list_id );
						unset ( $list_name );
						unset ( $list_available );						
						unset ( $list_order );	
						echo '<div id="message" class="updated fade"><p>'. __("Updated", "alo-easymail") .'</p></div>';
					} else {
						echo '<div id="message" class="error"><p>'. __("Error during operation.", "alo-easymail") .'</p></div>';
					}
				} else {
					echo '<div id="message" class="error"><p>'. __("Inputs are incompled or wrong. Please check and try again.", "alo-easymail") .'</p></div>';
				}
			}	
			break;
		case "del_list":	// DELETE a Mailing list
			if ( $list_id  ) {
				$mailinglists = alo_em_get_mailinglists ( 'hidden,admin,public' );
				//$mailinglists [$list_id]["available"] = "deleted";
				unset ( $mailinglists [$list_id] );
				if ( alo_em_save_mailinglists ( $mailinglists ) && alo_em_delete_all_subscribers_from_lists ($list_id) ) {	
					unset ( $list_id );
					unset ( $list_name );
					unset ( $list_available );	
					unset ( $list_order );				
					echo '<div id="message" class="updated fade"><p>'. __("Updated", "alo-easymail") .'</p></div>';
				} else {
					echo '<div id="message" class="error"><p>'. __("Error during operation.", "alo-easymail") .'</p></div>';
				}					
			} else {
				echo '<div id="message" class="error"><p>'. __("Error during operation.", "alo-easymail") .'</p></div>';
			}				
			break;								
	}
}
?>
	   	
<div style="padding: 10px">
<?php _e("You can setup mailing lists. For each you have to specify the name, the order (the lowest appear at top) and the availability", "alo-easymail") ?>:
<ul style="margin:10px">
<li><code><?php _e('hidden', 'alo-easymail')?></code>: <span class="description"><?php _e('the list can be shown only here in settings and nowhere in the site', 'alo-easymail')?></span></li>
<li><code><?php _e('admin side only', 'alo-easymail')?></code>: <span class="description"><?php _e('the list is available only for administratrion use (settings, sending page, subscribers), so subscribers cannot see it', 'alo-easymail')?></span></li>
<li><code><?php _e('entire site', 'alo-easymail')?></code>: <span class="description"><?php _e('the list is available in the whole site, so subscribers can see it', 'alo-easymail')?></span></li>
</ul>
</div>

<h3><?php if ( isset ( $_REQUEST['task'] ) && $_REQUEST['task'] == 'edit_list' && $list_id ) { _e("Edit list", "alo-easymail"); } else { _e("New list", "alo-easymail"); } ?></h3>
<!-- Edit the new/selected list-->
<form action="#mailinglists" method="post">
<table <?php if ( isset ( $_REQUEST['task'] ) && $_REQUEST['task'] == 'edit_list' && $list_id) echo "style='background-color:#FFFFC0'" ?> ><tbody>
<tr valign="top">
	<th><?php _e('List name', 'alo-easymail') ?></th>
	<th><?php _e('Availability', 'alo-easymail') ?></th>
	<th><?php _e('Order', 'alo-easymail') ?></th>
	<th></th>
</tr>	
<tr  valign="bottom">
<td>

<!--
<input type="text" name="elp_list_name" value="<?php if ( isset ( $_REQUEST['task'] ) && $_REQUEST['task'] == 'edit_list' && $list_id ) echo $list_name; ?>" id="elp_list_name" size="30" maxlength="50" />
-->

<div id="listname_container">
	<?php 
	// Set tabs and fields
	if ( $languages ) {
		$lang_li = array();
		$lang_div = array();	
		foreach ( $languages as $key => $lang ) {
			$lang_li[$lang] = '<li><a href="#listname_div_'.$lang.'"><strong>'. alo_em_get_lang_flag($lang, 'code') . '</strong>';
			$lang_li[$lang] .= ( isset( $_REQUEST['task'] ) && $_REQUEST['task'] == 'edit_list' && $list_id && !alo_em_translate_multilangs_array ( $lang, $list_name, false ) ) ? '<img src="'.ALO_EM_PLUGIN_URL.'/images/12-exclamation.png" alt="" style="vertical-align:middle;margin-left:2px;margin-top:-2px;" title="'. __("no translation for this language, yet", "alo-easymail") .'!" />' : '';
			$lang_li[$lang] .= '</a></li>';
			$lang_div[$lang] = '<div id ="listname_div_'.$lang.'">';
			$name_value = ( isset( $_REQUEST['task'] ) && $_REQUEST['task'] == 'edit_list' && $list_id ) ? esc_attr( alo_em_translate_multilangs_array ( $lang, $list_name, false ) ) : "";
			$lang_div[$lang] .= '<input type="text" name="listname_'.$lang.'" value="' . $name_value .'" id="listname_'.$lang.'" maxlength="100" style="width:100%;" />';	
			$lang_div[$lang] .= '</div>';
		}
	}
	?>
	<ul id="activamail_tabs">
	<?php echo implode ( "\n\n", $lang_li); ?>
	</ul>

<?php echo implode ( "\n\n", $lang_div);?>
</div>



</td>

<td><select name="elp_list_available" id="elp_list_available">
		<option value='hidden' <?php if ( isset ( $_REQUEST['task'] ) && $_REQUEST['task'] == 'edit_list' && $list_id && $list_available == 'hidden') echo 'selected="selected"'; ?> ><?php _e('hidden', 'alo-easymail') ?> </option>
		<option value='admin' <?php if ( isset ( $_REQUEST['task'] ) && $_REQUEST['task'] == 'edit_list' && $list_id && $list_available == 'admin') echo 'selected="selected"'; ?> ><?php echo __('admin side only', 'alo-easymail') ?> </option>
		<option value='public' <?php if ( isset ( $_REQUEST['task'] ) && $_REQUEST['task'] == 'edit_list' && $list_id && $list_available == 'public') echo 'selected="selected"'; ?> ><?php echo __('entire site', 'alo-easymail') ?> </option>
	</select></td>
<td><input type="text" name="elp_list_order" value="<?php if ( isset ( $_REQUEST['task'] ) && $_REQUEST['task'] == 'edit_list' && $list_order ) { echo $list_order; }else{ echo '0'; }; ?>" id="elp_list_order" size="3" maxlength="3" /></td>
<td>
	<input type="hidden" name="user_ID" value="<?php echo (int) $user_ID ?>" />
	<input type="hidden" name="task" value="save_list" />
	<?php if ( isset ( $_REQUEST['task'] ) && $_REQUEST['task'] == 'edit_list' && $list_id ) { ?>
		<input type="hidden" name="list_id" value="<?php echo $list_id ?>" />
	<?php } else { ?>
		<input type="hidden" name="list_id" value="" />	
	<?php }  ?>
	<input type="submit" name="submit_list" value="<?php _e('Save', 'alo-easymail') ?>"  class="button-primary" />
	<?php if ( isset ( $_REQUEST['task'] ) && $_REQUEST['task'] == 'edit_list' && $list_id ) { ?>
		<a href='options-general.php?page=alo-easymail/alo-easymail_options.php#mailinglists' title="<?php _e('Cancel', 'alo-easymail') ?>" ><?php _e('Cancel', 'alo-easymail') ?></a>
	<?php } ?>
</td>
</tr>
</tbody> </table>

</form>

<h3><?php _e("Mailing Lists", "alo-easymail") ?></h3>    
<table class="widefat">
<thead><tr valign="top">
	<th scope="col"><?php _e('ID') ?></th>
	<th scope="col" style="width:40%"><?php _e('List name', 'alo-easymail') ?></th>
	<th scope="col"><?php _e('Availability', 'alo-easymail') ?></th>
	<th scope="col"><?php _e('Order', 'alo-easymail') ?></th>
	<th scope="col"><?php _e('Subscribers', 'alo-easymail') ?></th>
	<th scope="col"><?php _e('Action', 'alo-easymail') ?></th>
</tr></thead>
<tbody>
<?php

$tab_mailinglists = alo_em_get_mailinglists( 'hidden,admin,public' );
if ($tab_mailinglists) {
	foreach ( $tab_mailinglists as $list => $val) { 
		if ($val['available'] == "deleted") continue; 
		?>
		<tr>
			<td><strong><?php echo $list ?></strong></td>
			<td><strong><?php echo alo_em_translate_multilangs_array ( alo_em_get_language(), $val['name'], true ) ?></strong></td>
			<td><?php
				switch ($val['available']) {
					case "hidden":
						echo __('hidden', 'alo-easymail');
						break;
					case "admin":
						echo __('admin side only', 'alo-easymail');
						break;
					case "public":
						echo __('entire site', 'alo-easymail');
						break;
					default:
				}
				?>
			</td>
			<td><strong><?php echo $val['order'] ?></strong></td>
			
			<td><?php // echo count ( alo_em_get_recipients_subscribers( $list ) )
			$link_subscr = "edit.php?post_type=newsletter&page=alo-easymail/alo-easymail_subscribers.php&filter_list=".$list;
			echo '<a href="'. admin_url( $link_subscr ). '">'. __('View') .'</a>';
			?></td>
			
			<td><?php
				echo "<a href='edit.php?post_type=newsletter&page=alo-easymail/alo-easymail_options.php&amp;task=edit_list&amp;list_id=". $list . "&amp;rand=".rand(1,99999)."#mailinglists' title='".__("Edit list", "alo-easymail")."' >";
				echo "<img src='".ALO_EM_PLUGIN_URL."/images/16-edit.png' alt='" . __("Edit list", "alo-easymail") ."' /></a>";
				echo " ";
				echo "<a href='edit.php?post_type=newsletter&page=alo-easymail/alo-easymail_options.php&amp;task=del_list&amp;list_id=". $list . "&amp;rand=".rand(1,99999)."#mailinglists' title='".__("Delete list", "alo-easymail")."' ";
				echo " onclick=\"return confirm('".__("Do you really want to DELETE this list?", "alo-easymail")."');\">";
				echo "<img src='".ALO_EM_PLUGIN_URL."/images/trash.png' alt='" . __("Delete list", "alo-easymail") ."' /></a>";
				?>
			</td>
		</tr>
	<?php 
	}
} else { ?>
	<tr><td colspan="6"><?php _e('There are no available lists', 'alo-easymail') ?></td></tr>
<?php
}
?>
</tbody> </table>

<?php //echo "<pre style='font-size:80%'>"; print_r( $tab_mailinglists ); echo "</pre>"; // DEBUG ?>

</div> <!-- end Mailing Lists -->



<!-- --------------------------------------------
BOUNCES
--------------------------------------------  -->

<?php if ( current_user_can('manage_options') ) : /* only admin can */ ?>

<div id="bounces">

<form action="#bounces" method="post">
<h2><?php _e("Bounces", "alo-easymail") ?></h2>

<table class="form-table"><tbody>

<?php 
// Get array of saved settings
$bounce_settings = alo_em_bounce_settings();

// IMAP not installed
if ( ! $imap_installed ) {
	echo '<tr valign="top">';
	echo '<td colspan="2">';
		echo '<div class="text-alert"><p><img src="'.ALO_EM_PLUGIN_URL.'/images/12-exclamation.png" /> ';
		echo '<strong>' . sprintf( __('PHP %s extension was not detected', 'alo-easymail'), 'IMAP' ) .'.</strong><br />';
		echo __( 'This plugin feature can not work now.', 'alo-easymail' ) .' ';
		echo sprintf( __( 'Ask your hosting provider to enable %s for PHP', 'alo-easymail' ), '<code>IMAP</code>' ).'.';
		echo '</p></div>';
	echo '</td></tr>';	
}
// Test bounce connection
else if ( isset( $_POST['test_bounce_connection'] ) )
{
	$bounce_connection = alo_em_bounce_connect();

	echo '<tr valign="top">';
	echo '<td colspan="2">';	
	if ( ! $bounce_connection ) { // connection error
		echo '<div class="text-alert"><p><img src="'.ALO_EM_PLUGIN_URL.'/images/12-exclamation.png" /> ';
		echo '<strong>' . @imap_last_error() .'.</strong><br />';
		echo '</p></div>';
	}
	else
	{
		echo '<div class="easymail-alert" style="background-color:#99FF66">';
		echo '<img src="'.ALO_EM_PLUGIN_URL.'/images/yes.png" style="vertical-align: text-bottom;" /> ';
		echo __('The connection test has been successfully completed', 'alo-easymail') . '</div>';
	}
	echo '</td></tr>';	
}	
// Manually check bounces now
else if ( isset( $_POST['check_bounces_now'] ) )
{
	$bounce_connection = alo_em_bounce_connect();

	echo '<tr valign="top">';
	echo '<td colspan="2">';	
	if ( ! $bounce_connection ) { // connection error
		echo '<div class="text-alert"><p><img src="'.ALO_EM_PLUGIN_URL.'/images/12-exclamation.png" /> ';
		echo '<strong>' . @imap_last_error() .'.</strong><br />';
		echo '</p></div>';
	}
	else
	{

		echo '<div class="easymail-alert" style="background-color:#99FF66">';
		echo '<img src="'.ALO_EM_PLUGIN_URL.'/images/yes.png" style="vertical-align: text-bottom;" /> ';
		echo '<strong>'. __('The bounces has been successfully handled', 'alo-easymail') .'</strong><br />';
		// Manually check bounce now!
		$bounce_report = alo_em_handle_bounces( true );
		echo '<div style="overflow: auto;max-height: 150px">'. $bounce_report .'</div>';
		echo '</div>';		
	}
	echo '</td></tr>';	
}	
?>

<tr valign="top">
<th scope="row">
	<h3 style="margin-bottom: 0"><?php _e("Email address", "alo-easymail");?></h3>
	</th>
	<td></td>
</tr>

<tr valign="top">
<th scope="row"><label for="bounce_email"><?php _e("Email address", "alo-easymail") ?>:</label></th>
<td><input type="text" name="bounce_email" value="<?php echo $bounce_settings['bounce_email'] ?>" id="bounce_email" size="30" maxlength="100" />
<span class="description"><?php _e("The email address to which bounce messages are delivered", "alo-easymail");?>
</span></td>
</tr>

<tr valign="top">
<th scope="row">
	<h3 style="margin-bottom: 0"><?php _e("Connection settings", "alo-easymail");?></h3>
	</th>
	<td></td>
</tr>

<tr valign="top">
<th scope="row"><label for="bounce_host"><?php _e("Server host", "alo-easymail") ?>:</label></th>
<td><input type="text" name="bounce_host" value="<?php echo $bounce_settings['bounce_host'] ?>" id="bounce_host" size="30" maxlength="100" />
<span class="description"><?php _e("The host remote server", "alo-easymail");?>.
<?php echo __("E.g.", "alo-easymail").': imap.example.com' ;?>
</span></td>
</tr>

<tr valign="top">
<th scope="row"><label for="bounce_port"><?php _e("Port number", "alo-easymail") ?>:</label></th>
<td><input type="text" name="bounce_port" value="<?php echo $bounce_settings['bounce_port'] ?>" id="bounce_port"  size="5" maxlength="5" /></td>
</tr>

<tr valign="top">
<th scope="row"><?php _e("Protocol", "alo-easymail") ?>:</th>
<td>
<?php $protocol_vars_list = array (
	"imap"	=>	'IMAP',
	"pop3" 	=> 	'POP3',
);
foreach( $protocol_vars_list as $key => $label ) :
	echo '<input type="radio" name="bounce_protocol" value="'.$key.'" id="bounce_protocol_'.$key.'" '. ( ( $key == $bounce_settings['bounce_protocol'] )? 'checked="checked"': "") .' />';
	echo ' <label for="bounce_protocol_'.$key.'" style="margin-right: 20px">'. $label .'</label>';
endforeach; ?>
</td>
</tr>

<tr valign="top">
<th scope="row"><label for="bounce_folder"><?php _e("Folder", "alo-easymail") ?>:</label></th>
<td><input type="text" name="bounce_folder" value="<?php echo $bounce_settings['bounce_folder'] ?>" id="bounce_folder" size="30" maxlength="100" />
<span class="description"><?php _e("The host remote folder", "alo-easymail");?>.
<?php echo __("Default", "alo-easymail").': '.__("empty", "alo-easymail");?>
</span></td>
</tr>

<tr valign="top">
<th scope="row"><label for="bounce_username"><?php _e("Username") ?>:</label></th>
<td><input type="text" name="bounce_username" value="<?php echo $bounce_settings['bounce_username'] ?>" id="bounce_username" size="30" maxlength="100" />
</td>
</tr>

<tr valign="top">
<th scope="row"><label for="bounce_password"><?php _e("Password") ?>:</label></th>
<td><input type="password" name="bounce_password" value="<?php echo $bounce_settings['bounce_password'] ?>" id="bounce_password" size="30" maxlength="100" />
</td>
</tr>

<tr valign="top">
<th scope="row"><label for="bounce_flags"><?php _e("Optional flags", "alo-easymail") ?>:</label></th>
<td><input type="text" name="bounce_flags" value="<?php echo $bounce_settings['bounce_flags'] ?>" id="bounce_flags" size="30" maxlength="100" />
<span class="description"><?php echo __("Default", "alo-easymail").': '.__("empty", "alo-easymail");?>.
<?php echo __("E.g.", "alo-easymail").': /ssl/novalidate-cert' ;?>
</span>
</td>
</tr>

<?php if ( $imap_installed ) : ?>
<tr valign="top">
<th scope="row"><label for=""><em><?php _e("Test the connection now", "alo-easymail") ?>:</em></label></th>
<td><input type="submit" name="test_bounce_connection" class="button" value="<?php echo esc_attr(__("Save settings and test now", "alo-easymail") ); ?>" />
</td>
</tr>
<?php endif; ?>


<tr valign="top">
<th scope="row">
	<h3 style="margin-bottom: 0"><?php _e("Bounce policy", "alo-easymail");?></h3>
	</th>
	<td></td>
</tr>

<?php if ( $imap_installed ) : ?>
<tr valign="top">
<th scope="row"><label for="bounce_interval"><?php _e("Handle automatically bounces", "alo-easymail") ?>:</label></th>
<td>
<select name='bounce_interval' id='bounce_interval'>
	<?php $values_bounce_interval = array ( 
		"" => __("never", "alo-easymail"),
		1 => sprintf( __("every %s hour(s)", "alo-easymail"), 1 ),
		2 => sprintf( __("every %s hour(s)", "alo-easymail"), 2 ),
		3 => sprintf( __("every %s hour(s)", "alo-easymail"), 3 ),
		4 => sprintf( __("every %s hour(s)", "alo-easymail"), 4 ),
		6 => sprintf( __("every %s hour(s)", "alo-easymail"), 6 ),
		8 => sprintf( __("every %s hour(s)", "alo-easymail"), 8 ),
		12 => sprintf( __("every %s hour(s)", "alo-easymail"), 12 ),
		24 => sprintf( __("every %s hour(s)", "alo-easymail"), 24 ),
	);
	foreach( $values_bounce_interval as $key => $label ) :
		echo "<option value='$key' ". ( ( $key == $bounce_settings['bounce_interval'] )? " selected='selected'": "") .">". esc_html( $label ). "</option>";
	endforeach; ?>
</select>	
<span class="description"><?php _e("If you select *never* you can handle bounces only manually", "alo-easymail"); ?>
</span>
</td>
</tr>
<?php endif; ?>

<tr valign="top">
<th scope="row"><label for="bounce_maxmsg"><?php _e("Maximum number of emails that can be check per bounce batch", "alo-easymail") ?>:</label></th>
<td><input type="text" name="bounce_maxmsg" value="<?php echo $bounce_settings['bounce_maxmsg'] ?>" id="bounce_maxmsg"  size="3" maxlength="3" />
</td>
</tr>

<?php if ( $imap_installed ) : ?>
<tr valign="top">
<th scope="row"><label for=""><em><?php _e("Handle manually bounces now", "alo-easymail") ?>:</em></label></th>
<td><input type="submit" name="check_bounces_now" class="button" value="<?php echo esc_attr(__("Save settings and handle manually bounces now", "alo-easymail") ); ?>" />
</td>
</tr>
<?php endif; ?>


</tbody> </table>

<p class="submit">
<input type="hidden" name="user_ID" value="<?php echo (int) $user_ID ?>" />
<input type="hidden" name="task" value="tab_bounces" /> <?php // reset task ?>
<!--<span id="autosave"></span>-->
<input type="submit" name="submit" value="<?php _e('Update', 'alo-easymail') ?>" class="button-primary" />
</p>
</form>

</div> <!-- end bounces -->

<?php endif; /* only admin can */ ?>


<p><?php alo_em_show_credit_banners( true ); ?></p>

</div><!-- end wrap -->
