<?php


/*******************************************************************************
 * 
 * Form custom fields
 *
 * The following functions add custom fields in subscription form 
 *
 * @since: 2.4
 *
 ******************************************************************************/


/**
 * Add these custom fields to in subscription form.
 * In this sample you will add 2 fields: Company (text) and Favourite music (select).
 * 
 * TO ADD THE SAMPLE FIELDS you have to uncomment the code inside the next function
 *
 * From v.2.4.13 there is automatically a newsletter placehoolder for each custom field: e.g. 'cf_country' => [USER-CF_COUNTRY].
 * 
 * You have to populate an array following these rules.
 *
 * The array KEYS are the names of custom fields: they are used for database column name and variable name (so take care about variable names limitations).
 * It could be a good idea to use a 'cf_' prefix in name: e.g. cf_surname
 *
 * The array VALUES are arrays with parameters for custom fields.
 * Here you are the details:
 *
 *	humans_name			:	the "human readable" name used in blog (ugly default: the key)
 * 	sql_attr			: 	the attributes for the column in database table (default: "VARCHAR(100) DEFAULT NULL")
 * 	sql_key				:	the column in database table is an index (default: false): set up it to yes if you like to make custom queries
 * 							looking for the field. Note: if true, in subscribers list table, the column is ordinable by this field
 *  input_mandatory		:	the field must be filled (default: false)
 * 	input_validation	:	a string rappresenting the name of a php function to be invoked to check the value
 * 							when submitted by subscriber. It must return a bolean true or false.
 * 							Leave false for no validation check (default: false).
 * 							You can use:
 *							- php native functions: e.g. "is_numeric" (note: the submitted value is always a string, so "is_int" does not work as expected)
 *							- WP functions: e.g. "is_email"
 * 							- custom function: you can define it in this file (see below the "custom_easymail_cf_check_number_5_digits" function)
 *	input_type 			:	the type of the form field: "text", "textarea", "select" (default: "text")
 * 	input_values 		:	if the "input_type" is "select", you have to wrtie an array with option values (default: false).
 * 							E.g. for a Sex field: array( 'male' => __("Male", "alo-easymail"), 'female' => __("Female", "alo-easymail") )
 * 	input_attr			:	string with html attributes for the form field (default: ""): e.g "style=\"color: #f00\" width=\"20\" onclick=\"\""
							Do not add these attaributes: id, name, class, value, type, onchange, onblur, onkeydown
 */

function custom_easymail_set_my_custom_fields ( $fields ) {

	/*
	// Custom field: Company
	$fields['cf_company'] = array(
		'humans_name'		=> __("Company", "alo-easymail"),
		'sql_attr' 			=> "VARCHAR(200) NOT NULL AFTER `name`",	
		'input_type' 		=> "text",
		'input_mandatory' 	=> true,
		'input_validation' 	=> false
	);
	*/

	/*
	// Custom field: Fovourite music
	$fields['cf_music'] = array(
		'humans_name'		=> __("Favourite music", "alo-easymail"),
		'sql_attr' 			=> "VARCHAR(100) DEFAULT NULL",
		'sql_key' 			=> true,	
		'input_type' 		=> "select", 
		'input_options' 	=> array(
			"" 			=> '',
			"rock" 		=> __("Rock / Metal", "alo-easymail"),
			"jazz" 		=> __("Jazz", "alo-easymail"),
			"classic" 	=> __("Classic", "alo-easymail"),
			"country" 	=> __("Country / Folk", "alo-easymail"),
			"other" 	=> __("Other", "alo-easymail")
		),
		'input_mandatory' 	=> false,
		'input_validation' 	=> false,
		'input_attr'		=> "style=\"color: #f00\""
	);
	*/

	return $fields;
}
add_filter ( 'alo_easymail_newsletter_set_custom_fields', 'custom_easymail_set_my_custom_fields' );


/**
 * Sample of validation function: check if the passed data is a number 5 digits
 * 
 * To apply it to a custom field, add the name as value in field array:
 * 'input_validation' => 'custom_easymail_cf_check_number_5_digits'
 *
 */
function custom_easymail_cf_check_number_5_digits ($data) {
	if ( preg_match( "/^[0-9]{5}$/", $data ) ) {
		return true;
	} else {
		return false;
	}
}


/* EOF */
