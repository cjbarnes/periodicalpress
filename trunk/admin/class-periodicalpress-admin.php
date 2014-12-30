<?php

/**
 * The dashboard-specific functionality of the plugin.
 *
 * @link http://github.com/cjbarnes/periodicalpress
 *
 * @package WordPress
 * @subpackage PeriodicalPress
 * @since PeriodicalPress 1.0.0
 */

/**
 * The dashboard-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the dashboard-specific stylesheet and JavaScript.
 *
 * @package WordPress
 * @subpackage PeriodicalPress
 * @since PeriodicalPress 1.0.0
 *
 * @author cJ barnes <mail@cjbarnes.co.uk>
 */
class PeriodicalPress_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since PeriodicalPress 1.0.0
	 * @access private
	 *
	 * @var string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since PeriodicalPress 1.0.0
	 * @access private
	 *
	 * @var string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since PeriodicalPress 1.0.0
	 *
	 * @var string $plugin_name The name of this plugin.
	 * @var string $version     The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the Dashboard.
	 *
	 * @since PeriodicalPress 1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in PeriodicalPress_Admin_Loader as all of the hooks are
		 * defined in that particular class.
		 *
		 * The PeriodicalPress_Admin_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this class.
		 */

		wp_enqueue_style(
			$this->plugin_name,
			plugin_dir_url( __FILE__ ) . 'css/periodicalpress-admin.css',
			array(),
			$this->version,
			'all'
		);

	}

	/**
	 * Register the JavaScript for the dashboard.
	 *
	 * @since PeriodicalPress 1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in PeriodicalPress_Admin_Loader as all of the hooks are
		 * defined in that particular class.
		 *
		 * The PeriodicalPress_Admin_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this class.
		 */

		wp_enqueue_script(
			$this->plugin_name,
			plugin_dir_url( __FILE__ ) . 'js/periodicalpress-admin.js',
			array( 'jquery' ),
			$this->version,
			false
		);

	}

	/**
	 * All changes to admin menu and submenu items.
	 *
	 * @since PeriodicalPress 1.0.0
	 */
	public function admin_menu_setup() {

		// Get Issues taxonomy labels for use by menu pages/subpages.
		$tax_labels = get_taxonomy( 'pp_issue' )->labels;

		/*
		 * Main Issues menu, containing the Issues taxonomy page and some
		 * plugin settings.
		 */
		add_menu_page(
			$tax_labels->name,
			$tax_labels->menu_name,
			'edit_pp_issues', // user capability required to show this menu
			'pp_issues_admin',
			array( $this, 'issues_admin' ),
			'dashicons-pressthis',
			'4.44' // position in the menu (Posts is 5)
		);

		/*
		 * Issues submenu: repeat of top-level menu page
		 */
		add_submenu_page(
			'pp_issues_admin',
			__( 'Issues Settings', 'periodicalpress' ),
			__( 'Issues Settings', 'periodicalpress' ),
			'edit_pp_issues',
			'pp_issues_admin'
		);

		/*
		 * Issues submenu: Add and edit the Issues taxonomy.
		 */
		add_submenu_page(
			'pp_issues_admin',
			$tax_labels->name,
			$tax_labels->all_items,
			'edit_pp_issues', // cap required
			'edit-tags.php?taxonomy=pp_issue'
		);

	}

	/**
	 * Display the main Issues admin page.
	 *
	 * @since PeriodicalPress 1.0.0
	 */
	public function issues_admin() {

		echo 'TODO';

	}

	/**
	 * Output the form for choosing the current issue.
	 *
	 * Shown above the Add New Issue form on the main Issues taxonomy admin
	 * page. Only available to users with capability manage_pp_issues.
	 *
	 * @since PeriodicalPress 1.0.0
	 */
	public function current_issue_field() {

		if ( current_user_can( 'manage_pp_issues' ) ) {

			/*
			 * Output the form for selecting the current issue.
			 */
			@include plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/periodicalpress-current-issue-form.php';

		}


	}

}
