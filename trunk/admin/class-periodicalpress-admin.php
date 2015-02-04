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

		// Date formats filter.
		add_filter( 'periodicalpress_date_formats', array( $this, 'add_date_format_suggestions' ) );

		// Plugins page.
		$plugin_name = $this->plugin->get_plugin_name();
		$actions_filter = "plugin_action_links_$plugin_name/$plugin_name.php";
		add_filter( $actions_filter, array( $this, 'add_plugin_row_actions' ), 10, 4 );

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

		// Script used thoughout the admin area.
		wp_enqueue_script( $name, "{$path}periodicalpress-admin.js", array( 'jquery' ), $version, true );

		// Script that enables Quick Editing for posts.
		$screen = get_current_screen();
		if ( 'edit-post' === $screen->id ) {

			wp_enqueue_script( "{$name}_posts_list_table", "{$path}periodicalpress-posts-list-table.js", array( 'jquery' ), $version, true );
			/*
			 * Localization object for the posts list table script file. All
			 * key-value pairs in this array will be available as a global
			 * 'l10n' object in the JavaScript file
			 * ({@see wp_localize_script()}).
			 */
			wp_localize_script( "{$name}_posts_list_table", 'l10n', array(
				'publishStatusName' => esc_html__( 'Published' )
			) );

		}

	}

	/**
	 * All changes to admin menu and submenu items.
	 *
	 * @since 1.0.0
	 */
	public function admin_menu_setup() {

		$plugin_edit_issues = PeriodicalPress_Edit_Issues::get_instance( $this->plugin );

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

		add_submenu_page(
			'pp_edit_issues',
			/*
			 * For translators: HTML title of the plugin settings page. %s is
			 * the plugin name.
			 */
 			sprintf( __( '%s Settings', 'periodicalpress' ), 'PeriodicalPress' ),
			_x( 'Settings', 'Admin menu link for plugin settings page', 'periodicalpress' ),
			$tax->cap->manage_terms,
			'periodicalpress_settings',
			array( $this, 'render_plugin_settings' )
		);

		/*
		 * Issues submenu: Add and edit the Issues taxonomy - old version for
		 * debugging purposes.
		 */
		if ( WP_DEBUG ) {
			add_submenu_page(
				'pp_edit_issues',
				$tax->labels->name,
				sprintf( _x( '%s (debugging)', 'Admin menu', 'periodicalpress' ), $tax->labels->all_items ),
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
	 * Add a Settings link for this plugin to the Plugins table.
	 *
	 * Filters the row-action links for this plugin's entry in the Plugins page
	 * list table.
	 *
	 * @since 1.0.0
	 *
	 * @param array  $actions     The currently set row-actions link elements.
	 * @param string $plugin_file Path to the plugin file.
	 * @param array  $plugin_data Array of plugin data.
	 * @param string $context     The plugin context (e.g. Must-Use,
	 *                            'Inactive').
	 */
	public function add_plugin_row_actions( $actions, $plugin_file, $plugin_data, $context ) {

		$link = sprintf( '<a href="%s" title="%s" class="settings">%s</a>',
			admin_url( 'admin.php?page=periodicalpress_settings' ),
			esc_attr__( 'PeriodicalPress Settings', 'periodicalpress' ),
			esc_html_x( 'Settings', 'Plugins table link to plugin settings page', 'periodicalpress' )
		);

		array_unshift( $actions, $link );
		return $actions;
	}

	/**
	 * Filter to customize the date formats suggested for the Issue Date Format
	 * setting.
	 *
	 * @since 1.0.0
	 *
	 * @param array $date_formats Date format string suggestions.
	 * @return array Revised date format suggestions.
	 */
	public function add_date_format_suggestions( $date_formats ) {
		return array_merge( $date_formats, array( 'F Y', 'Y' ) );
	}

	/**
	 * Output the contents of the Plugin Settings page.
	 *
	 * @since 1.0.0
	 */
	public function render_plugin_settings() {

		$tax = get_taxonomy( $this->plugin->get_taxonomy_name() );

		if ( current_user_can( $tax->cap->manage_terms ) ) {

			/**
			 * Output the Settings page partial.
			 */
			$path = $this->plugin->get_partials_path( 'admin' );
			require $path . 'periodicalpress-settings.php';

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

}
