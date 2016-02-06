<?php if ( !defined('ABSPATH') ) die(); // If this file is called directly, abort.

/**
 * Functions about multilingual, including integrations with dedicated plugins
 *
 * @package WordPress
 * @subpackage ALO EasyMail plugin
 */


/**
 * Fix a type in English text: 'e-email' must become 'e-mail'.
 *
 * We cannot update the strings because in that case we should update all translation files.
 *
 * @param $translated_text
 * @param $untranslated_text
 * @param $domain
 * @return mixed
 */
function alo_em_filter_gettext ( $translated_text, $untranslated_text, $domain ) {

	if( $domain == 'alo-easymail' )  {
		$translated_text = str_replace( 'e-email', 'e-mail', $translated_text );
	}
	return $translated_text;
}

add_filter('gettext', 'alo_em_filter_gettext', 20, 3 );


/**
 * Count subscribers reading the selected language
 * @param	lang		if false return no langs or no longer available langs
 * @param	active		if only activated subscribers or all subscribers
 * @return int
 */
function alo_em_count_subscribers_by_lang ( $lang=false, $only_activated=false ) {
	global $wpdb;
	if ( $lang ) {
		$str_lang = "lang='$lang'";
	} else {
		// search with no selected langs or old langs now not requested
		$langs = alo_em_get_all_languages();
		$str_lang = "lang IS NULL OR lang NOT IN (";
		if ( is_array($langs) ) {
			foreach ( $langs as $k => $l ) {
				$str_lang .= "'$l',";
			}
		}
		$str_lang = rtrim ($str_lang, ",");
		$str_lang .= ")" ;
	}
	$str_activated = ( $only_activated ) ? " AND active = '1'" : "";
	return $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}easymail_subscribers WHERE $str_lang $str_activated" );
}



/**
 * Check if there is a multiplanguage enabled plugin
 *
 * @return the name of plugin, or false
 */
function alo_em_multilang_enabled_plugin () {
	// Choice by custom filters
	$plugin_by_filter = apply_filters ( 'alo_easymail_multilang_enabled_plugin', false ); // Hook
	if ( $plugin_by_filter ) return $plugin_by_filter;

	// 1st choice: qTranslate
	global $q_config;
	if( function_exists( 'qtrans_init') && isset($q_config) ) return "qTrans";

	// 2nd choice: using WPML
	if( defined('ICL_SITEPRESS_VERSION') ) return "WPML";

	// TODO other choices...

	// no plugin: return false
	return false;
}


/**
 * Return a text after applying a multilanguage filter
 */
function alo_em___ ( $text ) {
	// 1st choice: using qTranslate
	if( alo_em_multilang_enabled_plugin() == "qTrans" && function_exists( 'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage') ) {
		return qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage ( $text );
	}

	// Choice by custom filters
	$text = apply_filters ( 'alo_easymail_multilang_alo_em___', $text ); // Hook

	// last case: return without translating
	return $text ;
}

/**
 * Echo a text after applying a multilanguage filter (based on 'alo_em___')
 */
function alo_em__e ( $text ) {
	echo alo_em___ ( $text );
}



/**
 * Return a text after applying a multilanguage filter
 *
 * 2nd param:			useful for qTrans: get the part of text of selected lang, e.g.: "<!--:en-->english text<!--:-->"
 * 3rd and 4th params: 	useful for WPML: to get the $prop (title, content. excerpt...) of the $post
 */
function alo_em_translate_text ( $lang, $text, $post=false, $prop="post_title" ) {
	// if blank lang or not installed on blog, get default lang
	if ( empty($lang) || !in_array ( $lang, alo_em_get_all_languages( false )) ) $lang = alo_em_short_langcode ( get_locale() );

	// 1st choice: if using qTranslate, get the part of text of selected lang
	if( alo_em_multilang_enabled_plugin() == "qTrans" && function_exists( 'qtrans_use') ) {
		return qtrans_use ( $lang, $text, false);
	}

	// 2nd choice: using WPML
	if( alo_em_multilang_enabled_plugin() == "WPML" && is_numeric( $post ) && function_exists( 'icl_object_id' ) ) {
		$transl_post = icl_object_id( $post, get_post_type( $post ), true, $lang );
		$transl_post_obj = get_post( $transl_post );
		$transl_post_text = $transl_post_obj->{$prop};
		if ( $transl_post_text ) return $transl_post_text;
	}

	// Choice by custom filters
	$text = apply_filters ( 'alo_easymail_multilang_translate_text', $text, $lang, $post, $prop ); // Hook

	// last case: return as is
	return $text ;
}


/**
 * Return a text of the requested lang from a saved option or default option
 * @param	fallback	if requested lang not exists and fallback true returns a lang default
 */
function alo_em_translate_option ( $lang, $key , $fallback=true ) {
	$default_lang = alo_em_short_langcode ( get_locale() ); // default lang
	$fallback_lang = "en"; // latest default...
	$text_1 = $text_2 = $text_3 = false;

	// from default option if exists
	if ( get_option( $key."_default" ) ) {
		$get = get_option( $key."_default" );
		if ( is_array($get) ) {
			foreach ( $get as $k => $v ) {
				if ( $k == $lang )			$text_1 = $v;	// the requested lang
				if ( $k == $default_lang )	$text_2 = $v;	// the default lang
				if ( $k == $fallback_lang ) $text_3 = $v;	// the fallback lang
			}
		}
	}

	// from option
	if ( get_option( $key ) ) {
		$get = get_option( $key );
		if ( is_array($get) ) {
			foreach ( $get as $k => $v ) {
				if ( !empty($v) ) { // if not empty
					if ( $k == $lang )			$text_1 = $v;	// the requested lang
					if ( $k == $default_lang )	$text_2 = $v;	// the default lang
					if ( $k == $fallback_lang ) $text_3 = $v;	// the fallback lang
				}
			}
		}
	}

	if ( $text_1 ) return $text_1;
	if ( $text_2 && $fallback ) return $text_2;
	if ( $text_3 && $fallback ) return $text_3;
	return false;
}


/**
 * Return a text of the requested lang from an array with same text in several langs ( "en" => "hi", "es" => "hola"...)
 * @param	fallback	if requested lang not exists and fallback true returns a lang default
 */
function alo_em_translate_multilangs_array ( $lang, $array, $fallback=true ) {
	if ( !is_array($array) ) return $array; // if not array, return the text

	$default_lang = alo_em_short_langcode ( get_locale() ); // default lang
	$fallback_lang = "en"; // latest default...
	$text_1 = $text_2 = $text_3 = false;

	foreach ( $array as $k => $v ) {
		if ( $k == $lang ) 			$text_1 = $v;	// the requested lang
		if ( $k == $default_lang ) 	$text_2 = $v;	// the default lang
		if ( $k == $fallback_lang ) $text_3 = $v;	// the fallback lang
	}

	if ( $text_1 ) return $text_1;
	if ( $text_2 && $fallback ) return $text_2;
	if ( $text_3 && $fallback ) return $text_3;
	return false;
}


/**
 * Return the url localised for the requested lang
 *
 * @param	$post	int		the post/page ID
 * @param	$lang	str		two-letter language codes, e.g.: "it"
 */
function alo_em_translate_url ( $post, $lang ) {
	// if blank lang or not installed on blog, get default lang
	if ( empty($lang) || !in_array ( $lang, alo_em_get_all_languages( false )) ) $lang = alo_em_short_langcode ( get_locale() );

	// Choice by custom filters
	$url_by_filter = apply_filters ( 'alo_easymail_multilang_translate_url', false, $post, $lang ); // Hook
	if ( $url_by_filter ) return $url_by_filter;

	// 1st choice: using qTranslate
	if( alo_em_multilang_enabled_plugin() == "qTrans" ) {
		return add_query_arg( "lang", $lang, get_permalink( $post ) );
	}

	// 2nd choice: using WPML
	if( alo_em_multilang_enabled_plugin() == "WPML" && function_exists( 'icl_object_id' ) ) {
		$translated_post = icl_object_id( $post, get_post_type( $post ), true, $lang );
		//return add_query_arg( "lang", $lang, $url );
		return add_query_arg( "lang", $lang, get_permalink( $translated_post ) );
	}

	// last case: return th url with a "lang" var... maybe it could be useful...
	return add_query_arg( "lang", $lang, get_permalink( $post ) );
}


/**
 * Return the homepage url localised for the requested lang
 *
 * @param	$lang	str		two-letter language codes, e.g.: "it"
 */
function alo_em_translate_home_url ( $lang ) {
	// if blank lang or not installed on blog, get default lang
	if ( empty($lang) || !in_array ( $lang, alo_em_get_all_languages( false )) ) $lang = alo_em_short_langcode ( get_locale() );

	// Choice by custom filters
	$url_by_filter = apply_filters ( 'alo_easymail_multilang_translate_home_url', false, $lang ); // Hook
	if ( $url_by_filter ) return $url_by_filter;

	// 1st choice: using qTranslate
	if( alo_em_multilang_enabled_plugin() == "qTrans" ) {
		return add_query_arg( "lang", $lang, trailingslashit( get_home_url() ) );
	}

	// 2nd choice: using WPML
	if( alo_em_multilang_enabled_plugin() == "WPML" && function_exists( 'icl_get_home_url' ) ) {
		return icl_get_home_url();
	}

	// last case: return th url with a "lang" var... maybe it could be useful...
	return add_query_arg( "lang", $lang, trailingslashit( get_home_url() ) );
}


/**
 * Return the ID of subscription page: e.g. useful for WPML, or other purposes
 */
function alo_em_get_subscrpage_id ( $lang=false ) {

	// Choice by custom filters
	$page_by_filter = apply_filters ( 'alo_easymail_multilang_get_subscrpage_id', false, $lang ); // Hook
	if ( $page_by_filter ) return $page_by_filter;

	// 1st choice: using qTranslate
	if( alo_em_multilang_enabled_plugin() == "qTrans" ) {
		return get_option('alo_em_subsc_page');
	}

	// 2nd choice: using WPML
	if( alo_em_multilang_enabled_plugin() == "WPML" && function_exists( 'icl_object_id' ) ) {
		$original = get_option('alo_em_subsc_page');
		return icl_object_id( $original, 'page', true, $lang );
	}

	// last case: return th same ID
	return get_option('alo_em_subsc_page');
}


/**
 * Return the current language
 *
 * @param	bol		try lang detection form browser (eg. useful for subscription if multilang plugin not installed)
 */
function alo_em_get_language ( $detect_from_browser=false ) {
	// Choice by custom filters
	$lang_by_filter = apply_filters ( 'alo_easymail_multilang_get_language', false, $detect_from_browser ); // Hook
	if ( $lang_by_filter ) return strtolower( $lang_by_filter );

	// 1st choice: using qTranslate
	if( alo_em_multilang_enabled_plugin() == "qTrans" && function_exists( 'qtrans_getLanguage') ) {
		return strtolower( qtrans_getLanguage() );
	}

	// 2nd choice: using WPML
	if( alo_em_multilang_enabled_plugin() == "WPML" && defined('ICL_LANGUAGE_CODE') ) {
		return strtolower( ICL_LANGUAGE_CODE );
	}

	// Last choice: get from browser only if requested and the lang .mo is available on blog
	if ( $detect_from_browser ) {
		if ( !empty($_SERVER['HTTP_ACCEPT_LANGUAGE']) ) $lang = alo_em_short_langcode ( $_SERVER['HTTP_ACCEPT_LANGUAGE'] );
		if ( !empty($lang) && in_array($lang, alo_em_get_all_languages(false)) ) {
			return $lang;
		} else {
			return "";
		}
	} else {
		// otherwise return default blog language
		return alo_em_short_langcode ( get_locale() );
	}
}

/**
 * Return 2 chars lowercase lang code (eg. from "it_IT" to "it")
 */
function alo_em_short_langcode ( $lang ) {
	return strtolower ( substr( $lang, 0, 2) );
}

/**
 * Return the long name of language
 */
function alo_em_get_lang_name ( $lang_code ) {
	global $q_config;
	$lang_code = alo_em_short_langcode( $lang_code );
	if ( alo_em_multilang_enabled_plugin() == "qTrans" && isset($q_config) ) { // qTranslate
		$name = $q_config['language_name'][$lang_code];
	} else { // default
		$longname = alo_em_format_code_lang ( $lang_code );
		$splitname = explode ( ";", $longname );
		$name = $splitname[0];
	}
	return $name;
}


/**
 * Return the lang flag
 * @param 	fallback	if there is not the image, return the lang code ('code') or lang name ('name') or nothing
 */
function alo_em_get_lang_flag ( $lang_code, $fallback=false ) {
	global $q_config;
	if ( empty($lang_code) ) return;
	$flag = false;
	$lang_code =  alo_em_short_langcode ( $lang_code );
	if ( alo_em_multilang_enabled_plugin() == "qTrans" && isset($q_config) ) { // qTranslate
		if ( $lang_code == "en" && !file_exists ( trailingslashit(WP_CONTENT_DIR).$q_config['flag_location']. $lang_code .".png" ) ) {
			$img_code = "gb";
		} else {
			$img_code = $lang_code;
		}
		$flag = "<img src='". trailingslashit(WP_CONTENT_URL).$q_config['flag_location']. $img_code .".png' alt='".$q_config['language_name'][$lang_code]."' title='".$q_config['language_name'][$lang_code]."' alt='' />" ;
	} else { // default
		if ( $fallback == "code" ) $flag = $lang_code;
		if ( $fallback == "name" ) $flag = alo_em_get_lang_name ( $lang_code );
	}
	return $flag;
}


/**
 * Return an array with availables languages
 * @param 	by_users	if true and no other translation plugins get all langs chosen by users, if not only langs installed on blog
 */
function alo_em_get_all_languages ( $fallback_by_users=false ) {
	global $wp_version, $alo_em_all_languages;

	if(empty($alo_em_all_languages)){

		// Choice by custom filters
		$langs_by_filter = apply_filters ( 'alo_easymail_multilang_get_all_languages', false, $fallback_by_users ); // Hook
		if ( !empty( $langs_by_filter ) && is_array( $langs_by_filter ) ) $alo_em_all_languages = $langs_by_filter;

		// Case 1: using qTranslate
		elseif( alo_em_multilang_enabled_plugin() == "qTrans" && function_exists( 'qtrans_getSortedLanguages') ) {
			$alo_em_all_languages = qtrans_getSortedLanguages();
		}

		// Case 2: using WPML
		elseif( alo_em_multilang_enabled_plugin() == "WPML" && function_exists( 'icl_get_languages') ) {
			$languages = icl_get_languages('skip_missing=0&orderby=code');
			if ( is_array( $languages ) ) $alo_em_all_languages = array_keys( $languages );
		}

		// Case: search for setting
		elseif ( get_option( 'alo_em_langs_list' ) != "" ) {
			$languages = explode ( ",", get_option( 'alo_em_langs_list' ) );

			// If languages, add locale lang (if not yet) and return
			if ( !empty ($languages[0]) ) {
				$default = alo_em_short_langcode ( get_locale() );
				if ( !in_array( $default, $languages ) ) $languages[] = $default;
				$alo_em_all_languages = $languages;
			}
		}

		// Last case: return all langs chosen by users or default
		elseif ( $fallback_by_users ) {
			$alo_em_all_languages = alo_em_get_all_languages_by_users();
		} else {
			$alo_em_all_languages = array( alo_em_short_langcode ( get_locale() ) );
		}
	}

	return $alo_em_all_languages;
}


/**
 * Return an array with all languages chosen by users
 */
function alo_em_get_all_languages_by_users () {
	global $wpdb;
	$langs = $wpdb->get_results( "SELECT lang FROM {$wpdb->prefix}easymail_subscribers GROUP BY lang" , ARRAY_N );
	if ( $langs ) {
		$output = array();
		foreach ( $langs as $key => $val ) {
			if ( !empty($val[0]) ) $output[] = $val[0];
		}
		return $output;
	} else {
		return array( alo_em_short_langcode ( get_locale() ) );
	}
}



/**
 * Return the long name of language
 */
function alo_em_format_code_lang( $code = '' ) {
	$code = strtolower( substr( $code, 0, 2 ) );
	$lang_codes = array(
		'aa' => 'Afar', 'ab' => 'Abkhazian', 'af' => 'Afrikaans', 'ak' => 'Akan', 'sq' => 'Albanian', 'am' => 'Amharic', 'ar' => 'Arabic', 'an' => 'Aragonese', 'hy' => 'Armenian', 'as' => 'Assamese', 'av' => 'Avaric', 'ae' => 'Avestan', 'ay' => 'Aymara', 'az' => 'Azerbaijani', 'ba' => 'Bashkir', 'bm' => 'Bambara', 'eu' => 'Basque', 'be' => 'Belarusian', 'bn' => 'Bengali',
		'bh' => 'Bihari', 'bi' => 'Bislama', 'bs' => 'Bosnian', 'br' => 'Breton', 'bg' => 'Bulgarian', 'my' => 'Burmese', 'ca' => 'Catalan; Valencian', 'ch' => 'Chamorro', 'ce' => 'Chechen', 'zh' => 'Chinese', 'cu' => 'Church Slavic; Old Slavonic; Church Slavonic; Old Bulgarian; Old Church Slavonic', 'cv' => 'Chuvash', 'kw' => 'Cornish', 'co' => 'Corsican', 'cr' => 'Cree',
		'cs' => 'Czech', 'da' => 'Danish', 'dv' => 'Divehi; Dhivehi; Maldivian', 'nl' => 'Dutch; Flemish', 'dz' => 'Dzongkha', 'en' => 'English', 'eo' => 'Esperanto', 'et' => 'Estonian', 'ee' => 'Ewe', 'fo' => 'Faroese', 'fj' => 'Fijjian', 'fi' => 'Finnish', 'fr' => 'French', 'fy' => 'Western Frisian', 'ff' => 'Fulah', 'ka' => 'Georgian', 'de' => 'German', 'gd' => 'Gaelic; Scottish Gaelic',
		'ga' => 'Irish', 'gl' => 'Galician', 'gv' => 'Manx', 'el' => 'Greek, Modern', 'gn' => 'Guarani', 'gu' => 'Gujarati', 'ht' => 'Haitian; Haitian Creole', 'ha' => 'Hausa', 'he' => 'Hebrew', 'hz' => 'Herero', 'hi' => 'Hindi', 'ho' => 'Hiri Motu', 'hu' => 'Hungarian', 'ig' => 'Igbo', 'is' => 'Icelandic', 'io' => 'Ido', 'ii' => 'Sichuan Yi', 'iu' => 'Inuktitut', 'ie' => 'Interlingue',
		'ia' => 'Interlingua (International Auxiliary Language Association)', 'id' => 'Indonesian', 'ik' => 'Inupiaq', 'it' => 'Italian', 'jv' => 'Javanese', 'ja' => 'Japanese', 'kl' => 'Kalaallisut; Greenlandic', 'kn' => 'Kannada', 'ks' => 'Kashmiri', 'kr' => 'Kanuri', 'kk' => 'Kazakh', 'km' => 'Central Khmer', 'ki' => 'Kikuyu; Gikuyu', 'rw' => 'Kinyarwanda', 'ky' => 'Kirghiz; Kyrgyz',
		'kv' => 'Komi', 'kg' => 'Kongo', 'ko' => 'Korean', 'kj' => 'Kuanyama; Kwanyama', 'ku' => 'Kurdish', 'lo' => 'Lao', 'la' => 'Latin', 'lv' => 'Latvian', 'li' => 'Limburgan; Limburger; Limburgish', 'ln' => 'Lingala', 'lt' => 'Lithuanian', 'lb' => 'Luxembourgish; Letzeburgesch', 'lu' => 'Luba-Katanga', 'lg' => 'Ganda', 'mk' => 'Macedonian', 'mh' => 'Marshallese', 'ml' => 'Malayalam',
		'mi' => 'Maori', 'mr' => 'Marathi', 'ms' => 'Malay', 'mg' => 'Malagasy', 'mt' => 'Maltese', 'mo' => 'Moldavian', 'mn' => 'Mongolian', 'na' => 'Nauru', 'nv' => 'Navajo; Navaho', 'nr' => 'Ndebele, South; South Ndebele', 'nd' => 'Ndebele, North; North Ndebele', 'ng' => 'Ndonga', 'ne' => 'Nepali', 'nn' => 'Norwegian Nynorsk; Nynorsk, Norwegian', 'nb' => 'Bokmål, Norwegian, Norwegian Bokmål',
		'no' => 'Norwegian', 'ny' => 'Chichewa; Chewa; Nyanja', 'oc' => 'Occitan, Provençal', 'oj' => 'Ojibwa', 'or' => 'Oriya', 'om' => 'Oromo', 'os' => 'Ossetian; Ossetic', 'pa' => 'Panjabi; Punjabi', 'fa' => 'Persian', 'pi' => 'Pali', 'pl' => 'Polish', 'pt' => 'Portuguese', 'ps' => 'Pushto', 'qu' => 'Quechua', 'rm' => 'Romansh', 'ro' => 'Romanian', 'rn' => 'Rundi', 'ru' => 'Russian',
		'sg' => 'Sango', 'sa' => 'Sanskrit', 'sr' => 'Serbian', 'hr' => 'Croatian', 'si' => 'Sinhala; Sinhalese', 'sk' => 'Slovak', 'sl' => 'Slovenian', 'se' => 'Northern Sami', 'sm' => 'Samoan', 'sn' => 'Shona', 'sd' => 'Sindhi', 'so' => 'Somali', 'st' => 'Sotho, Southern', 'es' => 'Spanish; Castilian', 'sc' => 'Sardinian', 'ss' => 'Swati', 'su' => 'Sundanese', 'sw' => 'Swahili',
		'sv' => 'Swedish', 'ty' => 'Tahitian', 'ta' => 'Tamil', 'tt' => 'Tatar', 'te' => 'Telugu', 'tg' => 'Tajik', 'tl' => 'Tagalog', 'th' => 'Thai', 'bo' => 'Tibetan', 'ti' => 'Tigrinya', 'to' => 'Tonga (Tonga Islands)', 'tn' => 'Tswana', 'ts' => 'Tsonga', 'tk' => 'Turkmen', 'tr' => 'Turkish', 'tw' => 'Twi', 'ug' => 'Uighur; Uyghur', 'uk' => 'Ukrainian', 'ur' => 'Urdu', 'uz' => 'Uzbek',
		've' => 'Venda', 'vi' => 'Vietnamese', 'vo' => 'Volapük', 'cy' => 'Welsh','wa' => 'Walloon','wo' => 'Wolof', 'xh' => 'Xhosa', 'yi' => 'Yiddish', 'yo' => 'Yoruba', 'za' => 'Zhuang; Chuang', 'zu' => 'Zulu' );
	//$lang_codes = apply_filters( 'lang_codes', $lang_codes, $code );
	return strtr( $code, $lang_codes );
}


/**
 * Create options (if not exist yet) with array of pre-domain text in all languages
 *
 * @deprecated
 *
 * @param 	$reset_defaults		if yes create defaults (useful also if new langs installed)
 */
function alo_em_setup_predomain_texts( $reset_defaults = false ) {
	//Required pre-domain text
	@include( ALO_EM_PLUGIN_ABS.'/languages/alo-easymail-predomain.php');

	global $alo_em_textpre;
	if ( isset($alo_em_textpre) && is_array($alo_em_textpre) ) {
		foreach ( $alo_em_textpre as $key => $sub ) {
			// add/update only if not exists or forced
			if ( !get_option($key.'_default') || $reset_defaults ) {
				update_option ( $key.'_default', $sub );
			}
		}
	}
}

/**
 * Assign a subscriber to a language
 */
function alo_em_assign_subscriber_to_lang ( $subscriber, $lang ) {
	global $wpdb;
	$wpdb->update(    "{$wpdb->prefix}easymail_subscribers",
		array ( 'lang' => $lang ),
		array ( 'ID' => $subscriber )
	);
}


/**********************************************************************
 * WPML integration
 **********************************************************************/

/**
 * If WPML is used: Try to create automatically a Newsletter subscription page
 * for each language, when language list is changed
 */
function alo_em_on_loaded_wpml () {
	function alo_em_create_wpml_subscrpage_translations( $settings ) {
		if ( !function_exists( 'wpml_get_active_languages' ) ) return; // if runs before WPML is completely loaded
		$langs = wpml_get_active_languages();

		if ( is_array( $langs ) ) {
			foreach ( $langs as $lang ) {
				// Original page ID
				$original_page_id = get_option('alo_em_subsc_page');

				// If the translated page doesn't exist, now create it
				if ( icl_object_id( $original_page_id, 'page', false, $lang['code'] ) == null ) {

					// Found at: http://wordpress.stackexchange.com/questions/20143/plugin-wpml-how-to-create-a-translation-of-a-post-using-the-wpml-api

					$post_translated_title = get_post( $original_page_id )->post_title . ' (' . $lang['code'] . ')';

					// All page stuff
					$my_page = array();
					$my_page['post_title'] 		= $post_translated_title;
					$my_page['post_content'] 	= '[ALO-EASYMAIL-PAGE]';
					$my_page['post_status'] 	= 'publish';
					$my_page['post_author'] 	= 1;
					$my_page['comment_status'] 	= 'closed';
					$my_page['post_type'] 		= 'page';

					// Insert translated post
					$post_translated_id = wp_insert_post( $my_page );

					// Get trid of original post
					$trid = wpml_get_content_trid( 'post_'.'page', $original_page_id );

					// Get default language
					$default_lang = wpml_get_default_language();

					// Associate original post and translated post
					global $wpdb;
					$wpdb->update( $wpdb->prefix.'icl_translations', array( 'trid' => $trid, 'language_code' => $lang['code'], 'source_language_code' => $default_lang ), array( 'element_id' => $post_translated_id ) );
				}
			}
		}
	}
	add_action('icl_save_settings', 'alo_em_create_wpml_subscrpage_translations' );
}
add_action('wpml_loaded', 'alo_em_on_loaded_wpml');


/**********************************************************************
 * Polylang integration
 **********************************************************************/

function alo_em_polylang_set_plugin( $multilang_plugin ){

	if ( defined('POLYLANG_VERSION') )
		$multilang_plugin = 'polylang';
	return $multilang_plugin;
}
add_filter ( 'alo_easymail_multilang_enabled_plugin', 'alo_em_polylang_set_plugin' );

function alo_em_polylang_get_language( $lang, $detect_from_browser ){

	if ( function_exists('pll_current_language') )
		$lang = pll_current_language('slug');
	return $lang;
}
add_filter ( 'alo_easymail_multilang_get_language', 'alo_em_polylang_get_language', 10, 2 );

function alo_em_polylang_get_all_languages( $langs, $fallback_by_users  ){

	if ( function_exists('pll_the_languages') )
	{
		global $polylang;
		if (isset($polylang))
		{
			$pl_languages = $polylang->get_languages_list();
			if ( is_array($pl_languages) ) foreach( $pl_languages as $i =>$pl_lang )
				$langs[] = $pl_lang->slug;
		}
	}
	return $langs;
}
add_filter ( 'alo_easymail_multilang_get_all_languages', 'alo_em_polylang_get_all_languages', 10, 2 );

function alo_em_polylang_translate_url( $filtered_url, $post, $lang ){

	if ( function_exists('pll_get_post') )
	{
		if ( $translated_id = pll_get_post( $post, $lang ) )
		{
			$filtered_url = get_permalink( $translated_id );
		}
	}
	return $filtered_url;
}
add_filter ( 'alo_easymail_multilang_translate_url', 'alo_em_polylang_translate_url', 10, 3 );

function alo_em_polylang_get_subscrpage_id( $translated_id, $lang ){

	if ( function_exists('pll_get_post') )
	{
		$original = get_option('alo_em_subsc_page');
		$translated_id = pll_get_post( $original, $lang );
	}
	return $translated_id;
}
add_filter ( 'alo_easymail_multilang_get_subscrpage_id', 'alo_em_polylang_get_subscrpage_id', 10, 2 );


/**********************************************************************
 * zTranslate integration
 **********************************************************************/

function alo_em_ztrans_set_plugin( $multilang_plugin ){

	if ( function_exists('ztrans_init') )
		$multilang_plugin = 'zTrans';
	return $multilang_plugin;
}
add_filter ( 'alo_easymail_multilang_enabled_plugin', 'alo_em_ztrans_set_plugin' );

function alo_em_ztrans_get_language( $lang, $detect_from_browser ){

	if ( function_exists('ztrans_init') )
		$lang = ztrans_getLanguage();
	return $lang;
}
add_filter ( 'alo_easymail_multilang_get_language', 'alo_em_ztrans_get_language', 10, 2 );

function alo_em_ztrans_get_all_languages( $langs, $fallback_by_users  ){

	if ( function_exists('ztrans_init') )
	{
		return ztrans_getSortedLanguages();
	}
	return $langs;
}
add_filter ( 'alo_easymail_multilang_get_all_languages', 'alo_em_ztrans_get_all_languages', 10, 2 );

function alo_em_ztrans_translate_url( $filtered_url, $post, $lang ){

	if ( function_exists('ztrans_init') )
	{
		$filtered_url = add_query_arg( "lang", $lang, get_permalink( $post ) );
	}
	return $filtered_url;
}
add_filter ( 'alo_easymail_multilang_translate_url', 'alo_em_ztrans_translate_url', 10, 3 );

function alo_em_ztrans_get_subscrpage_id( $translated_id, $lang ){

	if ( function_exists('ztrans_init') )
	{
		$translated_id = get_option('alo_em_subsc_page');
	}
	return $translated_id;
}
add_filter ( 'alo_easymail_multilang_get_subscrpage_id', 'alo_em_ztrans_get_subscrpage_id', 10, 2 );

function alo_em_ztrans_filter_title( $subject, $newsletter, $recipient ) {

	if ( function_exists('ztrans_init') )
	{
		$subject = ztrans_use($recipient->lang, $newsletter->post_title, false);
	}
	return $subject;
}
add_filter ( 'alo_easymail_newsletter_title',  'alo_em_ztrans_filter_title', 2, 3 );

function alo_em_ztrans_alo_em___( $text ) {

	if ( function_exists('ztrans_useCurrentLanguageIfNotFoundUseDefaultLanguage') )
	{
		$text = ztrans_useCurrentLanguageIfNotFoundUseDefaultLanguage($text);
	}
	return $text;
}
add_filter ( 'alo_easymail_multilang_alo_em___',  'alo_em_ztrans_alo_em___' );

function alo_em_ztrans_translate_text( $text, $lang, $post, $prop ) {

	if ( function_exists('ztrans_init') )
	{
		$text = ztrans_use($lang, $text, false);
	}
	return $text;
}
add_filter ( 'alo_easymail_multilang_translate_text',  'alo_em_ztrans_translate_text', 2, 4 );



/**********************************************************************
 * qTranslate Plus
 **********************************************************************/

function alo_em_ppqtrans_set_plugin( $multilang_plugin ){

	if ( function_exists('ppqtrans_init') )
		$multilang_plugin = 'zTrans';
	return $multilang_plugin;
}
add_filter ( 'alo_easymail_multilang_enabled_plugin', 'alo_em_ppqtrans_set_plugin' );

function alo_em_ppqtrans_get_language( $lang, $detect_from_browser ){

	if ( function_exists('ppqtrans_init') )
		$lang = ppqtrans_getLanguage();
	return $lang;
}
add_filter ( 'alo_easymail_multilang_get_language', 'alo_em_ppqtrans_get_language', 10, 2 );

function alo_em_ppqtrans_get_all_languages( $langs, $fallback_by_users  ){

	if ( function_exists('ppqtrans_init') )
	{
		return ppqtrans_getSortedLanguages();
	}
	return $langs;
}
add_filter ( 'alo_easymail_multilang_get_all_languages', 'alo_em_ppqtrans_get_all_languages', 10, 2 );

function alo_em_ppqtrans_translate_url( $filtered_url, $post, $lang ){

	if ( function_exists('ppqtrans_init') )
	{
		$filtered_url = add_query_arg( "lang", $lang, get_permalink( $post ) );
	}
	return $filtered_url;
}
add_filter ( 'alo_easymail_multilang_translate_url', 'alo_em_ppqtrans_translate_url', 10, 3 );

function alo_em_ppqtrans_get_subscrpage_id( $translated_id, $lang ){

	if ( function_exists('ppqtrans_init') )
	{
		$translated_id = get_option('alo_em_subsc_page');
	}
	return $translated_id;
}
add_filter ( 'alo_easymail_multilang_get_subscrpage_id', 'alo_em_ppqtrans_get_subscrpage_id', 10, 2 );

function alo_em_ppqtrans_filter_title( $subject, $newsletter, $recipient ) {

	if ( function_exists('ppqtrans_init') )
	{
		$subject = ppqtrans_use($recipient->lang, $newsletter->post_title, false);
	}
	return $subject;
}
add_filter ( 'alo_easymail_newsletter_title',  'alo_em_ppqtrans_filter_title', 2, 3 );

function alo_em_ppqtrans_alo_em___( $text ) {

	if ( function_exists('ppqtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage') )
	{
		$text = ppqtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage($text);
	}
	return $text;
}
add_filter ( 'alo_easymail_multilang_alo_em___',  'alo_em_ppqtrans_alo_em___' );

function alo_em_ppqtrans_translate_text( $text, $lang, $post, $prop ) {

	if ( function_exists('ppqtrans_init') )
	{
		$text = ppqtrans_use($lang, $text, false);
	}
	return $text;
}
add_filter ( 'alo_easymail_multilang_translate_text',  'alo_em_ppqtrans_translate_text', 2, 4 );


/**********************************************************************
 * qTranslate X
 * thanks to Gunu
 **********************************************************************/

function alo_em_qtranxf_set_plugin( $multilang_plugin ){

	if ( function_exists('qtranxf_init') )
		$multilang_plugin = 'qtranslate-x';
	return $multilang_plugin;
}
add_filter ( 'alo_easymail_multilang_enabled_plugin', 'alo_em_qtranxf_set_plugin' );

function alo_em_qtranxf_get_language( $lang, $detect_from_browser ){

	if ( function_exists('qtranxf_init') )
		$lang = qtranxf_getLanguage();
	return $lang;
}
add_filter ( 'alo_easymail_multilang_get_language', 'alo_em_qtranxf_get_language', 10, 2 );

function alo_em_qtranxf_get_all_languages( $langs, $fallback_by_users ){

	if ( function_exists('qtranxf_init') )
	{
		return qtranxf_getSortedLanguages();
	}
	return $langs;
}
add_filter ( 'alo_easymail_multilang_get_all_languages', 'alo_em_qtranxf_get_all_languages', 10, 2 );

function alo_em_qtranxf_translate_url( $filtered_url, $post, $lang ){

	if ( function_exists('qtranxf_init') )
	{
		$filtered_url = add_query_arg( "lang", $lang, get_permalink( $post ) );
	}
	return $filtered_url;
}
add_filter ( 'alo_easymail_multilang_translate_url', 'alo_em_qtranxf_translate_url', 10, 3 );

function alo_em_qtranxf_get_subscrpage_id( $translated_id, $lang ){

	if ( function_exists('qtranxf_init') )
	{
		$translated_id = get_option('alo_em_subsc_page');
	}
	return $translated_id;
}
add_filter ( 'alo_easymail_multilang_get_subscrpage_id', 'alo_em_qtranxf_get_subscrpage_id', 10, 2 );

function alo_em_qtranxf_filter_title( $subject, $newsletter, $recipient ) {
	if ( function_exists('qtranxf_init') )
	{
		$subject = qtranxf_use($recipient->lang, $newsletter->post_title, false);
	}
	return $subject;
}
add_filter ( 'alo_easymail_newsletter_title', 'alo_em_qtranxf_filter_title', 2, 3 );

function alo_em_qtranxf_alo_em___( $text ) {
	if ( function_exists('qtranxf_useCurrentLanguageIfNotFoundUseDefaultLanguage') )
	{
		$text = qtranxf_useCurrentLanguageIfNotFoundUseDefaultLanguage($text);
	}
	return $text;
}
add_filter ( 'alo_easymail_multilang_alo_em___', 'alo_em_qtranxf_alo_em___' );

function alo_em_qtranxf_translate_text( $text, $lang, $post, $prop ) {

	if ( function_exists('qtranxf_init') )
	{
		$text = qtranxf_use($lang, $text, false);
	}
	return $text;
}
add_filter ( 'alo_easymail_multilang_translate_text', 'alo_em_qtranxf_translate_text', 2, 4 );


/* EOF */