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
// TODO:
switch ( $list_table->current_action() ) {

	case 'add-tag':

		break;

	case 'delete':

		break;

	case 'bulk_delete':

		break;

	case 'edit':

		break;

	case 'editedtag':

		break;

}

if ( ! $location && ! empty( $_REQUEST['_wp_http_referer'] ) ) {
	$location = remove_query_arg( array( '_wp_http_referer', '_wpnonce' ), wp_unslash( $_SERVER['REQUEST_URI'] ) );
}

if ( $location ) {
	if ( ! empty( $_REQUEST['paged'] ) ) {
		$location = add_query_arg( 'paged', (int) $_REQUEST['paged'], $location );
	}
	wp_redirect( $location );
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
	0 => '', // Unused. Messages start at index 1.
	1 => __( 'Issue added.' ),
	2 => __( 'Issue deleted.' ),
	3 => __( 'Issue updated.' ),
	4 => __( 'Issue not added.' ),
	5 => __( 'Issue not updated.' ),
	6 => __( 'Issues deleted.' )
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
	<div id="col-container">
		<div id="col-right">
			<div class="col-wrap">
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
			</div><!-- /col-wrap -->
		</div><!-- /col-right -->
		<div id="col-left">
			<div class="col-wrap">
				<?php
				if ( current_user_can( $tax->cap->edit_terms ) ) :
					/**
					 * Fires before the Add Issue form.
					 *
					 * The hook name includes the taxonomy name. By default, the
					 * actual hook to add_action to is:
					 *
					 *     pp_issue_pre_add_form
					 *
					 * @since 1.0.0
					 *
					 * @param string $tax_name The Issues taxonomy name.
					 */
					do_action( "{$tax_name}_pre_add_form", $tax_name );
					?>
					<div class="form-wrap">
						<h3><?php echo $tax->labels->add_new_item; ?></h3>
						<?php /* TODO: import Add Issue form partial. Probably include the div.form_wrap within the partial. */ ?>
					</div>
				<?php endif; ?>
			</div><!-- /col-wrap -->
		</div><!-- /col-left -->
	</div><!-- /col-container -->
</div><!-- /wrap -->
<?php if ( ! wp_is_mobile() ) : ?>
	<script type="text/javascript">
		try{document.forms.addtag['tag-name'].focus();}catch(e){}
	</script>
<?php endif; ?>
<?php
