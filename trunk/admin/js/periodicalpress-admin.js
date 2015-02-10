/* global l10n */

( function( $ ) {
'use strict';

/**
 * On DOM ready.
 *
 * @since 1.0.0
 */
$( function initPeriodicalPress() {

	( function initPostsOrdering() {

		var $postsdiv = $( '#pp_issue_postsdiv' );
		if ( $postsdiv.length ) {

			// Replace the ordering inputs with move up/down links.
			var $orderInputs = $( '#pp_issue_postsdiv .issue-posts-order' );
			$orderInputs.hide();
			$orderInputs.after( '<span>Move:&nbsp;</span> <a class="issue-posts-updown issue-posts-up" data-direction="up" title="Move up"><span class="dashicons dashicons-arrow-up-alt2"></span></a> <span class="screen-reader-text">|</span> <a class="issue-posts-updown issue-posts-down" data-direction="down" title="Move down"><span class="dashicons dashicons-arrow-down-alt2"></span></a>' );

			// Hook up the Up/Down click event listener.
			$postsdiv.on( 'click', '.issue-posts-updown', function swapPosts( e ) {

				var $link = $( this );
				var $direction = $link.attr( 'data-direction' );
				var $row = $link.parents( '.issue-post' ).eq(0);

				// Move the row itself in the DOM.
				if ( 'up' === $direction ) {
					var $adjacentRow = $row.prev( '.issue-post' );
					if ( $adjacentRow.length ) {
						$adjacentRow.before( $row );
					}
				} else {
					var $adjacentRow = $row.next( '.issue-post' );
					if ( $adjacentRow.length ) {
						$adjacentRow.after( $row );
					}
				}

				// Update values of the order form fields for *all* posts.
				$( '#pp_issue_postsdiv .issue-posts-order' ).each( function updateOrderInput( i ) {
					$( this ).val( i + 1 );
				} );

			} );

		}

	} )();



} );

} )( jQuery );
