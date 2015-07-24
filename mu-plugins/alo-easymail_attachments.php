<?php


/*******************************************************************************
 * 
 * Add attachments to newsletter
 *
 * Add an attachment to newsletters. In this sample there is only the same attach
 * for every newsletter, but you can use $newsletter object to add different
 * attachments for different newsletters.
 *
 * @since: 2.4.15 
 *
 ******************************************************************************/
 
function custom_easymail_newsletter_attachment ( $attachs, $newsletter ) {

	$attach = WP_CONTENT_DIR . '/uploads/sample.pdf';
	
	return $attach;
}
// UNCOMMENT NEXT LINE TO ENABLE IT
// add_filter ( 'alo_easymail_newsletter_attachments',  'custom_easymail_newsletter_attachment', 10, 2 );


/* EOF */
