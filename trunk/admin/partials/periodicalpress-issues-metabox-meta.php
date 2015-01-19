<?php

/**
 * Display the Issue Date/Number metadata editing box
 *
 * Used on the Edit Issue screen.
 *
 * @todo Implement input type=month, input type=week, and a select for year -
 * depending on Issues settings
 *
 * @since 1.0.0
 *
 * @package PeriodicalPress\Admin
 */

/*
 * Don't allow direct loading of this file, and make sure we have an Issue to
 * work with.
 */
if ( ! defined( 'ABSPATH' ) || ! isset( $issue ) ) {
	exit;
}

$domain = $this->plugin->get_plugin_name();
$tax_name = $this->plugin->get_taxonomy_name();

// Get the Issue's metadata.
$meta = get_metadata( 'pp_term', $issue->term_id );
$meta_date = isset( $meta['pp_issue_date'] )
	? $meta['pp_issue_date'][0]
	: time();
$meta_number = isset( $meta['pp_issue_number'] )
	? $meta['pp_issue_number'][0]
	: '';

if ( ! class_exists( 'PeriodicalPress_Touch_Time' ) ) {
	/**
	 * Load the class for outputting datetime input fields.
	 */
	require_once $this->plugin->get_plugin_path() . 'admin/class-periodicalpress-touch-time.php';
}
$datefield = new PeriodicalPress_Touch_Time( mysql2date( 'U', $meta_date ) );

?>

<div class="pp-side-row">
<label class="pp-side-label" for="pp-issue-number">
	<?php echo esc_html_x( 'Issue Number', 'Edit Issue', $domain ); ?>
</label>
<input type="text" name="pp_issue_number" id="pp-issue-number" size="4" maxlength="4" autocomplete="off" value="<?php echo $meta_number; ?>" />
</div>

<div class="pp-side-row">
<label class="pp-side-label">
	<?php echo esc_html_x( 'Issue Date', 'Edit Issue', $domain ); ?>
</label>
</div>
<?php $datefield->display( 'day', 0, false ); ?>

<?php
