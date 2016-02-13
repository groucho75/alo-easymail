<?php if ( !defined('ABSPATH') ) die(); // If this file is called directly, abort.

/**
 * Misc function helpers.
 *
 * @package WordPress
 * @subpackage ALO EasyMail plugin
 */

/**
 * Add help image with tooltip
 */
function alo_em_help_tooltip ( $text ) {
	$text = str_replace( array("'", '"'), "", $text );
	$html = "<img src='".ALO_EM_PLUGIN_URL."/images/12-help.png' title='". esc_attr($text) ."' style='cursor:help;vertical-align:middle;margin-left:3px' alt='(?)' />";
	return $html;
}


/**
 * Sort a multidimensional array on a array kay (found on http://php.net/manual/en/function.sort.php)
 * @array		array	the array
 * @key			str		the field to use as key to sort
 * @order		str		sort method: "ASC", "DESC"
 */
function alo_em_msort  ($array, $key, $order = "ASC") {
	$tmp = array();
	foreach($array as $akey => $array2)  {
		$tmp[$akey] = $array2[$key];
	}
	if ($order == "DESC") {
		arsort($tmp , SORT_NUMERIC );
	} else {
		asort($tmp , SORT_NUMERIC );
	}
	$tmp2 = array();
	foreach($tmp as $key => $value) {
		$tmp2[$key] = $array[$key];
	}
	return $tmp2;
}


/**
 * Remove HTML tags, including invisible text such as style and
 * script code, and embedded objects.  Add line breaks around
 * block-level tags to prevent word joining after tag removal.
 * (based on http://nadeausoftware.com/articles/2007/09/php_tip_how_strip_html_tags_web_page )
 */
function alo_em_html2plain ( $text ) {
	// transform in utf-8 if not yet
	//$text = utf8_encode($text);
	if ( function_exists( 'mb_detect_encoding' ) && mb_detect_encoding($text, "UTF-8") != "UTF-8" ) $text = utf8_encode($text);
	$text = preg_replace(
		array(
			// Remove invisible content
			'@<head[^>]*?>.*?</head>@siu',
			'@<style[^>]*?>.*?</style>@siu',
			'@<script[^>]*?.*?</script>@siu',
			'@<object[^>]*?.*?</object>@siu',
			'@<embed[^>]*?.*?</embed>@siu',
			'@<applet[^>]*?.*?</applet>@siu',
			'@<noframes[^>]*?.*?</noframes>@siu',
			'@<noscript[^>]*?.*?</noscript>@siu',
			'@<noembed[^>]*?.*?</noembed>@siu',
			// Add line breaks before and after blocks
			'@</?((address)|(blockquote)|(center)|(del))@iu',
			'@</?((div)|(h[1-9])|(ins)|(isindex)|(p)|(pre))@iu',
			'@</?((dir)|(dl)|(dt)|(dd)|(li)|(menu)|(ol)|(ul))@iu',
			'@</?((table)|(th)|(td)|(caption))@iu',
			'@</?((form)|(button)|(fieldset)|(legend)|(input))@iu',
			'@</?((label)|(select)|(optgroup)|(option)|(textarea))@iu',
			'@</?((frameset)|(frame)|(iframe))@iu',
		),
		array(
			' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ',
			"\n\$0", "\n\$0", "\n\$0", "\n\$0", "\n\$0", "\n\$0",
			"\n\$0"
		),
		$text );

	// Next lines added by sanderbontje, patched by Thomas Heinen

	// Try to preserve links before stripping all tags
	// by rewriting '<a id="123" href="url" rel="bookmark" target="_blank" style="mystyle">link</a>' to 'link (url)'
	$text = preg_replace('/<a(.*)href=[\'|"]([^"]+)[\'|"](.*)>(.*)<\/a>/', "$0 ($2)", $text );

	// from <br> to \n - do this after rewriting links, so that links with <br> in them are still recognized by the regex
	$text = preg_replace('/<br(\s+)?\/?>/i', "\n", $text );

	$text = strip_tags( $text );

	// remove excessive spaces and tabs
	$text = preg_replace("/[ \t]+/", " ", $text);

	// replace quotes by their plain-text variants
	$text = preg_replace("/“/", "\"", $text);
	$text = preg_replace("/”/", "\"", $text);

	// replace dashes by =
	$text = preg_replace("/–/", "=", $text);
	$text = preg_replace("/—/", "=", $text);

	// strip blank lines (blank, with tabs or spaces)
	$text = preg_replace("/[\r\n]+[\s\t]*[\r\n]+/", "\n\n", $text );

	return $text;
}


/**
 * Show credit and banners
 *@param	bol		only donate (false) or all banners (true)
 */
function alo_em_show_credit_banners ( $all=false ) {
	if ( get_option('alo_em_show_credit_banners') == "no" ) return; ?>
	<style type="text/css">
		.alo-banner { border:1px solid #ccc; background-color: #ffffff; width:300px; height: 130px; padding: 6px; margin-right: 15px; float: left }
		.alo-banner p { font-size: 0.9em; margin: 0.5em 0 }
	</style>
	<ul style="width:100%; margin-top:20px">
		<li class="alo-banner">
			<p><em><?php _e("Please visit the plugin site for more info and feedback", "alo-easymail") ?>.
					<?php if ( function_exists('add_contextual_help') ) : ?>
						<?php _e("For more links you can use the Help button", "alo-easymail") ?>.
					<?php endif; ?>
					<br /><a href='http://www.eventualo.net/blog/wp-alo-easymail-newsletter/' target='_blank'>www.eventualo.net</a>
				</em></p>

			<p><em><?php _e("If you use this plugin consider the idea of donating and supporting its development", "alo-easymail") ?>:</em></p><form action='https://www.paypal.com/cgi-bin/webscr' method='post' style='display:inline'>
				<input name='cmd' value='_s-xclick' type='hidden'><input name='lc' value='EN' type='hidden'><input name='hosted_button_id' value='9E6BPXEZVQYHA' type='hidden'>
				<input src='https://www.paypal.com/en_US/i/btn/btn_donate_SM.gif' name='submit' alt='Donate via PayPal' title='Donate via PayPal' border='0' type='image'>
				<img src='https://www.paypal.com/it_IT/i/scr/pixel.gif' border='0' height='1' width='1'><br>	</form>
		</li>
		<?php if ( $all ) : ?>
			<li class="alo-banner">
				<a href="http://account.themefuse.com/aff/go/groucho75/?i=114" target="_blank"><img src="http://themefuse.com/banners/125x125.jpg" border="0" alt="Original WP Themes by ThemeFuse" width="125" height="125" style="float:right;margin-left:10px"></a>
				<p><em>If you are interested in buying <strong>original WP themes</strong> I would recommend <a href="http://account.themefuse.com/aff/go/groucho75/?i=114" title="Original WP Themes by ThemeFuse" target="_blank">ThemeFuse</a>.</em></p>
			</li>
			<li class="alo-banner">
				<a href="http://www.smtp2go.com/?s=eventualo" title="Worldwide SMTP Service" target="_blank"> <img border="0" src="http://www.smtp2go.com/images/partner/smtp2go-logo-dark-square-125.png" alt="Original WP by ThemeFuse" width="125" height="125" style="float:right;margin-left:10px" /></a>
				<p><em><strong>Do you need SMTP?</strong><br /><a href="http://www.smtp2go.com/?s=eventualo" title="Worldwide SMTP Service" target="_blank">SMTP2GO</a> provides a complete outgoing email infrastructure allowing you to send your emails through a professional SMTP service.</em></p>
			</li>
		<?php endif; ?>
	</ul>
	<?php
}


/**
 * Return a Rate about the 1st Number on 2nd Number
 *
 * return 	int		%
 */
function alo_em_rate_on_total ( $number, $total, $float=1 ) {
	return ( $number > 0 ) ? number_format ( ( $number * 100 / $total ), $float ) : 0;
}


/**
 * Get IP address: useful if you like to filter it
 */
function alo_em_ip_address() {
	if ( get_option('alo_em_collect_ip_address') == 'yes' ){
		$ip_address = preg_replace( '/[^0-9a-fA-F:., ]/', '',$_SERVER['REMOTE_ADDR'] );
	} else {
		$ip_address = '';
	}
	return apply_filters ( 'alo_easymail_ip_address', $ip_address );
}


/**
 * Get a checkbox list of WP roles, useful in options
 *
 * @param	str		name field
 * @param	arr		the caps to search in roles
 * @param	str		more attributes
 * @return	html
 *
 * echo alo_em_role_checkboxes( 'roles', 'publish_posts' );
 */

function alo_em_role_checkboxes ( $name='roles', $search_caps=array(), $attrs='' ) {

	settype( $search_caps, 'array' );

	if ( empty($search_caps[0]) ) return '';

	$get_editable_roles = get_editable_roles();

	$html = '';

	foreach ($get_editable_roles as $role => $val )
	{
		// Search the req caps in role caps
		$has_caps = array_intersect( array_keys($val['capabilities']), $search_caps );
		// Compare the reordered arrays: must be identical
		sort( $search_caps );
		sort( $has_caps );
		$checked = ( $search_caps == $has_caps )? 'checked="checked"' : '';

		// Admin always checked
		$disabled = ( $role == 'administrator' ) ? 'disabled="disabled"' : '';

		$html .= '<input type="checkbox" name="'.$name.'[]" id="'.$name.'-'.$role.'" value="'.$role.'" '.$checked .' '.$attrs.' '. $disabled.' /> ';
		$html .= '<label for="'.$name.'-'.$role.'">'.$val['name'].'</label><br />';
	}
	return $html;
}



/* EOF */