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
	 * - PeriodicalPress_Settings. The Plugin Settings page.
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
		require_once $path . 'admin/class-periodicalpress-settings.php';

		// Instantiate classes.
		PeriodicalPress_Edit_Issues::get_instance( $this->plugin );
		PeriodicalPress_Post_Issue_Box::get_instance( $this->plugin );
		PeriodicalPress_Save_Issues::get_instance( $this->plugin );
		PeriodicalPress_Settings::get_instance( $this->plugin );

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

		// Admin menu items setup.
		add_action( 'admin_menu', array( $this, 'admin_menu_setup' ) );
		add_filter( 'parent_file', array( $this, 'fix_submenu_parent_files' ) );

		// Date formats filter.
		add_filter( 'periodicalpress_date_formats', array( $this, 'add_date_format_suggestions' ) );

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
		wp_enqueue_script( $name, "{$path}periodicalpress-admin.js", array( 'jquery' ), $version );

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
	 * Basic changes to admin menu and submenu items.
	 *
	 * @since 1.0.0
	 */
	public function admin_menu_setup() {

		$pp_edit_issues = PeriodicalPress_Edit_Issues::get_instance( $this->plugin );

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
			array( $pp_edit_issues, 'edit_issues_screen' ),
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

		// Issues submenu: Add new Issue.
		add_submenu_page(
			'pp_edit_issues',
			$tax->labels->add_new_item,
			_x( 'Add New', 'Admin menu link to Add New Issue', 'periodicalpress' ),
			$tax->cap->edit_terms,
			'pp_add_issue',
			array( $pp_edit_issues, 'add_issue_screen' )
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

}
