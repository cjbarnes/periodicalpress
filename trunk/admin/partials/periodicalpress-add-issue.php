<?php

/**
 * Display the Add New Issue administration screen.
 *
 * @since 1.0.0
 *
 * @package PeriodicalPress\Admin
 */

// Don't allow direct loading of this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$pp_common = PeriodicalPress_Common::get_instance( $this->plugin );

$screen = get_current_screen();

$tax_name = $this->plugin->get_taxonomy_name();
$tax = get_taxonomy( $tax_name );

/**
 * Fires after all hard-coded metaboxes have been added.
 *
 * @since 1.0.0
 */
do_action( "add_meta_boxes_{$tax_name}_new" );

// TODO: screen options
$screen_columns = 2;
?>

<div class="wrap">

<h2><?php echo esc_html( $tax->labels->add_new_item ); ?></h2>

<form name="editpp_issue" id="editpp-issue" method="post" action="admin.php?page=pp_edit_issues" autocomplete="off"<?php
/**
 * Fires inside the Issue editor form tag.
 *
 * @since 1.0.0
 */
do_action( 'periodicalpress_issue_edit_form_tag_new' );
?>>
	<?php wp_nonce_field( "new-tag" ); ?>
	<input type="hidden" name="action" value="new" />
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
	 */
	do_action( 'periodicalpress_issue_edit_form_top_new' );
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
					 */
					$name_placeholder = apply_filters( 'periodicalpress_issue_name_placeholder_new', __( 'Enter issue title here', 'periodicalpress' ) );

					$disabled = ( 'title' !== get_option( 'pp_issue_naming' ) )
						? true
						: false;
					?>
					<label class="screen-reader-text" id="name-prompt-text" for="name">
						<?php echo $name_placeholder; ?>
					</label>
					<?php if ( $disabled ) : ?>
						<input name="name" size="30" value="<?php esc_attr_e( 'New Issue', 'periodicalpress' ); ?>" class="pp-issue-name" id="name" disabled="disabled" />
					<?php else : ?>
						<input name="name" size="30" value="" class="pp-issue-name" id="name" spellcheck="true" autocomplete="off" />
					<?php endif; ?>

				</div><!-- /#namewrap -->

			</div><!-- /#namediv -->

			<?php
			/**
			 * Fires after the title field.
			 *
			 * @since 1.0.0
			 */
			do_action( 'periodicalpress_issue_edit_form_after_title_new' );
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
			 */
			do_action( 'periodicalpress_submitissue_box_new' );

			do_meta_boxes( 'pp_issue', 'side', 0 );
			?>
		</div><!-- /#issuebox-container-1 -->

		<div id="issuebox-container-2" class="issuebox-container">

			<?php do_meta_boxes( 'pp_issue', 'normal', 0 ); ?>
			<?php do_meta_boxes( 'pp_issue', 'advanced', 0 ); ?>

		</div><!-- /#issuebox-container-2 -->

		<?php
		/**
		 * Fires after all meta box sections have been output, before the
		 * closing #issue-body div.
		 *
		 * @since 1.0.0
		 */
		do_action( 'periodicalpress_after_meta_boxes_new' );
		?>

	</div><!-- /#issue-body -->

	<br class="clear" />
	</div><!-- /#issuestuff -->
</form>

</div><!-- /wrap -->
<?php




