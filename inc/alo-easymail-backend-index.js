(function($){

	$(document).ready(function(){
		var $aloEasymailWidgetNews = $('#alo-easymail-widget-latest-news');
		$.ajax( {
			url: 'https://www.eventualo.net/blog/wp-json/wp/v2/posts?categories=143&per_page=3',
			success: function ( data ) {
				$aloEasymailWidgetNews.empty();
				$.each( data, function( index, post ) {
					var postDate = new Date( post.date );
					$aloEasymailWidgetNews.append(
						'<li><a href="' + post.link + '" >' + post.title.rendered + '</a>'
						+ '&nbsp; (' + postDate.getFullYear() + '/' + ( postDate.getMonth() +1 ) + '/' + postDate.getDate()
						+ ')</li>'
					);
				});
			},
			error: function ( jqXHR, exception ) {
				$aloEasymailWidgetNews.empty().append(
					'<li><em>' + easymailJsIndex.errGeneric + '</em></li>'
				);
			}
		} );
	});

})(jQuery)
