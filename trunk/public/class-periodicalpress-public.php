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
	 * The ID of this plugin.
	 *
	 * @since 1.0.0
	 * @access private
	 * @var string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since 1.0.0
	 * @access private
	 * @var string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 1.0.0
	 *
	 * @var string $plugin_name The name of the plugin.
	 * @var string $version     The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Display a public website HTML ‘partial’.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param string $file_name The PHP file to be included (just the filename,
	 *                          not including path, extension, or plugin-name
	 *                          prefix).
	 * @param array  $vars      Any variables being passed from the parent
	 *                          function's scope into the partial's scope.
	 */
	private function load_partial( $file_name ) {

		if ( ! $file_name ) {
			return false;
		}

		/*
		 * Convert array of items passed to this function into fully-fledged
		 * variables, which can then be accessed by the included partial.
		 */
		extract( $vars, EXTR_SKIP );

		$file_path = 'public/partials/periodicalpress-' . $file_name . '.php';

		/**
		 * Include the partial.
		 */
		@include plugin_dir_path( dirname( __FILE__ ) ) . $file_path;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style(
			$this->plugin_name,
			plugin_dir_url( __FILE__ ) . 'css/periodicalpress-public.css',
			array(),
			$this->version,
			'all'
		);

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_scripts() {

		wp_enqueue_script(
			$this->plugin_name,
			plugin_dir_url( __FILE__ ) . 'js/periodicalpress-public.js',
			array( 'jquery' ),
			$this->version,
			false
		);

	}

}
