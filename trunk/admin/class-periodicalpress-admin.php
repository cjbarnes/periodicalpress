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
		 * Main Issues menu, containing the Issues list table.
		 */
		add_menu_page(
			$tax->labels->name,
			$tax->labels->menu_name,
			$tax->cap->edit_terms, // user capability required to show this menu
			'pp_edit_issues',
			array( $this, 'edit_issues_screen' ),
			'dashicons-pressthis',
			'4.44' // position in the menu (Posts is 5)
		);

		// Issues submenu: repeat of top-level menu page.
		add_submenu_page(
			'pp_edit_issues',
			$tax->labels->name,
			$tax->labels->all_items,
			$tax->cap->edit_terms,
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
				$tax->cap->edit_terms,
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
		$domain = $this->plugin->get_taxonomy_name();

		return array(
			'publish' => __( 'Published', $domain ),
			'draft' => __( 'Draft', $domain )
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
	 * Display the Edit Issues admin page.
	 *
	 * @since 1.0.0
	 */
	public function edit_issues_screen() {

		/**
		 * Output the Edit Issues page.
		 */
		$path = $this->plugin->get_partials_path( 'admin' );
		require $path . 'periodicalpress-edit-pp-issues.php';

	}

	/**
	 * Registers the Screen Options for the Edit Issues admin page.
	 *
	 * @since 1.0.0
	 */
	public function edit_issues_screen_options() {

		$tax = get_taxonomy( $this->plugin->get_taxonomy_name() );

		add_screen_option( 'per_page', array(
			'label' => $tax->labels->name,
			'default' => 20,
			'option' => 'edit_' . $tax->name . '_per_page'
		) );

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

		// Issue Title
		if ( isset( $_POST["{$tax_name}_title"] ) ) {
			update_metadata( 'pp_term', $issue_id, "{$tax_name}_title", trim( $_POST["{$tax_name}_title"] ) );
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
	 * Delete an Issue from the plugin taxonomy.
	 *
	 * Includes deletion of metadata. Note that the returned success/failure/
	 * error value is *not* affected by success or failure of metadata deletion,
	 * since the crucial part of the deletion is the Issue taxonomy term itself.
	 * The worst that can happen is some orphaned metadata rows remain in the
	 * pp_termmeta table.
	 *
	 * @since 1.0.0
	 *
	 * @param int|object $term Either the Issue's term ID or its term object.
	 * @return bool|WP_Error Success/failure of term deletion, or error object.
	 */
	public function delete_issue( $term ) {

		if ( empty( $term ) ) {
			return false;
		}

		$tax_name = $this->plugin->get_taxonomy_name();

		/*
		 * Check this ID matches an existing Issue. If a term slug or object was
		 * passed in (instead of an integer term_id), get the term_id.
		 */
		$term_object = get_term( $term, $tax_name );

		// Return FALSE if Issue doesn't exist, WP_Error if error.
		if ( is_null( $term_object ) || is_wp_error( $term_object ) ) {
			if ( is_null( $term_object ) ) {
				$term_object = false;
			}
			return $term_object;
		}

		$term_id = $term_object->term_id;

		/**
		 * Hook for just before an Issue is deleted.
		 *
		 * @since 1.0.0
		 *
		 * @param int    $term_id     The ID of the Issue taxonomy term to be
		 *                            deleted.
		 * @param object $term_object The complete term object for the Issue.
		 */
		do_action( 'periodicalpress_before_delete_issue', $term_id, $term_object );

		// Returns boolean for success/failure or WP_Error on error.
		$result = wp_delete_term( $term_id, $tax_name );

		// Delete the cached highest issue number.
		delete_transient( 'periodicalpress_highest_issue_num' );

		/*
		 * Only proceed to delete metadata if the term it attaches to was first
		 * deleted successfully.
		 */
		if ( ! is_wp_error( $result ) && $result ) {
			$meta_to_delete = get_metadata( 'pp_term', $term_id );
			foreach ( $meta_to_delete as $meta_key => $meta_values ) {
				delete_metadata( 'pp_term', $term_id, $meta_key );
			}
		}

		/**
		 * Hook for after an Issue is deleted.
		 *
		 * @since 1.0.0
		 *
		 * @param int    $term_id     The ID of the Issue taxonomy term that was
		 *                            deleted.
		 * @param object $term_object The complete term object for the Issue.
		 */
		do_action( 'periodicalpress_delete_issue', $term_id, $term_object );

		return $result;
	}

	/**
	 * Publish a draft Issue.
	 *
	 * Includes setting the Issue's status, slug, number, name, and date (if
	 * empty), and publishing all associated Posts as well.
	 *
	 * @since 1.0.0
	 *
	 * @param int|object $term Either the Issue's term ID or its term object.
	 * @return bool|WP_Error Success/failure of publish, or error object.
	 */
	public function publish_issue( $term ) {

		if ( empty( $term ) ) {
			return false;
		}

		$domain = $this->plugin->get_plugin_name();
		$tax_name = $this->plugin->get_taxonomy_name();

		/*
		 * Check this ID matches an existing Issue. If a term slug or object was
		 * passed in (instead of an integer term_id), get the term_id.
		 */
		$term_object = get_term( $term, $tax_name );

		// Return FALSE if Issue doesn't exist, WP_Error if error.
		if ( is_null( $term_object ) || is_wp_error( $term_object ) ) {
			if ( is_null( $term_object ) ) {
				$term_object = false;
			}
			return $term_object;
		}

		$term_id = $term_object->term_id;

		// End here if the issue is published already.
		$old_status = get_metadata( 'pp_term', $term_id, "{$tax_name}_status" );
		if ( 'publish' === $old_status ) {
			return true;
		}

		// Get a free issue number if there isn't one yet.
		$issue_num = get_metadata( 'pp_term', $term_id, "{$tax_name}_number" );
		if ( ! is_int( $issue_num ) ) {
			$issue_num = $this->create_issue_number();
		}

		$results = array();

		// Term object changes.
		$term_updates = array(
			'name' => sprintf( __( 'Issue %d', $domain ), $issue_num ),
			'slug' => "$issue_num"
		);
		$temp_result = wp_update_term( $term_id, $tax_name, $term_updates );
		$results[] = is_wp_error( $temp_result )
			? $temp_result
			: false;

		// Metadata changes.
		$results[] = update_metadata( 'pp_term', $term_id, "{$tax_name}_number", $issue_num );


		/*
		 * TODO post changes.
		 */

		/**
		 * Action for whenever an Issue status changes.
		 *
		 * @since 1.0.0
		 *
		 * @param string $new_status The new Issue publication status.
		 * @param string $old_status The previous Issue publication status.
		 */
		do_action( 'periodicalpress_transition_issue_status', 'publish', $old_status, $term_id );

		// Only set status to Published if all other changes were successful.
		if ( in_array( false, $results ) ) {
			$result = update_metadata( 'pp_term', $term_id, "{$tax_name}_status", 'publish' );
		} else {
			$result = false;
		}
		return $result;
	}

	/**
	 * Set a published Issue as a draft.
	 *
	 * Hides the Issue and its posts from the public website. Includes setting
	 * the Issue's status, slug, number, and name, and unpublishing all
	 * associated Posts as well.
	 *
	 * @since 1.0.0
	 *
	 * @param int|object $term Either the Issue's term ID or its term object.
	 * @return bool|WP_Error Success/failure of publish, or error object.
	 */
	public function unpublish_issue( $term ) {

		if ( empty( $term ) ) {
			return false;
		}

		$domain = $this->plugin->get_plugin_name();
		$tax_name = $this->plugin->get_taxonomy_name();

		/*
		 * Check this ID matches an existing Issue. If a term slug or object was
		 * passed in (instead of an integer term_id), get the term_id.
		 */
		$term_object = get_term( $term, $tax_name );

		// Return FALSE if Issue doesn't exist, WP_Error if error.
		if ( is_null( $term_object ) || is_wp_error( $term_object ) ) {
			if ( is_null( $term_object ) ) {
				$term_object = false;
			}
			return $term_object;
		}

		$term_id = $term_object->term_id;

		// End here if the issue is unpublished already.
		$old_status = get_metadata( 'pp_term', $term_id, "{$tax_name}_status" );
		if ( 'publish' !== $old_status ) {
			return true;
		}

		// Metadata changes.
		$result = update_metadata( 'pp_term', $term_id, "{$tax_name}_status", 'draft' );


		/*
		 * TODO post changes.
		 */


		$created = time();

		// Create a unique slug for this draft Issue.
		$slug = sprintf( 'draft-%s', date( 'Y-m-d', $created ) );
		$new_slug = $slug;
		$copy = 1;
		while ( get_term_by( 'slug', $new_slug, $tax_name ) ) {
			$new_slug = $slug . '_' . ++$copy;
			write_log( $new_slug );
		}

		// Create a unique name.
		$name = sprintf( __( 'Unpublished Issue (created&nbsp;%s)', $domain ), date( 'Y/m/d', $created ) );
		if ( $copy > 1 ) {
			$name .= ' (' . $copy . ')';
		}

		// Make the DB changes.
		$term_updates = array(
			'name' => $name,
			'slug' => $new_slug
		);
		wp_update_term( $term_id, $tax_name, $term_updates );
		delete_metadata( 'pp_term', $term_id, "{$tax_name}_number" );

		/** This action is documented in admin/class-periodicalpress-admin.php */
		do_action( 'periodicalpress_transition_issue_status', 'draft', $old_status, $term_id );

		// Delete the cached highest issue number.
		delete_transient( 'periodicalpress_highest_issue_num' );

		return $result;
	}

	/**
	 * Returns the next Issue number.
	 *
	 * Uses the transient `periodicalpress_highest_issue_num` for caching, so
	 * this should be invalidated whenever an Issue number is changed elsewhere
	 * (e.g. if an Issue is unpublished or manually edited).
	 *
	 * @since 1.0.0
	 *
	 * @return int The next free Issue number.
	 */
	public function create_issue_number() {

		$transient = 'periodicalpress_highest_issue_num';
		$tax_name = $this->plugin->get_taxonomy_name();

		// Get the highest existing Issue number.
		$highest_num = (int) get_transient( $transient );
		if ( empty( $highest_num ) ) {

			$existing_issues = $this->get_issues_metadata_column( 'pp_issue_number' );
			if ( ! is_array( $existing_issues ) ) {
				return false;
			}

			// Empty array returned, so this is Issue 1.
			if ( ! $existing_issues ) {
				return 1;
			}

			$highest_num = (int) max( $existing_issues );

		}

		$new_issue_num = $highest_num + 1;

		// Get the next number that is not taken.
		while ( get_term_by( 'slug', "$new_issue_num", $tax_name ) ) {
			$new_issue_num++;
		}

		// Cache new highest issue number for later.
		set_transient( $transient, $new_issue_num, 2 * HOUR_IN_SECONDS );

		/**
		 * Filter for newly created Issue numbers.
		 *
		 * @since 1.0.0
		 *
		 * @param int $new_issue_num The Issue number that was created.
		 */
		return apply_filters( 'periodicalpress-new-issue-number', $new_issue_num );
	}

	/**
	 * Retrieves a list of all unique meta values for a set meta key in Issues.
	 *
	 * @since 1.0.0
	 *
	 * @global wpdb $wpdb The WordPress database class.
	 *
	 * @param string $key The meta key to retrieve values for.
	 * @return array|false An array of meta values, or false if unsuccessful. If
	 *                     there are no values, an empty array will be returned.
	 */
	public function get_issues_metadata_column( $key ) {
		global $wpdb;

		if ( empty( $key ) ) {
			return false;
		}

		$sql = $wpdb->prepare( "
			SELECT DISTINCT meta_value FROM {$wpdb->pp_termmeta}
			WHERE meta_key = '$key'
		", $key );
		$values = $wpdb->get_col( $sql );

		return $values;
	}

	/**
	 * Changes Issues metabox placement and title on the Post Editor.
	 *
	 * @since 1.0.0
	 */
	public function add_remove_metaboxes() {

		$domain = $this->plugin->get_plugin_name();
		$tax = get_taxonomy( $this->plugin->get_taxonomy_name() );

		// Remove old Issues metabox.
		remove_meta_box( 'pp_issuediv', 'post', 'side' );

		// Add an identical Issue metabox, further up the page.
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

		$tax_name = $this->plugin->get_taxonomy_name();
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
