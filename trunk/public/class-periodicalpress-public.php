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
	 * The plugin's main class.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var PeriodicalPress $plugin
	 */
	protected $plugin;

	/**
	 * Returns the instance of this class.
	 *
	 * The key method that enables the Singleton pattern for this class. Calls
	 * __construct() to create the class instance if it doesn't exist yet.
	 *
	 * @since 1.0.0
	 *
	 * @param PeriodicalPress $plugin The main plugin class instance.
	 * @return PeriodicalPress_Public Instance of this class.
	 */
	public static function get_instance( $plugin ) {

		static $instance = null;
		if ( null === $instance ) {
			$instance = new static( $plugin );
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
	 *
	 * @var PeriodicalPress $plugin The main plugin class instance.
	 */
	protected function __construct( $plugin ) {

		$this->plugin = $plugin;

		$this->define_hooks();
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
	 * - PeriodicalPress_Theme_Patching. Customises themes that do not natively
	 *   support this plugin.
	 *
	 * @since 1.0.0
	 * @access private
	 */
	private function load_dependencies() {

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
	 * @since 1.0.0
	 * @access private
	 */
	private function define_hooks() {

		// Public-facing CSS and JavaScript
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		/*
		 * Register the theme patching actions and filters, after init (to
		 * allow time for add_theme_supports() to be called by the theme).
		 */
		add_action( 'init', array( $this, 'patch_theme' ), 999 );

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * Stylesheets used:
	 * - periodicalpress-public.css - Styles loaded on whole public site.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_styles() {

		$name = $this->plugin->get_plugin_name();
		$path = plugin_dir_url( __FILE__ ) . 'css/';
		$version = $this->plugin->get_version();

		wp_enqueue_style( $name, "{$path}periodicalpress-public.css", array(), $version, 'all' );

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * Scripts used:
	 * - periodicalpress-admin.js - Script loaded on whole public site.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_scripts() {

		$name = $this->plugin->get_plugin_name();
		$path = plugin_dir_url( __FILE__ ) . 'js/';
		$version = $this->plugin->get_version();

		wp_enqueue_script( $name, "{$path}periodicalpress-public.js", array( 'jquery' ), $version, true );

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
