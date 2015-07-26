<?php if ( !defined('ABSPATH') ) die(); // If this file is called directly, abort.

/**
 * Functions about Custom Fields
 *
 * @package WordPress
 * @subpackage ALO EasyMail plugin
 */



/**
 * Maybe install custom fields on plugin init
 */
function alo_em_install_custom_fields() {

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
}
add_action( 'init', 'alo_em_install_custom_fields' );



/**
 * Prepare and return the custom field array built by filters
 *
 * @return arr|false
 */

function alo_easymail_get_custom_fields () {
	$fields = false;
	$fields = apply_filters ( 'alo_easymail_newsletter_set_custom_fields', $fields );
	if ( !empty($fields) && is_array($fields) )
	{
		foreach ( $fields as $key => $value )
		{
			// Defaults for each field
			$defaults = array(
				'humans_name' 	=> $key,
				'sql_attr' 		=> "VARCHAR(100) DEFAULT NULL",
				'sql_key'		=> false,
				'input_type' 	=> "text",
				'input_options'	=> false,
				'input_mandatory'=>false,
				'input_validation'=> false,
				'input_attr'	=> ""
			);
			$fields[$key] = wp_parse_args( $value, $defaults );
		}
	}
	else
	{
		$fields = false;
	}
	return $fields;
}


/**
 * Get the edit html of a custom field
 *
 * @param	str		the field key
 * @param	arr		the field array
 * @param	str		the name of html element
 * @param	str		the preset value
 * @param	bol		edit or view
 * @param	str		js when input is blured (or changed)
 * @return	html
 */
function alo_easymail_custom_field_html ( $key, $field, $input_name="", $value="", $edit=false, $js_onblur="" ) {
	if ( empty($key) ||  empty($field) ) return "";
	$field_id = empty($input_name) ? "alo_em_".$key : $input_name;
	$input = "";
	if (isset($field['input_type']) )
	{
		switch ( $field['input_type'] )
		{
			case 'select':
				if ( $edit )
				{
					$input .= "<select id=\"$field_id\" name=\"$field_id\" class=\"input-select\" {$field['input_attr']} onchange=\"$js_onblur\">\n";
					if ( isset($field['input_options']) && is_array($field['input_options']) )
					{
						foreach ( $field['input_options'] as $k => $v )
						{
							$selected = $value == $k ? "selected=\"selected\"" : "";
							$input .= "<option value=\"$k\" $selected>".esc_html(__($v, "alo-easymail"))."</option>\n";
						}
					}
					$input .= "</select>\n";
				}
				else
				{
					$input .= isset($field['input_options'][$value]) ? esc_html($field['input_options'][$value]) : esc_html($value);
				}
				break;

			case 'checkbox':
				if ( $edit )
				{
					$checked = $value == 1 ? "checked=\"checked\"" : "";
					$input .= "<input type=\"checkbox\" id=\"$field_id\" name=\"$field_id\" class=\"input-checkbox\" value=\"1\" $checked {$field['input_attr']} onchange=\"$js_onblur\" />\n";
				}
				else
				{
					$input .= "<img src=\"".ALO_EM_PLUGIN_URL."/images/".( ($value == '1') ? "yes.png":"no_grey.png" ) ."\"";
					$input .= " alt=\"".( ($value == '1') ? __("yes", "alo-easymail"):__("no", "alo-easymail") ) ."\" />\n";
				}
				break;

			case 'textarea':
				if ( $edit )
				{
					$input .= "<textarea id=\"$field_id\" name=\"$field_id\" class=\"input-textarea\" {$field['input_attr']} onblur=\"$js_onblur\">".format_to_edit($value)."</textarea>\n";
				}
				else
				{
					$input .= esc_html($value);
				}
				break;

			default:
			case 'text':
				if ( $edit )
				{
					$input .= "<input type=\"text\" id=\"$field_id\" name=\"$field_id\" class=\"input-text\" value=\"".esc_attr($value)."\" {$field['input_attr']} onblur=\"$js_onblur\" onkeydown=\"if(window.event) {keynum = event.keyCode;} else if(event.which) {keynum = event.which;}; if (keynum==13) { $js_onblur; return false;}\" />\n";
				}
				else
				{
					$input .= esc_html($value);
				}
				break;

		}
	}
	return $input;
}



/* EOF */