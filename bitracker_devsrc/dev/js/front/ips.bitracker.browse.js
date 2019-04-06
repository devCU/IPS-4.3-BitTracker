/**
 *
 * ips.bitracker.browse.js - Handles general browsing controller needs
 *
 */
;( function($, _, undefined){
	&quot;use strict&quot;;

	ips.controller.register('bitracker.front.portal.main', {

		initialize: function () {
			this.on( 'click', '[data-action=&quot;markCategoryRead&quot;]', this.markCategoryRead );
		},

		/**
		 * Marks all files in a category as read, triggering an event on the table
		 *
		 * @param 	{event} 	e 	Event object
		 * @returns {void}
		 */
		markCategoryRead: function (e) {
			e.preventDefault();

			var self = this;

			ips.ui.alert.show( {
				type: 'confirm',
				icon: 'question',
				message: ips.getString('markCategoryAsReadConfirm'),
				subText: '',
				callbacks: {
					ok: function () {
						var url = $( e.currentTarget ).attr('href');

						ips.getAjax()( url, {
							showLoading: true,
							bypassRedirect: true
						})
							.done( function () {
								// Trigger event on the table to hide unread markets
								self.triggerOn( 'core.global.core.table', 'markTableRead' );

								// Hide the link we've just clicked
								ips.utils.anim.go( 'fadeOut', $( e.currentTarget ) );

								ips.ui.flashMsg.show( ips.getString('categoryMarkedRead') );
							})
							.fail( function (jqXHR, textStatus, errorThrown) {
								window.location = url;
							});
					}
				}
			});
		}
	});
}(jQuery, _));