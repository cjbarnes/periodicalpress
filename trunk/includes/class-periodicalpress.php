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
 * Uses the Singleton pattern.
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
	 * Also used for the translations text domain.
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
	 * The path to the plugin (used for includes and requires).
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string $plugin_path
	 */
	protected $plugin_path;

	/**
	 * The path to HTML partials used in the public-facing side of this plugin.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string $partials_path_public
	 */
	protected $partials_path_public;

	/**
	 * The path to HTML partials used in the admin area of this plugin.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string $partials_path_admin
	 */
	protected $partials_path_admin;

	/**
	 * The name the Issues taxonomy is registered under.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string $taxonomy_name
	 */
	protected $taxonomy_name;

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

		/*
		 * Init class properties
		 */

		$this->plugin_name = 'periodicalpress';
		$this->version = '1.0.0';

		$this->plugin_path = plugin_dir_path( dirname( __FILE__ ) );
		$this->partials_path_public = $this->plugin_path . 'public/partials/';
		$this->partials_path_admin = $this->plugin_path . 'admin/partials/';

		$this->taxonomy_name = 'pp_issue';

		/*
		 * Call plugin init methods
		 */

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
	 * - PeriodicalPress_Taxonomy. Defines the Issues taxonomy.
	 * - PeriodicalPress_Common. Defines all hooks shared by the dashboard and
	 *   the public side of the site.
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
		require_once $this->plugin_path . 'includes/class-periodicalpress-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once $this->plugin_path . 'includes/class-periodicalpress-i18n.php';

		/**
		 * The class responsible for registering the main plugin taxonomy.
		 */
		require_once $this->plugin_path . 'includes/class-periodicalpress-taxonomy.php';

		/**
		 * The class responsible for defining all actions that are common to
		 * both Dashboard and public-facing pages.
		 */
		require_once $this->plugin_path . 'includes/class-periodicalpress-common.php';

		if ( is_admin() ) {

			/**
			 * The class responsible for defining all actions that occur in the
			 * Dashboard.
			 */
			require_once $this->plugin_path . 'admin/class-periodicalpress-admin.php';

		} else {

			/**
			 * The class responsible for defining all actions that occur in the
			 * public-facing side of the site.
			 */
			require_once $this->plugin_path . 'public/class-periodicalpress-public.php';

		}

		/**
		 * All plugin template tags. These will be registered as methods of
		 * PeriodicalPress (this class).
		 */
		require_once $this->plugin_path . 'includes/periodicalpress-template-tags.php';

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

		$plugin_common = PeriodicalPress_Common::get_instance( $this );
		$plugin_taxonomy = PeriodicalPress_Taxonomy::get_instance( $this );

		$tax_name = $this->taxonomy_name;

		/*
		 * Setup custom taxonomies, including the main pp_issue taxonomy
		 */
		$this->loader->add_action(
			'init',
			$plugin_taxonomy,
			'register_taxonomy',
			0
		);

		/*
		 * Register custom taxonomy metadata table with $wpdb database object.
		 */
		$this->loader->add_action(
			'init',
			$plugin_common,
			'register_metadata_table'
		);

		/*
		 * Set allowed Issue publication statuses.
		 */
		$this->loader->add_filter(
			"{$tax_name}_statuses",
			$plugin_common,
			'set_issue_statuses_list',
			1,
			1
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

		$plugin_admin = PeriodicalPress_Admin::get_instance( $this );
		$plugin_edit_issues = PeriodicalPress_Edit_Issues::get_instance( $this );
		$plugin_save_issues = PeriodicalPress_Save_Issues::get_instance( $this );
		$plugin_post_issue_box = new PeriodicalPress_Post_Issue_Box( $this );

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
		 * Create hooks to set up screen options and help tabs for menus and
		 * submenus.
		 */
		$this->loader->add_action(
			"load-toplevel_page_pp_edit_issues",
			$plugin_edit_issues,
			'edit_issues_screen_options'
		);

		/*
		 * Set up the metaboxes for the Edit Issue page.
		 */
		$this->loader->add_action(
			'add_meta_boxes_pp_issue',
			$plugin_edit_issues,
			'add_remove_metaboxes'
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
		 * Reorder the Posts table columns (and Quick Edit boxes).
		 */
		$this->loader->add_action(
			'manage_posts_columns',
			$plugin_post_issue_box,
			'posts_move_issue_column'
		);

		/*
		 * Add a custom Issues box to the Quick Edit for posts.
		 */
		$this->loader->add_action(
			'quick_edit_custom_box',
			$plugin_post_issue_box,
			'render_issue_quick_edit_box',
			10,
			2
		);

		/*
		 * Replace the Issues box on the Post Add/Edit page.
		 */
		$this->loader->add_action(
			'add_meta_boxes_post',
			$plugin_post_issue_box,
			'add_remove_metaboxes'
		);

		/*
		 * Save the Issue for a post, whether set by the Edit Post screen or
		 * the Quick Edit.
		 */
		$this->loader->add_action(
			'save_post',
			$plugin_post_issue_box,
			'save_post_issue',
			10,
			2
		);

		/*
		 * Unpublish an Issue when all posts within it are unpublished.
		 */
		$this->loader->add_action(
			'transition_post_status',
			$plugin_save_issues,
			'unpublish_post_issues_if_empty',
			10,
			3
		);
		$this->loader->add_action(
			'edited_term_taxonomy',
			$plugin_save_issues,
			'unpublish_issue_if_empty',
			10,
			2
		);

		/*
		 * Manually add the Issue column to the Posts list table.
		 */
		$this->loader->add_action(
			'manage_posts_custom_column',
			$plugin_post_issue_box,
			'list_table_column_pp_issue',
			10,
			2
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

		$plugin_public = PeriodicalPress_Public::get_instance( $this );
		$plugin_theme_patching = PeriodicalPress_Theme_Patching::get_instance( $this );

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

		/*
		 * Register the theme patching actions and filters, after init (to
		 * allow time for add_theme_supports() to be called by the theme).
		 */
		$this->loader->add_action(
			'init',
			$plugin_theme_patching,
			'define_hooks',
			999
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

	/**
	 * Retrieve the path to this plugin's root directory.
	 *
	 * @since 1.0.0
	 *
	 * @return string The path to use in requires and includes.
	 */
	public function get_plugin_path() {
		return $this->plugin_path;
	}

	/**
	 * Retrieve the path to be used for including an HTML partial.
	 *
	 * @since 1.0.0
	 *
	 * @param  string $subpackage Default 'public'. Accepts 'public', 'admin'.
	 *                            The area of the site this partial is used in.
	 * @return string|false The path to the partials folder for this subpackage.
	 */
	public function get_partials_path( $subpackage = 'public' ) {

		switch ( $subpackage ) {

			case 'public':
				return $this->partials_path_public;

			case 'admin':
				return $this->partials_path_admin;

			default:
				return false;

		}

	}

	/**
	 * Retrieve the name the Issues taxonomy is registered under.
	 *
	 * @since 1.0.0
	 *
	 * @return string The taxonomy name.
	 */
	public function get_taxonomy_name() {
		return $this->taxonomy_name;
	}

}
