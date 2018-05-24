<?php if ( !defined('ABSPATH') ) die(); // If this file is called directly, abort.

/**
 * Functions about Privacy Page
 *
 * @package WordPress
 * @subpackage ALO EasyMail plugin
 */


/**
 * Add suggesting text for the site privacy policy
 */
function alo_em_add_privacy_policy_content() {
	if ( ! function_exists( 'wp_add_privacy_policy_content' ) ) {
		return;
	}

	$content = '<div class="wp-suggested-text">';

	$content .= '<h3>' . __( 'Newsletter', "alo-easymail") . '</h3>';
	$content .= '<p class="privacy-policy-tutorial">';
	$content .= __( 'In this subsection you should note what information is captured through newsletter subscription form and when recipients perform actions on newsletters. ', "alo-easymail");
	$content .= '</p>';
	$content .= '<p><strong class="privacy-policy-tutorial">' . __( 'Suggested text:' ) . ' </strong>: ';
	$content .= __( 'When visitors subscribe the newsletter we collect the data shown in the subscription form.', "alo-easymail");
	if ( get_option('alo_em_collect_ip_address') == "yes" ) {
		$content .= ' ' . __( 'We store also the IP address of subscribers.', "alo-easymail");
	}
	$content .= '</p>';

	$content .= '<p>';
	$content .= __( 'We try to collect some recipient actions on newsletters:', "alo-easymail");
	if ( get_option('alo_em_use_tracking_pixel') != "no" ) {
		$content .= ' ' . __( 'newsletter views', "alo-easymail") . ', ';
	}
	$content .= __( 'clicks on links.', "alo-easymail");
	$content .= '</p>';

	$content .= '<p>';
	$content .= __( 'Subscribers can edit or remove own newsletter subscription through unsubscription link provided in each newsletter.', "alo-easymail");
	$content .= ' ' . __( 'Subscribers can contact the website administrators to export or remove own subscription data.', "alo-easymail");
	$content .= '</p>';

	$content .= '<div>'; // .wp-suggested-text;

	wp_add_privacy_policy_content(
		'ALO EasyMail Newsletter',
		wp_kses_post( $content )
	);
}
add_action( 'admin_init', 'alo_em_add_privacy_policy_content' );


/**
 * Register plugin exporter for privacy: Subscriber data
 *
 * @param array
 * @return array
 */
function alo_em_register_privacy_exporter_subscriber( $exporters ) {
	$exporters['alo-easymail-subscriber'] = array(
		'exporter_friendly_name' => 'ALO EasyMail Newsletter: ' . __( 'Subscriber', 'alo-easymail' ),
		'callback' => 'alo_em_privacy_exporter_subscriber',
	);
	return $exporters;
}

add_filter(	'wp_privacy_personal_data_exporters', 'alo_em_register_privacy_exporter_subscriber', 10 );

/**
 * Plugin exporter for privacy: Subscriber data
 *
 * @param string
 * @param string
 * @return array
 */
function alo_em_privacy_exporter_subscriber( $email_address, $page = 1 ) {
	$number = 100; // Limit us to avoid timing out
	$page = (int) $page;

	$export_items = array();

	$subscriber = alo_em_get_subscriber ( $email_address );

	if ( $subscriber ) {
		$data = array();

		/**
		 * Subscriber data
		 */
		$item_id = "alo-newsletter-subscriber-{$subscriber->ID}";
		$group_id = 'alo-newsletter-subscriber';
		$group_label = 'ALO EasyMail Newsletter: ' . __( 'Subscriber', 'alo-easymail' );

		// Subscriber standard data
		$subscriber_data = (array)$subscriber;

		$whitelist_subscriber_data = array( 'email', 'name', 'join_date', 'active', 'lang', 'last_act' );

		foreach ( $subscriber_data as $key => $value ) {
			if ( in_array( $key, $whitelist_subscriber_data) ) {
				$data[] = array(
					'name'  => $key,
					'value' => $value,
				);
			}
		};

		// Subscriber custom fields
		$custom_fields = alo_easymail_get_custom_fields();
		if ( $custom_fields ) {
			foreach( $custom_fields as $key => $value ) {
				if ( isset( $subscriber_data->$key ) ) {
					$data[] = array(
						'name'  => $value['humans_name'],
						'value' => $subscriber_data->$key,
					);
				}
			}
		}

		// Mailing lists
		$mailing_lists = alo_em_get_mailinglists( 'public' );
		$subscriber_lists = alo_em_get_user_mailinglists ( $subscriber->ID );
		if ( $subscriber_lists ) {
			$list_labels = array();
			foreach( $subscriber_lists as $index => $key ) {
				$list_labels[] = alo_em_translate_multilangs_array ( alo_em_get_language(), $mailing_lists[$key]['name'], true );
			}
			$data[] = array(
				'name'  => __("Mailing Lists", "alo-easymail"),
				'value' => implode( ' ,' , $list_labels ),
			);
		}

		$export_items[] = array(
			'group_id' => $group_id,
			'group_label' => $group_label,
			'item_id' => $item_id,
			'data' => $data,
		);
	}

	$done = true;
	return array(
		'data' => $export_items,
		'done' => $done,
	);
}


/**
 * Register plugin exporter for privacy: Received newsletters
 *
 * @param array
 * @return array
 */
function alo_em_register_privacy_exporter_received( $exporters ) {
	$exporters['alo-easymail-received'] = array(
		'exporter_friendly_name' => 'ALO EasyMail Newsletter: ' . __( 'Received newsletters', 'alo-easymail' ),
		'callback' => 'alo_em_privacy_exporter_received',
	);
	return $exporters;
}

add_filter(	'wp_privacy_personal_data_exporters', 'alo_em_register_privacy_exporter_received', 10 );

/**
 * Plugin exporter for privacy: Received newsletters
 *
 * @param string
 * @param string
 * @return array
 */
function alo_em_privacy_exporter_received( $email_address, $page = 1 ) {
	$number = 100; // Limit us to avoid timing out
	$page = (int) $page;

	$export_items = array();

	$subscriber = alo_em_get_subscriber ( $email_address );

	if ( $subscriber ) {
		$data = array();

		/**
		 * Newsletter received (recipients)
		 */
		$item_id = "alo-newsletter-received-{$subscriber->ID}";
		$group_id = 'alo-newsletter-received';
		$group_label = 'ALO EasyMail Newsletter: ' . __( 'Newsletters received', 'alo-easymail' );

		$recipients = alo_em_get_newsletter_received_by_subscriber( $email_address, ( $page - 1 ), $page * $number );

		foreach( $recipients as $key => $value ) {
			$data[] = array(
				'name'  => __( 'Newsletter', 'alo-easymail' ),
				'value' => esc_html( $value->newsletter_title ),
			);
		}

		$export_items[] = array(
			'group_id' => $group_id,
			'group_label' => $group_label,
			'item_id' => $item_id,
			'data' => $data,
		);

	}

	// Tell core if we have more comments to work on still
	$done = count( $recipients ) < $number;
	return array(
		'data' => $export_items,
		'done' => $done,
	);
}



/**
 * Register plugin exporter for privacy: Actions on newsletters
 *
 * @param array
 * @return array
 */
function alo_em_register_privacy_exporter_actions( $exporters ) {
	$exporters['alo-easymail-actions'] = array(
		'exporter_friendly_name' => 'ALO EasyMail Newsletter: ' . __( 'Actions on newsletters', 'alo-easymail' ),
		'callback' => 'alo_em_privacy_exporter_actions',
	);
	return $exporters;
}

add_filter(	'wp_privacy_personal_data_exporters', 'alo_em_register_privacy_exporter_actions', 10 );

/**
 * Plugin exporter for privacy: Actions on newsletters
 *
 * @param string
 * @param string
 * @return array
 */
function alo_em_privacy_exporter_actions( $email_address, $page = 1 ) {
	$number = 100; // Limit us to avoid timing out
	$page = (int) $page;

	$export_items = array();

	$subscriber = alo_em_get_subscriber ( $email_address );

	if ( $subscriber ) {
		$data = array();

		/**
		 * Actions on newsletters (stats)
		 */
		$item_id = "alo-newsletter-actions-{$subscriber->ID}";
		$group_id = 'alo-newsletter-actions';
		$group_label = 'ALO EasyMail Newsletter: ' . __( 'Actions on newsletters', 'alo-easymail' );

		$recipients = alo_em_get_newsletter_received_by_subscriber( $email_address, ( $page - 1 ), $page * $number );

		foreach( $recipients as $key => $value ) {
			$views = alo_em_get_recipient_trackings( $value->recipient_id );
			foreach( $views as $i => $stat ) {
				$data[] = array(
                    'name'  => __("Viewed", "alo-easymail"),
                    'value' => esc_html( $value->newsletter_title ),
                );
			}
			$clicks = alo_em_get_recipient_trackings_except_views( $value->recipient_id );
			foreach( $clicks as $i => $stat ) {
				$data[] = array(
					'name'  => __("Clicks", "alo-easymail"),
					'value' => esc_html( $value->newsletter_title ) . ': ' . $stat->request,
				);
			}
		}

		$export_items[] = array(
			'group_id' => $group_id,
			'group_label' => $group_label,
			'item_id' => $item_id,
			'data' => $data,
		);

	}

	// Tell core if we have more comments to work on still
	$done = count( $recipients ) < $number;
	return array(
		'data' => $export_items,
		'done' => $done,
	);
}


/**
 * Register plugin eraser for privacy
 */
function alo_em_register_privacy_eraser( $erasers ) {
	$erasers['alo-easymail-eraser'] = array(
		'eraser_friendly_name' => 'ALO EasyMail Newsletter: ',
		'callback'             => 'alo_em_privacy_eraser',
	);

	return $erasers;
}
add_filter( 'wp_privacy_personal_data_erasers', 'alo_em_register_privacy_eraser', 10 );

/**
 * Register plugin eraser for privacy
 *
 * @param string
 * @param string
 * @return array
 */
function alo_em_privacy_eraser( $email_address, $page = 1 ) {

	if ( empty( $email_address ) ) {
		return array(
			'items_removed'  => false,
			'items_retained' => false,
			'messages'       => array(),
			'done'           => true,
		);
	}

	$subscriber = alo_em_get_subscriber ( $email_address );

	$messages = array();
	$items_removed  = false;
	$items_retained = false;

	if ( $subscriber ) {
		global $wpdb;

		// Delete subscriber
		alo_em_delete_subscriber_by_id( $subscriber->ID );
		alo_em_add_email_in_unsubscribed ( $email_address );

		$messages[] = __( 'Your subscription data was removed.', "alo-easymail" );

		// Anonimize recipients
		$updated = $wpdb->update( "{$wpdb->prefix}easymail_recipients",
			array ( 'user_id' => 0, 'email' => '[ ' . __( 'email deleted on request', "alo-easymail" ) . ']' ),
			array ( 'email' => $email_address )
		);

		if ( false === $updated ) {
			$messages[] = __( 'Recipient data was unable to be removed at this time.', "alo-easymail" );
			$items_retained = true;
		} else {
			$items_removed = count( $updated );
			$messages[] = sprintf( __( 'Removed %s recipients data.', "alo-easymail" ), $items_removed );
		}

	}

	return array(
		'items_removed'  => $items_removed,
		'items_retained' => $items_retained,
		'messages'       => $messages,
		'done'           => true,
	);
}

/* EOF */
