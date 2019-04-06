/**
 *
 * ips.submit.linkedScreenshots.js - Controller to handle linked screenshots
 *
 */
;( function($, _, undefined){
	&quot;use strict&quot;;

	ips.controller.register('bitracker.front.submit.linkedScreenshots', {

		initialize: function () {
			this.on( 'click', '[data-action=&quot;addField&quot;]', this.addFieldButton );
			this.on( 'click', '[data-action=&quot;removeField&quot;]', this.removeField );
			this.setup();
		},

		/**
		 * Setup method
		 *
		 * @returns 	{void}
		 */
		setup: function () {
			var initialValues = $.parseJSON( $(this.scope).attr('data-initialValue') );

			if( initialValues == null )
			{
				return;
			}

			var i;
			for ( i in initialValues.values ) {
				this.addField( i, initialValues.values[i], i == initialValues.default );
			}
		},
		
		/**
		 * Add a field
		 *
		 * @returns 	{void}
		 */
		addField: function ( id, value, isDefault ) {
			$(this.scope).find('[data-role=&quot;fieldsArea&quot;]').append( ips.templates.render( 'bitracker.submit.linkedScreenshot', {
				'name': $(this.scope).attr('data-name'),
				'id': id,
				'value': value,
				'extra': isDefault ? 'checked' : ''
			}) );
		},
		
		/**
		 * Remove a field
		 *
		 * @returns 	{void}
		 */
		removeField: function ( e ) {
			e.preventDefault();
			$(e.currentTarget).closest('li').remove();
		},
		
		/**
		 * Respond to add button
		 *
		 * @returns 	{void}
		 */
		addFieldButton: function () {
			this.addField( 'linked_' + new Date().getTime(), '', false );
		}
	});
}(jQuery, _));