(function($){

	AstraSitesAPI = {

		_api_url      : astraSitesApi.ApiURL,
		_stored_data  : astraSitesApi._stored_data,

		/**
		 * API Request
		 */
		_api_request: function( args, callback ) {

			// Set API Request Data.
			var data = {
				url: AstraSitesAPI._api_url + args.slug,
			};

			if( astraRenderGrid.headers ) {
				data.headers = astraRenderGrid.headers;
			}

			$.ajax( data )
			.done(function( items, status, XHR ) {

				if( 'success' === status && XHR.getResponseHeader('x-wp-total') ) {

					if( args.id ) {
						AstraSitesAPI._stored_data[ args.id ] = $.merge( AstraSitesAPI._stored_data[ args.id ], items );
					}

					var data = {
						args 		: args,
						items 		: items,
						items_count	: XHR.getResponseHeader('x-wp-total') || 0,
						item_pages	: XHR.getResponseHeader('x-wp-totalpages') || 0,
					};

					if( 'undefined' !== typeof args.trigger && '' !== args.trigger ) {
						$(document).trigger( args.trigger, [data] );
					}
				} else {
					$(document).trigger( 'astra-sites-api-request-error' );
				}

			})
			.fail(function( jqXHR, textStatus ) {

				$(document).trigger( 'astra-sites-api-request-fail', [jqXHR, textStatus, args] );

			})
			.always(function() {
				$(document).trigger( 'astra-sites-api-request-always' );
			});

		},

	};

})(jQuery);
