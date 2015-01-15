/* global inlineEditPost */

(function( $ ) {
	'use strict';

	/**
	 * On DOM ready, add to the Quick Edit preparation function so it can set
	 * the selected Issue and to prevent editing of Status.
	 *
	 * @since 1.0.0
	 */
	$( function () {
		// Store the original inline-editing method for later.
		var coreInlineEditMethod = inlineEditPost.edit;

		/**
		 * Set initial values of Quick Edit form when activated.
		 *
		 * Does the following:
		 * - Set the initial values specified in Core.
		 * - Set the Issues dropdown selected option.
		 * - Prevent user editing of Status.
		 *
		 * @since 1.0.0
		 *
		 * @param {(number|object)} post The current post's ID or post object.
		 */
		inlineEditPost.edit = function ppInlineEditMethod( id ) {

			// First call the original method.
			coreInlineEditMethod.apply( this, arguments );

			var postID = 0;
			if ( 'object' === typeof id ) {
				postID = parseInt( this.getId( id ), 10 );
			}

			if ( postID > 0 ) {
				var $editRow = $( '#edit-' + postID );
				var $postRow = $( '#post-' + postID );

				// Get the existing Issue data from the DOM.
				var $issues = $postRow.find( '.column-pp_issue [data-issue-id]' );

				// -1 selects the 'No issue' option.
				var issueID = -1;
				if ( $issues.length ) {
					issueID = parseInt( $issues.attr( 'data-issue-id' ), 10 );
				}

				// Select the correct Issue.
				$editRow.find( '#pp-issue' ).val( issueID );

				// Replace the Status field with a hidden field.
				var $statusField = $editRow.find( 'select[name=_status]' );
				var $status = $statusField.val() || 'publish';
				$statusField
					.parents('.inline-edit-group')
						.eq(0)
						.replaceWith( '<input type="hidden" name="_status" value="' + $status + '" />' )
						.remove();

			}


		};

	} );

})( jQuery );
