/* global l10n */

(function( $ ) {
	'use strict';

	/**
	 * On DOM ready.
	 *
	 * @since 1.0.0
	 */
	$( function initPeriodicalPress() {

		/**
		 * Initialize datepickers with options.
		 *
		 * @see {@link http://api.jqueryui.com/datepicker/}
		 */
		$( '.pp-datepicker' ).datepicker({
			altFormat: 'yy-mm-dd',
			changeMonth: true,
			changeYear: true,
			currentText: l10n.datepickerCurrentText,
			dateFormat: l10n.datepickerDateFormat,
			firstDay: 1,
			hideIfNoPrevNext: true,
			isRTL: ( 'true' === l10n.isRTL ? true : false ),
			showButtonPanel: true
		});

	} );

})( jQuery );
