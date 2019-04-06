/**
 *
 * ips.view.bitracker.js - Torrent popup controller
 *
 */
;( function($, _, undefined){
	"use strict";

	ips.controller.register('bitracker.front.view.download', {

		initialize: function () {
			this.on( 'click', '[data-action="dialogClose"]', this.closeDialog );
			this.on( 'click', '[data-action="selectFile"]', this.selectFile );
			this.on( 'click', '[data-action="download"]', this.doDownload );
		},

		/**
		 * Agree to the terms and update the display to show the list
		 *
		 * @param 		{event} 	e 		Event object
		 * @returns 	{void}
		 */
		selectFile: function (e) {
			var url = $( e.currentTarget ).attr('href');
			var self = this;

			e.preventDefault();

			// Load the download page
			this.scope
				.html('')
				.css({
					height: '250px'
				})
				.addClass('ipsLoading');

			ips.getAjax()( url )
				.done( function (response) {
					self.scope
						.html( response )
						.css({
							height: 'auto'
						})
						.removeClass('ipsLoading');
				})
				.fail( function (jqXHR, textStatus, errorThrown) {
					window.location = url;
				});
		},
		
		/**
		 * Event handler for the 'cancel' link
		 *
		 * @param 		{event} 	e 		Event object
		 * @returns 	{void}
		 */
		closeDialog: function (e) {
			e.preventDefault();
			this._closeDialog( $( e.currentTarget ).attr('href') );
		},

		/**
		 * Closes the dialog
		 *
		 * @param 		{string} 	href 	Href to redirect to if not in a dialog
		 * @returns 	{void}
		 */
		_closeDialog: function (href) {
			if( this.scope.closest('.ipsDialog').length ){
				this.scope.closest('.ipsDialog').trigger('closeDialog');
			} else {
				window.location = href;
			}
		},

		/**
		 * Initiate the actual download
		 *
		 * @param 		{event} 	e 		Event object
		 * @returns 	{void}
		 */
		doDownload: function (e) {
			var that = this;
			if ( $( e.currentTarget ).attr('data-wait') ) {
				e.preventDefault();
				
				ips.getAjax()( $( e.currentTarget ).attr('href') )
					.done( function (response) {
						var secondsRemaining = response.download - response.currentTime;
						
						$( e.currentTarget )
							.hide()
							.siblings()
								.find('[data-role="downloadCounter"]')
									.html( secondsRemaining )
								.end()
							.end()
							.siblings('[data-role="downloadCounterContainer"]')
								.removeClass('ipsHide');
						
						var interval = setInterval( function () {
							secondsRemaining--;
							$( e.currentTarget ).siblings().find('[data-role="downloadCounter"]').html( secondsRemaining );
							if ( secondsRemaining === 0 ) {
								clearInterval(interval);
								window.location = $( e.currentTarget ).attr('href');
                                that.scope.closest('.ipsDialog').trigger('closeDialog');
							}
						}, 1000 );						
					})
					.fail( function () {
						window.location = $(e.currentTarget).attr('href');
					})
			}
			else
			{
                this.scope.closest('.ipsDialog').trigger('closeDialog');
			}
		}
		
	});
}(jQuery, _));