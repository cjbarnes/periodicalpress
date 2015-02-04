<?php

/**
 * Display the Plugin Settings page.
 *
 * @since 1.0.0
 *
 * @package PeriodicalPress\Admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$pp_common = PeriodicalPress_Common::get_instance( $this->plugin );
$tax_name = $this->plugin->get_taxonomy_name();

?>
<div class="wrap">
<h2><?php echo sprintf( __( '%s Settings', 'periodicalpress' ), 'PeriodicalPress' ); ?></h2>

<form method="post" action="<?php echo admin_url( 'admin.php?page=periodicalpress_settings' ); ?>">

	<?php wp_nonce_field( 'save-plugin-settings', 'periodicalpress-settings-nonce' ); ?>
	<input type="hidden" name="action" value="save-plugin-settings" />

	<table class="form-table">
	<tbody>

		<!-- Current Issue (dropdown of published issues) -->
		<tr>
			<th scope="row">
				<label for="current-issue"><?php esc_html_e( 'Current Issue', 'periodicalpress' ); ?></label>
			</th>
			<td>
				<?php
				$args = array(
					'hide_unpublished' => 1,
					'name'             => 'pp_current_issue',
					'id'               => 'current-issue',
					'selected'         => get_option( 'pp_current_issue', 0 )
				);
				$pp_common->dropdown_issues( $args );
				?>
				<p class="description"><?php esc_html_e( 'The Current Issue is the issue featured on your site homepage. Usually it is the most recently published issue.', 'periodicalpress' ); ?></p>
			</td>
		</tr>
	<!-- include text noting that the Issue must be published to appear in the dropdown -->


	<!-- TODO: Default Issue identifier: number, title, date? -->


	<!-- TODO: Date precision (or maybe 'how often are issues published?') -->


	<!-- TODO: Date format -->


	<!-- TODO: Slug format? (Or just do it automatically) -->


	<!-- TODO: js: preview what the Slug will look like -->


	<!-- TODO: No Issue name -->

	</tbody>
	</table>


	<!-- Submit button -->
	<p class="submit">
		<input type="submit" name="submit" id="submit" class="button button-primary" value="<?php esc_attr_e( 'Save and Update All Issues', 'periodicalpress' ); ?>" />
	</p>

	<!-- TODO: Clear Caches and Rebuild button -->


</form>

</div>
