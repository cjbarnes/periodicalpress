<?php

/**
 * Display the Issues taxonomy terms administration screen.
 *
 * A heavily customized version of the Edit Tags Administration screen in Core.
 *
 * @since 1.0.0
 *
 * @package PeriodicalPress\Admin
 */

// Don't allow direct loading of this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * Load the taxonomy.
 */
$tax_name = $this->plugin->get_taxonomy_name();
$tax = get_taxonomy( $tax_name );
if ( ! is_object( $tax ) ) {
	exit();
}

$title = $tax->labels->name;

// Check security
if ( ! current_user_can( $tax->cap->assign_terms ) ) {
	exit();
}

/**
 * Include the list table class, which extends PeriodicalPress_List_Table (a
 * duplicate of Core's {@see WP_List_Table}).
 */
require_once $this->plugin->get_plugin_path() . 'admin/class-periodicalpress-issues-list-table.php';
$list_table = new PeriodicalPress_Issues_List_Table( 'admin.php?page=pp_edit_issues' );

$pagenum = $list_table->get_pagenum();

/**
 * Handle user actions from the list table form.
 */

$location = false;

switch ( $list_table->current_action() ) {

	case 'edit':

		/*
		 * Check the Issue ID has been correctly passed via URL. Nonce is not
		 * used, since we want to be able to link to this Edit Issue page from
		 * elsewhere.
		 */
		if ( ! isset( $_REQUEST['tag_id'] ) ) {
			break;
		}
		$term_id = (int) $_REQUEST['tag_id'];
		if ( ! $term_id ) {
			break;
		};

		// Check permissions.
		if ( ! current_user_can( $tax->cap->edit_terms ) ) {
			exit();
		}

		// If this Issue doesn't exist, show error message on List Table page.
		if ( ! get_term( $term_id, $tax_name ) ) {
			$location = 'admin.php?page=pp_edit_issues';
			$location = add_query_arg( 'message', 90, $location );
			break;
		}

		/**
		 * Show the Edit Issue form instead of the issues page.
		 */
		include $this->plugin->get_plugin_path() . 'admin/partials/periodicalpress-edit-issue.php';

		exit;

	case 'update':
	case 'delete':
	case 'publish':
	case 'unpublish':

		$action = $list_table->current_action();
		$location = 'admin.php?page=pp_edit_issues';

		// Check the Issue ID and nonce have been correctly passed via URL.
		if ( ! isset( $_REQUEST['tag_id'] ) ) {
			break;
		}
		$term_id = (int) $_REQUEST['tag_id'];
		check_admin_referer( "$action-tag_" . $term_id );

		// Check permissions.
		if ( 'delete' === $action ) {
			if ( ! current_user_can( $tax->cap->delete_terms ) ) {

				// Not Deleted error message.
				$msg_id = 6;
				$location = add_query_arg( 'message', $msg_id, $location );

				break;
			}

		} elseif ( 'update' === $action ) {
			if ( ! current_user_can( $tax->cap->edit_terms ) ) {

				// Not Updated error message.
				$msg_id = 5;
				$location = add_query_arg( 'message', $msg_id, $location );

				break;
			}

		} elseif ( ! current_user_can( $tax->cap->manage_terms ) ) {

			// Not Published/Unpublished error message.
			if ( 'publish' === $action ) {
				$msg_id = 87;
			} elseif ( 'unpublish' === $action ) {
				$msg_id = 89;
			} else {
				$msg_id = 5;
			}
			$location = add_query_arg( 'message', $msg_id, $location );

			break;
		}

		$pp_save_issues = PeriodicalPress_Save_Issues::get_instance( $this->plugin );

		/*
		 * Actually change the Issue in the database using the appropriate
		 * method on {@see PeriodicalPress_Save_Issues}, and add result message
		 * to the URL query string.
		 */
		if ( 'update' === $action ) {
			$result = $pp_save_issues->update_issue( $term_id, $_POST );
		} else {
			$do_it = $action . '_issue';
			$result = $pp_save_issues->$do_it( $term_id );
		}

		// Direct back to Edit Issue page on Edit success or failure.
		if ( 'update' === $action ) {

			// Direct back to the Edit Issue screen.
			$location = add_query_arg( 'action', 'edit', $location );
			$location = add_query_arg( 'term_id', $term_id, $location );

			if ( ! is_wp_error( $result ) && $result ) {
				$msg_id = 3;
			} else {
				$msg_id = 5;
			}

			$location = add_query_arg( 'message', $msg_id, $location );


		} else {

			/*
			 * Stay on this page, and determine which success/failure message
			 * should be displayed.
			 */
			if ( ! is_wp_error( $result ) && $result ) {
				switch ( $action ) {
					case 'delete':
						$msg_id = 2;
						break;
					case 'publish':
						$msg_id = 86;
						break;
					case 'unpublish':
						$msg_id = 88;
						break;
				}
			} else {
				switch ( $action ) {
					case 'delete':
						$msg_id = 82;
						break;
					case 'publish':
						$msg_id = 87;
						break;
					case 'unpublish':
						$msg_id = 89;
						break;
				}
			}

		}

		$location = add_query_arg( 'message', $msg_id, $location );

		break;

}

// If not redirecting, no need for referer or nonce.
if ( ! $location && ! empty( $_REQUEST['_wp_http_referer'] ) ) {
	$location = remove_query_arg( array( '_wp_http_referer', '_wpnonce' ), wp_unslash( $_SERVER['REQUEST_URI'] ) );
}

/**
 * Handle redirections. Uses JavaScript instead of wp_redirect() because output
 * has already started (and therefore headers can no longer be set).
 */
if ( $location ) {

	// Preserve place in pagination.
	if ( ! empty( $_REQUEST['paged'] ) ) {
		$location = add_query_arg( 'paged', (int) $_REQUEST['paged'], $location );
	}

	$location = wp_sanitize_redirect( $location );

	echo "<script>window.location='$location';</script>";
	echo "<noscript><a href='$location'>Click here to continue.</a></noscript>";

	exit;
}

// Add extra javascript just for list tables.
wp_enqueue_script( 'admin_tags' );

// Set up the List Table data and properties.
$list_table->prepare_items();

/*
 * End if the user is trying to see a higher page number than the total number
 * of possible pages.
 */
$total_pages = $list_table->get_pagination_arg( 'total_pages' );
if ( ( $pagenum > $total_pages ) && ( 0 < $total_pages ) ) {
	wp_redirect( add_query_arg( 'paged', $total_pages ) );
	exit;
}

// The term-updated messages.
$messages = array(
	0  => '', // Unused. Messages start at index 1.
	1  => __( 'Issue added.', 'periodicalpress' ),
	2  => __( 'Issue deleted.', 'periodicalpress' ),
	3  => __( 'Issue updated.', 'periodicalpress' ),
	4  => __( 'Issue not added.', 'periodicalpress' ),
	5  => __( 'Issue not updated.', 'periodicalpress' ),
	6  => __( 'Issues deleted.', 'periodicalpress' ),
	// Plugin messages start here.
	82 => __( 'Issue not deleted.', 'periodicalpress' ),
	86 => __( 'Issue published.', 'periodicalpress' ),
	87 => __( 'Issue not published.', 'periodicalpress' ),
	88 => __( 'Issue unpublished.', 'periodicalpress' ),
	89 => __( 'Issue not unpublished.', 'periodicalpress' ),
	90 => __( 'You attempted to edit an item that doesn&#8217;t exist. Perhaps it was deleted?', 'periodicalpress' ),
);

/**
 * Filter the messages displayed when an Issue is updated.
 *
 * @since 1.0.0
 *
 * @param array $messages The messages to be displayed.
 */
$messages = apply_filters( 'periodicalpress_issue_updated_messages', $messages );

// Find the correct term-updated message.
$message = false;
if ( isset( $_REQUEST['message'] )
	&& ( $msg = (int) $_REQUEST['message'] )
	&& isset( $messages[ $msg ] ) ) {
	$message = $messages[ $msg ];
}
?>
<div class="wrap nosubsub">
	<h2><?php echo esc_html( $title ); ?></h2>
	<?php if ( $message ) : ?>
		<div id="message" class="updated">
			<p><?php echo $message; ?></p>
		</div>
		<?php $_SERVER['REQUEST_URI'] = remove_query_arg( array( 'message' ), $_SERVER['REQUEST_URI'] ); ?>
	<?php endif; ?>
	<div id="ajax-response"></div>
	<br class="clear" />
	<form id="post-filter" action="" method="post">
		<input type="hidden" name="taxonomy" value="<?php echo esc_attr($tax_name); ?>" />
		<input type="hidden" name="post_type" value="post" />
		<?php
		/*
		 * Output the list table.
		 */
		$list_table->display();
		?>
		<br class="clear" />
	</form>
	<?php
	/**
	 * Fires after the Issues list table.
	 *
	 * The hook name includes the taxonomy name. By default, the
	 * actual hook to add_action to is:
	 *
	 *     after-pp_issue-table
	 *
	 * @since 1.0.0
	 *
	 * @param string $tax_name The Issues taxonomy name.
	 */
	do_action( "after-{$tax_name}-table", $tax_name );
	?>
</div><!-- /wrap -->
<?php
