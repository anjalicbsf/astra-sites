(function($){

	AstraSitesTracking = {

		init: function()
		{
			$( document ).on( 'astra-sites-tracking-preview', AstraSitesTracking._trackPreview );

			$( document ).on( 'astra-sites-tracking-import', AstraSitesTracking._trackImport );
		},

		_trackImport: function() {
			let params = trackingData.params
			AstraSitesTracking._track( params, 'import' );
		},

		_trackPreview: function() {
			let params = trackingData.params
			AstraSitesTracking._track( params, 'preview' );
		},

		_track: function( data, type ) {

			data['url'] = AstraSitesAdmin.templateData.astra_demo_url;
			data['demo_id'] = AstraSitesAdmin.templateData.id;
			data['type'] = type;

			let post_data = {
				action: 'push_to_ga',
				params: data
			}

			console.log(post_data);

			$.ajax({
				url  : trackingData.ajax_url,
				type : 'POST',
				data : post_data
			})
			.fail(function( jqXHR ){
				//console.log( jqXHR );
		    })
			.done(function ( data ) {
				//console.log( data );
			});
		}

	};

	/**
	 * Initialize AstraSitesTracking
	 */
	$(function(){
		AstraSitesTracking.init();
	});

})(jQuery);