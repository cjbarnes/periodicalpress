<?php

/**
 * Adding, editing, and deleting functionality for Issues
 *
 * @since 1.0.0
 *
 * @package PeriodicalPress\Admin
 */

/**
 * Adding, editing, and deleting functionality for Issues.
 *
 * Uses the Singleton pattern.
 *
 * @since 1.0.0
 */
class PeriodicalPress_Edit_Issues {

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
	 * @return PeriodicalPress_Edit_Issues Instance of this class.
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
	 * Register all meta boxes for the Edit Issue screen.
	 *
	 * @since 1.0.0
	 */
	public function add_remove_metaboxes() {

		$domain = $this->plugin->get_taxonomy_name();

		/*
		 * Side column metaboxes:
		 * - Meta - number (read only) and date
		 * - Status - save, publish, unpublish, delete, Set Current Issue,
		 * 	 Preview buttons
		 * - Colour (@todo)
		 * - Cover photo (@todo)
		 * - Body class (@todo)
		 */
		add_meta_box(
			'pp_issue_metadiv',
			_x( 'Meta', 'Edit Issue meta box title', $domain ),
			array( $this, 'render_meta_metabox' ),
			'pp_issue',
			'side',
			'core'
		);
		add_meta_box(
			'pp_issue_submitdiv',
			_x( 'Publish', 'Edit Issue meta box title', $domain ),
			array( $this, 'render_submit_metabox' ),
			'pp_issue',
			'side',
			'core'
		);


		/*
		 * Main column metaboxes:
		 * - Description
		 */
		add_meta_box(
			'pp_issue_descriptiondiv',
			_x( 'Description', 'Edit Issue meta box title', $domain ),
			array( $this, 'render_description_metabox' ),
			'pp_issue',
			'normal',
			'core'
		);


	}

	/**
	 * Output the Issue status metabox on the Edit Issue screen.
	 *
	 * @since 1.0.0
	 *
	 * @param object $issue The Issue term object.
	 */
	public function render_submit_metabox( $issue ) {

	}

	/**
	 * Output the Issue Date/Number metabox on the Edit Issue screen.
	 *
	 * @since 1.0.0
	 *
	 * @param object $issue The Issue term object.
	 */
	public function render_meta_metabox( $issue ) {

	}

	/**
	 * Output the Issue Description metabox on the Edit Issue screen.
	 *
	 * @since 1.0.0
	 *
	 * @param object $issue The Issue term object.
	 */
	public function render_description_metabox( $issue ) {

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

		$plugin_common = PeriodicalPress_Common::get_instance( $this->plugin );

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
		$old_status = get_metadata( 'pp_term', $term_id, "{$tax_name}_status", true );
		if ( 'publish' === $old_status ) {
			return true;
		}

		// Get a free issue number if there isn't one yet.
		$issue_num = get_metadata( 'pp_term', $term_id, "{$tax_name}_number", true );
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

		// Get the ready-to-publish posts attached to this Issue.
		$post_statuses = array(
			'pending',
			'future'
		);
		$term_posts = $plugin_common->get_issue_posts( $term_id, $post_statuses );

		// Publish the waiting Issues.
		foreach ( $term_posts as $post ) {
			$new_post_data = array(
				'ID'          => $post->ID,
				'post_status' => 'publish'
			);
			wp_update_post( $new_post_data );
		}

		// Set this as the current issue.
		update_option( 'pp_current_issue', $term_id );

		$plugin_common->delete_issue_transients();

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

		$plugin_common = PeriodicalPress_Common::get_instance( $this->plugin );

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
		$old_status = get_metadata( 'pp_term', $term_id, "{$tax_name}_status", true );

		if ( 'publish' !== $old_status ) {
			return true;
		}

		// Metadata changes.
		$result = update_metadata( 'pp_term', $term_id, "{$tax_name}_status", 'draft' );

		// Get the already published posts attached to this Issue.
		$term_posts = $plugin_common->get_issue_posts( $term_id, 'publish' );

		// Send them back to Pending.
		foreach ( $term_posts as $post ) {
			$new_post_data = array(
				'ID'          => $post->ID,
				'post_status' => 'pending'
			);
			wp_update_post( $new_post_data );
		}

		/*
		 * If this is the current issue, change the current issue to the most
		 * recent issue.
		 */
		$this->set_not_current_issue( $term_id );

		$created = time();

		// Create a unique slug for this draft Issue.
		$slug = sprintf( 'draft-%s', date( 'Y-m-d', $created ) );
		$new_slug = $slug;
		$copy = 1;
		while ( get_term_by( 'slug', $new_slug, $tax_name ) ) {
			$new_slug = $slug . '_' . ++$copy;
			$new_slug;
		}

		// Create a unique name.
		$name = sprintf( __( 'New Issue (unpublished&nbsp;%s)', $domain ), date( 'Y/m/d', $created ) );
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

		$plugin_common->delete_issue_transients();

		return $result;
	}

	/**
	 * If this post is being unpublished, check whether its Issue is now empty
	 * and therefore should also be unpublished.
	 *
	 * Hooks into all post transitions.
	 *
	 * @since 1.0.0
	 *
	 * @param string  $new_status The new post status.
	 * @param string  $old_status The original post status.
	 * @param WP_Post $post       The current post object.
	 */
	public function unpublish_post_issues_if_empty( $new_status, $old_status, $post ) {

		/*
		 * End here if this post isn't changing from Published to some other
		 * status.
		 */
		if ( ( 'publish' !== $old_status ) || ( $new_status === $old_status ) ) {
			return;
		}

		$tax_name = $this->plugin->get_taxonomy_name();

		$issues = wp_get_post_terms( $post->ID, $tax_name );

		foreach( $issues as $issue ) {
			unpublish_issue_if_empty( $issue, $tax_name );
		}

	}

	/**
	 * Unpublish this Issue if it has no posts.
	 *
	 * @since 1.0.0
	 *
	 * @param int|object    $issue The Issue ID or object to unpublish if empty.
	 * @param string|object $tax   The Issues taxonomy or its name.
	 * @return bool|WP_Error Success, failure, or error.
	 */
	public function unpublish_issue_if_empty( $issue, $tax ) {

		$tax_name = ( is_object( $tax ) && isset( $tax->name ) )
			? $tax->name
			: $tax;

		// Get the Issue object if only an ID was passed.
		$issue = get_term( $issue, $tax_name );

		// Return FALSE if Issue doesn't exist, WP_Error if error.
		if ( is_null( $issue ) || is_wp_error( $issue ) ) {
			if ( is_null( $issue ) ) {
				$issue = false;
			}
			return $issue;
		}

		$status = get_metadata( 'pp_term', $issue->term_id, "{$tax_name}_status", true );

		// Unpublish if the Issue both is published and has no posts.
		if ( ( 'publish' === $status ) && ( 0 === $issue->count ) ) {
			$ret = $this->unpublish_issue( $issue );
		} else {
			$ret = true;
		}

		return $ret;
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

		$plugin_common = PeriodicalPress_Common::get_instance( $this->plugin );

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

		/*
		 * If this is the current issue, change the current issue to the most
		 * recent issue.
		 */
		$this->set_not_current_issue( $term_id );

		// Get the posts attached to this Issue.
		$post_statuses = array(
			'publish',
			'pending',
			'draft',
			'future',
			'private'
		);
		$term_posts = $plugin_common->get_issue_posts( $term_id, $post_statuses );

		/*
		 * Update posts in this Issue. The Issue term is removed and the post is
		 * moved to Trash.
		 */
		foreach ( $term_posts as $post ) {
			wp_remove_object_terms( $post->ID, $term_id, $tax_name );
			$new_post_data = array(
				'ID'          => $post->ID,
				'post_status' => 'trash'
			);
			wp_update_post( $new_post_data );
		}

		// Returns boolean for success/failure or WP_Error on error.
		$result = wp_delete_term( $term_id, $tax_name );

		$plugin_common->delete_issue_transients();

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

		$plugin_common = PeriodicalPress_Common::get_instance( $this->plugin );

		$transient = 'periodicalpress_highest_issue_num';
		$tax_name = $this->plugin->get_taxonomy_name();

		// Get the highest existing Issue number.
		$highest_num = (int) get_transient( $transient );
		if ( empty( $highest_num ) ) {

			$existing_issues = $plugin_common->get_issues_metadata_column( 'pp_issue_number' );
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
	 * Make sure a given Issue is not the Current Issue.
	 *
	 * Used when deleting or unpublishing Issues, to check that the Issue to be
	 * removed is not the Current Issue. If it is, the Current Issue is
	 * automatically set to the next best issue.
	 *
	 * @since 1.0.0
	 *
	 * @param int $excluded_id The Issue term_id that should not be the Current
	 *                         Issue.
	 * @return bool Result.
	 */
	public function set_not_current_issue( $excluded_id = 0 ) {

		$plugin_common = PeriodicalPress_Common::get_instance( $this->plugin );

		$excluded_id = (int) $excluded_id;

		// Check whether the Issue passed in is the Current Issue.
		if ( (int) get_option( 'pp_current_issue' ) === $excluded_id ) {

			$new_current_issue = $plugin_common->get_newest_issue( array( $excluded_id ) );

			if ( is_object( $new_current_issue )
			&& ! empty( $new_current_issue->term_id ) ) {
				update_option( 'pp_current_issue', $new_current_issue->term_id );
			} else {
				delete_option( 'pp_current_issue' );
			}

		}

		return true;
	}

}
