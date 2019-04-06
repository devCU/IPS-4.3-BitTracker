/**
 *
 * ips.submit.main.js - Submit controller
 *
 */
;( function($, _, undefined){
	"use strict";

	ips.controller.register('bitracker.front.submit.main', {

		_progressbarTimeout: null,
		_requireScreenshots: false,
		_bulkUpload: false,
		_ui: {},
		_hiddenUploader: false,
		_overriddenUploader: false,

		initialize: function () {
			this.on( 'uploadedCountChanged', this.uploadCounter );
			this.on( 'uploadProgress', this.uploadProgress );
			this.on( 'fileAdded', this.fileAdded );
			this.on( 'fileDeleted', this.fileDeleted );
			this.on( 'click', '[data-action="confirmUrls"]', this.confirmURLs );
			this.on( 'click', '[data-action="confirmImports"]', this.confirmImports );
			this.on( 'click', '[data-action="confirmScreenshotUrls"]', this.confirmScreenshots );
			this.on( 'click', '[data-action="uploadMore"]', this.uploadMore );

			this.setup();
		},

		/**
		 * Setup method - hides necessary sections of the form
		 *
		 * @returns 	{void}
		 */
		setup: function () {
			var self = this;

			if( this.scope.attr('data-screenshotsReq') ){
				this._requireScreenshots = true;
			}

			if( this.scope.attr('data-bulkUpload') ){
				this._bulkUpload = true;
			}

			this._ui = {
				progressBar: this.scope.find('#elBitrackerSubmit_progress'),
				screenshots: this.scope.find('#elBitrackerSubmit_screenshots'),
				fileInfo: this.scope.find('#elBitrackerSubmit_otherinfo')
			};


			var hideProgressBar = function () {
				if( !_.isUndefined( self._ui.progressBar.attr('data-ipsSticky') ) ){
					self.on( 'stickyInit', function () {
						self._ui.progressBar.hide();
					});
				} else {
					self._ui.progressBar.hide();
				}
			};

			// Are there any existing files?
			if( !this._hasExistingFiles() ){
				hideProgressBar();
				this._ui.screenshots.hide();
				this._ui.fileInfo.hide();
				this.scope.find('[data-role="submitForm"]').prop( 'disabled', true );
			} else {
				if( !this.scope.find('input[name^="files_existing"]').length ){
					hideProgressBar();
				} else {
					this.scope.find('#elBitrackerSubmit_uploader .ipsAttachment_dropZone').hide();
					this.scope.find('#elBitrackerSubmit_uploader [data-action="uploadMore"]').show();
					this._hiddenUploader = true;
				}

				if( !this._hasExistingScreenshots() && this._requireScreenshots ){
					this._ui.fileInfo.hide();
					this.scope.find('[data-role="submitForm"]').prop( 'disabled', true );
				}
			}
		},

		/**
		 * Responds to clicking Confirm in the URLs popup. If there's URLs, we show the next steps
		 *
		 * @param 		{event} 	e 		Event object
		 * @returns 	{void}
		 */
		confirmURLs: function (e) {
			e.preventDefault();

			var gotURLs = this._confirmMenu( 'url_files', 'elURLFiles' );

			if( gotURLs ){
				this._doneUploadStep();
			}
		},

		/**
		 * Responds to clicking Confirm in the Import Files popup. If there's files, we show the next steps
		 *
		 * @param 		{event} 	e 		Event object
		 * @returns 	{void}
		 */
		confirmImports: function (e) {
			e.preventDefault();

			var gotImports = this._confirmMenu( 'import_files', 'elImportFiles' );

			if( gotImports ){
				this._doneUploadStep();
			}
		},

		/**
		 * Responds to clicking Confirm in the Import Files popup. If there's files, we show the next steps
		 *
		 * @param 		{event} 	e 		Event object
		 * @returns 	{void}
		 */
		confirmScreenshots: function (e) {
			e.preventDefault();

			var gotURLs = this._confirmMenu( 'url_screenshots', 'elURLScreenshots' );

			if( gotURLs ){
				this._doneScreenshotStep();
			}
		},

		/**
		 * Responds to clicking the 'Upload more files' button
		 *
		 * @param 		{event} 	e 		Event object
		 * @returns 	{void}
		 */
		uploadMore: function (e) {
			e.preventDefault();

			this.scope.find('#elBitrackerSubmit_uploader .ipsAttachment_dropZone').show();
			this.scope.find('#elBitrackerSubmit_uploader [data-action="uploadMore"]').hide();
			this._hiddenUploader = false;
			this._overriddenUploader = true;
		},

		/**
		 * Responds to fileAdded event from the uploader to show the screenshot and/or file information sections
		 *
		 * @param 		{event} 	e 		Event object
		 * @param 		{object} 	data 	Event data object from the uploader
		 * @returns 	{void}
		 */
		fileAdded: function (e, data) {
			if( !this._bulkUpload ){
				if( data.uploader == 'files' ){
					this._doneUploadStep();
				} else if( data.uploader == 'screenshots' ){
					this._doneScreenshotStep();
				}
			} else {
				this.scope.find('[data-role="submitForm"]').prop( 'disabled', false );
			}
		},

		/**
		 * Responds to fileDeleted event from uploader
		 *
		 * @param 		{event} 	e 		Event object
		 * @param 		{object} 	data 	Event data object from the uploader
		 * @returns 	{void}
		 */
		fileDeleted: function (e, data) {
			if( data.uploader != 'files' ){
				return;
			}

			if( data.count === 0 ){
				this.scope.find( '#elBitrackerSubmit_progress .ipsProgressBar_progress')
					.attr('data-progress', '0%')
					.css( {
						width: '0%'
					});

				this.scope.find('#elBitrackerSubmit_uploader .ipsAttachment_dropZone').show();
				this.scope.find('#elBitrackerSubmit_uploader [data-action="uploadMore"]').hide();
				this._hiddenUploader = false;
				this._overriddenUploader = true;
			}
		},

		/**
		 * Responds to uploadCountChanged event from uploader
		 *
		 * @param 		{event} 	e 		Event object
		 * @param 		{object} 	data 	Event data object from the uploader
		 * @returns 	{void}
		 */
		uploadCounter: function (e, data) {
			if( data.uploader != 'files' ){
				return;
			}
		},

		/**
		 * Responds to uploadProgress event from uploader, which we use to adjust the main progressbar
		 *
		 * @param 		{event} 	e 		Event object
		 * @param 		{object} 	data 	Event data object from the uploader
		 * @returns 	{void}
		 */
		uploadProgress: function (e, data) {
			if( data.uploader != 'files' ){
				return;
			}

			var self = this;
			this._showProgress();

			this.scope.find( '#elBitrackerSubmit_progress .ipsProgressBar_progress')
				.attr('data-progress', data.percent + '%')
				.css( {
					width: data.percent + '%'
				});

			if( data.percent === 100 && !this._progressbarTimeout ){
				this._progressbarTimeout = setTimeout( function () {
					self._ui.progressBar.find('.ipsProgressBar').removeClass('ipsProgressBar_animated');
					self._progressbarTimeout = null;
				}, 300 );
			}
		},

		/**
		 * Handles the menus for specifying urls or file paths. If there's values entered, we show a count balloon.
		 *
		 * @param 		{string} 	inputName 		Name of the form fields containing the values
		 * @param 		{string} 	elemID 			ID of the menu trigger button (no #)
		 * @returns 	{boolean}	Returns true if some urls/paths have been entered
		 */
		_confirmMenu: function (inputName, elemID) {
			// Do we have a value?
			var length = 0;
			var invalid = 0;

			this.scope.find('input[name^="' + inputName + '"]').each( function () {
				if( $.trim( $( this ).val() ) ){
					length++;
				} 

				if( !_.isUndefined( this.checkValidity ) && !this.checkValidity() ){
					invalid++;
				}
			});

			if( !invalid ){
				this.scope.find( '#' + elemID ).trigger('closeMenu');
			}

			this.scope.find('#' + elemID + ' [data-role="fileCount"]').text( length );

			if( length ){
				this.scope.find('#' + elemID + ' [data-role="fileCount"]').show();
				return true;
			} else {
				this.scope.find('#' + elemID + ' [data-role="fileCount"]').hide();
				return false;
			}
		},

		/**
		 * Returns true if there are existing files on the form (from upload, url or file path)
		 *
		 * @returns 	{boolean}	Returns true if there are existing files
		 */
		_hasExistingFiles: function () {
			if( this.scope.find('input[name^="files_existing"]').length ){
				return true;
			}

			var hasURL = [];
			var hasImport = [];

			if( this.scope.find('input[name^="url_files"]').length ){
				hasURL = _.filter( this.scope.find('input[name^="url_files"]'), function (item) {
					if( $.trim( $( item ).val() ) != '' ){
						return true;
					}

					return false;
				});
			}

			if( this.scope.find('input[name^="import_files"]').length ){
				hasImport = _.filter( this.scope.find('input[name^="import_files"]'), function (item) {
					if( $.trim( $( item ).val() ) != '' ){
						return true;
					}

					return false;
				});
			}

			if( hasURL.length || hasImport.length ){
				return true;
			}

			return false;
		},

		/**
		 * Returns true if there are existing screenshots on the form (from upload or url)
		 *
		 * @returns 	{boolean}	Returns true if there are existing screenshots
		 */
		_hasExistingScreenshots: function () {
			if( this.scope.find('input[name^="screenshots_existing"]').length ){
				return true;
			}

			var hasURL = [];

			if( this.scope.find('input[name^="url_screenshots"]').length ){
				hasURL = _.filter( this.scope.find('input[name^="url_screenshots"]'), function (item) {
					if( $.trim( $( item ).val() ) != '' ){
						return true;
					}

					return false;
				});

				if( hasURL.length ){
					return true;
				}
			}

			return false;
		},

		/**
		 * Shows the next relevant steps of the upload process
		 *
		 * @returns 	{void}
		 */
		_doneUploadStep: function () {
			var self = this;

			// Show screenshot step
			if( this._ui.screenshots.length && !this._ui.screenshots.is(':visible') ){
				ips.utils.anim.go( 'fadeIn', this._ui.screenshots )
					.done( function () {
						$( document ).trigger('contentChange', [ self._ui.screenshots ] );
					});
			}

			if( !this._requireScreenshots && !this._ui.fileInfo.is(':visible') ){
				ips.utils.anim.go( 'fadeIn', this._ui.fileInfo )
					.done( function () {
						$( document ).trigger('contentChange', [ self._ui.fileInfo ] );
					});
			}
			if( !this._requireScreenshots )
			{
				this.scope.find('[data-role="submitForm"]').prop( 'disabled', false );
			}
		},

		/**
		 * Shows the next relevant steps of the upload process
		 *
		 * @returns 	{void}
		 */
		_doneScreenshotStep: function () {
			var self = this;
			
			ips.utils.anim.go( 'fadeIn', this._ui.fileInfo )
				.done( function () {
					$( document ).trigger('contentChange', [ self._ui.fileInfo ] );
				});

			this.scope.find('[data-role="submitForm"]').prop( 'disabled', false );
		},

		/**
		 * Shows the progress bar and hides the dropzone
		 *
		 * @returns 	{void}
		 */
		_showProgress: function () {
			if( !this._hiddenUploader && !this._overriddenUploader ){
				this._ui.progressBar.show().find('.ipsProgressBar').addClass('ipsProgressBar_animated');
				this.scope.find('#elBitrackerSubmit_uploader .ipsAttachment_dropZone').hide();
				this.scope.find('#elBitrackerSubmit_uploader [data-action="uploadMore"]').show();
				this._hiddenUploader = true;
			}
		}
	});
}(jQuery, _));