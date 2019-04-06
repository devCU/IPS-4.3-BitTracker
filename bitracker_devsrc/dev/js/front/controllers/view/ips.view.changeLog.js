/**
 *
 * ips.view.changeLog.js - Changelog controller
 *
 */
;( function($, _, undefined){
	"use strict";

	ips.controller.register('bitracker.front.view.changeLog', {

		initialize: function () {
			this.on( 'menuItemSelected', this.changeVersion );
			this.setup();

			// Primary event that watches for URL changes
			History.Adapter.bind( window, 'statechange', _.bind( this.stateChange, this ) );
		},

		/**
		 * Setup method
		 * Sets an initial state that we can use to go back to the default state
		 *
		 * @returns 	{void}
		 */
		setup: function () {
			// Update page URL
			History.replaceState( { 
				controller: 'changelog'
			}, document.title, window.location.href );
		},

		/**
		 * Updates the version changelog being shown
		 *
		 * @param 		{event} 	e 		Event object
		 * @param 		{object} 	data 	Event data object from the menu
		 * @returns 	{void}
		 */
		changeVersion: function (e, data) {
			data.originalEvent.preventDefault();

			var url = data.menuElem.find('[data-ipsMenuValue="' + data.selectedItemID + '"] > a').attr('href');

			// Update page URL
			History.pushState( { 
				controller: 'changelog',
				version: data.selectedItemID
			}, document.title, url );

			this._loadVersion( url, data.menuElem.find('[data-ipsMenuValue="' + data.selectedItemID + '"]').attr('data-changelogTitle') );
		},

		/**
		 * Event handler for History.js
		 * When the state changes, we locate that menu item based on the version, and then pull
		 * the version string and URL and load it
		 *
		 * @returns 	{void}
		 */
		stateChange: function () {
			var state = History.getState();

			// Other things on the page can change the URL, so make sure this is a changelog url
			if( state.data.controller != 'changelog' ){
				return;
			}

			var item;

			if( state.data.version ){
				item = $('#elChangelog_menu').find('[data-ipsMenuValue="' + state.data.version + '"]');
			} else {
				item = $('#elChangelog_menu').find('[data-ipsMenuValue]').first();
			}

			this._loadVersion( item.find('a').attr('href'), item.attr('data-ipsMenuValue') );
		},

		/**
		 * Loads version information
		 *
		 * @param 		{string} 	url 			URL of the version to load
		 * @param 		{string} 	versionTitle 	Title of version being loaded
		 * @returns 	{void}
		 */
		_loadVersion: function (url, versionTitle) {
			var self = this;

			// Update version
			this.scope.find('[data-role="versionTitle"]').text( versionTitle );

			// Set height on info area and set to loading
			this.scope
				.find('[data-role="changeLogData"]')
					.css( {
						height: this.scope.find('[data-role="changeLogData"]').height() + 'px'
					})
					.addClass('ipsLoading')
					.html('');

			// Load the new data
			ips.getAjax()( url )
				.done( function (response) {
					self.scope
						.find('[data-role="changeLogData"]')
							.html( response )
							.removeClass('ipsLoading')
							.css({
								height: 'auto'
							});
				})
				.fail( function (jqXHR, textStatus, errorThrown) {
					window.location = url;
				});
		}
		
	});
}(jQuery, _));