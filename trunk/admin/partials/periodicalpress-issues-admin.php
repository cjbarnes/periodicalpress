<?php

/**
 * The main admin page for the plugin
 *
 * Includes general settings and customization options. Options are limited on a
 * per-field rather than per-page basis.
 *
 * @since 1.0.0
 *
 * @package PeriodicalPress\Admin
 */
?>

<div class="wrap">
	<h2><?php _e( 'Issues Settings', 'periodicalpress' ); ?></h2>

<?php
	if ( current_user_can( 'manage_pp_issues' ) ) {

		/**
		 * Output the Current Issue form.
		 */
		$this->load_partial( 'current-issue-form' );

	}
?>

</div>
