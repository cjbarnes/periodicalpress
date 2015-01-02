<?php

/**
 * The main admin page for the plugin
 *
 * Includes the Current Issue setting, general instructions, and plugin-related
 * menu options.
 *
 * @since 1.0.0
 *
 * @package PeriodicalPress\Admin
 */
?>

<div class="wrap">
	<h2><?php _e( 'Issues Home', 'periodicalpress' ); ?></h2>

<?php
	/**
	 * Hook for before the content on every plugin page in the admin area.
	 *
	 * @since 1.0.0
	 */
	do_action( 'periodicalpress_admin_top' );

	if ( current_user_can( 'manage_pp_issues' ) ) {

		/**
		 * Output the Current Issue form.
		 */
		$this->load_partial( 'current-issue-form' );

	}

	/**
	 * Hook for after the content on every plugin page in the admin area.
	 *
	 * @since 1.0.0
	 */
	do_action( 'periodicalpress_admin_bottom' );
?>

</div>
