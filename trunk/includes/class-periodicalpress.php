<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both
 * the public-facing side of the site and the dashboard.
 *
 * @since 1.0.0
 *
 * @package PeriodicalPress
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, dashboard-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since 1.0.0
 */
class PeriodicalPress {

	/**
	 * The loader that's responsible for maintaining and registering all hooks
	 * that power the plugin.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var PeriodicalPress_Loader $loader
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string $plugin_name
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string $version
	 */
	protected $version;

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
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout
	 * the plugin. Load the dependencies, define the locale, and set the hooks
	 * for the Dashboard and the public-facing side of the site.
	 *
	 * Access `protected` enforces the Singleton pattern by disabling the `new`
	 * operator.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function __construct() {

		$this->plugin_name = 'periodicalpress';
		$this->version = '1.0.0';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_common_hooks();

		if ( is_admin() ) {
			$this->define_admin_hooks();
		} else {
			$this->define_public_hooks();
		}

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
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - PeriodicalPress_Loader. Orchestrates the hooks of the plugin.
	 * - PeriodicalPress_i18n. Defines internationalization functionality.
	 * - PeriodicalPress_Admin. Defines all hooks for the dashboard.
	 * - PeriodicalPress_Public. Defines all hooks for the public side of the
	 *   site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since 1.0.0
	 * @access private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of
		 * the core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-periodicalpress-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-periodicalpress-i18n.php';

		/**
		 * The class responsible for defining all actions that are common to
		 * both Dashboard and public-facing pages.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-periodicalpress-common.php';

		if ( is_admin() ) {

			/**
			 * The class responsible for defining all actions that occur in the
			 * Dashboard.
			 */
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-periodicalpress-admin.php';

		} else {

			/**
			 * The class responsible for defining all actions that occur in the
			 * public-facing side of the site.
			 */
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-periodicalpress-public.php';

		}

		/**
		 * All plugin template tags. These will be registered as methods of
		 * PeriodicalPress (this class).
		 */
		require plugin_dir_path( dirname( __FILE__ ) ) . 'includes/periodicalpress-template-tags.php';

		$this->loader = new PeriodicalPress_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the PeriodicalPress_i18n class in order to set the domain and to
	 * register the hook with WordPress.
	 *
	 * @since 1.0.0
	 * @access private
	 */
	private function set_locale() {

		$plugin_i18n = new PeriodicalPress_i18n();
		$plugin_i18n->set_domain( $this->get_plugin_name() );

		$this->loader->add_action(
			'plugins_loaded',
			$plugin_i18n,
			'load_plugin_textdomain'
		);

	}

	/**
	 * Register all of the hooks that affect both public and admin areas, e.g.
	 * custom post types.
	 *
	 * @since 1.0.0
	 * @access private
	 */
	private function define_common_hooks() {

		$plugin_common = new PeriodicalPress_Common();

		/*
		 * Setup custom taxonomies, including the main pp_issue taxonomy
		 */
		$this->loader->add_action(
			'init',
			$plugin_common,
			'register_taxonomies',
			0
		);

	}

	/**
	 * Register all of the hooks related to the dashboard functionality of the
	 * plugin.
	 *
	 * @since 1.0.0
	 * @access private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new PeriodicalPress_Admin();

		/*
		 * Admin CSS and JavaScript.
		 */
		$this->loader->add_action(
			'admin_enqueue_scripts',
			$plugin_admin,
			'enqueue_styles'
		);
		$this->loader->add_action(
			'admin_enqueue_scripts',
			$plugin_admin,
			'enqueue_scripts'
		);

		/*
		 * Admin menu item setup.
		 */
		$this->loader->add_action(
			'admin_menu',
			$plugin_admin,
			'admin_menu_setup'
		);
		$this->loader->add_filter(
			'parent_file',
			$plugin_admin,
			'fix_submenu_parent_files'
		);

		/*
		 * Display extra metadata boxes in Issues area.
		 */
		$this->loader->add_action(
			'pp_issue_add_form_fields',
			$plugin_admin,
			'display_add_issue_metadata_fields'
		);
		$this->loader->add_action(
			'pp_issue_edit_form_fields',
			$plugin_admin,
			'display_edit_issue_metadata_fields'
		);

		/*
		 * Sanitize settings choices and save to database.
		 */
		$this->loader->add_action(
			'periodicalpress_admin_top',
			$plugin_admin,
			'save_current_issue_field'
		);

		/*
		 * Replace the Issues box on the Post Add/Edit page.
		 */
		$this->loader->add_action(
			'add_meta_boxes_post',
			$plugin_admin,
			'add_remove_metaboxes'
		);
		$this->loader->add_action(
			'save_post',
			$plugin_admin,
			'save_issue_metabox'
		);

	}

	/**
	 * Register all of the hooks related to the public-facing functionality of
	 * the plugin.
	 *
	 * @since 1.0.0
	 * @access private
	 */
	private function define_public_hooks() {

		$plugin_public = new PeriodicalPress_Public();

		/*
		 * Public-facing CSS and JavaScript
		 */
		$this->loader->add_action(
			'wp_enqueue_scripts',
			$plugin_public,
			'enqueue_styles'
		);
		$this->loader->add_action(
			'wp_enqueue_scripts',
			$plugin_public,
			'enqueue_scripts'
		);

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since 1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since 1.0.0
	 *
	 * @return string The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since 1.0.0
	 *
	 * @return PeriodicalPress_Loader Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since 1.0.0
	 *
	 * @return string The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
