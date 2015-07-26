<?php if ( !defined('ABSPATH') ) die(); // If this file is called directly, abort.

/**
 * Functions about placeholders in newsletters
 *
 * @package WordPress
 * @subpackage ALO EasyMail plugin
 */


/**
 * Print table with tags summay
 */
function alo_em_newsletter_placeholders() {
	global $wp_version;
	$placeholders = array (
		"easymail_post" => array (
			"title" 		=> __( "Post tags", "alo-easymail" ),
			"tags" 			=> array (
				"[POST-TITLE]" 		=> __("The link to the title of the selected post.", "alo-easymail") ." ". __("This tag works also in the <strong>subject</strong>", "alo-easymail") . ". ". __("The visit to this url will be tracked.", "alo-easymail"),
				"[POST-EXCERPT]" 	=> __("The excerpt (if any) of the post.", "alo-easymail"). ( version_compare ( $wp_version , '3.3', '>=' ) ? " ". __("If it is empty, the beginning of post content will be used.", "alo-easymail") : "" ),
				"[POST-CONTENT]"	=> __("The main content of the post.", "alo-easymail")
			)
		),
		"easymail_subscriber" => array (
			"title" 		=> __( "Subscriber tags", "alo-easymail" ),
			"tags" 			=> array (
				"[USER-NAME]"		=> __("Name and surname of registered user.", "alo-easymail") . " (". __("For subscribers: the name used for registration", "alo-easymail") ."). ". __("This tag works also in the <strong>subject</strong>", "alo-easymail").".",
				"[USER-FIRST-NAME]"	=> __("First name of registered user.", "alo-easymail") . " (". __("For subscribers: the name used for registration", "alo-easymail") ."). ". __("This tag works also in the <strong>subject</strong>", "alo-easymail").".",
				"[USER-EMAIL]"	=> __("Email address of subscriber", "alo-easymail") . ". "
			)
		)
	);

	return apply_filters ( 'alo_easymail_newsletter_placeholders_table', $placeholders );
}


/**
 * Print table with tags summay
 */
function alo_em_tags_table ( $post_id ) {
	$placeholders = alo_em_newsletter_placeholders();

	if ( $placeholders ) :
		foreach ( $placeholders as $type => $placeholder ) :
			if ( isset( $placeholder['tags'] )) : ?>

				<table class="widefat" style="margin-top:10px">
					<thead><tr><th scope="col" style="width:20%"><?php esc_html_e ( $placeholder['title'] ) ?></th>
						<th scope="col"><?php do_action ( 'alo_easymail_newsletter_placeholders_title_'.$type, $post_id ); ?></th></tr>
					</thead>
					<tbody>

					<?php if ( !empty( $placeholder['tags'] ) ) : foreach ( $placeholder['tags'] as $tag => $desc ) : ?>
						<tr><td><?php esc_html_e ( $tag ) ?></td><td style='font-size:80%'>
								<span class="description"><?php echo $desc ?></span></td></tr>
					<?php endforeach; endif; // $placeholder['tags'] ?>

					</tbody></table>
				<?php
			endif;
		endforeach; // $placeholders

	endif; // if ( $placeholders ) ?>

	<?php
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
 * alo newsletter custom email hooks
 */
function alo_em_zirkuss_custom_easymail_placeholders( $placeholders ) {
	$warning_readonline = ( get_option('alo_em_publish_newsletters') == "no" ) ? " <strong>".__( 'This tag now does not work because the online publication of newsletters is disabled', 'alo-easymail' ).": ". __( 'you can set it up in settings', 'alo-easymail' )."</strong>" : "";

	$placeholders['easymail_subscriber']['tags']['[USER-UNSUBSCRIBE]'] = __ ( 'Text and URL to unsubscribe.', 'alo-easymail' ) . " (". __( 'You can customise this text in settings', 'alo-easymail' ) .".)";
	$placeholders['easymail_subscriber']['tags']['[USER-UNSUBSCRIBE-URL]'] = __ ( 'URL to unsubscribe.', 'alo-easymail' );

	$placeholders['easymail_newsletter']['title'] = __( "Newsletter tags", "alo-easymail" );
	$placeholders['easymail_newsletter']['tags']['[READ-ONLINE]'] = __ ( 'Text and URL to the online version.', 'alo-easymail' ) . " (". __( 'You can customise this text in settings', 'alo-easymail' ) ."). " . __('The visit to this url will be tracked.', 'alo-easymail'). " ".$warning_readonline;
	$placeholders['easymail_newsletter']['tags']['[READ-ONLINE-URL]'] = __ ( 'URL to the online version.', 'alo-easymail' ). " ". __('The visit to this url will be tracked.', 'alo-easymail'). " ". $warning_readonline;
	$placeholders['easymail_newsletter']['tags']['[TITLE]'] = __ ( 'Title of the newsletter.', 'alo-easymail' );
	$placeholders['easymail_newsletter']['tags']['[DATE]'] = __ ( 'Date of the newsletter.', 'alo-easymail' );
	if ( current_theme_supports( 'post-thumbnails' ) ) $placeholders['easymail_newsletter']['tags']['[THUMB]'] = __ ( 'Post Thumbnail of newsletter', 'alo-easymail' );
	$placeholders['easymail_newsletter']['tags']['[GALLERY]'] = __ ( 'Image gallery of newsletter', 'alo-easymail' );

	$placeholders['easymail_site']['title'] = __( "Site tags", "alo-easymail" );
	$placeholders['easymail_site']['tags']['[SITE-LINK]'] = __("The link to the site", "alo-easymail") .". ".__('The visit to this url will be tracked.', 'alo-easymail');
	$placeholders['easymail_site']['tags']['[SITE-URL]'] = __ ( 'URL to the site.', 'alo-easymail' ).". ".__('The visit to this url will be tracked.', 'alo-easymail');
	$placeholders['easymail_site']['tags']['[SITE-NAME]'] = __('Site Title');
	$placeholders['easymail_site']['tags']['[SITE-DESCRIPTION]'] = __('Tagline');

	if ( current_theme_supports( 'post-thumbnails' ) ) $placeholders['easymail_post']['tags']['[POST-THUMB]'] = __("Post Thumbnail", "alo-easymail");
	$placeholders['easymail_post']['tags']['[POST-GALLERY]'] = __("The image gallery of the post", "alo-easymail")	;

	return $placeholders;
}
add_filter ( 'alo_easymail_newsletter_placeholders_table', 'alo_em_zirkuss_custom_easymail_placeholders', 5 );


/**
 * alo newsletter content
 */
function alo_em_zirkuss_newsletter_content( $content, $newsletter, $recipient, $stop_recursive_the_content = false )
{
	if ( !is_object( $recipient ) ) $recipient = new stdClass();
	if ( empty( $recipient->lang ) ) $recipient->lang = alo_em_short_langcode ( get_locale() );

	// title
	$subject = stripslashes ( alo_em_translate_text ( $recipient->lang, $newsletter->post_title, $newsletter->ID, 'post_title' ) );
	$subject = apply_filters( 'alo_easymail_newsletter_title', $subject, $newsletter, $recipient );

	// use the email theme only when emailing the
	// newsletter. otherwise use the default
	// wordpress theme to display the newsletter.
	if ( isset( $recipient->ID ) )
	{
		// If newsletter publication online available, create the message to read the newsletter online
		if ( get_option('alo_em_publish_newsletters') == "no" ) {
			$viewonline_url = $viewonline_msg = $trackable_viewonline_url = "";
		} else {

			$viewonline_msg = alo_em_translate_option ( $recipient->lang, 'alo_em_custom_viewonline_msg', true );

			$viewonline_url = alo_em_translate_url ( $recipient->newsletter /*get_permalink( $recipient->newsletter )*/, $recipient->lang );
			$trackable_viewonline_url = alo_em_make_url_trackable ( $recipient, $viewonline_url );

			if( empty( $viewonline_msg ) )
			{
				$viewonline_msg = __('To read the newsletter online you can visit this link:', 'alo-easymail') . ' %NEWSLETTERLINK%';
			}

			$viewonline_msg = str_replace( '%NEWSLETTERLINK%', ' <a href="'.$trackable_viewonline_url/*$viewonline_url*/.'">'. $subject /*$viewonline_url*/ .'</a>', $viewonline_msg );
			$viewonline_msg = str_replace( '%NEWSLETTERURL%', $trackable_viewonline_url /*$viewonline_url*/, $viewonline_msg );
		}

		$unsubfooter = $uns_link = $tracking_view = ""; // default empty

		if ( isset( $recipient->unikey ) ) { // if subscriber
			$uns_vars = $recipient->subscriber . '|' . $recipient->unikey;
			$uns_vars = urlencode( base64_encode( $uns_vars ) );
			$uns_link = add_query_arg( 'emunsub', $uns_vars, alo_em_translate_home_url ( $recipient->lang ) /*trailingslashit( get_home_url() )*/ );
			//$uns_link = alo_em_translate_url ( $uns_link, $recipient->lang );

			$unsubfooter = alo_em_translate_option ( $recipient->lang, 'alo_em_custom_unsub_footer', true );

			if ( empty( $unsubfooter ) )
			{
				$unsubfooter = __('You have received this message because you subscribed to our newsletter. If you want to unsubscribe: ', 'alo-easymail').' %UNSUBSCRIBELINK%';
			}

			$unsubfooter = str_replace ( '%UNSUBSCRIBELINK%', ' <a href="'.$uns_link.'">'. $uns_link/*__('visit this link', 'alo-easymail')*/ .'</a>', $unsubfooter );
			$unsubfooter = str_replace ( '%UNSUBSCRIBEURL%', $uns_link, $unsubfooter );

			// Tracking code
			if ( get_option('alo_em_use_tracking_pixel') != "no" ) {
				$track_vars = $recipient->ID . '|' . $recipient->unikey;
				$track_vars = urlencode( base64_encode( $track_vars ) );
				$tracking_view = '<img src="'. ALO_EM_PLUGIN_URL .'/tr.php?v='. $track_vars .'" width="1" height="1" border="0" alt="" >';
			} else {
				$tracking_view = '';
			}
		}

		// Content default if not theme found
		$html = $content;

		// Get the theme file
		$default_theme = get_option('alo_em_use_themes');
		if ( $default_theme != 'no' ) {
			if ( $default_theme == "yes" ) { // Free choice
				$theme = get_post_meta ( $newsletter->ID, '_easymail_theme', true );
			} else { // Force theme by option
				$theme = $default_theme;
			}
			if ( $theme != "" && array_key_exists( $theme, alo_easymail_get_all_themes() ) ) {
				$themes = alo_easymail_get_all_themes();
				$theme_path = ( isset( $themes[$theme] ) && file_exists( $themes[$theme] ) ) ? $themes[$theme] : false;
				if ( $theme_path ) {
					//$html = file_get_contents( $theme_path ); // replaced by eqhes, for php themes
					ob_start();
					require( $theme_path );
					$html = ob_get_clean();

					$html = alo_em_translate_text ( $recipient->lang, $html ); // translate the text ih html theme
					$html = str_replace('[CONTENT]', $content, $html);
					$info = pathinfo( $theme );
					$theme_dir =  basename( $theme, '.' . $info['extension'] );
					//$html = str_replace( $theme_dir, alo_easymail_get_themes_url().$theme_dir, $html );
					$html = preg_replace( '/ src\=[\'|"]'. $theme_dir.'(.+?)[\'|"]/', ' src="'. alo_easymail_get_themes_url().$theme_dir. '$1"', $html ); // <img src="..." >
					$html = preg_replace( '/url(.+?)[\s|\'|"]'. $theme_dir.'(.+?)[\s|\'|"]/', "url('". alo_easymail_get_themes_url() .$theme_dir. "$2'", $html ); // in style: url("...")
					$html = preg_replace( '/ background\=[\'|"]'. $theme_dir.'(.+?)[\'|"]/', ' background="'. alo_easymail_get_themes_url().$theme_dir. '$1"', $html ); // <table background="..." >
				}
			}
		}
	}
	else
	{
		$viewonline_msg = $viewonline_url = $trackable_viewonline_url = ""; // nonsense: probably it's being read online...
		$unsubfooter = $uns_link = $tracking_view = ""; // unuseful

		// Get the content
		$html = $content;
	}


	// Create the image gallery
	$args = array( 'post_type' => 'attachment', 'post_mime_type' => array( 'image/jpeg', 'image/jpg', 'image/png', 'image/gif' ), 'numberposts' => -1, 'post_status' => null, 'post_parent' => $newsletter->ID, 'orderby' => 'menu_order', 'order' => 'ASC' );
	$attachments = get_posts( $args );
	$gallery = '';

	if ( $attachments ) {
		$size = ( $size = get_post_meta ( $newsletter->ID, '_placeholder_newsletter_imgsize', true ) ) ? $size : 'thumbnail';

		foreach( $attachments as $index => $attachment ) {
			$src = wp_get_attachment_image_src( $attachment->ID, $size );
			$gallery .= '<img class="alo-easymail-gallery-newsletter" src="' . $src[0] . '" width="' . $src[1] . '" height="' . $src[2] . '" border="0" alt="" />'."\n";
		}

		$gallery = apply_filters( 'alo_easymail_placeholder_newsletter_gallery', $gallery,  $attachments, $size, $newsletter->ID );
	}

	// post thumbnail
	$thumb = "";
	if ( current_theme_supports( 'post-thumbnails' ) ) {
		if ( has_post_thumbnail( $newsletter->ID ) ) {
			$size = ( $size = get_post_meta ( $newsletter->ID, '_placeholder_newsletter_imgsize', true ) ) ? $size : 'thumbnail';
			$thumb = get_the_post_thumbnail( $newsletter->ID, $size, array( 'class'	=> "alo-easymail-thumb-newsletter" ) );
			$thumb = apply_filters( 'alo_easymail_placeholder_newsletter_thumb', $thumb,  $size, $newsletter->ID );
		}
	}

	// post thumb and gallery
	$post_id = get_post_meta ( $newsletter->ID, '_placeholder_easymail_post', true );
	$post_thumb = $post_gallery = "";
	if ( $post_id ) {

		// Create the post gallery
		$args = array( 'post_type' => 'attachment', 'post_mime_type' => array( 'image/jpeg', 'image/jpg', 'image/png', 'image/gif' ), 'numberposts' => -1, 'post_status' => null, 'post_parent' => $post_id, 'orderby' => 'menu_order', 'order' => 'ASC' );
		$attachments = get_posts( $args );

		if ( $attachments ) {
			$size = ( $size = get_post_meta ( $post_id, '_placeholder_post_imgsize', true ) ) ? $size : 'thumbnail';

			foreach( $attachments as $index => $attachment ) {
				$src = wp_get_attachment_image_src( $post_id, $size );
				$post_gallery .= '<img class="alo-easymail-gallery-post" src="' . $src[0] . '" width="' . $src[1] . '" height="' . $src[2] . '" border="0" alt="" />'."\n";
			}

			$post_gallery = apply_filters( 'alo_easymail_placeholder_post_gallery', $post_gallery,  $attachments, $size, $post_id );
		}

		// post thumbnail
		if ( current_theme_supports( 'post-thumbnails' ) ) {
			if ( has_post_thumbnail( $post_id ) ) {
				$size = ( $size = get_post_meta ( $post_id, '_placeholder_post_imgsize', true ) ) ? $size : 'thumbnail';
				$post_thumb = get_the_post_thumbnail( $post_id, $size, array( 'class'	=> "alo-easymail-thumb-post" ) );
				$post_thumb = apply_filters( 'alo_easymail_placeholder_post_thumb', $post_thumb,  $size, $post_id );
			}
		}
	}

	// site
	$site_url = alo_em_translate_home_url ( $recipient->lang ); //get_option ('siteurl');
	$trackable_site_url = alo_em_make_url_trackable ( $recipient, $site_url );

	$blogname = esc_html( get_option('blogname') );
	$blogdescription = esc_html( get_option('blogdescription') );

	// newsletter
	$date = date_i18n( get_option('date_format')/*__( 'j / n / Y', "alo-easymail" )*/, strtotime( $newsletter->post_date ) );

	// replace all tags
	$html = str_replace('[READ-ONLINE]', $viewonline_msg, $html);
	$html = str_replace('[READ-ONLINE-URL]', $trackable_viewonline_url, $html);
	$html = str_replace('[USER-UNSUBSCRIBE]', $unsubfooter, $html);
	$html = str_replace('[USER-UNSUBSCRIBE-URL]', $uns_link, $html);
	$html = str_replace('[TITLE]', $subject, $html);
	$html = str_replace('[THUMB]', $thumb, $html);
	$html = str_replace('[GALLERY]', $gallery, $html);
	$html = str_replace('[SITE-URL]', $trackable_site_url, $html);
	$html = str_replace('[SITE-NAME]', $blogname, $html);
	$html = str_replace('[SITE-DESCRIPTION]', $blogdescription, $html);
	$html = str_replace('[DATE]', $date, $html);
	$html = str_replace('[POST-THUMB]', $post_thumb, $html);
	$html = str_replace('[POST-GALLERY]', $post_gallery, $html);

	// Insert tracking img before </body> if tag exists, otherwise at the end
	if ( strpos( $html, "</body") !== false ) {
		$html = str_replace( "</body", $tracking_view ."\n</body" , $html);
	} else {
		$html .= $tracking_view;
	}

	return $html;
}
add_filter ( 'alo_easymail_newsletter_content',  'alo_em_zirkuss_newsletter_content', 9, 4 );


/**
 * Add Img size in newsletter select in placeholders table
 *
 */
function alo_em_placeholders_title_newsletter_imgsize ( $post_id ) {
	echo __("Select the image size", "alo-easymail"). ": ";
	echo '<select name="placeholder_newsletter_imgsize" id="placeholder_newsletter_imgsize" >';
	$sizes = array( 'thumbnail', 'medium', 'large' );
	foreach ( $sizes as $size ) {
		$select_gallery_size = ( get_post_meta ( $post_id, '_placeholder_newsletter_imgsize', true) == $size ) ? 'selected="selected"': '';
		echo '<option value="'. $size .'" '. $select_gallery_size .'>'. $size . '</option>';
	}
	echo '</select>';
}
add_action('alo_easymail_newsletter_placeholders_title_easymail_newsletter', 'alo_em_placeholders_title_newsletter_imgsize', 12 );


/**
 * Add Img size in newsletter select in placeholders table
 *
 */
function alo_em_placeholders_title_post_imgsize ( $post_id ) {
	echo __("Select the image size", "alo-easymail"). ": ";
	echo '<select name="placeholder_post_imgsize" id="placeholder_post_imgsize" >';
	$sizes = array( 'thumbnail', 'medium', 'large' );
	foreach ( $sizes as $size ) {
		$select_gallery_size = ( get_post_meta ( $post_id, '_placeholder_post_imgsize', true) == $size ) ? 'selected="selected"': '';
		echo '<option value="'. $size .'" '. $select_gallery_size .'>'. $size . '</option>';
	}
	echo '</select>';
}
add_action('alo_easymail_newsletter_placeholders_title_easymail_post', 'alo_em_placeholders_title_post_imgsize', 12 );


/**
 * Save gallery size when the newsletter is saved
 */
function alo_em_save_placeholder_gallery ( $post_id ) {
	if ( isset( $_POST['placeholder_newsletter_imgsize'] ) ) {
		update_post_meta ( $post_id, '_placeholder_newsletter_imgsize', $_POST['placeholder_newsletter_imgsize'] );
	}
	if ( isset( $_POST['placeholder_post_imgsize'] ) ) {
		update_post_meta ( $post_id, '_placeholder_post_imgsize', $_POST['placeholder_post_imgsize'] );
	}
}
add_action('alo_easymail_save_newsletter_meta_extra', 'alo_em_save_placeholder_gallery' );


/**
 * Filter Newsletter Title when sending
 */
function alo_em_filter_title( $subject, $newsletter, $recipient ) {
	if ( !is_object( $recipient ) ) $recipient = new stdClass();
	if ( empty( $recipient->lang ) ) $recipient->lang = alo_em_short_langcode ( get_locale() );
	$post_id = get_post_meta ( $newsletter->ID, '_placeholder_easymail_post', true );
	$obj_post = ( $post_id ) ? get_post( $post_id ) : false;
	if ( $obj_post ) {
		$post_title = stripslashes ( alo_em_translate_text ( $recipient->lang, $obj_post->post_title, $post_id, 'post_title' ) );
		$subject = str_replace('[POST-TITLE]', $post_title, $subject);
	} else {
		$subject = str_replace('[POST-TITLE]', "", $subject);
	}
	if ( isset( $recipient ) && is_object( $recipient ) ) {
		if ( isset( $recipient->name ) ) {
			$subject = str_replace("[USER-NAME]", stripslashes ( $recipient->name ), $subject );
		} else {
			$subject = str_replace("[USER-NAME]", "", $subject );
		}
		if ( isset( $recipient->firstname ) ) {
			$subject = str_replace("[USER-FIRST-NAME]", stripslashes ( $recipient->firstname ), $subject );
		} else {
			$subject = str_replace("[USER-FIRST-NAME]", "", $subject );
		}
	}
	return $subject;
}
add_filter ( 'alo_easymail_newsletter_title',  'alo_em_filter_title', 10, 3 );


/**
 * Filter Newsletter Title when in title bar in site
 */
function alo_em_filter_title_bar( $subject ) {
	global $post;
	if ( get_post_type( $post->ID ) == 'newsletter' ) {
		$post_id = get_post_meta ( $post->ID, '_placeholder_easymail_post', true );
		$obj_post = ( $post_id ) ? get_post( $post_id ) : false;
		if ( $obj_post ) {
			$post_title = stripslashes ( alo_em_translate_text ( alo_em_get_language (), $obj_post->post_title, $post_id, 'post_title' ) );
			$subject = str_replace('[POST-TITLE]', $post_title, $subject);
		} else {
			$subject = str_replace('[POST-TITLE]', "", $subject);
		}
	}
	return $subject;
}
add_filter ( 'single_post_title',  'alo_em_filter_title_bar' );


/**
 * Filter Newsletter Title when viewed in site
 */
function alo_em_filter_title_in_site ( $subject ) {
	global $post, $pagenow;
	// in frontend and in 'edit.php' screen in backend
	if ( isset( $post ) && is_object( $post ) && ( !is_admin() || $pagenow == 'edit.php' ) ) {
		$post_id = get_post_meta ( $post->ID, '_placeholder_easymail_post', true );
		$obj_post = ( $post_id ) ? get_post( $post_id ) : false;
		if ( $obj_post ) {
			$post_title = stripslashes ( alo_em_translate_text ( false, $obj_post->post_title, $post_id, 'post_title' ) );
			$subject = str_replace('[POST-TITLE]', $post_title, $subject);
		} else {
			$subject = str_replace('[POST-TITLE]', "", $subject);
		}
	}
	return $subject;
}
add_filter ( 'the_title',  'alo_em_filter_title_in_site' );


/**
 * Filter Newsletter Content when sending
 */
function alo_em_filter_content ( $content, $newsletter, $recipient, $stop_recursive_the_content=false ) {
	global $wp_version;
	if ( !is_object( $recipient ) ) $recipient = new stdClass();
	if ( empty( $recipient->lang ) ) $recipient->lang = alo_em_short_langcode ( get_locale() );
	$post_id = get_post_meta ( $newsletter->ID, '_placeholder_easymail_post', true );
	$obj_post = ( $post_id ) ? get_post( $post_id ) : false;

	if ( $obj_post ) {
		$post_title = stripslashes ( alo_em_translate_text ( $recipient->lang, $obj_post->post_title, $post_id, 'post_title' ) );

		$post_link = alo_em_translate_url( $obj_post->ID, $recipient->lang );
		$trackable_post_link = alo_em_make_url_trackable ( $recipient, $post_link );

		$content = str_replace("[POST-TITLE]", "<a href='". $trackable_post_link /*esc_url ( alo_em_translate_url( $obj_post->ID, $recipient->lang ) )*/. "'>". $post_title ."</a>", $content);
	} else {
		$content = str_replace("[POST-TITLE]", "", $content);
	}

	if ( $obj_post ) {
		$postcontent =  stripslashes ( alo_em_translate_text ( $recipient->lang, $obj_post->post_content, $post_id, 'post_content' ) );
		if ( get_option('alo_em_filter_the_content') != "no" && !$stop_recursive_the_content ) $postcontent = apply_filters('the_content', $postcontent);
		$content = str_replace("[POST-CONTENT]", $postcontent, $content);

		// Get post excerpt: if not, uses trimmed post content (WP 3.3+)
		if ( !empty($obj_post->post_excerpt)) {
			$post_excerpt = stripslashes ( alo_em_translate_text ( $recipient->lang, $obj_post->post_excerpt, $post_id, 'post_excerpt' ) );
			$content = str_replace("[POST-EXCERPT]", $post_excerpt, $content);
		} else {
			if ( version_compare ( $wp_version, '3.3', '>=' ) ) {
				$content = str_replace("[POST-EXCERPT]", wp_trim_words( $postcontent, 50, ' [...]' ), $content);
			} else {
				$content = str_replace("[POST-EXCERPT]", "", $content);
			}
		}
	} else {
		$content = str_replace("[POST-CONTENT]", "", $content);
		$content = str_replace("[POST-EXCERPT]", "", $content);
	}
	/*
	if ( $obj_post && !empty($obj_post->post_excerpt)) {
		$post_excerpt = stripslashes ( alo_em_translate_text ( $recipient->lang, $obj_post->post_excerpt, $post_id, 'post_excerpt' ) );
	    $content = str_replace("[POST-EXCERPT]", $post_excerpt, $content);
	} else {
	    $content = str_replace("[POST-EXCERPT]", "", $content);
	}
	*/
	if ( $recipient ) {
		if ( isset( $recipient->name ) ) {
			$content = str_replace("[USER-NAME]", stripslashes ( $recipient->name ), $content);
		} else {
			$content = str_replace("[USER-NAME]", "", $content);
		}
		if ( isset( $recipient->firstname ) ) {
			$content = str_replace("[USER-FIRST-NAME]", stripslashes ( $recipient->firstname ), $content);
		} else {
			$content = str_replace("[USER-FIRST-NAME]", "", $content);
		}
		if ( isset( $recipient->email ) ) {
			$content = str_replace("[USER-EMAIL]", stripslashes ( $recipient->email ), $content);
		} else {
			$content = str_replace("[USER-EMAIL]", "", $content);
		}
	}

	$home_url = alo_em_translate_home_url ( $recipient->lang );
	$trackable_home_url = alo_em_make_url_trackable ( $recipient, $home_url );

	$content = str_replace("[SITE-LINK]", "<a href='". $trackable_home_url /*esc_url ( alo_em_translate_home_url ( $recipient->lang ) )*/ ."'>". esc_html( get_option('blogname') )."</a>", $content);

	return $content;
}
add_filter ( 'alo_easymail_newsletter_content',  'alo_em_filter_content', 10, 4 );


/*******************************************************************************
 *
 * Custom link placeholder: [CUSTOM-LINK]
 *
 * The following set of functions adds a new placeholder that includes a link
 * (to a post or to an external url) inside newsletter
 *
 * @since: 2.0
 *
 ******************************************************************************/

function alo_em_customlink_placeholder ( $placeholders ) {
	$placeholders["easymail_customlink"] = array (
		"title" 		=> __("Custom links", "alo-easymail"),
		"tags" 			=> array (
			"[CUSTOM-LINK]"	=> 	__("This placeholder produces a link (html &lt;a&gt; tag) and has the following parameters", "alo-easymail"). ":". "<ul style='margin-left: 2em;font-size: 90%'>".
				"<li><code style='font-style:normal;font-weight: bold'>". "href". "</code> ".
				__("the ID of a post or a full web address", "alo-easymail"). " (". __("mandatory", "alo-easymail") . ")</li>".
				"<li><code style='font-style:normal;'>". "title". "</code> ".
				__("the text of the link", "alo-easymail").". ". __("Default", "alo-easymail") .": " . __("the title of the post (if &#39;href&#39; is a post ID) or the &#39;href&#39; itself", "alo-easymail") . "</li>".
				"<li><code style='font-style:normal;'>". "tracking". "</code> ".
				__("the click on the link by the recipient will be tracked (1) or not (0)", "alo-easymail"). ". " .__("Default", "alo-easymail") . ": 1 (". __("Yes", "alo-easymail") . ")</li>".
				"<li><code style='font-style:normal;'>". "class". "</code> ".
				__("the class tag attribute", "alo-easymail"). ". " .__("Default", "alo-easymail") .": &#39;alo-easymail-link&#39;" . "</li>".
				"<li><code style='font-style:normal;'>". "style". "</code> ".
				__("the style tag attribute", "alo-easymail"). "</li>".
				"</ul>" .
				__("Sample:", "alo-easymail") . " ". __("a link to blog post with ID 1, with custom css style, without tracking", "alo-easymail") . ": <br />" .
				"<code style='font-style:normal;'>". "[CUSTOM-LINK href=1 style=\"color: #f00\" tracking=0]". "</code>" . "<br />" .
				__("Sample:", "alo-easymail") . " ". __("a link to Wordress.org, with custom title", "alo-easymail") . ": <br />" .
				"<code style='font-style:normal;'>". "[CUSTOM-LINK href=\"http://www.wordpress.org\" title=\"visit WordPress site\"]". "</code>"
		)
	);
	return $placeholders;
}
add_filter ( 'alo_easymail_newsletter_placeholders_table', 'alo_em_customlink_placeholder' );


function custom_easymail_placeholders_title_easymail_customlink ( $post_id ) {
	echo __("You can insert customised links to blog posts or external web addresses", "alo-easymail"). '.';
}
add_action('alo_easymail_newsletter_placeholders_title_easymail_customlink', 'custom_easymail_placeholders_title_easymail_customlink' );


function alo_em_placeholders_replace_customlink_tag ( $content, $newsletter, $recipient, $stop_recursive_the_content=false ) {
	if ( !is_object( $recipient ) ) $recipient = new stdClass();
	if ( empty( $recipient->lang ) ) $recipient->lang = alo_em_short_langcode ( get_locale() );

	if ( preg_match_all('/\[CUSTOM-LINK(.*)\]/i', $content, $matches, PREG_SET_ORDER)) {

		if ( is_array($matches) ) : foreach($matches as $match) :

			// Complete palceholder
			$found = $match[0];

			// Placeholder attributes
			$atts =  shortcode_parse_atts( trim($match[1]) );

			$params = shortcode_atts( array(
				'href' 		=> '',
				'title' 	=> '',
				'tracking' 	=> 1,
				'class'		=> 'alo-easymail-link',
				'style'		=> '',
			), $atts );

			if ( empty($params['href']) ) continue; // skip if 'href' is empty

			// Numeric = post ID
			if ( is_numeric( $params['href'] ) )
			{
				if ( $obj_post = get_post( $params['href'] ) )
				{
					$title = !empty($params['title']) ? stripslashes ( $params['title'] ) : stripslashes ( alo_em_translate_text ( $recipient->lang, $obj_post->post_title, $obj_post->ID, 'post_title' ) );
					$link = alo_em_translate_url( $obj_post->ID, $recipient->lang );
				}
			}
			else
				// Otherwise = url
			{
				$title = !empty($params['title']) ? stripslashes ( $params['title'] ) : esc_url( $params['href'] );
				$link = esc_url( $params['href'] );
			}

			if ( $params['tracking'] == 1 )
			{
				$link = alo_em_make_url_trackable ( $recipient, $link );
			}

			$content = str_replace( $found, '<a href="'. $link . '" class="'. esc_attr($params['class']) . '" style="'. esc_attr($params['style']) . '">'. $title .'</a>', $content );

		endforeach; endif;
	}

	return $content;
}
add_filter ( 'alo_easymail_newsletter_content',  'alo_em_placeholders_replace_customlink_tag', 10, 4 );


/*******************************************************************************
 *
 * Placeholders of custom fields: [USER-{cf-name}]
 *
 * The following set of functions adds a new placeholder for each custom field
 *
 * @since: 2.0
 *
 ******************************************************************************/

function alo_em_cf_placeholders ( $placeholders ) {

	$alo_em_cf = alo_easymail_get_custom_fields();

	if( $alo_em_cf ) {
		foreach( $alo_em_cf as $key => $value ){
			$placeholders['easymail_subscriber']['tags']['[USER-'.strtoupper($key).']'] = __('Subscriber custom field',"alo-easymail").': '. __($value['humans_name'],"alo-easymail");
		}
	}

	return $placeholders;
}
add_filter ( 'alo_easymail_newsletter_placeholders_table', 'alo_em_cf_placeholders' );


function alo_em_cf_placeholders_replace_tags ( $content, $newsletter, $recipient, $stop_recursive_the_content=false ) {
	if ( !is_object( $recipient ) ) $recipient = new stdClass();
	if ( empty( $recipient->lang ) ) $recipient->lang = alo_em_short_langcode ( get_locale() );

	$alo_em_cf = alo_easymail_get_custom_fields();

	if( $alo_em_cf ) {
		foreach( $alo_em_cf as $key => $value ){
			$content = str_replace('[USER-'.strtoupper($key).']', alo_easymail_custom_field_html ( $key, $value, $key, $recipient->{$key}, false ), $content);
		}
	}

	return $content;
}
add_filter ( 'alo_easymail_newsletter_content',  'alo_em_cf_placeholders_replace_tags', 10, 4 );



/*******************************************************************************
 *
 * Latest posts placeholder: [LATEST-POSTS]
 *
 * The following set of functions adds a new placeholder that includes the latest
 * published posts inside newsletter
 *
 * @since: 2.0
 *
 ******************************************************************************/


/**
 * Add placeholder to table in new/edit newsletter screen
 *
 */
function alo_em_latest_posts_placeholders ( $placeholders ) {
	$placeholders["custom_latest"] = array (
		"title" 		=> __("Latest posts", "alo-easymail"),
		"tags" 			=> array (
			"[LATEST-POSTS]"		=> __("A list with the latest published posts", "alo-easymail").". ".__("The visit to this url will be tracked.", "alo-easymail")
		)
	);
	return $placeholders;
}
add_filter ( 'alo_easymail_newsletter_placeholders_table', 'alo_em_latest_posts_placeholders' );


/**
 * Add selects in placeholders table
 *
 * Note that the hook name is based upon the name of placeholder given in previous function as index:
 * alo_easymail_newsletter_placeholders_title_{your_placeholder}
 * If placeholder is 'my_archive' the hook will be:
 * alo_easymail_newsletter_placeholders_title_my_archive
 *
 */
function alo_em_placeholders_title_custom_latest ( $post_id ) {
	echo __("Select how many posts", "alo-easymail"). ": ";
	echo '<select name="placeholder_custom_latest" id="placeholder_custom_latest" >';
	for ( $i = 3; $i <= 10; $i++ ) {
		$select_custom_latest = ( get_post_meta ( $post_id, '_placeholder_custom_latest', true) == $i ) ? 'selected="selected"': '';
		echo '<option value="'.$i.'" '. $select_custom_latest .'>'. $i. '</option>';
	}
	echo '</select><br />';

	$cat_args = array(
		'show_option_all' 	=> 	esc_html( '(no, all categories)' ),
		'name' 				=>	'placeholder_custom_latest_cat'
	);
	if ( $select_custom_latest_cat = get_post_meta ( $post_id, '_placeholder_custom_latest_cat', true ) ) {
		$cat_args['selected'] =  (int)$select_custom_latest_cat;
	}
	echo __("Filter by category", "alo-easymail"). ": ";
	wp_dropdown_categories( $cat_args );
}
add_action('alo_easymail_newsletter_placeholders_title_custom_latest', 'alo_em_placeholders_title_custom_latest' );


/**
 * Save latest post number when the newsletter is saved
 */
function alo_em_save_placeholder_custom_latest ( $post_id ) {
	if ( isset( $_POST['placeholder_custom_latest'] ) && is_numeric( $_POST['placeholder_custom_latest'] ) ) {
		update_post_meta ( $post_id, '_placeholder_custom_latest', $_POST['placeholder_custom_latest'] );
	}
	if ( isset( $_POST['placeholder_custom_latest_cat'] ) && is_numeric( $_POST['placeholder_custom_latest_cat'] ) ) {
		update_post_meta ( $post_id, '_placeholder_custom_latest_cat', $_POST['placeholder_custom_latest_cat'] );
	}
}
add_action('alo_easymail_save_newsletter_meta_extra', 'alo_em_save_placeholder_custom_latest' );


/**
 * Replace the placeholder when the newsletter is sending
 * @param	str		the newsletter text
 * @param	obj		newsletter object, with all post values
 * @param	obj		recipient object, with following properties: ID (int), newsletter (int: recipient ID), email (str), result (int: 1 if successfully sent or 0 if not), lang (str: 2 chars), unikey (str), name (str: subscriber name), user_id (int/false: user ID if registered user exists), subscriber (int: subscriber ID), firstname (str: firstname if registered user exists, otherwise subscriber name)
 * @param	bol    	if apply "the_content" filters: useful to avoid recursive and infinite loop
 */
function alo_em_placeholders_get_latest ( $content, $newsletter, $recipient, $stop_recursive_the_content=false ) {
	if ( !is_object( $recipient ) ) $recipient = new stdClass();
	if ( empty( $recipient->lang ) ) $recipient->lang = alo_em_short_langcode ( get_locale() );
	$limit = get_post_meta ( $newsletter->ID, '_placeholder_custom_latest', true );
	$categ = get_post_meta ( $newsletter->ID, '_placeholder_custom_latest_cat', true );
	$latest = "";
	if ( $limit ) {
		$args = array( 'numberposts' => $limit, 'order' => 'DESC', 'orderby' => 'date' );
		if ( (int)$categ > 0 ) $args['category'] = $categ;
		$myposts = get_posts( $args );
		if ( $myposts ) :
			$latest .= "<ul>\r\n";
			foreach( $myposts as $post ) :	// setup_postdata( $post );
				$post_title = stripslashes ( alo_em_translate_text ( $recipient->lang, $post->post_title, $post->ID, 'post_title' ) );

				$post_link = alo_em_translate_url( $post->ID, $recipient->lang );
				$trackable_post_link = alo_em_make_url_trackable ( $recipient, $post_link );

				$latest .= "<li><a href='". $trackable_post_link . "'>". $post_title ."</a></li>\r\n";
			endforeach;
			$latest .= "</ul>\r\n";
		endif;
	}
	$content = str_replace("[LATEST-POSTS]", $latest, $content);

	return $content;
}
add_filter ( 'alo_easymail_newsletter_content',  'alo_em_placeholders_get_latest', 10, 4 );



/* EOF */