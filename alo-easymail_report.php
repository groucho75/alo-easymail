<?php
include('../../../wp-load.php');
//auth_redirect();

if ( !current_user_can( "edit_newsletters" ) ) 	wp_die( __('Cheatin&#8217; uh?') );

//print_r ($_REQUEST); // DEBUG

check_admin_referer('alo-easymail_report');
global $wpdb;

/*
 * Checks Required vars
 */
if ( isset( $_REQUEST['newsletter'] ) ) {
	$newsletter = (int)$_REQUEST['newsletter'];
	if ( get_post_type( $newsletter ) != "newsletter" ) wp_die( __('The required newsletter does not exist', "alo-easymail") ); 
	if ( !get_post( $newsletter ) ) wp_die( __('The required newsletter does not exist', "alo-easymail") );
	if ( !alo_em_user_can_edit_newsletter( $newsletter ) ) wp_die( __('Cheatin&#8217; uh?') );
	$offset =  isset( $_REQUEST['offset'] ) ? $_REQUEST['offset'] : 0;
} else {
	wp_die(__('Cheatin&#8217; uh?') );
}


if ( $newsletter ) { 

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php do_action('admin_xml_ns'); ?> <?php language_attributes(); ?>>
<head>
<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php echo get_option('blog_charset'); ?>" />
<title><?php  _e('Newsletter report', "alo-easymail") ?></title>
<?php
	
    // ID of newsletter (to make the report)
    $id = $newsletter; // $_REQUEST['newsletter'];
    
    // Lang
    $lang = ( isset($_REQUEST['lang'])) ? $_REQUEST['lang'] : false;

	$newsletter_post = alo_em_get_newsletter( $newsletter );
	
	$per_page = 250;
	
	if ( !$newsletter ) {
		die("The requested page doesn't exists.");
	} else { 
		?>
		<link rel="stylesheet" href="<?php echo ALO_EM_PLUGIN_URL ?>/inc/jquery.ui.tabs.css" type="text/css" media="print, projection, screen" />
		<link rel="stylesheet" href="<?php echo ALO_EM_PLUGIN_URL ?>/inc/alo-easymail-backend.css" type="text/css" media="print, projection, screen" />
		
		</head>
		
		<body>
		
		<div class="wrap">
			
			<?php if ( isset($_GET['_wpnonce']) && !isset($_GET['isnewwin']) ) : ?>
				<a href="<?php echo wp_nonce_url( ALO_EM_PLUGIN_URL . '/alo-easymail_report.php?newsletter='.$newsletter.'&lang='.$lang.'&isnewwin=1', 'alo-easymail_report'); ?>" target="_blank" class="new-win-link">
				<?php _e("open in a new window", "alo-easymail") ?></a>
			<?php endif; ?>				
			
			<!-- Newsletter's general details -->
			<div id="par-1">
				<dl>
					<dt><?php _e("Subject", "alo-easymail");  ?>:</dt>
					<dd><strong><?php 
					$subject = get_the_title( $newsletter );
					/*
					TODO tag title in subject
					
					if ( $newsletter->tag ) {
						$obj_post = get_post( $newsletter->tag );
						$post_title = stripslashes ( alo_em___ ( $obj_post->post_title ) );
						$subject = str_replace('[POST-TITLE]', $post_title, $subject);
						echo "<strong>". stripslashes ( alo_em_translate_text ( $lang, $subject ) ) . "</strong>";
						echo "<br /><em>". stripslashes ( alo_em_translate_text ( $lang, $newsletter->subject ) ) ."</em>";
					} else {
						echo "<strong>". stripslashes ( alo_em_translate_text ( $lang, $subject ) ) . "</strong>";
					}
					*/
					echo $subject;
					?></strong></dd>
				</dl>
					<dl><dt><?php _e("Scheduled by", "alo-easymail") ?></dt>
					<dd><?php echo get_user_meta( $newsletter_post->post_author, 'nickname', true ) ?></dd></dl>
				<dl>
					<dt><?php _e("Start", "alo-easymail") ?>:</dt>
					<dd><?php echo date_i18n( __( 'j M Y @ G:i', "alo-easymail" ), strtotime( $newsletter_post->post_date ) ) ?></dd>
				</dl>
				<dl>
					<dt><?php _e("Completed", "alo-easymail") ?>:</dt>
					<dd><?php 
						$end = get_post_meta ( $newsletter, "_easymail_completed", current_time( 'mysql', 0 ) );
						echo ( $end ) ? date_i18n( __( 'j M Y @ G:i', "alo-easymail" ), strtotime( $end ) ) : __("No", "alo-easymail" );
					 ?></dd>
				</dl>		
				<dl>
					<dt><?php _e("Main body", "alo-easymail") ?> (<?php _e("without formatting", "alo-easymail") ?>):</dt>
					<dd id="mailbody">
						<?php echo strip_tags( alo_em_translate_text ( $lang, $newsletter_post->post_content ), "<img>");
						//echo apply_filters('the_content', $newsletter_post->post_content ) ?>
					</dd>
				</dl>	
			</div>
			
			<!-- Newsletter's recipients list -->
			<div id="par-2">
			
				<?php
				// If archived
				if ( $archived_raw = alo_em_is_newsletter_recipients_archived ( $newsletter ) ) {
					$archived_meta = $archived_raw[0];
					$tot_recipients 	= $archived_meta['tot'];
					$already_sent 		= $archived_meta['sent'];
					$sent_with_success 	= $archived_meta['success'];
					$sent_with_error 	= $archived_meta['error'];
					$unique_views 		= $archived_meta['uniqview'];
					$unique_clicks 		= $archived_meta['uniqclick'];

				// If regular, not archived
				} else {
					// List of recipients, paged
					$recipients = alo_em_get_newsletter_recipients( $newsletter, false, $offset, $per_page ); 
				
					// Total number of recipients
					$tot_recipients = alo_em_count_newsletter_recipients ( $newsletter );
				
					// Other info
					$already_sent = alo_em_count_newsletter_recipients_already_sent ( $newsletter );
					$sent_with_success = alo_em_count_newsletter_recipients_already_sent_with_success( $newsletter );
					$sent_with_error = alo_em_count_newsletter_recipients_already_sent_with_error( $newsletter );
					$unique_views = count( alo_em_all_newsletter_trackings ( $newsletter, '' ) );
					$unique_clicks = count ( alo_em_all_newsletter_trackings_except_views ( $newsletter ) );
				}
				?>		
			
				<?php // Archive (delete) detailed info of recipients
				if ( isset($_GET['archive']) && alo_em_get_newsletter_status( $newsletter ) == "sent" ) :
					$archived_recipients = array( 'tot' => $tot_recipients, 'sent' => $already_sent, 'success' => $sent_with_success, 'error' => $sent_with_error, 'uniqview' => $unique_views, 'uniqclick' => $unique_clicks );
					add_post_meta ( $newsletter, "_easymail_archived_recipients", $archived_recipients );
					alo_em_delete_newsletter_recipients ( $newsletter );
					echo "<div class=\"easymail-alert\">". __("Detailed report was archived", "alo-easymail") ."</div>\n";
				endif; ?>	
			
				<table class="summary">
					<thead><tr>
						<th scope="col"><?php _e("Total recipients", "alo-easymail") ?></th>
						<th scope="col"><?php _e("Sendings done", "alo-easymail") ?></th>
						<th scope="col"><?php _e("Sendings succesful", "alo-easymail") ?></th>
						<th scope="col"><?php _e("Sendings failed", "alo-easymail") ?></th>
						<th scope="col"><?php 
							echo __("Unique views", "alo-easymail") . " "; 
							echo alo_em_help_tooltip( 
								__("The plugin tries to count how many recipients open the newsletter", "alo-easymail"). ". "
								. __("The number includes max a view per recipient", "alo-easymail"). ". "
							);
						?></th>						
						<th scope="col"><?php 
							echo __("Clicks", "alo-easymail") . " "; 
							echo alo_em_help_tooltip( 
								__("The number includes max a view per recipient", "alo-easymail"). ". "
							);						
						?></th>
					</tr></thead>
				<tbody>
					<tr>
						<td class="tot center" style="width:20%"><?php echo $tot_recipients; ?></td>
						<td class="done center" style="width:20%"><?php echo $already_sent ?></td>
						<td class="success center" style="width:15%"><?php echo $sent_with_success ?></td>
						<td class="error center" style="width:15%"><?php echo $sent_with_error  ?>	</td>
						<td class="views center" style="width:15%"><?php echo $unique_views  ?></td>		
						<td class="success center" style="width:15%"><?php echo $unique_clicks ?><?php
						if ( $unique_clicks >0 ) : ?><a href="<?php echo wp_nonce_url(ALO_EM_PLUGIN_URL . '/alo-easymail_report.php?newsletter='.$newsletter.'&lang='.$lang.'&show_clicked=1', 'alo-easymail_report'); ?>" title="<?php esc_attr_e(__("click to view list of clicked links", "alo-easymail")) ?>"><img src="<?php echo ALO_EM_PLUGIN_URL ?>/images/16-arrow-right.png" /></a><?php
						endif; ?>
						</td>
					</tr>
					<tr style="font-size: 60%">
						<td class="tot center">100%</td>
						<td class="done center"><?php echo alo_em_rate_on_total($already_sent, $tot_recipients); ?>%</td>
						<td class="success center"><?php echo alo_em_rate_on_total($sent_with_success, $tot_recipients); ?>%</td>
						<td class="error center"><?php echo alo_em_rate_on_total($sent_with_error, $tot_recipients);  ?>%</td>
						<td class="views center"><?php echo alo_em_rate_on_total($unique_views, $tot_recipients);  ?>%</td>		
						<td class="success center"><?php echo alo_em_rate_on_total($unique_clicks, $tot_recipients); ?>%</td>
					</tr>
				</tbody>
				</table>
			
			<?php // Archive button
			if ( !isset($_GET['isnewwin']) ) { 
				if ( alo_em_is_newsletter_recipients_archived ( $newsletter ) ) {
					if ( !isset($_GET['archive']) ) echo "<div class=\"easymail-alert\">". __("Detailed report was archived", "alo-easymail") ."</div>\n";
				} else if ( alo_em_get_newsletter_status( $newsletter ) == "sent" ) { ?>
				<div id="par-3">
					<a href="<?php //wp_nonce_url( ALO_EM_PLUGIN_URL . '/alo-easymail_report.php?newsletter='.$newsletter.'&lang='.$lang.'&isnewwin=1', 'alo-easymail_report')
					echo wp_nonce_url( ALO_EM_PLUGIN_URL . '/alo-easymail_report.php?newsletter='.$newsletter.'&lang='.$lang.'&archive=1', 'alo-easymail_report') ; ?>" class="easymail-navbutton button-archive" onclick='javascript:if( confirm("<?php echo esc_js( __("Are you sure?", "alo-easymail")." " .__("You are about to DELETE the detailed info about recipients", "alo-easymail").". " . __("This action cannot be undone", "alo-easymail") ) ?>") == false ) return false;' title="<?php esc_attr_e(__("You are about to DELETE the detailed info about recipients", "alo-easymail")) ?>">
					<?php _e("Delete the detailed report of recipients", "alo-easymail") ?></a> 
					<?php echo alo_em_help_tooltip( __("You are about to DELETE the detailed info about recipients", "alo-easymail").". " .__("This action deletes the detailed info about recipients (see below) and keeps only the summary (see above)", "alo-easymail"). ". " .__("It reduces the data in database tables and make the plugin queries and actions faster", "alo-easymail"). ". " ); ?>
				</div>
			<?php } // if ( get_post_meta 
			} // if ( !isset($_GET['isnewwin']) )  ?>		

<?php
// Table with clicked links
if ( isset($_GET['show_clicked']) ) : ?>

<a href="<?php echo wp_nonce_url(ALO_EM_PLUGIN_URL . '/alo-easymail_report.php?newsletter='.$newsletter.'&lang='.$lang, 'alo-easymail_report'); ?>" class="easymail-navbutton" style="margin-top:15px;display: inline-block;">&laquo; <?php _e("Back to list of recipients", "alo-easymail") ?></a>

		<table style="margin-top:15px;width:100%;font-family: sans-serif">
			<thead>
			<tr>
				<th scope="col"><?php _e("Requested URL", "alo-easymail") ?></th>
				<th scope="col"><?php _e("Visits", "alo-easymail") ?></th>
			</tr>
		</thead>

		<tbody>
		<?php
		// Get all clicked url, grouped by visits
		$urls = $wpdb->get_results ( $wpdb->prepare( "SELECT request, COUNT(*) as num_visits FROM {$wpdb->prefix}easymail_stats WHERE newsletter=%d AND request!='' GROUP BY request ORDER BY num_visits DESC", $newsletter ) );
		//echo "<pre>"; print_r($urls);echo "</pre>";

		if ( $urls ) {
			$class = "";
			$n = 0;
			foreach ( $urls as $url  ) {
				$class = ('' == $class) ? "style='background-color:#eee;'" : "";
				$n ++;
				echo "<tr $class ><td><a href=\"".$url->request."\" target=\"_blank\" title=\"". esc_attr( sprintf( __( 'Visit %s' ), esc_url($url->request) ) )."\">" . $url->request ."</a></td>";
				echo "<td><strong>" . $url->num_visits ."</strong></td>";			 
				echo "</tr>";
			}
		}
		?>
	</tbody></table>


<?php
// Table with recipients
elseif ( !alo_em_is_newsletter_recipients_archived ( $newsletter ) ) : 	?>			
				<?php 
				$tot_pages = @ceil( $tot_recipients / $per_page ); 
				if ( $tot_pages > 1 ) : ?>
				<!-- Pagination -->	
				<ul id="easymail_report_tabs" class="ui-tabs-nav">
					<?php for( $i=0; $i < $tot_pages; $i++ ) : 
						$to_offset = ( $i * $per_page ); 
						$active = ( $offset == $to_offset ) ? "ui-tabs-selected ui-state-active" : "";
						$atitle = __("Recipients", "alo-easymail").": ". ($to_offset+1) ." - ". ( ( $i < $tot_pages-1 ) ? $to_offset + $per_page : $tot_recipients ); ?>		
						<li class="ui-state-default ui-corner-top <?php echo $active ?>"><a href="<?php echo wp_nonce_url( ALO_EM_PLUGIN_URL . '/alo-easymail_report.php?newsletter='.$newsletter.'&lang='.$lang.'&offset='. $to_offset, 'alo-easymail_report') ; ?>" title="<?php echo $atitle ?>"><?php echo $to_offset+1 ?></a></li>			
					<?php endfor; ?>
				</ul>
				<?php endif; // if ( $tot_pages > 1 ) ?>
				
				<table style="margin-top:15px;width:100%;font-family: sans-serif">
					<thead>
					<tr>
						<th scope="col"></th>
						<th scope="col"><?php _e("E-mail", "alo-easymail") ?></th>
						<th scope="col"><?php _e("Name", "alo-easymail") ?></th>
						<th scope="col"><?php _e("Language", "alo-easymail") ?></th>
						<th scope="col"><?php _e("Sent", "alo-easymail") ?></th>
						<th scope="col"><?php _e("Viewed", "alo-easymail") ?></th>						
						<th scope="col"><?php _e("Clicks", "alo-easymail") ?></th>
					</tr>
				</thead>

				<tbody>
				<?php
				$class = "";
				$n = $offset;
				foreach ($recipients as $recipient) {
					$class = ('' == $class) ? "style='background-color:#eee;'" : "";
					$n ++;
					echo "<tr $class ><td>".$n."</td><td>".$recipient->email."</td><td>".$recipient->name."</td>";
					echo "<td class='center'>";
					if ( isset( $recipient->lang ) ) echo alo_em_get_lang_flag( $recipient->lang, 'name' ) ;
					echo "</td>";

					echo "<td class='center'>".( ( $recipient->result == "1" ) ? __("Yes", "alo-easymail" ) : __("No", "alo-easymail" ) )." <img src='".ALO_EM_PLUGIN_URL."/images/".( ( $recipient->result == "1" ) ? "yes.png":"no.png" ) ."' alt='". ( ( $recipient->result == "1" ) ? __("Yes", "alo-easymail" ) : __("No", "alo-easymail" ) ) ."' />";
					if ( $recipient->result == "-3" ) echo " <img src='".ALO_EM_PLUGIN_URL."/images/16-email-bounce.png' alt='". esc_attr( __("Bounced", "alo-easymail" ) ) ."' title='". esc_attr( __("Bounced", "alo-easymail" ) .': '. __("the message was rejected by recipient mail server", "alo-easymail" ) ) ."' />";
					echo "</td>";
					
					echo "<td class='center'>";
					echo ( ( $recipient->result == "1" && alo_em_recipient_is_tracked ( $recipient->ID, '' ) ) ? __("Yes", "alo-easymail" ) : __("No", "alo-easymail" ) )." <img src='".ALO_EM_PLUGIN_URL."/images/".( ( $recipient->result == "1" && alo_em_recipient_is_tracked ( $recipient->ID, '' ) )? "yes.png":"no.png" ) ."' />";
					if ( count( alo_em_get_recipient_trackings( $recipient->ID, '' ) ) > 1 ) echo " ". count( alo_em_get_recipient_trackings( $recipient->ID, '' ) );
					echo "</td>";

					echo "<td class='center'>";
					echo ( ( $recipient->result == "1" && alo_em_get_recipient_trackings_except_views ( $recipient->ID) ) ? __("Yes", "alo-easymail" ) : __("No", "alo-easymail" ) )." <img src='".ALO_EM_PLUGIN_URL."/images/".( ( $recipient->result == "1" && alo_em_get_recipient_trackings_except_views ( $recipient->ID) )? "yes.png":"no.png" ) ."' />";
					if ( count( alo_em_get_recipient_trackings_except_views( $recipient->ID ) ) > 1 ) echo " ". count( alo_em_get_recipient_trackings_except_views( $recipient->ID ) );
					echo "</td>";
					 
					echo "</tr>";
					//echo "<pre>"; print_r($recipient);echo "</pre>";
				}
				?>
			</tbody></table>

<?php endif; // if ( !alo_em_is_newsletter_recipients_archived ( $newsletter ) ) : ?>	
			
			</div>
			
		</div> <!-- end slider -->
		
		</body>
		</html>
	<?php } // end if $newsletter
} // edn if (isset($_REQUEST['id']) && (int)$_REQUEST['id'])
exit;
?>
