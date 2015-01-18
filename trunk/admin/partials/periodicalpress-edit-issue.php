<?php

/**
 * Display the Edit Issue form
 *
 * Security for this form (i.e. checking capabilites), and saving, is handled by
 * the calling file, periodicalpress_edit_pp_issues.php.
 *
 * @since 1.0.0
 *
 * @package PeriodicalPress\Admin
 */

if ( ! $term_id || ! defined( 'ABSPATH' ) ) {
	exit;
}

$domain = $this->plugin->get_plugin_name();
$tax_name = $this->plugin->get_taxonomy_name();
$tax = get_taxonomy( $tax_name );

// Load all existing data for this Issue, for use within the form.
$issue = get_term( $term_id, $tax_name );
$metadata = get_metadata( $tax_name, $issue->term_id );
?>
<div class="wrap">
	<h2><?php echo esc_html( $tax->labels->edit_item ); ?></h2>

	<p>TODO.</p>

</div><!-- /wrap -->
<?php
