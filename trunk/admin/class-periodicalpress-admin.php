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
	 * @return PeriodicalPress_Admin Instance of this class.
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
		require_once $this->plugin->get_plugin_path() . 'admin/class-periodicalpress-list-table.php';

	}

	/**
	 * Register the stylesheets for the Dashboard.
	 *
	 * @since 1.0.0
	 *
	 * @see PeriodicalPress
	 */
	public function enqueue_styles() {

		wp_enqueue_style(
			$this->plugin->get_plugin_name(),
			plugin_dir_url( __FILE__ ) . 'css/periodicalpress-admin.css',
			array(),
			$this->plugin->get_version(),
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

		$domain = $this->plugin->get_plugin_name();

		wp_enqueue_script(
			$this->plugin->get_plugin_name(),
			plugin_dir_url( __FILE__ ) . 'js/periodicalpress-admin.js',
			array(
				'jquery',
				'jquery-ui-core',
				'jquery-ui-datepicker'
			),
			$this->plugin->get_version(),
			true
		);

		// Translation of JavaScript strings
		wp_localize_script(
			$this->plugin->get_plugin_name(),
			'l10n',
			array(
				'datepickerCurrentText' => _x( 'Today', 'Datepicker Button', $domain ),
				'datepickerDateFormat' => _x( 'dd/mm/yy', 'Datepicker Date Format', $domain ),
				'isRTL' => is_rtl() ? 'true' : 'false',

			)
		);

	}

	/**
	 * All changes to admin menu and submenu items.
	 *
	 * @since 1.0.0
	 */
	public function admin_menu_setup() {

		$domain = $this->plugin->get_plugin_name();
		$tax = get_taxonomy( $this->plugin->get_taxonomy_name() );

		// Get Issues taxonomy labels for use by menu pages/subpages.
		$tax_labels = $tax->labels;

		/*
		 * Main Issues menu, containing the Issues taxonomy page and some
		 * plugin settings.
		 */
		add_menu_page(
			$tax->labels->name,
			$tax->labels->menu_name,
			$tax->cap->edit_terms, // user capability required to show this menu
			'pp_issues_home',
			array( $this, 'issues_home_screen' ),
			'dashicons-pressthis',
			'4.44' // position in the menu (Posts is 5)
		);

		// Issues submenu: repeat of top-level menu page.
		add_submenu_page(
			'pp_issues_home',
			__( 'Issues Home', $domain ),
			__( 'Issues Home', $domain ),
			$tax->cap->edit_terms,
			'pp_issues_home'
		);

		// Issues submenu: Add and edit the Issues taxonomy.
		add_submenu_page(
			'pp_issues_home',
			$tax->labels->name,
			$tax->labels->all_items,
			'manage_pp_issues', // cap required
			"edit-tags.php?taxonomy={$tax->name}"
		);

		// Issues submenu: Add and edit the Issues taxonomy. (TEST).
		add_submenu_page(
			'pp_issues_home',
			$tax->labels->name,
			$tax->labels->all_items,
			$tax->cap->edit_terms,
			'pp_edit_issues',
			array( $this, 'edit_issues_screen' )
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

		if ( $this->plugin->get_taxonomy_name() === $screen->taxonomy )  {
			$parent_file = 'pp_issues_home';
		}

		return $parent_file;
	}

	/**
	 * Returns an associative array of allowed publication statuses for Issues.
	 *
	 * Called first by the filter {@see {$tax_name}_statuses}.
	 *
	 * @since 1.0.0
	 *
	 * @param array $statuses The allowed statuses.
	 * @return array The statuses array, in form ( name => display name ).
	 */
	public function set_issue_statuses_list( $statuses ) {
		return array(
			'publish' => 'Published',
			'draft' => 'Draft',
			'trash' => 'Trash'
		);
	}



	/**
	 * Display the main Issues admin page.
	 *
	 * @since 1.0.0
	 */
	public function issues_home_screen() {

		/**
		 * Output the Issue Settings page.
		 */
		$path = $this->plugin->get_partials_path( 'admin' );
		require $path . 'periodicalpress-issues-home.php';

	}

	/**
	 * Display the Edit Issues admin page. (TEST).
	 *
	 * @since 1.0.0
	 */
	public function edit_issues_screen() {

		/**
		 * Output the Edit Issues page.
		 */
		$path = $this->plugin->get_partials_path( 'admin' );
		require $path . 'periodicalpress-edit-pp-issues.php';
		//require $path . '../test.php';

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
	 * Outputs form fields on the Add Issue pages for metadata items.
	 *
	 * @since 1.0.0
	 */
	public function display_add_issue_metadata_fields() {

		$path = $this->plugin->get_partials_path( 'admin' );
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

		$path = $this->plugin->get_partials_path( 'admin' );
		require $path . 'periodicalpress-edit-issue-metadata.php';

	}

	/**
	 * Save submitted metadata form fields on the Add/Edit Issue pages.
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
			if ( ! $date ) {
				$date = uk_strtotime( $_POST["{$tax_name}_date"] );
			}

			write_log($date);

			if ( $date ) {
				update_metadata( 'pp_term', $issue_id, "{$tax_name}_date", date( 'Y-m-d', $date ) );
			}

		}

		// Issue Title
		if ( isset( $_POST["{$tax_name}_title"] ) ) {
			update_metadata( 'pp_term', $issue_id, "{$tax_name}_title", $_POST["{$tax_name}_title"] );
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

	/**
	 * Replaces Issues metabox on the Post Editor.
	 *
	 * Runs after all core metaboxes have been added. Removes the default
	 * taxonomy metabox and declares a simpler, bespoke alternative.
	 *
	 * @since 1.0.0
	 */
	public function add_remove_metaboxes() {

		$domain = $this->plugin->get_plugin_name();
		$tax = get_taxonomy( $this->plugin->get_taxonomy_name() );

		// Remove old, multi-selecting Issues metabox
		remove_meta_box( 'pp_issuediv', 'post', 'side' );

		// Add a new, bespoke Issue metabox
		if ( current_user_can( $tax->cap->assign_terms ) ) {

			add_meta_box(
				'pp_issuediv',
				__( 'Issue', $domain ),
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

		$path = $this->plugin->get_partials_path( 'admin' );
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

		$tax_name = $this->plugin->$get_taxonomy_name();
		$tax = get_taxonomy( $tax_name );

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
		if ( ! current_user_can( $tax->cap->assign_terms ) ) {
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

			wp_delete_object_term_relationships( $post_id, $tax_name );

		} elseif ( ! is_null( get_term( $new_issue, $tax_name ) ) ) {

			wp_set_post_terms( $post_id, $new_issue, $tax_name );

		} else {
			return $post_id;
		}

	}

}
