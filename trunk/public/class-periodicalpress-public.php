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
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the dashboard-specific stylesheet and JavaScript.
 *
 * Uses the Singleton pattern.
 *
 * @since 1.0.0
 */
class PeriodicalPress_Public {

	/**
	 * Returns the instance of this class.
	 *
	 * The key method that enables the Singleton pattern for this class. Calls
	 * __construct() to create the class instance if it doesn't exist yet.
	 *
	 * @since 1.0.0
	 *
	 * @return PeriodicalPress Instance of this class.
	 */
	public static function get_instance() {

		static $instance = null;
		if ( null === $instance ) {
			$instance = new static();
		}

		return $instance;
	}

	/**
	 * Initialize the class and set its properties.
	 *
	 * Access `protected` enforces the Singleton pattern by disabling the `new`
	 * operator.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function __construct() {

		$this->load_dependencies();

	}

	/**
	 * Private clone method to enforce the Singleton pattern.
	 *
	 * @since 1.0.0
	 * @access private
	 */
	private function __clone() {

	}

	/**
	 * Private unserialize method to enforce the Singleton pattern.
	 *
	 * @since 1.0.0
	 * @access private
	 */
	private function __wakeup() {

	}

	/**
	 * Load the required dependencies for the admin area.
	 *
	 * Include the following files:
	 *
	 * - (None)
	 *
	 * @since 1.0.0
	 * @access private
	 */
	private function load_dependencies() {

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since 1.0.0
	 *
	 * @see PeriodicalPress
	 */
	public function enqueue_styles() {

		$plugin = PeriodicalPress::get_instance();

		wp_enqueue_style(
			$plugin->get_plugin_name(),
			plugin_dir_url( __FILE__ ) . 'css/periodicalpress-public.css',
			array(),
			$plugin->get_version(),
			'all'
		);

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since 1.0.0
	 *
	 * @see PeriodicalPress
	 */
	public function enqueue_scripts() {

		$plugin = PeriodicalPress::get_instance();

		wp_enqueue_script(
			$plugin->get_plugin_name(),
			plugin_dir_url( __FILE__ ) . 'js/periodicalpress-public.js',
			array( 'jquery' ),
			$plugin->get_version(),
			false
		);

	}

}
