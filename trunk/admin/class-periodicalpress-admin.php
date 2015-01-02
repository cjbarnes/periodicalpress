<?php

/**
 * The dashboard-specific functionality of the plugin.
 *
 * @since 1.0.0
 *
 * @package PeriodicalPress\Admin
 */

/**
 * The dashboard-specific functionality of the plugin.
 *
 * @since 1.0.0
 */
class PeriodicalPress_Admin {

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
	 * @var string $plugin_name The name of this plugin.
	 * @var string $version     The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Display an admin area HTML ‘partial’.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param string $file_name The PHP file to be included (just the filename,
	 *                          not including path, extension, or plugin-name
	 *                          prefix).
	 */
	private function load_partial( $file_name ) {

		if ( ! $file_name ) {
			return false;
		}

		$file_path = 'admin/partials/periodicalpress-' . $file_name . '.php';

		/**
		 * Include the partial.
		 */
		@include plugin_dir_path( dirname( __FILE__ ) ) . $file_path;

	}

	/**
	 * Register the stylesheets for the Dashboard.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_styles() {

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
	 * @since 1.0.0
	 */
	public function enqueue_scripts() {

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
	 * @since 1.0.0
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
			'manage_pp_issues', // user capability required to show this menu
			'pp_issues_home',
			array( $this, 'issues_home' ),
			'dashicons-pressthis',
			'4.44' // position in the menu (Posts is 5)
		);

		// Issues submenu: repeat of top-level menu page.
		add_submenu_page(
			'pp_issues_home',
			__( 'Issues Home', 'periodicalpress' ),
			__( 'Issues Home', 'periodicalpress' ),
			'manage_pp_issues',
			'pp_issues_home'
		);

		// Issues submenu: Add and edit the Issues taxonomy.
		add_submenu_page(
			'pp_issues_home',
			$tax_labels->name,
			$tax_labels->all_items,
			'manage_pp_issues', // cap required
			'edit-tags.php?taxonomy=pp_issue'
		);

	}

	/**
	 * Change which top-level admin menus are used for this plugin’s submenus.
	 *
	 * Hooks into parent_file filter. Primarily exists to avoid taxonomy
	 * submenu items being forced under the Posts top-level menu.
	 *
	 * @since 1.0.0
	 *
	 * @see WP_Screen
	 *
	 * @param string $parent_file The slug of the top-level menu page that the
	 *                            current screen is a child of.
	 * @return string The modified parent file.
	 */
	public function fix_submenu_parent_files( $parent_file ) {

		// Get WP_Screen object
		$screen = get_current_screen();

		if ( 'pp_issue' === $screen->taxonomy )  {
			$parent_file = 'pp_issues_home';
		}

		return $parent_file;
	}

	/**
	 * Display the main Issues admin page.
	 *
	 * @since 1.0.0
	 */
	public function issues_home() {

		/**
		 * Output the Issue Settings page.
		 */
		$this->load_partial( 'issues-home' );

	}

	/**
	 * Output the Current Issue form.
	 *
	 * Only available to users with capability manage_pp_issues.
	 *
	 * @since 1.0.0
	 */
	public function current_issue_field() {

		if ( current_user_can( 'manage_pp_issues' ) ) {

			/**
			 * Output the Current Issue form.
			 */
			$this->load_partial( 'current-issue-form' );

		}

	}

	/**
	 * Save a newly selected Current Issue to the database.
	 *
	 * @since 1.0.0
	 */
	public function save_current_issue_field() {
		write_log( $_POST );

		// Check that the Current Issue form was submitted.
		if ( isset( $_POST['action'] )
			&& ( 'set-current-issue' === $_POST['action'] ) ) {

			// Check form nonce was properly set.
			if ( empty( $_POST['periodicalpress-current-issue-nonce'] )
				|| ( 1 !== wp_verify_nonce( $_POST['periodicalpress-current-issue-nonce'], 'set-current-issue' ) ) ) {
				wp_die( __( 'Cheatin&#8217; uh?' ), 403 );
			}

			// Check current user has sufficient permissions.
			if ( ! current_user_can( 'manage_pp_issues' ) ) {
				wp_die( __( 'Cheatin&#8217; uh?' ), 403 );
			}

			if ( empty( $_POST['current-issue'] ) ) {
				return;
			}
			$new_current_issue_id = $_POST['current-issue'];

			/*
			 * Check this Issue exists. Uses get_term() rather than
			 * term_exists() because the POST data contains a term ID, not a
			 * term slug.
			 */
			$new_current_issue = get_term( +$new_current_issue_id, 'pp_issue' );

			if ( ! is_null( $new_current_issue ) ) {
				update_option( 'pp_current_issue', $new_current_issue_id );
			}

		}

	}

	/**
	 * Adjusts Issues field on the Post Editor.
	 *
	 * Filter for the arguments passed to wp_terms_checklist(), which displays
	 * the Issues choice field within the Add/Edit Post page.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args    The original arguments array
	 * @param int   $post_id The ID of the post being edited
	 * @return array The revised arguments array
	 */
	public function filter_terms_checklist_args( $args, $post_id ) {

		if ( 'pp_issue' === $args['taxonomy'] ) {

			// Hide the 'Most Used' tab.
			$args['popular_cats'] = array( false );

			/**
			 * Select the Current Issue by default (but only for new posts).
			 */
			if ( 'add' === get_current_screen()->action ) {

				$current_issue = get_option( 'pp_current_issue', 0 );
				$args['selected_cats'] = $current_issue
					? array( $current_issue )
					: false;

			}

		}

		write_log( $args );

		return $args;
	}


}
