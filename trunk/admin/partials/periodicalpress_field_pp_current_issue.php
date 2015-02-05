<?php

/**
 * Display the Plugin Settings field that sets the Current Issue.
 *
 * @since 1.0.0
 *
 * @package PeriodicalPress\Admin
 */

if ( ! defined( 'ABSPATH' ) || ! isset( $field['name'] ) ) {
	exit;
}

$pp_common = PeriodicalPress_Common::get_instance( $this->plugin );
$current_issue = get_option( 'pp_current_issue', 0 );

// Output begins here:
?>

<?php
// Dropdown of published Issues.
$args = array(
	'hide_unpublished' => 1,
	'name'             => $field['name'],
	'id'               => $field['id'],
	'selected'         => $current_issue
);
$pp_common->dropdown_issues( $args );
?>
<p class="description"><?php esc_html_e( 'The Current Issue is the issue featured on your site homepage. Usually it is the most recently published issue.', 'periodicalpress' ); ?></p>
