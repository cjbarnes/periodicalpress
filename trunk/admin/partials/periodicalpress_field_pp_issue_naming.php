<?php

/**
 * Display the Plugin Settings field that sets the Issue Name Format.
 *
 * @since 1.0.0
 *
 * @package PeriodicalPress\Admin
 */

if ( ! defined( 'ABSPATH' ) || ! isset( $field['name'] ) ) {
	exit;
}

$pp_common = PeriodicalPress_Common::get_instance( $this->plugin );
$tax_name = $this->plugin->get_taxonomy_name();
$tax = get_taxonomy( $tax_name );

/*
 * We need the date format setting, as well as the name format setting, so we
 * can correctly output the 'Dates' name format example.
 */
$issue_date_format = get_option( 'pp_issue_date_format', get_option( 'date_format' ) );
$issue_naming = get_option( 'pp_issue_naming', '' );

// Output begins here:
?>
<fieldset>
	<legend class="screen-reader-text">
		<span><?php echo esc_html( $field['label'] ); ?></span>
	</legend>
	<p>
		<label>
			<input name="<?php echo $field['name']; ?>" type="radio" value="numbers" class="tog" <?php if ( 'numbers' === $issue_naming ) echo 'checked="checked"'; ?> />
			<?php echo esc_html_x( 'Numbers', 'Issue names format option', 'periodicalpress' ); ?>
		</label>
		&ndash;
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
			<input name="<?php echo $field['name']; ?>" type="radio" value="dates" class="tog" <?php if ( 'dates' === $issue_naming ) echo 'checked="checked"'; ?> />
			<?php echo esc_html_x( 'Dates', 'Issue names format option', 'periodicalpress' ); ?>
		</label>
		&ndash;
		<code>
			<?php
			/* Translators: this is documented above. */
			printf( _x( '%1$s: %2$s', 'Example of issue name format', 'periodicalpress' ),
				esc_html( $tax->labels->singular_name ),
				'<span class="pp-issue-date-format-example"> ' . esc_html( date_i18n( $issue_date_format ) ) . '</span>'
			);
			?>
		</code>
	</p>
	<p>
		<label>
			<input name="<?php echo $field['name']; ?>" type="radio" value="titles" class="tog" <?php if ( 'titles' === $issue_naming ) echo 'checked="checked"'; ?> />
			<?php echo esc_html_x( 'Titles', 'Issue names format option', 'periodicalpress' ); ?>
		</label>
		&ndash;
		<code>
			<?php
			/* Translators: this is documented above. */
			echo esc_html(
				sprintf( _x( '%1$s: %2$s', 'Example of issue name format', 'periodicalpress' ),
					$tax->labels->singular_name,
					_x( 'The Windows of Siracusa County', 'Example issue title', 'periodicalpress' )
				)
			);
			?>
		</code>
	</p>
</fieldset>
<p class="description"><?php _e( '<strong>Warning:</strong> Changing the Issue Names Format will change the names of all published issues on the site. This cannot be undone.', 'periodicalpress' ); ?></p>
