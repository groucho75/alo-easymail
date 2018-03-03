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


/*******************************************************************************
 *
 * Add a monthly pdf to newsletter
 *
 * It automatically adds an attachment to newsletter if find a pdf file named
 * 'newsletter_YYYY_MM.pdf' inside uploads folder, e.g.: newsletter_2018_03.pdf
 *
 * @since: 2.9.8
 *
 ******************************************************************************/

function custom_easymail_newsletter_monthly_attachment ( $attachs, $newsletter ) {

	$month_file = WP_CONTENT_DIR . '/uploads/newsletter_'.date( 'Y_m' ).'.pdf';

	if ( @file_exists( $month_file ) ) {
		return $month_file;
	}
	return array();
}
// UNCOMMENT NEXT LINE TO ENABLE IT
//add_filter ( 'alo_easymail_newsletter_attachments',  'custom_easymail_newsletter_monthly_attachment', 10, 2 );


/*******************************************************************************
 *
 * Add all the pdf attachments uploaded inside the newsletter
 *
 * In newsletter edit screen you can upload pdf files using "Add media" button.
 * Note that a link to pdf is added inside newsletter content: you can remove
 * safely the link from content because the file is marked as attached to
 * newsletter.
 * So, please remind that you cannot pick a pdf from media gallery, already
 * uploaded in other newsletters: you have to upload inside that newsletter
 * to attach the pdf to it.
 *
 * @since: 2.9.8
 *
 ******************************************************************************/

function custom_easymail_newsletter_get_pdf_attachments ( $attachs, $newsletter ) {

	$get_attachments = get_attached_media( 'application/pdf', $newsletter->ID );

	$attachs = array();
	if ( $get_attachments ) {
		foreach( $get_attachments as $attachment ) {
			$attachs[] = get_attached_file( $attachment->ID );
		}
	}
	return $attachs;
}
// UNCOMMENT NEXT LINE TO ENABLE IT
//add_filter ( 'alo_easymail_newsletter_attachments',  'custom_easymail_newsletter_get_pdf_attachments', 10, 2 );

/* EOF */
