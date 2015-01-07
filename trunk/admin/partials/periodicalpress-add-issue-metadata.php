<?php

/**
 * Display the custom metadata fields on the Add Issue form
 *
 * @since 1.0.0
 *
 * @package PeriodicalPress\Admin
 */

$domain = $this->plugin->get_plugin_name();
$tax_name = $this->plugin->get_taxonomy_name();

// Output a nonce field for security.
wp_nonce_field( 'set-issue-metadata', 'periodicalpress-set-issue-metadata-nonce' );
?>
<div class="form-field">
	<label for="pp-issue-date"><?php echo esc_html_x( 'Date', 'Edit Issue', $domain ); ?></label>
	<?php /* TODO: Inplement input type=month, input type=week, and a select for year - depending on Issues settings */ ?>
	<input type="date" name="pp_issue_date" id="pp-issue-date" class="pp-datepicker" value="<?php echo date( 'd/m/Y' ); ?>" />
</div>
<div class="form-field">
	<label for="pp-issue-title"><?php echo esc_html_x( 'Title', 'Edit Issue', $domain ); ?></label>
	<input type="text" name="pp_issue_title" id="pp-issue-title" size="40" />
</div>
<div class="form-field">
	<label for="pp-issue-status"><?php echo esc_html_x( 'Status', 'Edit Issue', $domain ); ?></label>
	<select name="pp_issue_status" id="pp-issue-status">
		<?php
		/** This filter is documented in admin/class-periodicalpress-admin.php */
		$statuses = apply_filters( "{$tax_name}_statuses", array() );

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
