<?php


/*******************************************************************************
 * 
 * Custom hooks introducing placeholders for multiple posts in newsletter content:
 *  [POST-TITLE-n], [POST-EXCERPT-n] ...
 *  [SECONDARY-CONTENT]
 *  [ACTION-LINK]
 *
 * @author  Wojtek Szałkiewicz
 * @email   wojtek@szalkiewicz.pl
 * @since: 2.5
 *
 ******************************************************************************/

/**
 * Add javascript on Admin panel
 */
function alo_em_mod_add_admin_script () {
	global $pagenow;
	$post_type = isset($_GET['post_type']) ? $_GET['post_type'] : 'post';
	if ( $pagenow == "post.php" || ($pagenow == "post-new.php" && $post_type == 'newsletter')) {
		wp_enqueue_script( 'alo-easymail-backend-mod', ALO_EM_PLUGIN_URL . '/inc/alo-easymail-backend-mod.js', array( 'jquery' ) );

	}
}
add_action('admin_print_scripts', 'alo_em_mod_add_admin_script' );

function alo_em_mod_placeholders_table($placeholders){
	if(isset($placeholders['easymail_post'])){
		unset($placeholders['easymail_post']);
	}

	return $placeholders;
}
add_filter('alo_easymail_newsletter_placeholders_table', 'alo_em_mod_placeholders_table');

/**
 * Boxes meta in Newsletter edit/new pages
 */
function alo_em_mod_newsletter_add_custom_box() {
    add_meta_box( "alo_easymail_newsletter_second_content", __("Secondary Content", "alo-easymail"), "alo_em_mod_second_content_field", "newsletter", "normal", "high" );
    add_meta_box( "alo_easymail_newsletter_post_placeholders", __("Post Placeholders", "alo-easymail"), "alo_em_mod_meta_placeholders", "newsletter", "normal", "high" );
    add_meta_box( "alo_easymail_newsletter_post_link", __("Action Link", "alo-easymail"), "alo_em_mod_meta_action_link", "newsletter", "normal", "high" );
}
add_action('add_meta_boxes', 'alo_em_mod_newsletter_add_custom_box');

function alo_em_mod_meta_placeholders ( $post ) { 
	wp_nonce_field( ALO_EM_PLUGIN_DIR, "edit_newsletter_mod" );

	$placeholders = get_post_meta($post->ID, '_placeholder_easymail_mod_posts', true);
	$count = get_post_meta($post->ID, '_placeholder_easymail_mod_posts_count', true);
	$real_count = count($placeholders);
	if($count != $real_count){
		update_post_meta($post->ID, '_placeholder_easymail_mod_posts_count', $real_count);
	}

	if(empty($placeholders)){
		$placeholders[] = 0;
	}

	$tags = alo_em_mod_get_placeholder_tags();

	foreach ( $placeholders as $key => $post_id ) : $key = $key + 1?>
		
		<table class="widefat" style="margin-top:10px">
		<thead><tr><th scope="col" style="width:20%"><?php esc_html_e ( __( "Post tags", "alo-easymail" ) ) ?></th>
		<th scope="col"><?php if($key > 1 && $key == $real_count) : ?>
			<a href="#" class="easymail-mod-remove-post-placeholder button-secondary" style="float:right; color:red;"><?php _e( "remove", "alo-easymail" ) ?></a><span class="spinner" style="float:right;margin-top:4px"></span><?php endif; ?>
			<?php do_action ( 'alo_easymail_newsletter_placeholders_title_easymail_mod_post', $post->ID, $post_id, $key ); ?>
		</th></tr>
		</thead>
		<tbody>
		
		<?php if ( !empty( $tags ) ) : foreach ( $tags as $tag => $desc ) : ?>
			<tr><td><?php esc_html_e ( sprintf($tag, $key) ) ?></td><td style='font-size:80%'>
			<span class="description"><?php echo $desc ?></span></td></tr>
		<?php endforeach; endif; // $placeholder['tags'] ?>
			
		</tbody></table>
	<?php endforeach; // $placeholders ?>

		<a href="#" id="easymail_mod_add_post" class="button-secondary" style="float:right; margin-top:10px;"><?php _e('Add Post', 'alo-easymail'); ?></a><span class="spinner" style="float:right;margin-top:14px"></span>
		<div style="clear:both;"></div>
	<?php
}

function alo_em_mod_get_placeholder_tags(){
	global $wp_version;
	return apply_filters('alo_easymail_mod_newsletter_post_placeholders_tags', array (
		"[POST-TITLE-%d]" 		=> __("The link to the title of the selected post.", "alo-easymail") ." ". __("This tag works also in the <strong>subject</strong>", "alo-easymail") . ". ". __("The visit to this url will be tracked.", "alo-easymail"),
		"[POST-EXCERPT-%d]" 	=> __("The excerpt (if any) of the post.", "alo-easymail"). ( version_compare ( $wp_version , '3.3', '>=' ) ? " ". __("If it is empty, the beginning of post content will be used.", "alo-easymail") : "" ),
		"[POST-CONTENT-%d]"	=> __("The main content of the post.", "alo-easymail")						
	));
}

function alo_em_mod_meta_action_link($post){
	$link = get_post_meta($post->ID, '_alo_em_action_link', true);
	?>
		<p class="description"><?php _e('use this link in your theme or content as [ACTION-LINK]', 'alo-easymail'); ?></p>
		<input type="text" style="width:100%;" name="alo_action_link" id="alo_action_link" value="<?php echo $link; ?>" />
	<?php
}

function alo_em_mod_add_post_placeholder_tags($tags){

	if ( current_theme_supports( 'post-thumbnails' ) ) $tags['[POST-THUMB-%d]'] = __("Post Thumbnail", "alo-easymail");
	$tags['[POST-GALLERY-%d]'] = __("The image gallery of the post", "alo-easymail");

	return $tags;
}
add_filter('alo_easymail_mod_newsletter_post_placeholders_tags', 'alo_em_mod_add_post_placeholder_tags');

/**
 * Add post select in Placeholders table
 */
function alo_em_mod_placeholders_title_easymail_post ( $newsletter_id, $post_id ) {
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
		echo '<select name="placeholder_easymail_mod_posts[]" id="placeholder_easymail_post_'.$post_id.'" >';	
		foreach($get_posts as $post) :
		    $select_post_selected = ( $post_id == $post->ID ) ? 'selected="selected"': '';
		    echo '<option value="'.$post->ID.'" '. $select_post_selected .'>['. date_i18n( __( 'j M Y', "alo-easymail" ), strtotime( $post->post_date ) ) .'] '. get_the_title( $post->ID ).' </option>';
		endforeach;
		echo '</select><br />'; 
	} else {
		echo "<span class='easymail-txtwarning'>" . esc_html( __("There are no posts", "alo-easymail") ) . "!</span> <br />";
	}
}
add_action('alo_easymail_newsletter_placeholders_title_easymail_mod_post',  'alo_em_mod_placeholders_title_easymail_post', 10, 2 );

/**
 * Add Img size in newsletter select in placeholders table
 *
 */
function alo_em_mod_placeholders_title_post_imgsize ( $newsletter_id, $post_id ) {
	echo __("Select the image size", "alo-easymail"). ": ";
	echo '<select name="placeholder_post_imgsizes[]" id="placeholder_post_imgsize_'.$post_id.'" >';
	$sizes = array( 'thumbnail', 'medium', 'large' );
	foreach ( $sizes as $size ) {
	    $select_gallery_size = ( get_post_meta ( $post_id, '_placeholder_mod_post_imgsize', true) == $size ) ? 'selected="selected"': '';
	    echo '<option value="'. $size .'" '. $select_gallery_size .'>'. $size . '</option>';
	}
	echo '</select>'; 
}
add_action('alo_easymail_newsletter_placeholders_title_easymail_mod_post', 'alo_em_mod_placeholders_title_post_imgsize', 12, 2 );

/**
 * Save Post select in Placeholder Box meta in Newsletter 
 */
function alo_em_mod_save_newsletter_placeholders_easymail_post ( $post_id ) {
	if ( isset( $_POST['placeholder_easymail_mod_posts'] ) && is_array( $_POST['placeholder_easymail_mod_posts'] ) ) {
		update_post_meta ( $post_id, '_placeholder_easymail_mod_posts', $_POST['placeholder_easymail_mod_posts'] );
		foreach($_POST['placeholder_easymail_mod_posts'] as $key => $id){
			if(isset($_POST['placeholder_post_imgsizes'][$key])){
				update_post_meta($id, '_placeholder_mod_post_imgsize', $_POST['placeholder_post_imgsizes'][$key]);	
			}
		}
	}

	if(isset($_POST['alo_secondary_content'])){
		$content = sanitize_post_field('secondary_content', $_POST['alo_secondary_content'], $post_id, 'display');

		update_post_meta($post_id, '_alo_em_secondary_content', $content);
	}

	if(isset($_POST['alo_action_link'])){
		update_post_meta($post_id, '_alo_em_action_link', $_POST['alo_action_link']);
	}
} 
add_action('alo_easymail_save_newsletter_meta_extra',  'alo_em_mod_save_newsletter_placeholders_easymail_post' );

function alo_em_mod_add_post_placeholder(){
	if(!isset($_REQUEST['newsletter_id'])) exit;

	$newsletter_id = (int) $_REQUEST['newsletter_id'];

	$count = get_post_meta($newsletter_id, '_placeholder_easymail_mod_posts_count', true);
	if(!is_numeric($count)) $count = 1;
	$key = $count + 1;
	update_post_meta($newsletter_id, '_placeholder_easymail_mod_posts_count', $key);

	$tags = alo_em_mod_get_placeholder_tags();
	?>

	<table class="widefat" style="margin-top:10px">
		<thead><tr><th scope="col" style="width:20%"><?php esc_html_e ( __( "Post tags", "alo-easymail" ) ) ?></th>
		<th scope="col">
			<a href="#" class="easymail-mod-remove-post-placeholder button-secondary" style="float:right; color:red;"><?php _e( "remove", "alo-easymail" ) ?></a><span class="spinner" style="float:right;margin-top:4px"></span>
			<?php do_action ( 'alo_easymail_newsletter_placeholders_title_easymail_mod_post', $newsletter_id, 0, $key ); ?>
		</th></tr>
		</thead>
		<tbody>
		
		<?php if ( !empty( $tags ) ) : foreach ( $tags as $tag => $desc ) : ?>
			<tr><td><?php esc_html_e ( sprintf($tag, $key) ) ?></td><td style='font-size:80%'>
			<span class="description"><?php echo $desc ?></span></td></tr>
		<?php endforeach; endif; // $placeholder['tags'] ?>
			
		</tbody></table>
	<?php
	exit;
}
add_action('wp_ajax_alo_em_mod_add_post_placeholder', 'alo_em_mod_add_post_placeholder');

function alo_em_mod_remove_post_placeholder(){
	if(!isset($_REQUEST['newsletter_id'])) exit;

	$newsletter_id = (int) $_REQUEST['newsletter_id'];

	$count = (int) get_post_meta($newsletter_id, '_placeholder_easymail_mod_posts_count', true);
	$count--;
	update_post_meta($newsletter_id, '_placeholder_easymail_mod_posts_count', $count);

	exit;
}
add_action('wp_ajax_alo_em_mod_remove_post_placeholder', 'alo_em_mod_remove_post_placeholder');

function alo_em_mod_filter_content($content, $newsletter, $recipient, $stop_recursive_the_content = false, $secondary = false){
	global $wp_version;
	$posts = get_post_meta($newsletter->ID, '_placeholder_easymail_mod_posts', true);

	if(!is_object($recipient)) $recipient = new stdClass();
	if ( empty( $recipient->lang ) ) $recipient->lang = alo_em_short_langcode ( get_locale() );

	if(isset( $recipient->ID ) && $secondary === false){
		// the template is loaded
		$secondary_content = get_post_meta($newsletter->ID, '_alo_em_secondary_content', true);

		// general filters and shortcodes applied to 'the_content'?
		if ( get_option('alo_em_filter_the_content') != "no" ) {
			add_filter ( 'the_content', 'do_shortcode', 11 );
			$secondary_content = apply_filters( "the_content", $secondary_content );
		}

		$is_secondary_content = true;
		$new_recipient = clone $recipient;
		unset($new_recipient->ID);
		$secondary_content = apply_filters('alo_easymail_newsletter_content', $secondary_content, $newsletter, $new_recipient, false, true);

		$content = str_replace('[SECONDARY-CONTENT]', $secondary_content, $content);
	}

	if(is_array($posts) && !empty($posts)){
		foreach($posts as $key => $post_id){
			$post = get_post($post_id);
			$key = $key + 1;
			if($post){
				// Title
				$post_title = stripslashes ( alo_em_translate_text ( $recipient->lang, $post->post_title, $post->ID, 'post_title' ) );
				$post_link = alo_em_translate_url( $post->ID, $recipient->lang );
				$trackable_post_link = alo_em_make_url_trackable ( $recipient, $post_link );

				// Content
				$postcontent =  stripslashes ( alo_em_translate_text ( $recipient->lang, $post->post_content, $post->ID, 'post_content' ) );
				if ( get_option('alo_em_filter_the_content') != "no" && !$stop_recursive_the_content )
					$postcontent = apply_filters('the_content', $postcontent);

				// Excerpt

				// Get post excerpt: if not, uses trimmed post content (WP 3.3+)
			    if ( !empty($obj_post->post_excerpt)) {
					$post_excerpt = stripslashes ( alo_em_translate_text ( $recipient->lang, $obj_post->post_excerpt, $post->ID, 'post_excerpt' ) );
				} else {
					if ( version_compare ( $wp_version, '3.3', '>=' ) ) {
						$post_excerpt = wp_trim_words( $postcontent, 50, ' [...]' );
					} else {
						$post_excerpt = '';
					}
				}

				// Blank Thumb and Gallery
				$post_thumb = $post_gallery = "";

				// Post Thumb
				if ( current_theme_supports( 'post-thumbnails' ) && has_post_thumbnail( $post->ID )) {
					$size = ( $size = get_post_meta ( $post->ID, '_placeholder_mod_post_imgsize', true ) ) ? $size : 'thumbnail';
					$post_thumb = get_the_post_thumbnail( $post->ID, $size, array( 'class'	=> "alo-easymail-thumb-post" ) );
					$post_thumb = apply_filters( 'alo_easymail_placeholder_post_thumb', $post_thumb,  $size, $post->ID );
				}

				// Post Gallery
				// Create the post gallery
				$args = array( 'post_type' => 'attachment', 'post_mime_type' => array( 'image/jpeg', 'image/jpg', 'image/png', 'image/gif' ), 'numberposts' => -1, 'post_parent' => $post->ID, 'orderby' => 'menu_order', 'order' => 'ASC' ); 
				$attachments = get_posts( $args );

				if ( $attachments ) {
					$size = ( $size = sprintf(get_post_meta ( $post->ID, '_placeholder_mod_post_imgsize', true ), $key) ) ? $size : 'thumbnail';						
					foreach( $attachments as $index => $attachment ) {
						$src = wp_get_attachment_image_src( $attachment->ID, $size );
						$post_gallery .= '<img class="alo-easymail-gallery-post" src="' . $src[0] . '" width="' . $src[1] . '" height="' . $src[2] . '" border="0" />'."\n";
					}

					$post_gallery = apply_filters( 'alo_easymail_placeholder_post_gallery', $post_gallery,  $attachments, $size, $post->ID );
				}

				$replacements = array(
					// Title
					"<a href='". $trackable_post_link /*esc_url ( alo_em_translate_url( $obj_post->ID, $recipient->lang ) )*/. "'>". $post_title ."</a>",
					// Content
					$postcontent,
					// Excerpt
					$post_excerpt,
					// Post Thumb
					$post_thumb,
					// Post Gallery
					$post_gallery
				);
			}
			else {
				$replacements = '';
			}

			$tags = array(
				'[POST-TITLE-'.$key.']',
				'[POST-CONTENT-'.$key.']',
				'[POST-EXCERPT-'.$key.']',
				'[POST-THUMB-'.$key.']',
				'[POST-GALLERY-'.$key.']',
			);

			$content = str_replace($tags, $replacements, $content);
		}

		$action_link = get_post_meta($newsletter->ID, '_alo_em_action_link', true);
		$content = str_replace('[ACTION-LINK]', $action_link, $content);
	}

	return $content;
}
add_filter ( 'alo_easymail_newsletter_content',  'alo_em_mod_filter_content', 12, 4 );

/**
 * Filter Newsletter Title when sending
 */
function alo_em_mod_filter_title( $subject, $newsletter, $recipient = null) {
	$posts = get_post_meta($newsletter->ID, '_placeholder_easymail_mod_posts', true);

	$lang = empty($recipient) ? alo_em_get_language() : $recipient->lang;

	if(is_array($posts) && !empty($posts)){
		foreach($posts as $key => $post_id){
			$obj_post = ( $post_id ) ? get_post( $post_id ) : false;
			$key = $key + 1;

			if($obj_post){
				$post_title = stripslashes ( $obj_post->post_title );
			}
			else $post_title = '';

		    $subject = str_replace('[POST-TITLE-'.$key.']', $post_title, $subject);
		}
	}

	return $subject;
}
add_filter ( 'alo_easymail_newsletter_title',  'alo_em_mod_filter_title', 11, 3 );

/**
 * Filter Newsletter Title when in title bar in site
 */
function alo_em_mod_filter_title_bar($title){
//	global $post;
//	return alo_em_mod_filter_title($title, $post);
	return $title;
}
add_filter ( 'single_post_title',  'alo_em_mod_filter_title_bar', 11 );

/**
 * Filter Newsletter Title when viewed in site
 */
function alo_em_mod_filter_title_in_site($title){
	global $post, $pagenow;
	// in frontend and in 'edit.php' screen in backend
	if ( isset( $post ) && is_object( $post ) && ( !is_admin() || $pagenow == 'edit.php' ) ) {
		$title = alo_em_mod_filter_title($title, $post);
	}
	return $title;
}
add_filter ( 'the_title',  'alo_em_mod_filter_title_in_site', 11 );

function alo_em_mod_second_content_field($post){
	$content = get_post_meta($post->ID, '_alo_em_secondary_content', true);
	?>
	<?php echo sprintf( __("Use as %s in your theme","alo-easymail"), '<code style="font-style:normal;">[SECONDARY-CONTENT]</code>' ) ?>
	<div id="alo_secondary_content_container" class="wp-editor-container">
		<textarea id="alo_em_mod_secondary_content" name="alo_secondary_content"><?php echo $content; ?></textarea>
	</div>
	<?php
}

function alo_em_mod_add_wysiwyg_editor(){
	global $pagenow;
	$post_type = isset($_GET['post_type']) ? $_GET['post_type'] : 'post';
	if ( $pagenow == "post.php" || ($pagenow == "post-new.php" && $post_type == 'newsletter')) :
	?>
<script type="text/javascript">/* <![CDATA[ */
	(function($){
		$(document).ready(function(){
			$('#alo_em_mod_secondary_content').addClass("mceEditor");
			if ( typeof( tinyMCE ) == "object" && typeof( tinyMCE.execCommand ) == "function" ) {
				tinyMCE.execCommand("mceAddControl", false, "alo_em_mod_secondary_content");
			}
		});
	})(jQuery);
/* ]]> */</script>
	<style>
		#alo_secondary_content_container .mceIframeContainer {background-color:white;}
	</style>
	<?php endif;
}
add_action( 'admin_print_footer_scripts', 'alo_em_mod_add_wysiwyg_editor', 99 );

/**
 * Apply filters when newsletter is read on blog
 */ 
function alo_em_mod_filter_content_in_site ( $content ) {  
	global $post;
	if ( !is_admin() && isset($post) && $post->post_type == 'newsletter' ) {
		$recipient = (object) array( "name" => __( "Subscriber", "alo-easymail" ), "firstname" => __( "Subscriber", "alo-easymail" ) );
		$secondary_content = get_post_meta($post->ID, '_alo_em_secondary_content', true);
		$secondary_content = apply_filters( 'alo_easymail_newsletter_content', $secondary_content, $post, $recipient, true );

		$content = $content . $secondary_content;
	}
	return $content;	
}
add_filter ( 'the_content',  'alo_em_mod_filter_content_in_site', 99 );



/* EOF */
