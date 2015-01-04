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
 * @since 1.0.0
 */
class PeriodicalPress_Public {

	/**
	 * The path for including HTML partials.
	 *
	 * @since 1.0.0
	 * @access private
	 * @var string $partials_path
	 */
	private $partials_path;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		$this->partials_path = plugin_dir_path( dirname( __FILE__ ) ) . 'public/partials/periodicalpress-';

		$this->load_dependencies();

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
