<?php

/**
 * Display the Issue Publish/Preview editing box
 *
 * Used on the Edit Issue screen.
 *
 * @since 1.0.0
 *
 * @package PeriodicalPress\Admin
 */

/*
 * Don't allow direct loading of this file, and make sure we have an Issue to
 * work with.
 */
if ( ! defined( 'ABSPATH' ) || ! isset( $issue ) ) {
	exit;
}

$domain = $this->plugin->get_plugin_name();
$tax_name = $this->plugin->get_taxonomy_name();
$tax = get_taxonomy( $tax_name );

// Get status and its display name.
if ( empty( $status ) ) {
	$pp_common = PeriodicalPress_Common::get_instance( $this->plugin );
	$status = $pp_common->get_issue_meta( $issue->term_id, 'pp_issue_status' );
}
/** This filter is documented in admin/class-periodicalpress-admin.php */
$allowed_statuses = apply_filters( "{$tax_name}_statuses", array() );
$status_name = ( isset( $allowed_statuses[ $status ] ) )
	? esc_html( $allowed_statuses[ $status ] )
	: '';

?>

<div class="submitbox" id="submitissue">

<div id="minor-publishing">

<div id="minor-publishing-actions">

	<!-- Save Draft button -->
	<div id="save-action">
		<?php if ( 'publish' != $status ) : ?>
			<input type="submit" name="save" id="save-post" value="<?php echo esc_attr_x( 'Save Draft', 'Edit Issue', $domain ); ?>" class="button" />
		<?php endif; ?>
		<span class="spinner"></span>
	</div>

	<!-- Preview button -->
	<div id="preview-action">
		<?php
		$preview_link = esc_url( get_term_link( $issue, $tax_name ) );

		if ( 'publish' === $status ) {
			$preview_button = _x( 'Preview Changes', 'Edit Issue', $domain );
		} else {
			$preview_link = add_query_arg( 'preview', 'true', $preview_link );
			/**
			 * Filter the URI of an Issue preview in the Edit Issue submit box.
			 *
			 * @since 1.0.0
			 *
			 * @param string $preview_link URL the user will be directed to for
			 *                             an Issue preview.
			 * @param object $issue The Issue object.
			 */
			$preview_link = esc_url( apply_filters( 'periodicalpress_preview_issue_link', $preview_link, $issue ) );
			$preview_button = _x( 'Preview', 'Edit Issue', $domain );
		}
		?>
		<a class="preview button" href="<?php echo $preview_link; ?>" target="wp-preview-<?php echo (int) $issue->term_id; ?>" id="issue-preview"><?php echo $preview_button; ?></a>
		<input type="hidden" name="wp-preview" id="wp-preview" value="" />
	</div>

	<div class="clear"></div>
</div><!-- /minor-publishing-actions -->

<div id="misc-publishing-actions">

	<!-- Issue Status -->
	<div class="misc-pub-section misc-pub-issue-status">
		<label class="issue-status-label">
			<?php echo _x( 'Status:', 'Edit Issue', $domain ); ?>
		</label>
		<strong id="issue-status-display">
			<?php
			$current_issue = (int) get_option( 'pp_current_issue', 0 );
			if ( $current_issue === $issue->term_id ) {
				// Translators: %s = Issue Status display name.
				printf( _x( '%s (Current Issue)', 'Edit Issue', $domain ), $status_name );
			} else {
				echo $status_name;
			}
			?>
		</strong>
	</div>

	<!-- Issue Posts count -->
	<div class="misc-pub-section misc-pub-issue-posts">
		<label class="issue-posts-label">
			<?php echo _x( 'Posts:', 'Edit Issue', $domain ); ?>
		</label>
		<strong id="issue-posts-display">
			<?php echo (int) $issue->count; ?>
		</strong>
		<?php
		// Assemble link to the Posts list table, filtered for this Issue.
		$tax_query = $tax->query_var;
		$term = $issue->slug;
		?>
		<a href="<?php echo "edit.php?$tax_query=$term"; ?>">
			<span aria-hidden="true">View</span>
			<span class="screen-reader-text">View Posts</span>
		</a>
	</div>

	<?php
	/**
	 * Fires at the end of the 'minor' (top) section of the Publish metabox on
	 * the Edit Issue page.
	 *
	 * @since 1.0.0
	 */
	do_action( 'periodicalpress_issue_submitbox_misc_actions' );
	?>
</div><!-- /misc-publishing-actions -->

<div class="clear"></div>

</div><!-- /minor-publishing -->

<div id="major-publishing-actions">
	<?php
	/**
	 * Fires at the beginning of the publishing actions section of the Edit
	 * Issue - Publish metabox.
	 *
	 * @since 1.0.0
	 */
	do_action( 'periodicalpress_issue_submitbox_start' );
	?>

	<!-- Unpublish/Delete link -->
	<div id="delete-action">
		<?php if ( 'publish' === $status ) : ?>
			<!-- Unpublish link -->
			<?php
			// TODO: Unpublish link.
			$unpublish_link = '';
			?>
			<a class="submitdelete unpublish" href="<?php echo esc_url( $unpublish_link ); ?>">
				<?php echo _x( 'Unpublish Issue', 'Edit Issue', $domain ); ?>
			</a>
		<?php else: ?>
			<!-- Delete link -->
			<?php if ( current_user_can( $tax->cap->delete_terms ) ) : ?>
				<?php
				$delete_url = wp_nonce_url( admin_url( 'admin.php?page=pp_edit_issues' ) . "&amp;action=delete&amp;tag_id={$issue->term_id}&amp;delete-tag_{$issue->term_id}", "delete-tag_{$issue->term_id}" );
				?>
				<a class="submitdelete deletion" href="<?php echo $delete_url; ?>">
					<?php echo _x( 'Delete Permanently', 'Edit Issue', $domain ); ?>
				</a>
			<?php endif; ?>
		<?php endif; ?>
	</div>

	<!-- Publish/Update button -->
	<div id="publishing-action">
		<span class="spinner"></span>
		<?php if ( ( 'publish' !== $status) || ( 0 === $issue->term_id ) ) : ?>
			<!-- Publish button -->
			<?php
			if ( current_user_can( $tax->cap->manage_terms )
			&& ( 0 < $issue->count ) ) :
			?>
				<input name="original_publish" type="hidden" id="original_publish" value="<?php echo esc_attr_x( 'Publish', 'Edit Issue', $domain ) ?>" />
				<?php submit_button( _x( 'Publish', 'Edit Issue', $domain ), 'primary button-large', 'publish', false, array( 'accesskey' => 'p' ) ); ?>
			<?php endif; ?>
		<?php else: ?>
			<!-- Update button -->
			<input name="original_publish" type="hidden" id="original_publish" value="<?php echo esc_attr_x( 'Update', 'Edit Issue', $domain ) ?>" />
			<input name="save" type="submit" class="button button-primary button-large" id="publish" accesskey="p" value="<?php echo esc_attr_x( 'Update', 'Edit Issue', $domain ); ?>" />
		<?php endif; ?>
	</div>

	<div class="clear"></div>
</div>

</div><!-- /submitissue -->

<?php

// TODO: Set Current Issue option. Unpublish, then Delete permanently.
