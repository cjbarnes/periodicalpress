<?php

/**
 * The functionality of the plugin that affects both Dashboard and public-
 * facing pages
 *
 * @since 1.0.0
 *
 * @package PeriodicalPress
 */

/**
 * The shared functionality of the plugin.
 *
 * Defines the plugin name, version, and custom post types.
 *
 * @since 1.0.0
 */
class PeriodicalPress_Common {

	/**
	 * The ID of this plugin.
	 *
	 * @since 1.0.0
	 * @access private
	 * @var string $plugin_name
	 */
	private $plugin_name;

	/**
	 * The current version of this plugin.
	 *
	 * @since 1.0.0
	 * @access private
	 * @var string $version
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 1.0.0
	 *
	 * @var string $plugin_name The name of this plugin.
	 * @var string $version     The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

		$this->load_dependencies();

	}

	/**
	 * Load the required dependencies for the admin area.
	 *
	 * Include the following files:
	 *
	 * - PeriodicalPress_Issue_Taxonomy. Class for the Issues taxonomy setup.
	 *
	 * @since 1.0.0
	 * @access private
	 */
	private function load_dependencies() {

		/**
		 * Sets up the Issues taxonomy that is the basis for this plugin's
		 * data structure.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-periodicalpress-issue-taxonomy.php';

	}

}
