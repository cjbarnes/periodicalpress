<?php

/**
 * Display the Edit Issue form
 *
 * Security for this form (i.e. checking capabilites), and saving, is handled by
 * the calling file, periodicalpress_edit_pp_issues.php.
 *
 * @since 1.0.0
 *
 * @package PeriodicalPress\Admin
 */

if ( ! $term_id || ! defined( 'ABSPATH' ) ) {
	exit;
}

$pp_common = PeriodicalPress_Common::get_instance( $this->plugin );

$screen = get_current_screen();

$tax_name = $this->plugin->get_taxonomy_name();
$tax = get_taxonomy( $tax_name );

// Load existing data for this Issue, for use within the form.
$issue = get_term( $term_id, $tax_name );
$issue_link = esc_url( get_term_link( $issue, $tax_name ) );

$status = $pp_common->get_issue_meta( $issue->term_id, 'pp_issue_status' );

/**
 * Fires after all hard-coded metaboxes have been added.
 *
 * @since 1.0.0
 *
 * @param object $issue The Issue object.
 */
do_action( "add_meta_boxes_{$tax_name}", $issue );

// TODO: screen options
$screen_columns = 2;
?>

<div class="wrap">

<h2>
	<?php echo esc_html( $tax->labels->edit_item ); ?>
	<?php if ( current_user_can( $tax->cap->edit_terms ) ) : ?>
		<a href="<?php echo admin_url( 'admin.php?page=pp_add_issue' ); ?>" class="add-new-h2"><?php echo esc_html_x( 'Add New', 'New Issue button', 'periodicalpress'); ?></a>
	<?php endif; ?>
</h2>

<form name="editpp_issue" id="editpp-issue" method="post" action="admin.php?page=pp_edit_issues" autocomplete="off"<?php
/**
 * Fires inside the Issue editor form tag.
 *
 * @since 1.0.0
 *
 * @param object $issue The Issue object.
 */
do_action( 'periodicalpress_issue_edit_form_tag', $issue );
?>>
	<?php wp_nonce_field( "update-tag_$term_id" ); ?>
	<input type="hidden" name="action" value="update" />
	<input type="hidden" name="screen" value="<?php echo $screen->id; ?>" />
	<input type="hidden" name="taxonomy" value="<?php echo $tax_name; ?>" />
	<input type="hidden" name="tag_id" value="<?php echo $term_id; ?>" />

	<?php
	/**
	 * Fires at the beginning of the Issue edit form.
	 *
	 * At this point, the required hidden fields and nonces have already been
	 * output.
	 *
	 * @since 1.0.0
	 *
	 * @param object $issue The Issue object.
	 */
	do_action( 'periodicalpress_issue_edit_form_top', $issue );
	?>

	<div id="issuestuff">
	<div id="issue-body" class="columns-<?php echo $screen_columns; ?>">
		<div id="issue-body-content">

			<!-- Issue Name -->
			<div id="namediv">
				<div id="namewrap">

					<?php
					/**
					 * Filter the name field placeholder text.
					 *
					 * @since 1.0.0
					 *
					 * @param string $text Placeholder text. Default 'Enter
					 *                     title here'.
					 * @param object $issue The Issue object.
					 */
					$name_placeholder = apply_filters( 'periodicalpress_issue_name_placeholder', __( 'Enter issue title here', 'periodicalpress' ), $issue );

					$disabled = ( 'title' !== get_option( 'pp_issue_naming' ) )
						? true
						: false;
					?>
					<label class="screen-reader-text" id="name-prompt-text" for="name">
						<?php echo $name_placeholder; ?>
					</label>
					<?php if ( $disabled ) : ?>
						<?php
						/*
						 * Translators:
						 * '%1$s' = the Issue taxonomy's singular name.
						 * '%2$s' = the unique part of the Issue name.
						 *
						 * This is only used in the admin area to demo how
						 * Issue name formats will appear in unmodified
						 * themes, so only localize the ordering if the
						 * Core themes localize it as well.
						 */
						$prefixed_issue_name = sprintf(
							_x( '%1$s: %2$s', 'Issue name format', 'periodicalpress' ),
							$tax->labels->singular_name,
							$issue->name
						);
						?>
						<input name="name" size="30" value="<?php echo esc_attr( htmlspecialchars( $prefixed_issue_name ) ); ?>" class="pp-issue-name" id="name" disabled="disabled" />
					<?php else : ?>
						<input name="name" size="30" value="<?php echo esc_attr( htmlspecialchars( $issue->name ) ); ?>" class="pp-issue-name" id="name" spellcheck="true" autocomplete="off" />
					<?php endif; ?>

				</div><!-- /#namewrap -->

				<?php
				/**
				 * Fires before the permalink field in the Issue edit form.
				 *
				 * @since 1.0.0
				 *
				 * @param object $issue The Issue.
				 */
				do_action( 'periodicalpress_issue_edit_form_before_permalink', $issue );
				?>
				<div class="inside">
					<?php if ( 'publish' === $status ) : ?>
						<!-- Permalink and View button -->
						<div id="edit-slug-box">
							<strong>
								<?php _e( 'Permalink:', 'periodicalpress' ); ?>
							</strong>
							<span id="sample-permalink">
								<?php echo $issue_link; ?>
							</span>
							<span id="view-issue-btn">
								<a href="<?php echo $issue_link; ?>" class="button button-small">
									<?php _e( 'View Issue', 'periodicalpress' ); ?>
								</a>
							</span>
						</div>
					<?php endif; ?>
				</div>
			</div><!-- /#namediv -->

			<?php
			/**
			 * Fires after the title field.
			 *
			 * @since 1.0.0
			 *
			 * @param object $issue The Issue object.
			 */
			do_action( 'periodicalpress_issue_edit_form_after_title', $issue );
			?>

		</div><!-- /#issue-body-content -->

		<div id="issuebox-container-1" class="issuebox-container">
			<?php
			/**
			 * Fires before meta boxes with 'side' context are output.
			 *
			 * The submitissue box is a meta box with 'side' context, so this
			 * hook fires just before it is output.
			 *
			 * @since 1.0.0
			 *
			 * @param object $issue The Issue object.
			 */
			do_action( 'periodicalpress_submitissue_box', $issue );

			do_meta_boxes( 'pp_issue', 'side', $issue );
			?>
		</div><!-- /#issuebox-container-1 -->

		<div id="issuebox-container-2" class="issuebox-container">

			<?php do_meta_boxes( 'pp_issue', 'normal', $issue ); ?>
			<?php do_meta_boxes( 'pp_issue', 'advanced', $issue ); ?>

		</div><!-- /#issuebox-container-2 -->

		<?php
		/**
		 * Fires after all meta box sections have been output, before the
		 * closing #issue-body div.
		 *
		 * @since 1.0.0
		 *
		 * @param object $issue The Issue object.
		 */
		do_action( 'periodicalpress_after_meta_boxes', $issue );
		?>

	</div><!-- /#issue-body -->

	<br class="clear" />
	</div><!-- /#issuestuff -->
</form>

</div><!-- /wrap -->
<?php
