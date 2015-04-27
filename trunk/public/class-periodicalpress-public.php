<?php

/**
 * The public-facing functionality of the plugin
 *
 * @since 1.0.0
 *
 * @package PeriodicalPress\Public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Uses the Singleton pattern.
 *
 * @since 1.0.0
 */
class PeriodicalPress_Public extends PeriodicalPress_Singleton {

	/**
	 * Load the required dependencies for the admin area.
	 *
	 * Include the following files:
	 *
	 * - PeriodicalPress_Theme_Patching. Customises themes that do not natively
	 *   support this plugin.
	 *
	 * Called by the parent class's Constructor.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function load_dependencies() {

		$path = $this->plugin->get_plugin_path();

		/*
		 * Include the class that patches unsupported themes. (Not instantiated
		 * until init, when we know whether the current theme supports this
		 * plugin.)
		 */
		require_once $path . 'public/class-periodicalpress-theme-patching.php';

	}

	/**
	 * Register all hooks for actions and filters in this class.
	 *
	 * Called by the parent class's Constructor.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function define_hooks() {

		/*
		 * Register the theme patching actions and filters, after init (to
		 * allow time for add_theme_supports() to be called by the theme).
		 */
		add_action( 'init', array( $this, 'patch_theme' ), 999 );

	}

	/**
	 * Loads the Theme Patching class if the current theme does not natively
	 * support this plugin.
	 *
	 * Cannot be called before the init hook because of
	 * {@see current_theme_supports()}. We can't assume the theme will declare
	 * theme support for 'periodicalpress' before init.
	 *
	 * @since 1.0.0
	 */
	public function patch_theme() {

		if ( ! current_theme_supports( 'periodicalpress' ) ) {
			PeriodicalPress_Theme_Patching::get_instance( $this->plugin );
		}

	}

}
