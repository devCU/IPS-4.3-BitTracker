/**
 *
 * ips.settings.settings.js
 *
 */
;( function($, _, undefined){
	&quot;use strict&quot;;

	ips.controller.register('bitracker.admin.settings.settings', {
		alertOpen: false,

		initialize: function () {
			this.on( 'uploadComplete', '[data-ipsUploader]', this.promptRebuildPreference );
			this.on( 'fileDeleted', this.promptRebuildPreference );
		},

		promptRebuildPreference: function (e) {

			if( this.alertOpen )
			{
				return;
			}

			this.alertOpen = true;

			/* Show Rebuild Prompt */
			ips.ui.alert.show({
				type: 'confirm',
				message: ips.getString('bitrackerScreenshotsWatermark'),
				subText: ips.getString('bitrackerScreenshotsWatermarkBlurb'),
				icon: 'question',
				buttons: {
					ok: ips.getString('bitrackerScreenshotsWatermarkYes'),
					cancel: ips.getString('bitrackerScreenshotsWatermarkNo')
				},
				callbacks: {
					ok: function(){
						$('input[name=rebuildWatermarkScreenshots]').val( 1 );
						this.alertOpen = false;
					},
					cancel: function(){
						$('input[name=rebuildWatermarkScreenshots]').val( 0 );
						this.alertOpen = false;
					}
				}
			});
		}

	});
}(jQuery, _));