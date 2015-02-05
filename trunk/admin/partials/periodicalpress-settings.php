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
 * Load existing options.
 */
$current_issue = get_option( 'pp_current_issue', 0 );
$issue_date_format = get_option( 'pp_issue_date_format', get_option( 'date_format' ) );
$issue_naming = get_option( 'pp_issue_naming', '' );

// Output begins:
?>
<div class="wrap">
<h2><?php echo sprintf( __( '%s Settings', 'periodicalpress' ), 'PeriodicalPress' ); ?></h2>

<?php
/* Output any validation or other errors. */
settings_errors();
?>

<form method="post" action="options.php">
	<?php
	settings_fields( 'periodicalpress_settings' );

	/*
	 * Allows addition of further settings fields to this page by themes and
	 * other plugins.
	 */
	do_settings_sections( 'periodicalpress_settings' );

	submit_button( esc_attr_x( 'Save and Update All Issues', 'Submit button label', 'periodicalpress' ) );
	?>
</form>

</div>
