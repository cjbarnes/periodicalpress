/* global inlineEditPost */
/* global l10n */

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
				var $statusField = $( 'select[name=_status]' );
				var $issueField = $editRow.find( '#pp-issue' );
				var $issueLink = $postRow.find( '.column-pp_issue [data-issue-id]' );

				if ( $postRow.hasClass( 'status-publish' ) ) {

					// Add back the 'Published' Status option when the current
					// post is already published, to prevent JS save errors due
					// to the missing data.
					$statusField.prepend( '<option value="publish">' + l10n.publishStatusName + '</option>' );

					// Hide the Issue field, and show a simple link instead.
					$issueLink.clone().prependTo( '.pp-issue-readonly' );
					$issueField.hide();


				} else {

					// Re-remove 'Published' Status option for not-yet-published
					// posts.
					$statusField.find( 'option[value=publish]' ).remove();

					// -1 selects the 'No issue' option in the Issues dropdown.
					var issueID = -1;
					if ( $issueLink.length ) {
						issueID = parseInt( $issueLink.attr( 'data-issue-id' ), 10 );
					}

					// Select the correct Issue and display.
					$issueField.val( issueID ).show();

					// Hide the read-only Issue link.
					$( '.pp-issue-readonly' ).empty();

				}

			}


		};

	} );

})( jQuery );
