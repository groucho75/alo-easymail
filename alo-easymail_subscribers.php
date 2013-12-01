<?php // No direct access, only through WP
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) die('You can\'t call this page directly.'); 
if ( !current_user_can('manage_newsletter_subscribers') ) 	wp_die(__('Cheatin&#8217; uh?'));
global $user_ID;
?>


<?php // action and feedback

// Base link
$link_base = "edit.php?post_type=newsletter&page=alo-easymail/alo-easymail_subscribers.php";


// change state activity of subscriber
if ( isset($_REQUEST['task']) && $_REQUEST['task'] == 'active' && is_numeric($_REQUEST['subscriber_id'])) {
    if ( alo_em_edit_subscriber_state_by_id($_REQUEST['subscriber_id'], $_REQUEST['act']) ) {
	    print '<div id="message" class="updated fade"><p>'.__("Activation state updated", "alo-easymail").'.</p></div>';
	} else {
	    print '<div id="message" class="error"><p>'.__("Error during operation.", "alo-easymail") ." ". __("Not updated", "alo-easymail").'.</p></div>';
	}
}

// delete partecipation
if( isset($_REQUEST['task']) && $_REQUEST['task'] == 'delete' && is_numeric($_REQUEST['subscriber_id'])) {
    if ( alo_em_delete_subscriber_by_id ($_REQUEST['subscriber_id']) ) {
	    print '<div id="message" class="updated fade"><p>'.__("Subscriber deleted", "alo-easymail").'.</p></div>';
	} else {
		if ( alo_em_is_subscriber_by_id($_REQUEST['subscriber_id']) ) { // error only if this subscriber exists yet, to prevent error on refresh page
	   		print '<div id="message" class="error"><p>'.__("Error during operation.", "alo-easymail") ." ". __("Not deleted", "alo-easymail").'.</p></div>';
	   	}
	}
}

// delete welcome import alert
if ( isset($_REQUEST['import_alert']) && $_REQUEST['import_alert'] == "stop" ) {
	update_option( 'alo_em_import_alert', "hide" ); 
}

?>

<?php 
// Prepare mailing lists details
$mailinglists = alo_em_get_mailinglists( 'admin,public' );

// Prepare languages
$languages = alo_em_get_all_languages ( true );

// Prepare Custom Fields
$alo_em_cf = alo_easymail_get_custom_fields();
?>

<div class="wrap">
    <div id="icon-users" class="icon32"><br /></div>
    <h2>Alo EasyMail Newsletter: <?php _e("Subscribers", "alo-easymail") ?></h2>
    <div id="dashboard-widgets-wrap" style="margin: 0 0.4em">
    
<?php 
/**
 * --- start MAIN --------------------------------------------------------------
 */
?>

<?php
wp_enqueue_script( 'listman' );
wp_print_scripts();
?>

	
<?php
// pagination info
$offset = 0;
$page = 1;
$items_per_page = get_user_meta( $user_ID, 'edit_per_page', true);
if ( (int)$items_per_page == 0 ) $items_per_page = 20;

if(isset($_REQUEST['paged']) and $_REQUEST['paged']) {
	$page = intval($_REQUEST['paged']);
	$offset = ($page - 1) * $items_per_page;
}

// order default
if( !isset($_GET['sortby']) ) {
	$_GET['sortby'] = 'join_date'; //'ID';
}

// string to search
$s = ( isset( $_GET[ 's' ] ) ) ? esc_html ( trim( $_GET[ 's' ] ) ) : "";
$filter_list = ( isset( $_GET[ 'filter_list' ] ) ) ? (int)$_GET[ 'filter_list' ] : "";
$filter_lang = ( isset( $_GET[ 'filter_lang' ] ) ) ? $_GET[ 'filter_lang' ] : "";
?>

<?php 
/**
 * Bulk action: Step #1/2
 */
if ( isset($_REQUEST['doaction_step1']) ) {
	check_admin_referer('alo-easymail_subscribers');
	
	if ( isset($_REQUEST['action']) && $_REQUEST['action'] != "" ) {
		switch ( $_REQUEST['action'] ) { // go on with step 1!
		 	case "lists":
		 		if ( !isset($_REQUEST['subscribers'] ) || $_REQUEST['subscribers'] == "" )  {
		 			echo '<div id="message" class="error"><p>'.__("Error during operation.", "alo-easymail");
					if ( !isset($_REQUEST['subscribers']) || $_REQUEST['subscribers'] == "" ) echo " ". __("No subscriber selected", "alo-easymail") .".";
					if ( !isset($_REQUEST['check_list']) || count ($_REQUEST['check_list']) == 0 ) echo " ". __("No list selected", "alo-easymail") .".";
					echo '</p></div>';
					break;
				}
					
			 	if ($mailinglists) { ?>
					<h3><?php _e("Edit subscription to mailing lists", "alo-easymail") ?>:</h3>	
					<p><?php _e("Selected subscribers", "alo-easymail") ?>: <strong><?php echo count ($_REQUEST['subscribers']) ?></strong></p>
					
					<form method="get" action="" id="posts-filter">
					<input  type="hidden" name="s"  value="<?php if (!empty( $s )) echo stripslashes( $s ) ; ?>" />
					<input  type="hidden" name="page"   value="alo-easymail/alo-easymail_subscribers.php"/>
					<input  type="hidden" name="paged"  value="<?php echo $page ?>" />
					<input  type="hidden" name="num"    value="<?php echo $items_per_page ?>" />
					<input  type="hidden" name="sortby" value="<?php echo $_GET['sortby'] ?>" />
					<input  type="hidden" name="order"  value="<?php echo ( isset($_GET['order']) && $_GET['order'] == 'DESC' ) ? 'DESC' : 'ASC'; ?>" />
					<input  type="hidden" name="filter_list"  value="<?php echo $filter_list ?>" />					
					<input  type="hidden" name="filter_lang"  value="<?php echo $filter_lang ?>" />
					<input type="hidden" name="post_type"  value="newsletter" />
					
					<div style="margin-top:20px"><strong><?php _e("Choose mailing lists", "alo-easymail"); ?>:</strong><ul style="margin-top:10px">
					<?php
					foreach ( $mailinglists as $list => $val) { 
						if ( $val['available'] == "deleted" || $val['available'] == "hidden" ) {
							//continue; 
						} ?>
						<li><input type="checkbox" name="check_list[]" id="list_<?php echo $list ?>" value="<?php echo $list ?>" /><label for="list_<?php echo $list ?>"><?php echo alo_em_translate_multilangs_array ( alo_em_get_language(), $val['name'], true ) ?></label></li>
					<?php 
					} // end foreach ?>
					</ul></div>

					<div style="margin-top:20px"><strong><?php _e("Choose an action", "alo-easymail"); ?>:</strong><ul style="margin-top:10px">
						<li><input type="radio" name="mode" value="add" id="mode_add" checked="checked" />
							<label for="mode_add"><?php _e("Add to selected lists", "alo-easymail"); ?> (<?php _e("ignore not selected lists", "alo-easymail"); ?>)</label>
						</li>
						<li><input type="radio" name="mode" value="remove" id="mode_remove" />
							<label for="mode_remove"><?php _e("Remove from selected lists", "alo-easymail"); ?> (<?php _e("ignore not selected lists", "alo-easymail"); ?>)</label>
						</li>	
						<?php //TODO ?>
						<!--<li><input type="radio" name="mode" value="add_remove" id="mode_add_remove" />
							<label for="mode_add_remove"><?php _e("Add to selected lists and remove from not selected lists", "alo-easymail"); ?></label>
						</li>-->						
					</ul></div>		
					<?php //wp_nonce_field('alo-easymail_subscribers'); ?>
					<input type="hidden" name="post_type"  value="newsletter" />
					<input  type="hidden" name="action"  value="lists_step2" /> <?php // the action ?>
					<input  type="hidden" name="subscribers"  value="<?php echo implode ( ',', $_REQUEST['subscribers']); ?>" /> <?php // the subscriber ids ?>
					<div style="margin-top:20px">
			 			<input type="submit" class="button-primary" id="doaction_step2" name="doaction_step2" value="<?php _e('Apply') ?>" />
			 			<a href="javascript:history.back()"><?php _e("Cancel", "alo-easymail"); ?></a>
			 		</div>
					</form> <!-- end form -->
					<?php 
					exit();
				} else {
					print '<div id="message" class="updated fade"><p>'. __('There are no available lists', 'alo-easymail') .'.</p></div>';
					break;						
				}
		 		
		 	case "language":
		 		if ( !isset($_REQUEST['subscribers'] ) || $_REQUEST['subscribers'] == "" )  {
		 			echo '<div id="message" class="error"><p>'.__("Error during operation.", "alo-easymail");
					if ( !isset($_REQUEST['subscribers']) || $_REQUEST['subscribers'] == "" ) echo " ". __("No subscriber selected", "alo-easymail") .".";
					if ( !isset($_REQUEST['check_lang']) || count ($_REQUEST['check_lang']) == 0 ) echo " ". __("No language option selected", "alo-easymail") .".";
					echo '</p></div>';
					break;
				}
					
			 	if ( $languages ) { ?>
					<h3><?php _e("Change language ascribed to subscribers", "alo-easymail") ?>:</h3>	
					<p><?php _e("Selected subscribers", "alo-easymail") ?>: <strong><?php echo count ($_REQUEST['subscribers']) ?></strong></p>
					
					<form method="get" action="" id="posts-filter">
					<input  type="hidden" name="s"  value="<?php if (!empty( $s )) echo stripslashes( $s ) ; ?>" />
					<input  type="hidden" name="page"   value="alo-easymail/alo-easymail_subscribers.php"/>
					<input  type="hidden" name="paged"  value="<?php echo $page ?>" />
					<input  type="hidden" name="num"    value="<?php echo $items_per_page ?>" />
					<input  type="hidden" name="sortby" value="<?php echo $_GET['sortby'] ?>" />
					<input  type="hidden" name="order"  value="<?php echo ( isset($_GET['order']) && $_GET['order'] == 'DESC' ) ? 'DESC' : 'ASC'; ?>" />
					<input  type="hidden" name="filter_list"  value="<?php echo $filter_list ?>" />					
					<input  type="hidden" name="filter_lang"  value="<?php echo $filter_lang ?>" />
					
					<div style="margin-top:20px"><strong><?php _e("Choose a language", "alo-easymail"); ?>:</strong><ul style="margin-top:10px">
					<li><input type="radio" name="check_lang" id="lang_blank" value="blank" checked="checked" /> <label for="lang_blank"><?php _e("No language", "alo-easymail"); ?></label></li>
					<?php
					foreach ( $languages as $index => $lang ) { ?>
						<li><input type="radio" name="check_lang" id="lang_<?php echo $lang ?>" value="<?php echo $lang ?>" /><label for="lang_<?php echo $lang ?>"><?php echo alo_em_get_lang_flag ($lang, false) ." ". esc_html ( alo_em_get_lang_name ( $lang ) ); ?></label></li>
					<?php 
					} // end foreach ?>
					</ul></div>
					
					<input type="hidden" name="post_type"  value="newsletter" />
					<input  type="hidden" name="action"  value="langs_step2" /> <?php // the action ?>
					<input  type="hidden" name="subscribers"  value="<?php echo implode ( ',', $_REQUEST['subscribers']); ?>" /> <?php // the subscriber ids ?>
					<div style="margin-top:20px">
			 			<input type="submit" class="button-primary" id="doaction_step2" name="doaction_step2" value="<?php _e('Apply') ?>" />
			 			<a href="javascript:history.back()"><?php _e("Cancel", "alo-easymail"); ?></a>
			 		</div>
					</form> <!-- end form -->
					<?php 
					exit();
				} else {
					print '<div id="message" class="updated fade"><p>'. __('No language available', 'alo-easymail') .'.</p></div>';
					break;						
				}
		 				 		
		 	case "delete":
		 		if ( !isset($_REQUEST['subscribers'] ) || $_REQUEST['subscribers'] == "" )  {
		 			echo '<div id="message" class="error"><p>'.__("Error during operation.", "alo-easymail");
					echo " ". __("No subscriber selected", "alo-easymail") .".";
					echo '</p></div>';
					break;
				}		 	
		 		foreach ($_REQUEST['subscribers'] as $subsc => $val) {
		 			alo_em_delete_subscriber_by_id ( $val );
		 		}
		 		print '<div id="message" class="updated fade"><p>'.__("Subscribers deleted", "alo-easymail").'.</p></div>';
		 		break;

		 	case "delete_and_unsubscribe":
		 		if ( !isset($_REQUEST['subscribers'] ) || $_REQUEST['subscribers'] == "" )  {
		 			echo '<div id="message" class="error"><p>'.__("Error during operation.", "alo-easymail");
					echo " ". __("No subscriber selected", "alo-easymail") .".";
					echo '</p></div>';
					break;
				}		 	
		 		foreach ($_REQUEST['subscribers'] as $subsc => $val) {

					$subscriber_obj =  alo_em_get_subscriber_by_id( $val );
					alo_em_add_email_in_unsubscribed ( $subscriber_obj->email );
										
		 			alo_em_delete_subscriber_by_id ( $val );
		 		}
		 		print '<div id="message" class="updated fade"><p>'.__("Subscribers deleted and added to list of who unsubscribed", "alo-easymail").'.</p></div>';
		 		break;
		 		
		 	case "activate":
		 		if ( !isset($_REQUEST['subscribers'] ) || $_REQUEST['subscribers'] == "" )  {
		 			echo '<div id="message" class="error"><p>'.__("Error during operation.", "alo-easymail");
					echo " ". __("No subscriber selected", "alo-easymail") .".";
					echo '</p></div>';
					break;
				}		 	
		 		foreach ($_REQUEST['subscribers'] as $subsc => $val) {
		 			alo_em_edit_subscriber_state_by_id( $val , "1" );
		 		}
		 		print '<div id="message" class="updated fade"><p>'.__("Subscribers activated", "alo-easymail").'.</p></div>';
		 		break;

		 	case "deactivate":
		 		if ( !isset($_REQUEST['subscribers'] ) || $_REQUEST['subscribers'] == "" )  {
		 			echo '<div id="message" class="error"><p>'.__("Error during operation.", "alo-easymail");
					echo " ". __("No subscriber selected", "alo-easymail") .".";
					echo '</p></div>';
					break;
				}		 	
		 		foreach ($_REQUEST['subscribers'] as $subsc => $val) {
		 			alo_em_edit_subscriber_state_by_id( $val , "0" );
		 		}
		 		print '<div id="message" class="updated fade"><p>'.__("Subscribers deactivated", "alo-easymail").'.</p></div>';
		 		break;
		 				 				 	
		 	case "import":
			 	?>
			 	<style type="text/css">
					hr.break {
						border: none 0;
						border-top: 1px dashed #ccc;
						width: 100%;
						height: 1px;
						margin: 20px auto;
					}
					hr.close {
						border: none 0;
						border-top: 1px solid #aaa;
						width: 100%;
						height: 1px;
						margin: 15px auto;
					}
				</style>

				<div class="updated" style="background-color:#fffefe"><p><?php _e("Note: the email addresses of who unsubscribed the newsletter are archivied in a database table, so you cannot add or import those addresses using the tools in this page", "alo-easymail") ?>. <a href="#easymail-export-unsubscribed"><?php _e("You can view and export these email addresses", "alo-easymail"); ?></a>.</p></div>
				
				<hr class="break" />
				<a href="javascript:history.back()"><?php _e("Cancel", "alo-easymail"); ?></a>
				<hr class="break" />

			 	<h3 style="margin-top:20px"><?php _e("Add a subscriber", "alo-easymail") ?></h3>
			 	<form action="" method="get" >
			 		<table class="form-table"><tbody>
			 			<tr valign="top"><th scope="row"><label for="addsingle_email"><?php echo __("E-mail", "alo-easymail"). " <span class='description'>(". __("mandatory", "alo-easymail"). ")</span>" ?></label></th>
				 		<td><input type="text" class="regular-text" value="" name="addsingle_email" id="addsingle_email"></td></tr>				 		
			 			<tr valign="top"><th scope="row"><label for="addsingle_name"><?php _e("Name", "alo-easymail") ?></label></th>
				 		<td><input type="text" class="regular-text" value="" name="addsingle_name" id="addsingle_name"></td></tr>		
                        
                        <?php
						//edit : added all this foreach
						if( $alo_em_cf ) {
							foreach( $alo_em_cf as $key => $value ){
								?>
								<tr valign="top"><th scope="row"><label for="addsingle_<?php echo $key; ?>"><?php echo $value['humans_name']; ?></label></th>
		
								<td>
									<?php echo alo_easymail_custom_field_html ( $key, $value, "addsingle_".$key, "", true ) ?>
									</td></tr>
								<?php
							}
						}
						?>	 	

				 		<?php if ( $languages ) { ?>
				 		<tr valign="top"><th scope="row"><label for="addsingle_lang"><?php _e("Language", "alo-easymail") ?></label></th>
				 		<td>
						<select name="addsingle_lang">
							<option selected="selected" value=""></option>
							<?php foreach ( $languages as $key => $val ) {
								$selected = ( $filter_lang == $val ) ? 'selected="selected"' : '';
								$lang_name = esc_html ( alo_em_get_lang_name ( $val ) );
								echo '<option value="'.$val.'" '.$selected.'>'. $lang_name .'</option>';
							} ?>
						</select>
					 	</td></tr>							
						<?php } // end if languages ?>
					 					 		
				 		<?php
				 		if ( $mailinglists) { ?>
				 			<tr valign="top"><th scope="row"><label><?php _e("Mailing Lists", "alo-easymail"); ?></label></th>
				 			<td><?php
				 			echo "<ul style='font-size:90%;margin-bottom:10px'>";
					 		foreach ( $mailinglists as $list => $val) { 
								if ( $val['available'] == "deleted" || $val['available'] == "hidden" ) {
									//continue; 
								} ?>
								<li style="display:inline-block;margin-right:10px"><input type="checkbox" name="check_list[]" id="single_list_<?php echo $list ?>" value="<?php echo $list ?>" /><label for="single_list_<?php echo $list ?>"><?php echo alo_em_translate_multilangs_array ( alo_em_get_language(), $val['name'], true ) ?></label></li>
							<?php 
							} // end foreach 
							echo "</ul>"; ?>
						</td></tr>							
						<?php } // end if mailinglists ?>
					 	
					</tbody></table>
					<input type="hidden" name="post_type"  value="newsletter" />
					<input type="hidden" name="action"  value="addsingle_step2" /> <?php // the action ?>
					<input  type="hidden" name="page"   value="alo-easymail/alo-easymail_subscribers.php"/>
					<input type="submit" value="<?php _e('Add', 'alo-easymail') ?>" class="button" name="doaction_step2" />
				</form>					 	
			 	<hr class="break" />
			 					
			 	<h3 style="margin-top:20px"><?php _e("Import from WP registered users", "alo-easymail") ?></h3>
			 	<p><?php _e("You can import new subscribers from WordPress registered users. Each member will be subscribed to the newsletter.", "alo-easymail") ?></p>
			 	<p><em><?php _e("This feature is useful if you have just installed the plugin and you would like to automatically subscribe existing users.", "alo-easymail") ?></em></p>
			 	<p>&middot; <?php _e("Newsletter subscribers", "alo-easymail") ?>: <?php list ( $total_sub, $ac_sub, $noac_sub ) = alo_em_count_subscribers (); echo (int) $total_sub; ?><br />
			 	&middot; <?php _e("Blog registered users", "alo-easymail") ?>: <?php echo (int) count (alo_em_get_recipients_registered()); ?></p>
			 	<form action="" method="get">			 	
					<input type="hidden" name="action"  value="wpusers_step2" /> <?php // the action ?>
					<input type="hidden" name="post_type"  value="newsletter" />
					<input  type="hidden" name="page"   value="alo-easymail/alo-easymail_subscribers.php"/>
					<input type="submit" value="<?php _e('Import from WP members', 'alo-easymail') ?>" class="button" name="doaction_step2" />
				</form>			 	
			 	<hr class="break" />
			 				 	
			 	<h3 style="margin-top:20px"><?php _e("Import from uploaded CSV file", "alo-easymail") ?></h3>
			 	<p><?php _e("You can import new subscribers from a CSV file.", "alo-easymail") ?></p>
			 	<p><?php _e("For each line you have to specify: e-mail address (mandatory), name (optional), language code (optional). Use semicolon (;) to separate the fields.", "alo-easymail");	
			 	echo "<br />". __('You can list only languages available in the blog', 'alo-easymail') .": ". __('you can set them up in Settings &gt; Newsletter', 'alo-easymail').  ". (". __('Language codes', 'alo-easymail') .": ". sprintf( '<a href="http://en.wikipedia.org/wiki/List_of_ISO_639-1_codes" target="_blank">%s</a>', __('iso 639-1 codes', 'alo-easymail') ). ") " ?></p>
			 	
			 	<code>email_address1@domain.ltd;name1 surname1;en<?php
						//edit : added all this foreach
						if( $alo_em_cf ) {
							foreach( $alo_em_cf as $key => $value ){
								  echo ';'.$value['humans_name'].'</th>';
							}
						}
				?></code><br />
			 	<code>email_address2@domain.ltd;name2 surname2;it</code><br />
			 	<code>email_address3@domain.ltd;name3</code><br />
			 	<code>email_address4@domain.ltd;;en</code>
			 	<p><?php echo __("Tips if you have problems: you can try changing the file extension from .csv to .txt; use double quotes to delimit each field (&quot;email_address1@domain.ltd&quot;;&quot;name1 surname1&quot;)", "alo-easymail"). "; ". __("try using a different browser", "alo-easymail") ?>.</p>
			 	<form enctype="multipart/form-data" action="" method="POST">
			 		<p><input type="checkbox" name="test_only" id="test_only" value="yes" /><label for="test_only"><?php _e('Test mode (no importation, show records on screen)', 'alo-easymail') ?></label></p>
			 		
			 		<?php
			 		if ( $mailinglists) {
			 			echo "<p>".__("Add to selected lists", "alo-easymail"). ":</p>";
			 			echo "<ul style='font-size:80%;margin-bottom:10px'>";
				 		foreach ( $mailinglists as $list => $val) { 
							if ( $val['available'] == "deleted" || $val['available'] == "hidden" ) {
								//continue; 
							} ?>
							<li style="display:inline-block;margin-right:10px"><input type="checkbox" name="check_list[]" id="list_<?php echo $list ?>" value="<?php echo $list ?>" /><label for="list_<?php echo $list ?>"><?php echo alo_em_translate_multilangs_array ( alo_em_get_language(), $val['name'], true ) ?></label></li>
						<?php 
						} // end foreach 
					echo "</ul>";
					} ?>
			 		
					<input name="uploaded_csv" type="file" class="button" />
					<input type="hidden" name="post_type"  value="newsletter" />
					<input  type="hidden" name="action"  value="import_step2" /> <?php // the action ?>
					<input type="submit" value="<?php _e('Upload CSV file', 'alo-easymail') ?>" class="button" name="doaction_step2" />
				</form>
				<hr class="break" />
			 					
			 	<h3 style="margin-top:20px"><?php _e("Export subscribers", "alo-easymail") ?></h3>
			 	<p><?php _e("You can export newsletter subscribers: the plugin shows them on screen so you can copy and paste them into a text file or into any application", "alo-easymail") ?></p>
			 	<form action="" method="get">			 	
			 		<input type="hidden" name="post_type"  value="newsletter" />
					<input type="hidden" name="action"  value="export_step2" /> <?php // the action ?>
					<input  type="hidden" name="page"   value="alo-easymail/alo-easymail_subscribers.php"/>
					<input type="submit" value="<?php _e('Export', 'alo-easymail') ?>" class="button" name="doaction_step2" />
				</form>		
			 	<hr class="break" />
			 					
			 	<h3 style="margin-top:20px" id="easymail-export-unsubscribed"><?php _e("Export unsubscribed emails", "alo-easymail") ?></h3>
			 	<p><?php _e("You can export email addresses of who unsubscribed the newsletter: the plugin shows them on screen so you can copy and paste them into a text file or into any application", "alo-easymail") ?></p>
			 	<form action="" method="get">			 	
			 		<input type="hidden" name="post_type"  value="newsletter" />
					<input type="hidden" name="action"  value="export_unsubscribers_step2" /> <?php // the action ?>
					<input  type="hidden" name="page"   value="alo-easymail/alo-easymail_subscribers.php"/>
					<input type="submit" value="<?php _e('Export', 'alo-easymail') ?>" class="button" name="doaction_step2" />
				</form>		
			 	<hr class="break" />
			 				 					
		 		<a href="javascript:history.back()"><?php _e("Cancel", "alo-easymail"); ?></a>
		 		<hr class="break" />
		 				 		
			 	<?php	
		 		exit();
		}
	} else { // errors
		echo '<div id="message" class="error"><p>'.__("Error during operation.", "alo-easymail");
		if ( !isset($_REQUEST['action']) || $_REQUEST['action'] == "" ) echo " ". __("No action selected", "alo-easymail") .".";
		if ( !isset($_REQUEST['subscribers'] )) echo " ". __("No subscriber selected", "alo-easymail") .".";
		echo '</p></div>';
	}
}

/**
 * Bulk action: Step #2/2
 */
if ( isset($_REQUEST['doaction_step2']) ) {
	//if($wp_version >= '2.6.5') check_admin_referer('alo-easymail_subscribers');
	if ( isset($_REQUEST['action']) && $_REQUEST['action'] != "" ) {
		switch ( $_REQUEST['action'] ) {
			// Add a subscriber with simple form
			case "addsingle_step2":
				if ( !isset($_REQUEST['addsingle_email']) || !is_email( $_REQUEST['addsingle_email']) ) {
					echo '<div id="message" class="error"><p>'.__("Error during operation.", "alo-easymail");
					echo "<br />". __("The e-email address is not correct", "alo-easymail") .".";
					echo '</p></div>';	
					echo '<p><a href="javascript:history.back()">'. __("Back", "alo-easymail"). '</a></p>';
					exit;
				}
				else if ( alo_em_check_email_in_unsubscribed( $_REQUEST['addsingle_email']) ) {
					echo '<div id="message" class="error"><p>'.__("Error during operation.", "alo-easymail");
					echo "<br />". __("The owner of the e-email address unsubscribed the newsletter", "alo-easymail");
					echo " (". sprintf( __( "%s ago", "alo-easymail" ), human_time_diff( strtotime(alo_em_when_email_unsubscribed($_REQUEST['addsingle_email'])), current_time('timestamp') ) ).")";
					echo '</p></div>';	
					echo '<p><a href="javascript:history.back()">'. __("Back", "alo-easymail"). '</a></p>';
					exit;
				}
				
				$email	= stripslashes ( $wpdb->escape ( trim( $_REQUEST['addsingle_email'] ) ) );
				$name 	= ( isset( $_REQUEST['addsingle_name'] ) ) ? stripslashes ( $wpdb->escape ( trim( $_REQUEST['addsingle_name'] ) ) ) : "";
				
				//edit : added all this foreach
				if( $alo_em_cf ) {
					foreach( $alo_em_cf as $key => $value ){
						$fields[$key] = ( isset( $_REQUEST['addsingle_'.$key] ) ) ? stripslashes ( $wpdb->escape ( trim( $_REQUEST['addsingle_'.$key] ) ) ) : "";
					}
				}
				
				$lang 	= ( isset( $_REQUEST['addsingle_name'] ) ) ? stripslashes ( $wpdb->escape ( trim( $_REQUEST['addsingle_lang'] ) ) ) : "";
				$fields['email'] = $email; //edit : added all this line
				$fields['name'] = $name; //edit : added all this line
				if ( $email && alo_em_add_subscriber( $fields, 1, $lang ) == "OK" ) { //edit : orig : if ( $email && alo_em_add_subscriber( $email , $name , 1, $lang ) == "OK" ) {
					if ( isset($_REQUEST['check_list']) ) {
						$subscriber_id = alo_em_is_subscriber( $email );
						foreach ( $_REQUEST['check_list'] as $list ) {
							alo_em_add_subscriber_to_list ( $subscriber_id, $list );
						}
					}
					print '<div id="message" class="updated fade"><p>'.__("New subscriber added", "alo-easymail").'.</p></div>';	
				}										
				break;
				
			// Save requested subscriptions to lists
			case "lists_step2":	
				if ( !isset($_REQUEST['subscribers']) || $_REQUEST['subscribers'] == "" || !isset($_REQUEST['check_list']) || count ($_REQUEST['check_list']) == 0 ) {
					echo '<div id="message" class="error"><p>'.__("Error during operation.", "alo-easymail");
					if ( !isset($_REQUEST['subscribers']) || $_REQUEST['subscribers'] == "" ) echo " ". __("No subscriber selected", "alo-easymail") .".";
					if ( !isset($_REQUEST['check_list']) || count ($_REQUEST['check_list']) == 0 ) echo " ". __("No list selected", "alo-easymail") .".";
					echo '</p></div>';	
					break;
				}
				$selected_subscribers = explode ( "," , $_REQUEST['subscribers'] );
				
				switch ( $_REQUEST['mode'] ) { // The requested mode
					case "add_remove":	// Add to selected lists and remove from not selected lists
						// TODO !		
						break;
					case "add":			// Add to selected lists
						foreach ($selected_subscribers as $subscriber ) {
							foreach ( $_REQUEST['check_list'] as $list ) {
								alo_em_add_subscriber_to_list ( $subscriber, $list );
							}
						}
						break;
					case "remove":		// Remove from selected lists 
						foreach ($selected_subscribers as $subscriber ) {
							foreach ( $_REQUEST['check_list'] as $list ) {
								alo_em_delete_subscriber_from_list ( $subscriber, $list );
							}
						}
						break;											
				} // end switch mode
				
				print '<div id="message" class="updated fade"><p>'.__("Updated", "alo-easymail").'.</p></div>';
				break;

			// Save requested language attribute
			case "langs_step2":	
				if ( !isset($_REQUEST['subscribers']) || $_REQUEST['subscribers'] == "" || !isset($_REQUEST['check_lang']) || count ($_REQUEST['check_lang']) == 0 ) {
					echo '<div id="message" class="error"><p>'.__("Error during operation.", "alo-easymail");
					if ( !isset($_REQUEST['subscribers']) || $_REQUEST['subscribers'] == "" ) echo " ". __("No subscriber selected", "alo-easymail") .".";
					if ( !isset($_REQUEST['check_lang']) ) echo " ". __("No language option selected", "alo-easymail") .".";
					echo '</p></div>';	
					break;
				}
				$selected_subscribers = explode ( "," , $_REQUEST['subscribers'] );
				foreach ($selected_subscribers as $subscriber ) {
					$lang = ( $_REQUEST['check_lang'] == "blank" ) ? "" : $_REQUEST['check_lang'] ; 
					alo_em_assign_subscriber_to_lang ( $subscriber, $lang );
				}
				print '<div id="message" class="updated fade"><p>'.__("Updated", "alo-easymail").'.</p></div>';
				break;
								
			// Upload csv and insert new subscribers into db
			case "import_step2":	
				if ( ($handle = fopen($_FILES['uploaded_csv']['tmp_name'], "r")) !== FALSE /* && ($_FILES['uploaded_csv']['type'] == "text/csv" || $_FILES['uploaded_csv']['type'] == "text/plain" || $_FILES['uploaded_csv']['type'] == "text/tsv") */ ) {
					$row = 0;
					$success = 0; // success
					$not_imported = array(); // list not imported and why
					if ( isset($_REQUEST['test_only']) && $_REQUEST['test_only'] == "yes") {
						$html = "";
						$html .= '<p><a href="javascript:history.back()">'. __("Back", "alo-easymail"). '</a></p>';
						$html .= '<table class="widefat">';
						$html .= '<thead><tr valign="top"><th scope="col"> </th><th scope="col">'.__("E-mail", "alo-easymail").'</th><th scope="col">'.__("Name", "alo-easymail").'</th><th scope="col">'.__("Language", "alo-easymail").'</th>'; //edit : splitted in two parts : orig : $html .= '<thead><tr valign="top"><th scope="col"> </th><th scope="col">'.__("E-mail", "alo-easymail").'</th><th scope="col">'.__("Name", "alo-easymail").'</th><th scope="col">'.__("Language", "alo-easymail").'</th><th scope="col">'.__("Error", "alo-easymail").'</th></thead>';
				
						//edit : added all this foreach
						if( $alo_em_cf ) {
							foreach( $alo_em_cf as $key => $value ){
								  $html .= '<th scope="col">'.$value['humans_name'].'</th>';
							}
						}
						$html .= '<th scope="col">'.__("Error", "alo-easymail").'</th></thead>'; //edit : splitted in two parts : orig : $html .= '<thead><tr valign="top"><th scope="col"> </th><th scope="col">'.__("E-mail", "alo-easymail").'</th><th scope="col">'.__("Name", "alo-easymail").'</th><th scope="col">'.__("Language", "alo-easymail").'</th><th scope="col">'.__("Error", "alo-easymail").'</th></thead>';
						$html .= '<tbody>';
					}
					while ( ($data = fgetcsv($handle, 1000, ";")) !== FALSE ) {
						$row++;
						// check data
						$data = array_map( 'strip_tags', $data );
						$email	= trim( $data[0] ); //stripslashes ( $wpdb->escape ( trim( $data[0] ) ) );
						$email 	= ( is_email( $email )) ? $email : false;
						$name 	= ( isset($data[1]) ) ? trim($data[1]) /*stripslashes ( $wpdb->escape ( trim($data[1]) ) )*/ : "";
						$lang 	= ( isset($data[2]) ) ? trim($data[2]) /*stripslashes ( $wpdb->escape ( trim($data[2]) ) )*/ : "";
						if ( !empty( $lang ) ) {
							$lang = alo_em_short_langcode ( $lang );
							if ( !in_array ( $lang, alo_em_get_all_languages( false )) ) $lang = "";
						}
						
				
						//edit : added all this foreach
						$i = 3;
						if( $alo_em_cf ) {
							foreach( $alo_em_cf as $key => $value ){
								  $fields[$key] 	= ( isset($data[$i]) ) ? trim($data[$i]) /*stripslashes ( $wpdb->escape ( trim($data[$i]) ) )*/ : "";
								  ++$i;
							}
						}
						// error
						if ( $email == false ) { // error: email incorrect
							$not_imported[$data[0]] = __("The e-email address is not correct", "alo-easymail") ;
						} else if ( alo_em_check_email_in_unsubscribed($email) ) {
							$not_imported[$data[0]] = __("The owner of the e-email address unsubscribed the newsletter", "alo-easymail"). " (". sprintf( __( "%s ago", "alo-easymail" ), human_time_diff( strtotime(alo_em_when_email_unsubscribed($email)), current_time('timestamp') ) ).")";
						} else { // error: already existing
							if ( alo_em_is_subscriber($email) ) $not_imported[$data[0]] = __("There is already a subscriber with this e-email address", "alo-easymail") ; 
						}
						if ( isset($_REQUEST['test_only']) && $_REQUEST['test_only'] == "yes") { // test, print records
							//if ( $row > 10 ) continue; // print only the 1st 10
							$span_email = ( isset($not_imported[$data[0]])) ? "<span style='color:#f00'>".esc_html(trim( $data[0] ))."</span>" : esc_html(trim( $data[0] )) ;							
							$html .= "<tr><td>$row: </td>";
							$html .= "<td>". $span_email ."</td><td>".esc_html($name)."</td><td>".alo_em_get_lang_flag($lang, 'name')."</td>";
				
							//edit : added all this foreach
							if ( isset($fields) && is_array($fields) ) {
								foreach( $fields as $key => $value ){
									$html .= "<td>". esc_html($value) ."</td>";
								}
							}
							$html .= "<td><span style='color:#f00'>". ( ( isset($not_imported[$data[0]]) ) ? $not_imported[$data[0]] : "") ."</span></tr>";
						} else { // insert records into db							
							
							$fields['email'] = $email; //edit : added all this line
							$fields['name'] = $name; //edit : added all this line
							if ( $email && !alo_em_check_email_in_unsubscribed($email) && $result = alo_em_add_subscriber( $fields, 1, $lang ) /*== "OK"*/ ) { //edit : orig : if ( $email && $result = alo_em_add_subscriber( $email , $name , 1, $lang ) /*== "OK"*/ ) {
								if ( isset($_REQUEST['check_list']) ) {
									$subscriber_id = alo_em_is_subscriber( $email );
									foreach ( $_REQUEST['check_list'] as $list ) {
										alo_em_add_subscriber_to_list ( $subscriber_id, $list );
										unset( $not_imported[$data[0]] );
										if ( $result != "OK" ) { // if not just added
											$not_imported[$data[0]] = __("There is already a subscriber with this e-email address", "alo-easymail").". ". __("Subscriber has been added to selected mailing lists", "alo-easymail") ;
										}
									}
								}
								if ( $result == "OK" ) $success ++;
							}
						}
					}
					fclose($handle);
					if ( isset($_REQUEST['test_only']) && $_REQUEST['test_only'] == "yes") { // test report
						$html .= '</tbody></table>';
						echo '<p>'. sprintf(__("Found %d records", "alo-easymail"), $row ) .'.</p>';
						if ( count($not_imported) ) echo '<p>'. __("Check the Error column to modify records that otherwise will not be imported", "alo-easymail"). '.</p>';
						echo $html; // print table						
						echo '<p><a href="javascript:history.back()">'. __("Back", "alo-easymail"). '</a></p>';
						exit();
					} else { // import report
						echo '<div id="message" class="updated fade"><p>'. sprintf(__("Successfully import: %d records out of %d total", "alo-easymail"), $success, $row ) .'.';
						if ( count($not_imported) ) {
							echo '<p>'.__("Some records have not been imported", "alo-easymail").':</p>';
							echo '<style type="text/css">';
							echo 'table.import-report { margin: 0 0 10px 20px; }';
							echo 'table.import-report th { font-size:0.8em; color:#f00; text-align:left; font-weight:normal; padding:1px 5px }';
							echo 'table.import-report td { font-size:0.8em; font-style:italic; padding:1px 5px }';
							echo '</style>';
							echo '<table class="import-report"><tbody>';
							foreach ($not_imported as $email => $error ) {
								echo '<tr><th scope="row">'.$email.'</th>';
								echo '<td>'.$error.'</td></tr>';
							}
							echo '</tbody></table>';
						}
						echo '</p></div>';
						break;
					}
				} else {
					echo '<div id="message" class="error"><p>'.__("Not valid CSV file uploaded", "alo-easymail") . '.</p></div>';
				}
				break;
			
			// Import from WP registrered users
			case "wpusers_step2":
				$reg_users =  alo_em_get_recipients_registered ();
				if ( $reg_users ) {
					$add = 0;
					foreach ( $reg_users as $reg_user ) {
						$email = $reg_user->user_email;
						if ( alo_em_is_subscriber($email) == false && ! alo_em_check_email_in_unsubscribed($email) ){ // if not already subscriber or email unsubscribed, add							
							if ( get_user_meta($reg_user->UID, 'first_name', true) != "" ) {
						 	   	$name = ucfirst(get_user_meta($reg_user->UID, 'first_name', true))." " .ucfirst(get_user_meta($reg_user->UID,'last_name', true));
						 	} else {
						 		$name = get_user_meta($reg_user->UID, 'nickname', true);
						 	}
							$fields['email'] = $email; //edit : added all this line
							$fields['name'] = $name; //edit : added all this line
							if ( alo_em_add_subscriber( $fields, 1, "" ) == "OK" ) $add ++;
						}
					}
				}
				if ( $add > 0 ) {
					echo '<div id="message" class="updated fade"><p>'. sprintf(__("%d new subscribers successfully added from blog members", "alo-easymail"), $add )  .'.</p></div>';
				} else {
					echo '<div id="message" class="updated fade"><p>'. __("No subscribers added from blog members", "alo-easymail") .'.</p></div>';
				}
				break;
			
			// Export: show subscribers on screen
			case "export_step2":
				
				//edit : added all this foreach
				$fields_name = "";
				if( $alo_em_cf ) {
					foreach( $alo_em_cf as $key => $value ){
						  $fields_name 	.= ", ".$key;
					}
				}
				$all_subs = $wpdb->get_results( "SELECT email, name, lang{$fields_name} FROM {$wpdb->prefix}easymail_subscribers" );
				if ( $all_subs ) {
					echo '<p><a href="javascript:history.back()">'. __("Back", "alo-easymail"). '</a></p>';
					echo "<pre style='font-family:Courier,Monospace;width:50%;height:300px;border:1px dotted grey;background-color:white;padding:5px 25px;list-style-type:none;overflow:auto;'>\r\n";
					foreach ( $all_subs as $sub ) {
						echo esc_html( $sub->email . ";" . $sub->name . ";" . $sub->lang ); //edit : splitted in two parts : orig : echo $sub->email . ";" . $sub->name . ";" . $sub->lang. "\r\n";
						//edit : added all this foreach
						if( $alo_em_cf ) {
							foreach( $alo_em_cf as $key => $value ){
								  echo ";". esc_html($sub->{$key});
							}
						}
						echo "\r\n"; //edit : splitted in two parts : orig : echo $sub->email . ";" . $sub->name . ";" . $sub->lang. "\r\n";
					}
					echo "\r\n</pre>";
				} else {
					echo '<div id="message" class="error"><p>'. __("No subscribers", "alo-easymail") . '.</p></div>';
				}
				echo '<p><a href="javascript:history.back()">'. __("Back", "alo-easymail"). '</a></p>';
				exit();
				break;


			// Export unsubscribed: show emails on screen
			case "export_unsubscribers_step2":
				
				//edit : added all this foreach
				if( $alo_em_cf ) {
					$fields_name = "";
					foreach( $alo_em_cf as $key => $value ){
						  $fields_name 	.= ", ".$key;
					}
				}
				$unsubs = $wpdb->get_results( "SELECT email FROM {$wpdb->prefix}easymail_unsubscribed" );
				if ( $unsubs ) {
					echo '<p><a href="javascript:history.back()">'. __("Back", "alo-easymail"). '</a></p>';
					echo "<pre style='font-family:Courier,Monospace;width:50%;height:300px;border:1px dotted grey;background-color:white;padding:5px 25px;list-style-type:none;overflow:auto;'>\r\n";
					foreach ( $unsubs as $sub ) {
						echo esc_html( $sub->email ). "\r\n";
					}
					echo "\r\n</pre>";
				} else {
					echo '<div id="message" class="error"><p>'. __("No unsubscribed emails", "alo-easymail") . '.</p></div>';
				}
				echo '<p><a href="javascript:history.back()">'. __("Back", "alo-easymail"). '</a></p>';
				exit();
				break;
									
		} // end switch action
	} else {
		echo '<div id="message" class="updated fade"><p>'. __("No action selected", "alo-easymail") .'.</p></div>';
	}
}
?>    

	    
<?php 
// Search / filter form
?>
<form action="" method="get" class="search-form">
	<p class="search-box">
	<input type="text" name="s" value="<?php if (!empty( $s )) echo stripslashes( $s ) ; ?>" class="search-input" id="s" />
	<input  type="hidden" name="post_type"   value="newsletter"/>
	<input  type="hidden" name="page"   value="alo-easymail/alo-easymail_subscribers.php"/>
	<input  type="hidden" name="paged"  value="1" />
	<input  type="hidden" name="num"    value="<?php echo $items_per_page ?>" />
	<input  type="hidden" name="sortby" value="<?php echo $_GET['sortby'] ?>" />
	<input  type="hidden" name="order"  value="<?php echo ( isset($_GET['order']) && $_GET['order'] == 'DESC' ) ? 'DESC' : 'ASC'; ?>" />
	
	<?php
	if ($mailinglists) { ?>
	<select name="filter_list">
		<option selected="selected" value=""><?php _e( 'Select a mailing list', "alo-easymail" ) ?>...</option>
		<?php foreach ( $mailinglists as $list => $val ) {
			$selected = ( $filter_list == $list ) ? 'selected="selected"' : '';
			echo '<option value="'.$list.'" '.$selected.'>'. alo_em_translate_multilangs_array ( alo_em_get_language(), $val['name'], true ) .'</option>';
		} ?>
	</select>
	<?php } // end if mailingslist ?>

	<?php // Lang select
	if ( $languages ) { ?>
	<select name="filter_lang">
		<option selected="selected" value=""><?php _e( 'Choose a language', "alo-easymail" ) ?>...</option>
		<?php foreach ( $languages as $key => $val ) {
			$selected = ( $filter_lang == $val ) ? 'selected="selected"' : '';
			$lang_name = esc_html ( alo_em_get_lang_name ( $val ) );
			echo '<option value="'.$val.'" '.$selected.'>'. $lang_name .'</option>';
		} ?>
	</select>
	<?php } // end if languages ?>
		
	<input type="submit" value="<?php _e("Search") ?>" class="button" />
	
	<?php if ( $s || $filter_list || $filter_lang ) echo "&nbsp;&nbsp;<a href='".$link_base."&amp;num=".$items_per_page."'>".__("Show all", "alo-easymail")."</a>" ?>
	
	</p>
</form>

<?php 
// Prepare link string (with common vars)
$link_string = $link_base . "&amp;paged=".$page."&amp;num=".$items_per_page. (($s)? "&amp;s=".$s : "") . (($filter_list)? "&amp;filter_list=".$filter_list : "") . (($filter_lang)? "&amp;filter_lang=".$filter_lang : "");
?>

<?php
$impexp_butt = __("Import/export subscribers", "alo-easymail");

// Import alert (WP<3.3)
global $wp_version;
if ( version_compare ( $wp_version, '3.3', '<' ) ) {
	if ( get_option('alo_em_import_alert') == "show" ) { 
		echo '<div class="updated fade" style="background-color:#99FF66">';
		echo '<p>'. sprintf( __('Maybe you would like to import subscribers from your blog registered members or an external archive (using CSV). Click the &#39;%s&#39; button', 'alo-easymail'), $impexp_butt) .'.</p>';
		echo "<p>(<a href='". $link_base ."&amp;import_alert=stop' />". __('Do not show it again', 'alo-easymail') ."</a>)</p>";
		echo '</div>';
	}
}
?>
<div style="margin-top:15px">
	<img src="<?php echo ALO_EM_PLUGIN_URL ?>/images/24-users.png" style="vertical-align:middle" />
	<?php $import_link = wp_nonce_url( admin_url() . $link_base . "&amp;doaction_step1=true&amp;action=import", 'alo-easymail_subscribers'); ?>
	<a href="<?php echo $import_link ?>" title="" id="easymail-subscribers-add-button"><?php echo $impexp_butt; ?></a>
</div>

<?php 
//SELECT NUM PER PAGE (items per page) 

// prepare url string
$link_string_js = str_replace("&amp;", "&", $link_string);
// use regexpr to set always page 1
//if(preg_match('/\s*paged=\s*(\d+)\s*/', $link_string_js, $matches)) {
//	$link_string_js = str_replace($matches[1], "1", $link_string_js);
//	//print_r($matches[1]);
//}
$link_string_js = remove_query_arg( "paged", $link_string_js );
$link_string_js = add_query_arg( "paged", "1", $link_string_js );
?>

<script type="text/JavaScript">
<!--
function MM_jumpMenu(targ,selObj,restore){ //v3.0
    window.location.href = "<?php echo $link_string_js?>&num=" + selObj.options[selObj.selectedIndex].value;
}

function toggleCheckboxes(current, form, field) {
	var val = current.checked;
	var cbs = document.getElementById(form).getElementsByTagName('input');
	var length = cbs.length;
	
	for (var i=0; i < length; i++) {
		if (cbs[i].name == field +'[]' && cbs[i].type == 'checkbox') {
			cbs[i].checked = val;
		}
	}
}

function checkBulkForm (form, field) {
	var cbs = document.getElementById(form).getElementsByTagName('input');
	var length = cbs.length;
	var output = false;
	for (var i=0; i < length; i++) {
		if (cbs[i].name == field +'[]' && cbs[i].type == 'checkbox') {
			if ( cbs[i].checked ) output = true
		}
	}
	if ( output == false ) {
		alert ('<?php echo esc_js( __("No subscriber selected", "alo-easymail") )?>');
		return false;
	}
	if (document.getElementById(form).action.value == '') {
		alert ('<?php echo esc_js( __("No action selected", "alo-easymail") ) ?>');
		return false;
	}
	if (document.getElementById(form).action.value == 'delete') {
		return confirm ('<?php echo esc_js( __("Do you really want to DELETE these subscribers?", "alo-easymail") )?>');
	}
	if (document.getElementById(form).action.value == 'delete_and_unsubscribe') {
		return confirm ('<?php echo esc_js( __("Do you really want to DELETE these subscribers?", "alo-easymail").' '. __("The email address will be added to the list of who unsubscribed", "alo-easymail") .": ". __("so you cannot add or import these email addresses using the tools in admin pages", "alo-easymail") ) ?>');
	}	
}

//-->
</script>


<style type="text/css">
	.widefat tr th {vertical-align: middle;}
	.widefat tr td {vertical-align: middle;}
	ul.userlists li {line-height:0.8em}
	ul.userlists {padding-top:0.3em}
	td .ip-address { font-size: 75%; }
</style>

<?php 
// Bulk action form
?>
<form method="post" action="" id="posts-filter" name="bulkform">
<div class="tablenav">
	<div class="alignleft actions">
		<select name="action">
			<option selected="selected" value=""><?php _e('Bulk Actions') ?></option>
			<option value="lists"><?php _e("Edit subscription to mailing lists", "alo-easymail") ?> ...</option>
			<option value="language"><?php _e("Change language ascribed to subscribers", "alo-easymail") ?> ...</option>
			<option value="activate"><?php _e("Activate", "alo-easymail") ?></option>
			<option value="deactivate"><?php _e("Deactivate", "alo-easymail") ?></option>			
			<option value="delete"><?php _e('Delete') ?></option>
			<option value="delete_and_unsubscribe"><?php _e("Delete and add to list of who unsubscribed", "alo-easymail") ?></option>	
		</select>
		
		<input  type="hidden" name="s"  value="<?php if (!empty( $s )) echo stripslashes( $s ) ; ?>" />
		<input  type="hidden" name="post_type"   value="newsletter"/>
		<input  type="hidden" name="page"   value="alo-easymail/alo-easymail_subscribers.php"/>
		<input  type="hidden" name="paged"  value="<?php echo $page ?>" />
		<input  type="hidden" name="num"    value="<?php echo $items_per_page ?>" />
		<input  type="hidden" name="sortby" value="<?php echo $_GET['sortby'] ?>" />
		<input  type="hidden" name="order"  value="<?php echo ( isset($_GET['order']) && $_GET['order'] == 'DESC' ) ? 'DESC' : 'ASC'; ?>" />
		<input  type="hidden" name="filter_list"  value="<?php echo $filter_list ?>" />
		<input  type="hidden" name="filter_lang"  value="<?php echo $filter_lang ?>" />
		 <?php wp_nonce_field('alo-easymail_subscribers'); ?>
		 
		<input type="submit" class="button-secondary action" id="doaction_step1" name="doaction_step1" value="<?php _e('Apply') ?>" onclick="return checkBulkForm('posts-filter', 'subscribers')">
	</div>
	

<?php

// BUILD THE QUERY
$query =    "SELECT * FROM {$wpdb->prefix}easymail_subscribers";

$where_search = "";
if( !empty( $s ) ) {
	$search = '%' . trim( $s ) . '%';
	$where_search = " WHERE (email LIKE '$search' OR name LIKE '$search'";
	if ( $alo_em_cf ) {
		foreach ( $alo_em_cf as $key => $value ) $where_search .= " OR $key LIKE '$search'";
	}
	$where_search .= ") ";
}
if( !empty( $filter_list ) ) {
	$filter_list = '%|' . trim( $filter_list ) . '|%';
	$where_search .= ( $where_search == "" ) ? " WHERE " : " AND " ;
	$where_search .= " lists LIKE '$filter_list' ";
}
if( !empty( $filter_lang ) ) {
	$where_search .= ( $where_search == "" ) ? " WHERE " : " AND " ;
	$where_search .= " lang = '$filter_lang' ";
}
$query .= $where_search;

// order
if ( isset($_GET['sortby']) ) {
	if( $_GET['sortby'] == 'email' ) {
		$query .= ' ORDER BY email ';
	} else if( $_GET['sortby'] == 'join_date' ) {
		$query .= ' ORDER BY join_date ';
	} else if( $_GET['sortby'] == 'active' ) {
		$query .= ' ORDER BY active ';
	} else if( $_GET['sortby'] == 'lang' ) {
		$query .= ' ORDER BY lang ';
	} else if( $_GET['sortby'] == 'last_act' ) {
		$query .= ' ORDER BY last_act ';		
	} else if( $alo_em_cf && array_key_exists( $_GET['sortby'], $alo_em_cf )) {
		$query .= ' ORDER BY '. $_GET['sortby']. ' ';
	} 
}

$query .= ( isset($_GET['order']) && $_GET['order'] == 'ASC' ) ? 'ASC' : 'DESC';

$query .= " LIMIT $offset, $items_per_page ";

//echo $query; //DEBUG


// TOTAL NUMBER OF SUBSCRIBERS
$total_items = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}easymail_subscribers" . (!empty( $s ) || !empty($filter_list) || !empty($filter_lang) ? $where_search : ""));
?>
<div class="tablenav-pages ">
	<span class="alignright displaying-num"><?php echo $total_items .' '. __("items") ?></span>
</div>

</div><!-- tablenav -->


<table class="widefat" style='margin-top:10px'>
	<thead>
	<tr>
		<th scope="col"> </th>
		<th scope="col"><input type="checkbox" name="checkall_subscribers" value="" onclick="toggleCheckboxes(this, 'posts-filter', 'subscribers');" style="margin:1px" /></th>
		<?php if ( get_option('show_avatars') ) : ?> <th scope="col"><div style="text-align: center;"><!-- Avatar --></div></th><?php endif; ?>
		<th scope="col"><?php echo "<a href='".$link_string."&amp;sortby=email".( ( isset($_GET['order']) && $_GET['order'] == 'DESC' )? "&amp;order=ASC": "&amp;order=DESC")."' title='".__("Order by e-mail", "alo-easymail")."'>".__("E-mail", "alo-easymail")."</a>"; ?>	</th>
		<th scope="col"><?php _e("Name", "alo-easymail") ?></th>
        
        <?php
		//edit : added the following foreach and its content
		if ( $alo_em_cf ) {
			foreach( $alo_em_cf as $key => $value ){
				//echo "<th scope=\"col\">".$value['humans_name']."</th>\n";
				?>
				<th scope="col"><?php
				if ( $value['sql_key'] ) echo "<a href='".$link_string."&amp;sortby=". $key .( ( isset($_GET['order']) && $_GET['order'] == 'DESC' )? "&amp;order=ASC": "&amp;order=DESC")."' title='". sprintf( __("Order by %s", "alo-easymail"), $value['humans_name'])."'>";
				echo $value['humans_name'];
				if ( $value['sql_key'] ) echo "</a>";
				?></th>
				<?php
			}
		}
		?>
		<th scope="col"><?php echo __("Username", "alo-easymail") . alo_em_help_tooltip( __("The username of registered users. It is blank for public subscribers.", "alo-easymail") ) ?></th>
		<th scope="col"><?php echo "<a href='".$link_string."&amp;sortby=join_date".( ( isset($_GET['order']) && $_GET['order'] == 'DESC' )? "&amp;order=ASC": "&amp;order=DESC")."' title='".esc_attr(__("Order by join date", "alo-easymail"))."'>".__("Join date", "alo-easymail")."</a>"; ?></th>
		<th scope="col"><?php echo "<a href='".$link_string."&amp;sortby=last_act".( ( isset($_GET['order']) && $_GET['order'] == 'DESC' )? "&amp;order=ASC": "&amp;order=DESC")."' title='".esc_attr(__("Order by date of last activity", "alo-easymail"))."'>".__("Last activity", "alo-easymail")."</a>" . alo_em_help_tooltip( __("The most recent date when the subcriber made an action", "alo-easymail") ." (". __("e.g. open a newsletter, click a newsletter link, update info in subscription form", "alo-easymail").")" ); ?></th>		
		<th scope="col"><?php echo "<a href='".$link_string."&amp;sortby=active".( ( isset($_GET['order']) && $_GET['order'] == 'DESC' )? "&amp;order=ASC": "&amp;order=DESC")."' title='".esc_attr(__("Order by activation state", "alo-easymail"))."'>".__("Activated", "alo-easymail")."</a>" .alo_em_help_tooltip( __("For registered users the dafault state is activated. For public subscribers the default state is deactivated: it will be activated by clicking on the activation link in the e-mail.", "alo-easymail") ." ". __("A subscriber will be deleted if not activated in 5 days.", "alo-easymail") ); ?></th>
		<th scope="col"><?php _e("Mailing Lists", "alo-easymail"); ?></th>
		<th scope="col"><?php echo "<a href='".$link_string."&amp;sortby=lang".( ( isset($_GET['order']) && $_GET['order'] == 'ASC' )? "&amp;order=DESC": "&amp;order=ASC")."' title='".esc_attr(__("Order by language", "alo-easymail"))."'>".__("Language", "alo-easymail")."</a>"; ?></th>				
		<th scope="col"><?php _e("Actions", "alo-easymail") ?></th>
	</tr>
	</thead>

	<tbody id="the-list">
		
<?php
// The QUERY on subscribers
$all_subscribers = $wpdb->get_results($query);

if (count($all_subscribers)) {
	$class = 'alternate';
	$row_count = 0;
	foreach($all_subscribers as $subscriber) {
		$row_count++;

		$row_index = ( ($page -1) * $items_per_page + $row_count);
		echo "<tr id='subscriber-row-{$subscriber->ID}' class='subscriber-row'>\n";		
		
		// the subscriber row
		echo alo_em_get_subscriber_table_row ( $subscriber->ID, $row_index, false, $mailinglists, $languages );
		echo "</tr>\n";
		?>
		
<?php
	}

} else {
?>
	<tr>
		<td colspan="8"><?php _e("No subscribers", "alo-easymail") ?>.</td>
	</tr>
<?php
} // subscribers
?>
	</tbody>
</table>

</form> <?php // close bulk action form ?>

<?php if(count($all_subscribers)) { ?>
<div class="tablenav">
<?php
$total_items = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}easymail_subscribers" . (!empty( $s ) || !empty($filter_list) || !empty($filter_lang) ? $where_search : ""));

$total_pages = ceil($total_items / $items_per_page);

$arr_params = array ('paged' => '%#%', 
                    'subscriber_id' => '' /* unset to avoid updated msg in next page */
                    );                     
$page_links = paginate_links( array(
	'base' => add_query_arg( $arr_params/*'paged', '%#%' */),
	'format' => '',
	'total' => $total_pages,
	//'show_all' => true,
	'current' => $page
));

if ( $page_links ) echo "<div class='tablenav-pages'>$page_links</div>";
?>
</div>
<?php } ?>

<p><?php alo_em_show_credit_banners( false ); ?></p>

<?php 
// DEBUG ------------------------------------------
//print "<pre>".$query."</pre>";
//print "<br />Totali = ".$total_items."<br />";
//print_r($_GET)    
// end DEBUG --------------------------------------

/**
 * --- end MAIN ----------------------------------------------------------------
 */
?>

        </div> <?php // Closes #dashboard-widgets ?>
        
        
        <div class="clear"></div>
  
</div><!-- wrap -->	
