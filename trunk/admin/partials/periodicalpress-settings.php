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
$tax = get_taxonomy( $tax_name );

/*
 * The current Issue Date Format is used a few times, so load it here. Fall back
 * to the site-wide date format.
 */
$issue_date_format = get_option( 'pp_issue_date_format', get_option( 'date_format' ) );

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

		<!-- Default Issue identifier: number, title, date? -->
		<tr>
			<th scope="row">
				<label for="issue-naming"><?php esc_html_e( 'Issue Names Format', 'periodicalpress' ); ?></label>
			</th>
			<td>
				<fieldset>
					<legend class="screen-reader-text">
						<span><?php esc_html_e( 'Issue Names Format', 'periodicalpress' ); ?></span>
					</legend>
					<p>
						<label>
							<input name="pp_issue_naming" type="radio" value="" class="tog" checked="checked" />
							<?php echo esc_html_x( 'Numbers', 'Issue names format option', 'periodicalpress' ); ?>
						</label>
						<?php esc_html_e( '–', 'periodicalpress' ); ?>
						<code>
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
							echo esc_html(
								sprintf( _x( '%1$s: %2$s', 'Example of issue name format', 'periodicalpress' ),
									$tax->labels->singular_name,
									number_format_i18n( 42 )
								)
							);
							?>
						</code>
					</p>
					<p>
						<label>
							<input name="pp_issue_naming" type="radio" value="" class="tog" checked="checked" />
							<?php echo esc_html_x( 'Dates', 'Issue names format option', 'periodicalpress' ); ?>
						</label>
						<?php esc_html_e( '–', 'periodicalpress' ); ?>
						<code>
							<?php
							/* Translators: this is documented above. */
							echo esc_html(
								sprintf( _x( '%1$s: %2$s', 'Example of issue name format', 'periodicalpress' ),
									$tax->labels->singular_name,
									date_i18n( $issue_date_format )
								)
							);
							?>
						</code>
					</p>
					<p>
						<label>
							<input name="pp_issue_naming" type="radio" value="" class="tog" checked="checked" />
							<?php echo esc_html_x( 'Titles', 'Issue names format option', 'periodicalpress' ); ?>
						</label>
						<?php esc_html_e( '–', 'periodicalpress' ); ?>
						<code>
							<?php
							/* Translators: this is documented above. */
							echo esc_html(
								sprintf( _x( '%1$s: %2$s', 'Example of issue name format', 'periodicalpress' ),
									$tax->labels->singular_name,
									_x( 'Hello Dolly', 'Example issue title', 'periodicalpress' )
								)
							);
							?>
						</code>
					</p>
				</fieldset>
				<p class="description"><?php _e( '<strong>Warning:</strong> Changing the Issue Names Format will change the names of all published issues on the site. This cannot be undone.', 'periodicalpress' ); ?></p>
			</td>
		</tr>

		<!-- Date format -->
		<?php
		/*
		 * Prepare the array of standard date formats. We start with the WP
		 * defaults - as used on the General Settings page - and then run these
		 * through a plugin-specific hook for further customization.
		 */

		/** This filter is documented in wp-admin/options-general.php */
		$date_formats = array_unique( apply_filters( 'date_formats', array( __( 'F j, Y' ), 'Y-m-d', 'm/d/Y', 'd/m/Y' ) ) );

		/**
		 * Filter the list of standard Issue Date formats to suggest on the
		 * PeriodicalPress settings page.
		 *
		 * @since 1.0.0
		 *
		 * @param array $date_formats WordPress's default set of date formats.
		 */
		$date_formats = array_unique( apply_filters( 'periodicalpress_date_formats', $date_formats ) );

		$site_date_format = get_option( 'date_format' );
		?>
		<tr>
			<th scope="row">
				<label for="pp_issue_date_format"><?php esc_html_e( 'Issue Date Format', 'periodicalpress' ); ?></label>
			</th>
			<td>
				<fieldset>
					<legend class="screen-reader-text">
						<span>Date Format</span>
					</legend>
					<!-- Current date format for the whole site -->
					<?php
					if ( $site_date_format === $issue_date_format ) {
						$checked = 'checked="checked" ';
						$found_checked = true;
					} else {
						$checked = '';
						$found_checked = false;
					}
					?>
					<label title="<?php echo esc_attr( $site_date_format ); ?>">
						<input type="radio" name="pp_issue_date_format" value="<?php echo esc_attr( $site_date_format ); ?>" <?php echo $checked; ?>/>
						<span><?php echo date_i18n( $site_date_format ); ?></span>
						<em>(default for this site)</em>
					</label>
					<br />
					<!-- All other suggested date formats -->
					<?php foreach ( $date_formats as $format ) : ?>
						<?php
						// Don't repeat the site-wide format.
						if ( $format === $site_date_format ) {
							continue;
						}

						if ( ! $found_checked && ( $format === $issue_date_format ) ) {
							$checked = 'checked="checked" ';
							$found_checked = true;
						} else {
							$checked = '';
						}
						?>
						<label title="<?php echo esc_attr( $format ); ?>">
							<input type="radio" name="pp_issue_date_format" value="<?php echo esc_attr( $format ); ?>" <?php echo $checked; ?>/>
							<span><?php echo date_i18n( $format ); ?></span>
						</label>
						<br>
					<?php endforeach; ?>
					<!-- Custom date format -->
					<?php
					$checked = $found_checked
						? ''
						: 'checked="checked" ';
					?>
					<label>
						<input type="radio" name="pp_issue_date_format" id="date_format_custom_radio" value="\c\u\s\t\o\m" $checked/>
						<?php echo esc_html_x( 'Custom:', 'Issue Date Format field', 'periodicalpress' ); ?>
					</label>
					<input type="text" name="pp_issue_date_format_custom" value="<?php echo esc_attr( $issue_date_format ); ?>" class="small-text" />
					<span class="example"> <?php echo date_i18n( $issue_date_format ); ?></span>
					<span class="spinner"></span>
					<p><?php _e( '<a href="http://codex.wordpress.org/Formatting_Date_and_Time">Documentation on date and time formatting</a>.' ); ?></p>
				</fieldset>
			</td>
		</tr>

		<!-- TODO: Date precision (or maybe 'how often are issues published?') -->


		<!-- TODO: Slug format? (Or just do it automatically) -->


		<!-- TODO: js: preview what the Slug will look like -->

	</tbody>
	</table>


	<!-- Submit button -->
	<p class="submit">
		<input type="submit" name="submit" id="submit" class="button button-primary" value="<?php esc_attr_e( 'Save and Update All Issues', 'periodicalpress' ); ?>" />
	</p>

	<!-- TODO: Clear Caches and Rebuild button -->


</form>

</div>
