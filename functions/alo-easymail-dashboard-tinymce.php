<?php if ( !defined('ABSPATH') ) die(); // If this file is called directly, abort.

/**
 * Tinymce Dashboard related functions
 *
 * @deprecated Working on old TINYMCE 3
 * @todo Upgrade scripts to TINYMCE 4
 *
 * @package WordPress
 * @subpackage ALO EasyMail plugin
 */



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
	//array_push($buttons, "easymail"); // TODO tinymce 4
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


/* EOF */