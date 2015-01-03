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
	 * @var string $plugin_name
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since 1.0.0
	 * @access private
	 * @var string $version
	 */
	private $version;

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
	 *
	 * @var string $plugin_name The name of the plugin.
	 * @var string $version     The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->partials_path = plugin_dir_path( dirname( __FILE__ ) ) . 'public/partials/periodicalpress-';

		$this->plugin_name = $plugin_name;
		$this->version = $version;

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
