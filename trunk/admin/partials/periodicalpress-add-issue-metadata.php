<?php

/**
 * Display the custom metadata fields on the Add Issue form
 *
 * @since 1.0.0
 *
 * @package PeriodicalPress\Admin
 */

// Output a nonce field for security.
wp_nonce_field( 'set-issue-metadata', 'periodicalpress-set-issue-metadata-nonce' );
?>
<div class="form-field">
	<label for="pp-issue-status"><?php esc_html_e( 'Status', 'periodicalpress' ); ?></label>

	<select name="pp_issue_status" id="pp-issue-status">
		<?php
		// The list of possible Issue statuses (a subset of Core post statuses).
		$statuses = array(
			'publish' => 'Published',
			'draft' => 'Draft',
			'trash' => 'Trash'
		);

		// For new Issues, Draft is the default status.
		$selected_status = 'draft';

		// Output the possible statuses as dropdown options.
		foreach ( $statuses as $value => $label ) : ?>
			<?php
			$selected = ( $selected_status === $value )
				? ' selected="selected"'
				: '';
			?>
			<option value="<?php echo $value; ?>"<?php echo $selected; ?>>
				<?php esc_html_e( $label ); ?>
			</option>
		<?php endforeach; ?>
	</select>
</div>
