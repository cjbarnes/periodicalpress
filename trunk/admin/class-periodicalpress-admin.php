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
 * Uses the Singleton pattern.
 *
 * @since 1.0.0
 */
class PeriodicalPress_Admin extends PeriodicalPress_Singleton {

	/**
	 * Load the required dependencies for the admin area.
	 *
	 * Include the following files:
	 *
	 * - PeriodicalPress_Edit_Issues. Issue editing screens.
	 * - PeriodicalPress_Save_Issues. Issue saving/deleting methods.
	 * - PeriodicalPress_Post_Metabox. Callbacks for Edit Post screen metabox.
	 *
	 * PeriodicalPress_List_Table is a parent class only, so we do not
	 * instantiate it here.
	 *
	 * Called by the parent class's Constructor.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function load_dependencies() {

		$path = $this->plugin->get_plugin_path();

		// Include all other dashboard classes.
		require_once $path . 'admin/class-periodicalpress-edit-issues.php';
		require_once $path . 'admin/class-periodicalpress-post-issue-box.php';
		require_once $path . 'admin/class-periodicalpress-save-issues.php';

		// Instantiate classes.
		PeriodicalPress_Edit_Issues::get_instance( $this->plugin );
		PeriodicalPress_Post_Issue_Box::get_instance( $this->plugin );
		PeriodicalPress_Save_Issues::get_instance( $this->plugin );

	}

	/**
	 * Register all hooks for actions and filters in this class.
	 *
	 * Called by the parent class's Constructor.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function define_hooks() {

		// Admin CSS and JavaScript.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		// Admin menu item setup.
		add_action( 'admin_menu', array( $this, 'admin_menu_setup' ) );
		add_filter( 'parent_file', array( $this, 'fix_submenu_parent_files' ) );

	}

	/**
	 * Register the stylesheets for the Dashboard.
	 *
	 * Stylesheets used:
	 * - periodicalpress-admin.css - Styles loaded on whole admin area.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_styles() {

		$name = $this->plugin->get_plugin_name();
		$path = plugin_dir_url( __FILE__ ) . 'css/';
		$version = $this->plugin->get_version();

		wp_enqueue_style( $name, "{$path}periodicalpress-admin.css", array(), $version, 'all' );

	}

	/**
	 * Register the JavaScript for the dashboard.
	 *
	 * Scripts used:
	 * - periodicalpress-admin.js - Script loaded on whole admin area.
	 * - periodicalpress-posts-list-table.js - Quick Editing for Posts list
	 *   table.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_scripts() {

		$name = $this->plugin->get_plugin_name();
		$path = plugin_dir_url( __FILE__ ) . 'js/';
		$version = $this->plugin->get_version();

		$domain = $name;

		// Script used thoughout the admin area.
		wp_enqueue_script( $name, "{$path}periodicalpress-admin.js", array( 'jquery' ), $version, true );

		/*
		 * Localization object for the main script file. All key-value pairs
		 * in this array will be available as a global 'l10n' object in the
		 * JavaScript file ({@see wp_localize_script()}).
		 */
		wp_localize_script( $name, 'l10n', array() );

		// Script that enables Quick Editing for posts.
		$screen = get_current_screen();
		if ( 'edit-post' === $screen->id ) {

			wp_enqueue_script( "{$name}_posts_list_table", "{$path}periodicalpress-posts-list-table.js", array( 'jquery' ), $version, true );

		}

	}

	/**
	 * All changes to admin menu and submenu items.
	 *
	 * @since 1.0.0
	 */
	public function admin_menu_setup() {

		$plugin_edit_issues = PeriodicalPress_Edit_Issues::get_instance( $this->plugin );

		$domain = $this->plugin->get_plugin_name();
		$tax = get_taxonomy( $this->plugin->get_taxonomy_name() );

		// Get Issues taxonomy labels for use by menu pages/subpages.
		$tax_labels = $tax->labels;

		/*
		 * Main Issues menu, containing the Issues list table.
		 */
		add_menu_page(
			$tax->labels->name,
			$tax->labels->menu_name,
			$tax->cap->assign_terms, // user capability required to show this menu
			'pp_edit_issues',
			array( $plugin_edit_issues, 'edit_issues_screen' ),
			'dashicons-pressthis',
			'4.44' // position in the menu (Posts is 5)
		);

		// Issues submenu: repeat of top-level menu page.
		add_submenu_page(
			'pp_edit_issues',
			$tax->labels->name,
			$tax->labels->all_items,
			$tax->cap->assign_terms,
			'pp_edit_issues'
		);

		/*
		 * Issues submenu: Add and edit the Issues taxonomy - old version for
		 * debugging purposes.
		 */
		if ( WP_DEBUG ) {
			add_submenu_page(
				'pp_edit_issues',
				$tax->labels->name,
				sprintf( _x( '%s (debugging)', 'Admin menu', $domain ), $tax->labels->all_items ),
				'activate_plugins',
				"edit-tags.php?taxonomy={$tax->name}"
			);
		}

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

		if ( $this->plugin->get_taxonomy_name() === $screen->taxonomy )  {
			$parent_file = 'pp_issues_home';
		}

		return $parent_file;
	}

	/**
	 * Output the Current Issue form.
	 *
	 * Only available to users with capability manage_pp_issues.
	 *
	 * @since 1.0.0
	 */
	public function current_issue_field() {

		$tax = get_taxonomy( $this->plugin->get_taxonomy_name() );

		if ( current_user_can( $tax->cap->manage_terms ) ) {

			/**
			 * Output the Current Issue form.
			 */
			$path = $this->plugin->get_partials_path( 'admin' );
			require $path . 'periodicalpress-current-issue-form.php';

		}

	}

	/**
	 * Save a newly selected Current Issue to the database.
	 *
	 * @since 1.0.0
	 */
	public function save_current_issue_field() {

		$tax_name = $this->plugin->get_taxonomy_name();
		$tax = get_taxonomy( $tax_name );

		// Check that the Current Issue form was submitted.
		if ( isset( $_POST['action'] )
			&& ( 'set-current-issue' === $_POST['action'] ) ) {

			// Check form nonce was properly set.
			if ( empty( $_POST['periodicalpress-current-issue-nonce'] )
				|| ( 1 !== wp_verify_nonce( $_POST['periodicalpress-current-issue-nonce'], 'set-current-issue' ) ) ) {
				wp_die( __( 'Cheatin&#8217; uh?' ), 403 );
			}

			// Check current user has sufficient permissions.
			if ( ! current_user_can( $tax->cap->manage_terms ) ) {
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
			$new_current_issue = get_term( +$new_current_issue_id, $tax_name );

			if ( ! is_null( $new_current_issue ) ) {
				update_option( 'pp_current_issue', $new_current_issue_id );
			}

		}

	}

	/**
	 * Save submitted metadata form fields on the Add/Edit Issue pages.
	 *
	 * **TODO repurpose into a full Save Issue Data function.**
	 *
	 * @since 1.0.0
	 *
	 * @global wpdb $wpdb The WordPress database class.
	 *
	 * @param int $issue_id The object ID for this Issue taxonomy term.
	 */
	public function save_issue_metadata_fields( $issue_id ) {
		global $wpdb;

		$tax_name = $this->plugin->get_taxonomy_name();
		$tax = get_taxonomy( $tax_name );

		// Check form nonce was properly set.
		if ( empty( $_POST['periodicalpress-set-issue-metadata-nonce'] )
			|| ( 1 !== wp_verify_nonce( $_POST['periodicalpress-set-issue-metadata-nonce'], 'set-issue-metadata' ) ) ) {
			return;
		}

		// Check current user has sufficient permissions.
		if ( ! current_user_can( $tax->cap->edit_terms ) ) {
			return;
		}

		// Validate the Issue ID by trying to fetch an associated term object.
		$issue = get_term( $issue_id, $tax_name );
		if ( is_null( $issue ) || is_wp_error( $issue ) ) {
			return;
		}

		/*
		 * Run through the metadata fields in turn.
		 */

		// Issue Date
		if ( isset( $_POST["{$tax_name}_date"] ) ) {

			/*
			 * First try creating a Date using the jQuery UI datepicker's date
			 * format.
			 */
			$date = DateTime::createFromFormat( 'd/m/y', $_POST["{$tax_name}_date"] );

			// Try interpreting a user-inputted date that isn't in the correct format.
			if ( $date ) {
				$date = $date->format( 'U' );
			} else {
				$date = uk_strtotime( $_POST["{$tax_name}_date"] );
			}

			if ( $date ) {
				$output_date = date( 'Y-m-d', $date );
				update_metadata( 'pp_term', $issue_id, "{$tax_name}_date", $output_date );
			}

		}

		// Issue Status
		if ( isset( $_POST["{$tax_name}_status"] )
			&& ( (string) $_POST["{$tax_name}_status"] ) ) {

			$new_value = $_POST["{$tax_name}_status"];

			/**
			 * Filter for allowed Issue publication statuses.
			 *
			 * The filter name is generated from the Issues taxonomy name. By
			 * default it is:
			 *
			 *     pp_issue_statuses
			 *
			 * The returned value should be an associative array of statuses in
			 * the form ( name => display name ).
			 *
			 * @since 1.0.0
			 *
			 * @param array $statuses Array of allowed statuses.
			 */
			$statuses = apply_filters( "{$tax_name}_statuses", array() );

			// Check this is an allowed status for Issues.
			if ( array_key_exists( $new_value, $statuses ) ) {
				update_metadata( 'pp_term', $issue_id, "{$tax_name}_status", $new_value );
			}

		}

	}

}
