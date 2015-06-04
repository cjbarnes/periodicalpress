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

		// When previewing an Issue, include Pending posts in query results.
		add_action( 'pre_get_posts', array( $this, 'preview_issue' ) );

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

	/**
	 * Convert Issue query into a Preview Issue query if preview flag is set.
	 *
	 * @since 1.1.0
	 *
	 * @param WP_Query $query The query object.
	 */
	public function preview_issue( $query ) {

		$tax_name = $this->plugin->get_taxonomy_name();

		/*
		 * The standard conditional function is_main_query() does not return the
		 * right results within the pre_get_posts hook.
		 */
		if ( ! $query->is_main_query()
		|| ! ( $query->is_tax( $tax_name ) || $query->is_home() )
		|| ! is_preview() ) {
			return;
		}

		// Get post statuses currently included in query.
		$statuses = $query->get( 'post_status' );
		if ( empty( $statuses ) ) {
			$statuses = array( 'publish' );
		} elseif ( ! is_array( $statuses ) ) {
			$statuses = array( $statuses );
		}

		// Add Pending posts to the queried statuses.
		$statuses[] = 'pending';
		$query->set( 'post_status', $statuses );

	}

}
