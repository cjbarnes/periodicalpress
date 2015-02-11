<?php

/**
 * Display the Plugin Settings field that sets the Issue Date Format.
 *
 * @since 1.0.0
 *
 * @package PeriodicalPress\Admin
 */

if ( ! defined( 'ABSPATH' ) || ! isset( $field['name'] ) ) {
	exit;
}

$pp_common = PeriodicalPress_Common::get_instance( $this->plugin );

$issue_date_format = get_option( 'pp_issue_date_format', get_option( 'date_format' ) );
$site_date_format = get_option( 'date_format' );

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

// Output begins here:
?>

<fieldset class="pp-issue-date-format">
	<legend class="screen-reader-text">
		<span><?php echo esc_html( $field['label'] ); ?></span>
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
		<input type="radio" name="<?php echo $field['name']; ?>" value="<?php echo esc_attr( $site_date_format ); ?>" <?php echo $checked; ?>/>
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
			<input type="radio" name="<?php echo $field['name']; ?>" value="<?php echo esc_attr( $format ); ?>" <?php echo $checked; ?>/>
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
		<input type="radio" name="<?php echo $field['name']; ?>" id="issue-date-format-custom-radio" value="\c\u\s\t\o\m" $checked/>
		<?php echo esc_html_x( 'Custom:', 'Issue Date Format field', 'periodicalpress' ); ?>
	</label>
	<input type="text" name="<?php echo $field['name']; ?>_custom" value="<?php echo esc_attr( $issue_date_format ); ?>" class="small-text" />
	<span class="example pp-issue-date-format-example"> <?php echo date_i18n( $issue_date_format ); ?></span>
	<span class="spinner"></span>
	<p><?php _e( '<a href="http://codex.wordpress.org/Formatting_Date_and_Time">Documentation on date and time formatting</a>.' ); ?></p>
</fieldset>
