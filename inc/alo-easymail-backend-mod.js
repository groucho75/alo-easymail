(function($){

	$(document).ready(function(){
		$('#easymail_mod_add_post').click(function(e){
			e.preventDefault();

			var link = $(this),
			table = $('#alo_easymail_newsletter_post_placeholders table:last'),
			rm = table.find('.easymail-mod-remove-post-placeholder'),
			spinner = link.parent().children('.spinner');

			spinner.show();
			$.ajax({
				url: easymailJs.ajaxurl,
				data: {
					action: 'alo_em_mod_add_post_placeholder',
					newsletter_id: easymailJs.postID
				},
				success: function(data){
					link.before(data);
					rm.remove();
					spinner.hide();
					$('.easymail-mod-remove-post-placeholder').click(removePost);
				}
			});
		});

		$('.easymail-mod-remove-post-placeholder').click(removePost);
	});

	function removePost(e){
		e.preventDefault();

		var link = $(this),
		clone = link.clone(true)
		spinner = link.parent().find('.spinner');

		spinner.show();
		$.ajax({
			url: easymailJs.ajaxurl,
			data: {
				action: 'alo_em_mod_remove_post_placeholder',
				newsletter_id: easymailJs.postID
			},
			success: function(data){
				link.parent().parent().parent().parent().remove();
				var table = $('#alo_easymail_newsletter_post_placeholders table');
				th = table.last().find('th:last');
				spinner.hide();

				if(table.length > 1)
					th.prepend(clone);
			}
		});
	}
})(jQuery)
