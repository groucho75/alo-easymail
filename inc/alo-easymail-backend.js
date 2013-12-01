var aloEM = jQuery.noConflict();
aloEM (document).ready ( function(){
	
	/*
	 * Edit or New newsletter pages
	 */
	
	if ( easymailJs.pagenow == 'post-new.php' || easymailJs.pagenow == 'post.php' ) {
	
	 	aloEM( "#easymail-filter-ul-languages" ).hide();
	 	aloEM( "#easymail-filter-ul-lists" ).hide(); 	
	 	aloEM( "#easymail-filter-ul-roles" ).hide();
	 	
		aloEM('.easymail-filter-subscribers-by-languages').on( "click", function() {
			aloEM( "#easymail-filter-ul-languages" ).toggle(); 
			return false;
		});
		aloEM('.easymail-filter-subscribers-by-lists').on( "click", function() {
			aloEM( "#easymail-filter-ul-lists" ).toggle(); 
			return false;		
		});
		aloEM('.easymail-filter-regusers-by-roles').on( "click", function() {
			aloEM( "#easymail-filter-ul-roles" ).toggle(); 
			return false;		
		});			
		
		aloEM( "#easymail-recipients-all-subscribers" ).on( "click", function() {
			var status = aloEM( this ).is(':checked');
			aloEM( ".check_list" ).prop( "checked", status );
		});

		aloEM( "#easymail-recipients-all-regusers" ).on( "click", function() {
			var status = aloEM( this ).is(':checked');
			aloEM( ".check_role" ).prop( "checked", status );
		});		
	
		aloEM( "#easymail-theme-select-preview" ).on( "click", function() {
			var theme = aloEM( '#easymail-theme-select' ).val();
			if ( theme != "" ) aloEM.fn.easymailThemePreviewPopup( theme );
			return false;
		});

	}

	/*
	 * Newsletters' Table page
	 */

	if ( easymailJs.pagenow == 'edit.php' ) {
	
		aloEM( ".easymail-column-short-summary" ).hide();
		
		aloEM('.easymail-toggle-short-summary').on( "click", function() {
			var postId = aloEM( this ).attr( 'rel' );
			aloEM( "#easymail-column-short-summary-"+ postId ).toggle(); 
			return false;
		});
		
		
		// Column status: Refresh
		//aloEM('.easymail-refresh-column-status').on( "click", function(e) {
		aloEM('body').on( "click", '.easymail-refresh-column-status', function(e) {
			e.preventDefault();
			var postId = aloEM( this ).attr( 'rel' );
			var data = {
				action: 'alo_easymail_update_column_status',
				post_id: postId
			};
			aloEM( '#easymail-refresh-column-status-loading-'+ postId ).show();
			aloEM( '#alo-easymail-column-status-'+ postId).hide();
			
			aloEM.post( easymailJs.ajaxurl, data, function(response) {
				aloEM( '#easymail-refresh-column-status-loading-'+ postId ).hide();
				aloEM( '#alo-easymail-column-status-'+postId).html( response ).show();
			});
		});

		// Column status: Pause
		aloEM('.easymail-pause-column-status').on( "click", function() {

		});		
	
	}	

	// Preview in newsletter theme
	if ( aloEM("#easymail-open-preview").length > 0 ) {
		aloEM("#easymail-open-preview")
			.insertAfter('a#post-preview')
			.click(function(event) {
				event.preventDefault();

				aloEM("#easymail-open-preview-loading").show();
				
				var theme = aloEM( '#easymail-theme-select' ).val();
							
				var data = {
					action			: 	'alo_easymail_save_newsletter_content_transient',
					newsletter		:	easymailJs.postID,
					theme			:	theme,
					_ajax_nonce		: 	easymailJs.nonce
				};				
				
				aloEM.post( easymailJs.ajaxurl, data, function(response) {
					aloEM( '#easymail-modal-preview-loading' ).hide();
					
					if ( response == "-1" ) {
						// error
						alert( easymailJs.errGeneric );
						aloEM("#easymail-open-preview-loading").hide();
						
					} else {

						autosave();

						setTimeout(function(){
							aloEM("#easymail-open-preview-loading").hide();
							window.open ( easymailJs.pluginPath + 'alo-easymail_preview.php?newsletter=' + easymailJs.postID + '&_wpnonce=' + easymailJs.nonce, 'easymail-preview-'+ easymailJs.postID ); 
						}, 1000);
				
					}
				});

			});
	}
			

	/*
	 * Subscribers' Table page
	 */

	if ( easymailJs.pagenow == 'edit.php' && easymailJs.screenID == 'alo-easymail/alo-easymail_subscribers' ) {
		
		// Start inline-editing a subscriber
		aloEM('.easymail-subscriber-edit-inline').on( "click", function() {
			var id = aloEM( this ).attr('rel');
			var row_index = aloEM.trim( aloEM('tr#subscriber-row-'+ id +' th.subscriber-row-index').html() );
			
			// Get data...
			var data = {
				action			: 	'alo_easymail_subscriber_edit_inline',
				inline_action	:	'edit',
				subscriber		: 	id,
				row_index		:	row_index,
				_ajax_nonce		: 	easymailJs.nonce
			};

			aloEM( '#easymail-subscriber-edit-inline_'+ id  ).hide();
			aloEM( '#easymail-subscriber-delete_'+ id  ).hide();
			aloEM( '#easymail-subscriber-delete-and-unsubscribe_'+ id  ).hide();					
			aloEM( '#easymail-subscriber-'+ id +'-actions-loading' ).show();
			
			aloEM.post( easymailJs.ajaxurl, data, function(response) {
				aloEM( '#easymail-subscriber-'+ id +'-actions-loading' ).hide();
				
				if ( response == "-1" ) { // error
					alert ( "ERROR" );
				} else {
					//console.log ( response );
					aloEM('tr#subscriber-row-'+ id ).html( response );
				}
			});
			
			return false;		
		});	
		
		
		// Save inline-editing subscriber
		//aloEM('.easymail-subscriber-edit-inline-save').on( "click", function() {
		aloEM('body').on( "click", '.easymail-subscriber-edit-inline-save', function(e) {
			e.preventDefault();
			var id = aloEM( this ).attr('rel');
			var row_index = aloEM.trim( aloEM('tr#subscriber-row-'+ id +' th.subscriber-row-index').html() );
			
			// Prepare new info
			var email = aloEM('#subscriber-'+ id +'-email-new').val();
			var sname = aloEM('#subscriber-'+ id +'-name-new').val();
			var lang = aloEM('#subscriber-'+ id +'-lang-new').val();
			var active = ( aloEM('#subscriber-'+ id +'-active-new').is(':checked') ) ? 1 : 0;
			//edit : added all this for
			var alo_cf_array_val = new Array();
			for( k in alo_cf_array ){
				alo_cf_array_val[ k ] = aloEM('#subscriber-' + id + '-' + alo_cf_array[k] + '-new').val();
			}
			var lists = "";
			aloEM('.subscriber-'+ id +'-lists-new:checked').each ( function () { 
			 	lists = lists + aloEM(this).val() +","; 
			});
			
			//console.log( lists );
			// Get data...
			var data = {
				action			: 	'alo_easymail_subscriber_edit_inline',
				inline_action	:	'save',
				subscriber		: 	id,
				new_name		:	sname,
				new_email		:	email,	
				new_active		:	active,
				new_lang		:	lang,
				new_lists		:	lists,					
				row_index		:	row_index,
				_ajax_nonce		: 	easymailJs.nonce
			};
			//edit : added all this for
			for( k in alo_cf_array_val ){
				data[ 'new_' + alo_cf_array[k] ] = alo_cf_array_val[ k ];
			}
			aloEM( '#easymail-subscriber-edit-inline-save_'+ id  ).hide();
			aloEM( '#easymail-subscriber-edit-inline-cancel_'+ id  ).hide();		
			aloEM( '#easymail-subscriber-'+ id +'-actions-loading' ).show();
			
			aloEM.post( easymailJs.ajaxurl, data, function(response) {
				aloEM( '#easymail-subscriber-'+ id +'-actions-loading' ).hide();
				
				switch ( response ) {
					case "-1":
						alert ( "ERROR" );
						break;
						
					case "-error-email-is-not-valid":
						aloEM( '#easymail-subscriber-edit-inline-save_'+ id  ).show();
						aloEM( '#easymail-subscriber-edit-inline-cancel_'+ id  ).show();	
						alert ( easymailJs.errEmailNotValid );
						break;

					case "-error-name-is-empty":
						aloEM( '#easymail-subscriber-edit-inline-save_'+ id  ).show();
						aloEM( '#easymail-subscriber-edit-inline-cancel_'+ id  ).show();	
						alert ( easymailJs.errNameIsBlank );
						break;

					case "-error-email-already-subscribed":
						aloEM( '#easymail-subscriber-edit-inline-save_'+ id  ).show();
						aloEM( '#easymail-subscriber-edit-inline-cancel_'+ id  ).show();	
						alert ( easymailJs.errEmailAlreadySubscribed );
						break;							
												
					default: 
						//console.log ( response );
						aloEM('tr#subscriber-row-'+ id ).html( response );
				}
			});
			
			return false;				
		});	
		
		// Cancel inline-editing subscriber
		aloEM('body').on( "click", '.easymail-subscriber-edit-inline-cancel', function(e) {
		//aloEM('.easymail-subscriber-edit-inline-cancel').on( "click", function() {
			e.preventDefault();
			var id = aloEM( this ).attr('rel');
			var row_index = aloEM.trim( aloEM('tr#subscriber-row-'+ id +' th.subscriber-row-index').html() );
			
			// Get data...
			var data = {
				action			: 	'alo_easymail_subscriber_edit_inline',
				inline_action	:	'cancel',
				subscriber		: 	id,
				row_index		:	row_index,
				_ajax_nonce		: 	easymailJs.nonce
			};

			aloEM( '#easymail-subscriber-edit-inline-save_'+ id  ).hide();
			aloEM( '#easymail-subscriber-edit-inline-cancel_'+ id  ).hide();
			aloEM( '#easymail-subscriber-delete-and-unsubscribe_'+ id  ).hide();						
			aloEM( '#easymail-subscriber-'+ id +'-actions-loading' ).show();
			
			aloEM.post( easymailJs.ajaxurl, data, function(response) {
				aloEM( '#easymail-subscriber-'+ id +'-actions-loading' ).hide();
				
				if ( response == "-1" ) { // error
					alert ( "ERROR" );
				} else {
					//console.log ( response );
					aloEM('tr#subscriber-row-'+ id ).html( response );
				}
			});
			return false;		
		});	

		// Delete a subscriber
		aloEM('.easymail-subscriber-delete').on( "click", function() {
			var id = aloEM( this ).attr('rel');
			var row_index = aloEM.trim( aloEM('tr#subscriber-row-'+ id +' th.subscriber-row-index').html() );

			var to_unsubscribe;
			if ( aloEM( this ).hasClass('and-unsubscribe') ) {
				to_unsubscribe = 1;
			} else {
				to_unsubscribe = 0;
			}

			if ( to_unsubscribe == 1 ) {
				if ( !confirm( easymailJs.confirmDelSubscriberAndUnsubscribe ) ) return false;
			} else {
				if ( !confirm( easymailJs.confirmDelSubscriber ) ) return false;
			}
			
			// Get data...
			var data = {
				action			: 	'alo_easymail_subscriber_edit_inline',
				inline_action	:	'delete',
				subscriber		: 	id,
				row_index		:	row_index,
				_ajax_nonce		: 	easymailJs.nonce,
				to_unsubscribe	:	to_unsubscribe
			};

			aloEM( '#easymail-subscriber-edit-inline_'+ id  ).hide();
			aloEM( '#easymail-subscriber-delete_'+ id  ).hide();
			aloEM( '#easymail-subscriber-delete-and-unsubscribe_'+ id  ).hide();			
			aloEM( '#easymail-subscriber-'+ id +'-actions-loading' ).show();
			
			aloEM.post( easymailJs.ajaxurl, data, function(response) {
				aloEM( '#easymail-subscriber-'+ id +'-actions-loading' ).hide();
				
				if ( response == "-1" ) { // error
					alert ( "ERROR" );
				} else if ( response == "-ok-deleted" ) {
					aloEM('tr#subscriber-row-'+ id ).animate({backgroundColor:'#ff0000'}, 500).fadeOut(
						'fast', 
						function() { 
							aloEM(this).remove(); 
						});
				}
			});
						
			return false;		
		});	

		// Disable Enter key when edit-inline
		var disable_classes = '.subscriber-email-new, .subscriber-name-new, .subscriber-active-new, .subscriber-lists-new'; //edit : added all this line
		//edit : added all this for
		for( k in alo_cf_array ){
			disable_classes = disable_classes + ', .subscriber-' + alo_cf_array[k] + '-new';
		}
		aloEM( disable_classes ).on("keypress", function(e) { //edit : orig : aloEM('.subscriber-email-new, .subscriber-name-new, .subscriber-active-new, .subscriber-lists-new').on("keypress", function(e) {
		 	if (e.keyCode == 13) return false;
		});		
				
	}	


	/*
	 * List of recipient modal
	 */
	var $listModal = aloEM("#easymail-recipient-list-modal");

	if ( $listModal.length > 0 ) {
		
		$listModal.dialog({                   
			dialogClass   : 'wp-dialog',           
			modal         : true,
			autoOpen      : false, 
			closeOnEscape : false,
			width			: 700,
			height			: 400,
			title			: easymailJs.titleRecListModal,
			resizable		: true,
			buttons       : [{
									text: easymailJs.txtClose,
									click: function() { aloEM(this).dialog("close"); },
									"class": 'button'
								}],
			beforeClose	:	function( event, ui ) {
									if ( aloEM('.easymail-recipients-pause-loop').is(':visible') ) {
										aloEM('.easymail-recipients-pause-loop').trigger( "click" );
									}
									aloEM(this).easymailUpdateColumStatus( aloEM(this).data('current-id') );
								},
			open			:	function( event, ui ) {
									// Modal about a new newsletter recipient list: clear bar and response and show disclaimer
									if ( aloEM(this).data('previous-id') != aloEM(this).data('current-id') ) {
										aloEM('#alo-easymail-list-disclaimer').show();

										aloEM('#ajaxloop-response').html('');
										aloEM('#alo-easymail-bar-outer').hide();
										aloEM('#alo-easymail-bar-inner').css( 'width', "0" );

										aloEM('.easymail-recipients-start-loop').show();
										aloEM('.easymail-recipients-start-loop-and-send').show();
										aloEM('.easymail-recipients-pause-loop').hide();
										aloEM('.easymail-recipients-restart-loop').hide();																
									}
								}								
		});
		
	}
	
	/*
	 * Functions
	 */
	
	aloEM.fn.easymailReportPopup = function( url, newsletter, lang ) {
		tb_show ( easymailJs.reportPopupTitle, url +"&newsletter=" + newsletter + "&lang=" + lang + "&TB_iframe=true&height=570&width=800", false );
		return false;
	}	

	//aloEM('.easymail-reciepient-list-open').on( "click", function(event) {
	aloEM('body').on( "click", '.easymail-reciepient-list-open', function(event) {
		event.preventDefault();
		$listModal.data('previous-id', $listModal.data('current-id') );
		$listModal.data('current-id', aloEM(this).attr('rel') );
		$listModal.dialog('open');
	});
    
	aloEM.fn.easymailThemePreviewPopup = function( theme ) {
		window.open ( easymailJs.themePreviewUrl + theme );
		return false;
	}	    
    
	aloEM.fn.easymailPausePlay = function( postId, button ) {
		var data = {
			action: 'alo_easymail_pauseplay_column_status',
			post_id: postId,
			button: button
		};
		aloEM( '#easymail-refresh-column-status-loading-'+ postId ).show();
		aloEM( '#alo-easymail-column-status-'+ postId).hide();
		
		aloEM.post( easymailJs.ajaxurl, data, function(response) {
			aloEM( '#easymail-refresh-column-status-loading-'+ postId ).hide();
			aloEM( '#alo-easymail-column-status-'+postId).html( response ).show();
		});
		return false;
	}	   


	/*
	 * Recipient list modal
	 */
	if ( $listModal.length > 0 ) {
			
		aloEM.fn.easymailStartRecipientsLoop = function( send ) {
			var sendnow = false;
			if ( send == true ) sendnow = "yes";

			aloEM('#ajaxloop-response').smartupdater( {
					url : easymailJs.ajaxurl,
					data: { 
						action: 			'alo_easymail_recipient_list_ajaxloop', 
						newsletter:			$listModal.data('current-id'), 
						_ajax_nonce: 		easymailJs.nonce, 
						txt_success_added: 	easymailJs.txt_success_added, 
						txt_success_sent: 	easymailJs.txt_success_sent, 
						sendnow: 			sendnow 
					},
					type: 'POST',
					dataType: 'json',
					minTimeout: 100
				},
				function ( data ) {
					aloEM(this).easymailReturnFromUpdate( data, sendnow, false );
			});
			
			aloEM('.easymail-recipients-start-loop').hide();
			aloEM('.easymail-recipients-start-loop-and-send').hide();
			aloEM('.easymail-recipients-pause-loop').show();
			aloEM('.easymail-recipients-restart-loop').hide();
			aloEM('#alo-easymail-list-disclaimer').hide();

			aloEM('#alo-easymail-bar-outer').show();
			aloEM('#ajaxloop-response').html( "<p>0% ...</p>" );
		}
		
		// After each periodic update...
		aloEM.fn.easymailReturnFromUpdate = function( data, sendnow, handle ) {
			if ( data.error == '' )
			{
				aloEM('#alo-easymail-bar-outer').show();
				aloEM('#alo-easymail-bar-inner').css( 'width', data.perc + "%" );
				aloEM('#ajaxloop-response').empty();
				if ( data.n_done >= data.n_tot ) {
					var txt_succ = ( sendnow == "yes" ) ? easymailJs.txt_success_sent : easymailJs.txt_success_added ;
					aloEM('#ajaxloop-response').html( "<p>"+ txt_succ + "!</p>" );
					aloEM( '#alo-easymail-bar-inner').addClass ( 'stopped' );
					aloEM('.easymail-recipients-start-loop').hide();
					aloEM('.easymail-recipients-start-loop-and-send').hide();
					aloEM('.easymail-recipients-pause-loop').hide();
					aloEM('.easymail-recipients-restart-loop').hide();					
					aloEM(this).easymailUpdateColumStatus( $listModal.data('current-id') );

					aloEM('#ajaxloop-response').smartupdaterStop();
				} else {
					aloEM('#ajaxloop-response').html( data.perc + "% <small>(" + data.n_done + "/" + data.n_tot + ")</small>" );
				}
			}
			else
			{
				aloEM('#ajaxloop-response').html( '<strong>' + data.error + '</strong>' );
			}
		}
		
		
		aloEM.fn.easymailSendMailTest = function() {
			var email = aloEM('#easymail-testmail').val();
			aloEM('#easymail-testmail-yes,#easymail-testmail-no').hide();
			aloEM('#easymail-testmail-loading').show();
			aloEM.post( easymailJs.ajaxurl, {
				action:			'easymail_send_mailtest',
				newsletter:		$listModal.data('current-id'), 
				_ajax_nonce: 	easymailJs.nonce, 
				email: 			email
			   }, 
			   function ( response ) {
					aloEM('#easymail-testmail-loading').hide();
					if ( response == 'yes' ) {
						aloEM('#easymail-testmail-yes').show();
					} else {
						aloEM('#easymail-testmail-no').show();
					}
			   }
			);
		};

		aloEM.fn.easymailUpdateColumStatus = function( postId ) {
			aloEM( '#easymail-refresh-column-status-loading-'+ postId ).show();
			aloEM( '#alo-easymail-column-status-'+postId).html('');		
			var data = {
				action: 'alo_easymail_update_column_status',
				post_id: postId
			};
			aloEM.post( easymailJs.ajaxurl, data, function(response) {
				aloEM( '#easymail-refresh-column-status-loading-'+ postId ).hide();
				aloEM( '#alo-easymail-column-status-'+postId).html( response );
			});
			return false;
		};
		
		// Click Send Test Mail button
		aloEM('.easymail-send-testmail').on( "click", function(event) {
			event.preventDefault();
			aloEM(this).easymailSendMailTest();
		});	
			
		// Click Start "Put in queue" loop button
		aloEM('.easymail-recipients-start-loop').on( "click", function(event) {
			event.preventDefault();
			aloEM(this).easymailStartRecipientsLoop( false );
		});	

		// Click Start "Send now" loop button
		aloEM('.easymail-recipients-start-loop-and-send').on( "click", function(event) {
			event.preventDefault();
			aloEM(this).easymailStartRecipientsLoop( true );
		});	
			
		aloEM('.easymail-recipients-restart-loop').on( "click", function(event) {
			event.preventDefault();
			aloEM('#ajaxloop-response').smartupdaterRestart();

			aloEM('.easymail-recipients-pause-loop').show();
			aloEM('.easymail-recipients-restart-loop').hide();
			aloEM( '#alo-easymail-bar-inner').removeClass ( 'stopped' );
		});

		aloEM('.easymail-recipients-pause-loop').on( "click", function(event) {
			event.preventDefault();
			aloEM('#ajaxloop-response').smartupdaterStop();

			aloEM('.easymail-recipients-restart-loop').show();
			aloEM('.easymail-recipients-pause-loop').hide();
			aloEM( '#alo-easymail-bar-inner').addClass ( 'stopped' );		
		});

	}  // if $modal
			   	
});

