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
class PeriodicalPress_Admin {

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
	 * Initialize the class and set its properties.
	 *
	 * Access `protected` enforces the Singleton pattern by disabling the `new`
	 * operator.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function __construct() {

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
	 * - PeriodicalPress_List_Table. Duplicate of private class WP_List_Table.
	 *
	 * @since 1.0.0
	 * @access private
	 */
	private function load_dependencies() {

		/**
		 * The base class that assembles and displays admin list tables.
		 * Duplicate of {@see WP_List_Table} in Core.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-periodicalpress-list-table.php';

	}

	/**
	 * Register the stylesheets for the Dashboard.
	 *
	 * @since 1.0.0
	 *
	 * @see PeriodicalPress
	 */
	public function enqueue_styles() {

		$plugin = PeriodicalPress::get_instance();

		wp_enqueue_style(
			$plugin->get_plugin_name(),
			plugin_dir_url( __FILE__ ) . 'css/periodicalpress-admin.css',
			array(),
			$plugin->get_version(),
			'all'
		);

	}

	/**
	 * Register the JavaScript for the dashboard.
	 *
	 * @since 1.0.0
	 *
	 * @see PeriodicalPress
	 */
	public function enqueue_scripts() {

		$plugin = PeriodicalPress::get_instance();

		wp_enqueue_script(
			$plugin->get_plugin_name(),
			plugin_dir_url( __FILE__ ) . 'js/periodicalpress-admin.js',
			array( 'jquery' ),
			$plugin->get_version(),
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
		$path = PeriodicalPress::get_instance()->get_partials_path( 'admin' );
		require $path . 'periodicalpress-issues-home.php';

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
			$path = PeriodicalPress::get_instance()->get_partials_path( 'admin' );
			require $path . 'periodicalpress-current-issue-form.php';

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
	 * Outputs form fields on the Add Issue pages for metadata items.
	 *
	 * @since 1.0.0
	 */
	public function display_add_issue_metadata_fields() {

		$path = PeriodicalPress::get_instance()->get_partials_path( 'admin' );
		require $path . 'periodicalpress-add-issue-metadata.php';

	}

	/**
	 * Outputs form fields on the Edit Issue pages for metadata items.
	 *
	 * @since 1.0.0
	 *
	 * @param object $issue    Taxonomy term object for the Issue being edited.
	 */
	public function display_edit_issue_metadata_fields( $issue ) {

		$path = PeriodicalPress::get_instance()->get_partials_path( 'admin' );
		require $path . 'periodicalpress-edit-issue-metadata.php';

	}

	/**
	 * Replaces Issues metabox on the Post Editor.
	 *
	 * Runs after all core metaboxes have been added. Removes the default
	 * taxonomy metabox and declares a simpler, bespoke alternative.
	 *
	 * @since 1.0.0
	 */
	public function add_remove_metaboxes() {

		// Remove old, multi-selecting Issues metabox
		remove_meta_box( 'pp_issuediv', 'post', 'side' );

		// Add a new, bespoke Issue metabox
		if ( current_user_can( 'assign_pp_issue' ) ) {

			add_meta_box(
				'pp_issuediv',
				__( 'Issue', 'periodicalpress' ),
				array( $this, 'render_issue_metabox' ),
				'post',
				'side',
				'high'
			);

		}

	}

	/**
	 * The Issue metabox contents.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Post $post The post being edited.
	 */
	public function render_issue_metabox( $post ) {

		$path = PeriodicalPress::get_instance()->get_partials_path( 'admin' );
		require $path . 'periodicalpress-issue-metabox.php';

	}

	/**
	 * Save a post’s chosen Issue to the DB.
	 *
	 * @since 1.0.0
	 * @link http://codex.wordpress.org/Function_Reference/add_meta_box
	 *
	 * @param int $post_id ID of the post being saved.
	 * @return int ID of the post being saved.
	 */
	public function save_issue_metabox( $post_id ) {

		/*
		 * If this is an autosave, our form has not been submitted, so we don't
         * want to do anything.
         */
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			return $post_id;

		// Check this is the correct post type.
		if ( ! isset( $_POST['post_type'] )
			|| ( 'post' !== $_POST['post_type'] ) ) {

			return $post_id;
		}

		// Check form nonce was properly set.
		if ( empty( $_POST['periodicalpress-post-issue-nonce'] )
			|| ( 1 !== wp_verify_nonce( $_POST['periodicalpress-post-issue-nonce'], 'set-post-issue' ) ) ) {
			return $post_id;
		}

		// Check permissions.
		if ( ! current_user_can( 'assign_pp_issue' ) ) {
			return $post_id;
		}

		// Check new issue data is present and valid.
		if ( ! isset( $_POST['pp_issue'] ) ) {
			return $post_id;
		}

		/**
		 * Update the DB if the new issue exists or if 'No issue' was selected.
		 */
		$new_issue = intval( $_POST['pp_issue'] );

		if ( -1 === $new_issue ) {

			wp_delete_object_term_relationships( $post_id, 'pp_issue' );

		} elseif ( ! is_null( get_term( $new_issue, 'pp_issue' ) ) ) {

			wp_set_post_terms( $post_id, $new_issue, 'pp_issue' );

		} else {
			return $post_id;
		}

	}

}
