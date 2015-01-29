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
 * @since 3.0.0
 *
 * @param object $issue The Issue object.
 */
do_action( "add_meta_boxes_{$tax_name}", $issue );

// TODO: screen options
$screen_columns = 2;
?>

<div class="wrap">

<h2><?php echo esc_html( $tax->labels->edit_item ); ?></h2>

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
	<?php wp_nonce_field( 'edit-issue', 'periodicalpress-edit-issue-nonce' ); ?>
	<input type="hidden" name="action" value="edited" />
	<input type="hidden" name="screen" value="<?php echo $screen->id; ?>" />
	<input type="hidden" name="taxonomy" value="<?php echo $tax_name; ?>" />

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
					?>
					<label class="screen-reader-text" id="name-prompt-text" for="name">
						<?php echo $name_placeholder; ?>
					</label>

					<input name="name" size="30" value="<?php echo esc_attr( htmlspecialchars( $issue->name ) ); ?>" id="name" spellcheck="true" autocomplete="off" />

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
