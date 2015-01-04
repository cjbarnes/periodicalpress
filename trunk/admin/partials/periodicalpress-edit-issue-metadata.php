<?php

/**
 * Display the custom metadata fields on the Edit Issue page
 *
 * @since 1.0.0
 *
 * @package PeriodicalPress\Admin
 */

$domain = $this->plugin->get_plugin_name();
$tax_name = $this->plugin->get_taxonomy_name();

// Load all existing metadata for this Issue, for use within the form.
$metadata = get_metadata( $tax_name, $issue->term_id );

// Output a nonce field for security.
wp_nonce_field( 'set-issue-metadata', 'periodicalpress-set-issue-metadata-nonce' );
?>
<tr class="form-field">
	<th scope="row" valign="top">
		<label for="pp-issue-status"><?php esc_html_e( 'Status', $domain ); ?></label>
	</th>
	<td>
		<select name="pp_issue_status" id="pp-issue-status">
			<?php
			/*
			 * The list of possible Issue statuses (a subset of Core post
			 * statuses).
			 */
			$statuses = array(
				'publish' => 'Published',
				'draft' => 'Draft',
				'trash' => 'Trash'
			);

			/*
			 * Get the current status from the DB and check it's valid. If not,
			 * the default status - 'draft' - is used instead.
			 */
			$selected_status = $metadata['pp_issue_status'];
			if ( ! array_key_exists( $selected_status, $statuses ) ) {
				$selected_status = 'draft';
			}

			// Output the possible statuses as dropdown options.
			foreach ( $statuses as $value => $label ) :
				$selected = ( $selected_status === $value )
					? ' selected="selected"'
					: '';
				?>
				<option value="<?php echo $value; ?>"<?php echo $selected; ?>>
					<?php esc_html_e( $label ); ?>
				</option>
			<?php endforeach; ?>
		</select>
	</td>
</tr>
