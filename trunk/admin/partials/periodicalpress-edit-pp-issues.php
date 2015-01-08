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

$domain = $this->plugin->get_plugin_name();

/*
 * Load the taxonomy.
 */
$tax_name = $this->plugin->get_taxonomy_name();
$tax = get_taxonomy( $tax_name );
if ( ! is_object( $tax ) ) {
	wp_die( __( 'Cannot find the Issues taxonomy', $domain ) );
}

$title = $tax->labels->name;

// Check security
if ( ! current_user_can( $tax->cap->manage_terms ) ) {
	wp_die( __( 'Cheatin&#8217; uh?' ), 403 );
}

/**
 * Include the list table class, which extends PeriodicalPress_List_Table (a
 * duplicate of Core's {@see WP_List_Table}).
 */
require_once $this->plugin->get_plugin_path() . 'admin/class-periodicalpress-issues-list-table.php';
$list_table = new PeriodicalPress_Issues_List_Table();

$pagenum = $list_table->get_pagenum();

/**
 * Handle redirects caused by user actions.
 */

$location = false;

switch ( $list_table->current_action() ) {

	case 'add-tag':

		break;

	case 'delete':
		$location = 'admin.php?page=pp_edit_issues';

		// Check the Issue ID and nonce have been correctly passed via URL.
		if ( ! isset( $_REQUEST['tag_id'] ) ) {
			break;
		}
		$term_id = (int) $_REQUEST['tag_id'];
		check_admin_referer( 'delete-tag_' . $term_id );

		if ( ! current_user_can( $tax->cap->delete_terms ) ) {
			wp_die( __( 'Cheatin&#8217; uh?' ), 403 );
		}

		/*
		 * Actually delete the Issue from the database using
		 * {@see PeriodicalPressAdmin::delete_issue()}, and add result message
		 * to the URL query string.
		 */
		$deletion = $this->delete_issue( $term_id );
		write_log( $deletion );
		if ( ! is_wp_error( $deletion ) && $deletion ) {
			$msg_id = 2;
		} else {
			$msg_id = 82;
		}
		$location = add_query_arg( 'message', $msg_id, $location );

		break;

	case 'bulk_delete':

		break;

	case 'edit':

		break;

	case 'editedtag':

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
if ( current_user_can( $tax->cap->edit_terms ) ) {
	wp_enqueue_script( 'inline-edit-tax' );
}

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

if ( ! current_user_can( $tax->cap->edit_terms ) ) {
	wp_die( __( 'You are not allowed to edit Issues.', $domain ) );
}

// The term-updated messages.
$messages = array(
	0  => '', // Unused. Messages start at index 1.
	1  => __( 'Issue added.' ),
	2  => __( 'Issue deleted.' ),
	3  => __( 'Issue updated.' ),
	4  => __( 'Issue not added.' ),
	5  => __( 'Issue not updated.' ),
	6  => __( 'Issues deleted.' ),
	82 => __( 'Issue not deleted.' ) // Plugin messages start at index 81.
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
	<h2>
		<?php echo esc_html( $title ); ?>
		<?php if ( ! empty( $_REQUEST['s'] ) ) : ?>
			<span class="subtitle"><?php printf( __( 'Search results for &#8220;%s&#8221;' ), esc_html( wp_unslash( $_REQUEST['s'] ) ) ); ?></span>
		<?php endif; ?>
	</h2>
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
<?php if ( ! wp_is_mobile() ) : ?>
	<script type="text/javascript">
		try{document.forms.addtag['tag-name'].focus();}catch(e){}
	</script>
<?php endif; ?>
<?php
