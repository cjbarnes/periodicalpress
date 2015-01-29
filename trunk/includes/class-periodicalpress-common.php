<?php

/**
 * The functionality of the plugin that affects both Dashboard and public-
 * facing pages
 *
 * @since 1.0.0
 *
 * @package PeriodicalPress
 */

/**
 * The shared functionality of the plugin.
 *
 * Many of these methods are utility functions for communicating with the DB,
 * retrieving Issues, and additions to the Core taxonomy functionality.
 *
 * Uses the Singleton pattern.
 *
 * @since 1.0.0
 */
class PeriodicalPress_Common extends PeriodicalPress_Singleton {

	/**
	 * Load the required dependencies for the admin area.
	 *
	 * Include the following files:
	 *
	 * - PeriodicalPress_Taxonomy. Registers the main Issues taxonomy.
	 *
	 * Called by the parent class's Constructor.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function load_dependencies() {

		$path = $this->plugin->get_plugin_path();

		// Include all other common-functionality classes.
		require_once $path . 'includes/class-periodicalpress-taxonomy.php';

		// Instantiate classes.
		PeriodicalPress_Taxonomy::get_instance( $this->plugin );

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

		$tax_name = $this->plugin->get_taxonomy_name();

		// Register custom taxonomy metadata table with $wpdb database object.
		add_action(	'init',	array( $this, 'register_metadata_table' ) );

		// Set allowed Issue publication statuses.
		add_filter( "{$tax_name}_statuses", array( $this, 'set_issue_statuses_list' ), 1, 1 );

	}

	/**
	 * Add the Issues metadata table to $wpdb object.
	 *
	 * Required for get_metadata(), update_metadata(), and delete_metadata() to
	 * work.
	 *
	 * @global wpdb $wpdb The WordPress database object.
	 *
	 * @since 1.0.0
	 */
	public function register_metadata_table() {
		global $wpdb;

		if ( empty( $wpdb->pp_termmeta ) ) {
			$wpdb->pp_termmeta = $wpdb->prefix . 'pp_termmeta';
		}
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
			'publish' => __( 'Published', 'periodicalpress' ),
			'draft' => __( 'Draft', 'periodicalpress' )
		);
	}

	/**
	 * Function to set the Issue number for an Issue.
	 *
	 * Checks that the new Issue number is unique first.
	 *
	 * @since 1.0.0
	 *
	 * @param int $issue_id  The Issue's term ID.
	 * @param int $issue_num The new Issue number.
	 * @return bool Success or failure of the DB update.
	 */
	public function update_issue_number( $issue_id, $issue_num ) {
		if ( ! is_numeric( $issue_num )
		|| ! ( $issue_num = absint( $issue_num ) ) ) {
			return false;
		}

		// Check for uniqueness of this value.
		$all_nums = $this->get_issues_metadata_column( 'pp_issue_number' );
		if ( in_array( $issue_num, $all_nums ) ) {
			return false;
		}

		$result = $this->update_issue_meta( $issue_id, 'pp_issue_number', $issue_num );

		// Delete cached issue numbers if they've changed.
		if ( $result ) {
			$this->delete_issue_transients();
		}

		return $result;
	}

	/**
	 * Wrapper for add_metadata() for Issues.
	 *
	 * Assumes that multiple values are not allowed for any Issue meta keys.
	 *
	 * If this meta key already exists for this Issue, nothing is changed. (Use
	 * {@see update_issue_meta()} to overwrite existing meta values.)
	 *
	 * @since 1.0.0
	 * @see add_metadata()
	 *
	 * @param int    $issue_id   ID of the Issue the new metadata belongs to.
	 * @param string $meta_key   Key to insert.
	 * @param string $meta_value Value to insert.
	 * @return int|bool The row ID of the inserted meta key-value pair, or FALSE
	 *                  on failure.
	 */
	public function add_issue_meta( $issue_id, $meta_key, $meta_value ) {
		return add_metadata( 'pp_term', $issue_id, $meta_key, $meta_value, true );
	}

	/**
	 * Wrapper for update_metadata() for Issues.
	 *
	 * @since 1.0.0
	 * @see update_metadata()
	 *
	 * @param int    $issue_id   ID of the Issue the metadata belongs to.
	 * @param string $meta_key   Key to edit/add.
	 * @param string $meta_value Value to edit/add.
	 * @param string $prev_value Optional. If set, only overwrite existing
	 *                           values if they match this param. Default ''.
	 * @return bool Success of update.
	 */
	public function update_issue_meta( $issue_id, $meta_key, $meta_value, $prev_value = '' ) {
		return update_metadata( 'pp_term', $issue_id, $meta_key, $meta_value, $prev_value );
	}

	/**
	 * Wrapper for delete_metadata() for Issues.
	 *
	 * Lacks the `$delete_all` param of `delete_metadata()`, which allows the
	 * bulk deletion of key-value pairs matching `$meta_key` and `$meta_value`
	 * across all Issues. Use `delete_metadata()` instead to do this.
	 *
	 * @since 1.0.0
	 * @see delete_metadata()
	 *
	 * @param int    $issue_id   ID of the Issue the metadata belongs to.
	 * @param string $meta_key   Key to delete.
	 * @param string $meta_value Optional. If set, only delete key-value pairs
	 *                           whose values match this param.
	 * @return bool Success of deletion.
	 */
	public function delete_issue_meta( $issue_id, $meta_key, $meta_value ) {
		return delete_metadata( 'pp_term', $issue_id, $meta_key, $meta_value );
	}

	/**
	 * Wrapper for get_metadata() for Issues.
	 *
	 * Assumes that only one meta value is allowed per meta key, and therefore
	 * returns a one-dimensional array when multiple key-value pairs are being
	 * retrieved (unlike the Core {@see get_metadata()} function, which returns
	 * an array of values for each meta key).
	 *
	 * @since 1.0.0
	 * @see get_metadata()
	 *
	 * @param int    $issue_id The Issue ID to retrieve metadata for.
	 * @param string $meta_key Optional. The metadata key to retrieve. If empty,
	 *                         an associative array of all metadata for this
	 *                         Issue will be retrieved. Default ''.
	 * @return string|array|bool The metadata value or array of values.
	 *                           Returned string|array is empty if meta_key
	 *                           doesn't exist. FALSE if a param is invalid.
	 */
	public function get_issue_meta( $issue_id, $meta_key = '' ) {

		$meta_raw = get_metadata( 'pp_term', $issue_id, $meta_key, true );

		if ( ! empty( $meta_key ) ) {
			$meta = $meta_raw;
		} else {
			// Convert two-dimensional array into a simple associative array.
			$meta = array_combine( array_keys( $meta_raw ), array_column( $meta_raw, 0 ) );
		}
		return $meta;
	}

	/**
	 * Retrieve an array of the posts objects associated with a single Issue.
	 *
	 * Note that a `$status` **must** be provided, or the function fails. This
	 * is to prevent likely errors, because {@see get_posts()} does not just
	 * return everything when no `post_status` arg is provided.
	 *
	 * To retrieve posts of any status (other than auto-drafts and trash), pass
	 * in 'any' as the `$status` param.
	 *
	 * @since 1.0.0
	 *
	 * @param int          $issue_id The Issue term ID to get posts for.
	 * @param array|string $status   Which post statuses to return.
	 * @return array The post objects {@see WP_Post}.
	 */
	public function get_issue_posts( $issue_id, $status ) {

		// Sanitize and prep the post statuses passed in.
		$allowed_statuses = get_post_stati();
		if ( is_array( $status ) ) {
			$statuses = array_intersect( $status, $allowed_statuses );
		} else {
			if ( ( 'any' === $status )
			|| in_array( $status, $allowed_statuses ) ) {
				$statuses = $status;
			}
		}

		if ( empty( $statuses ) ) {
			return false;
		}

		// Get the post objects.
		$args = array(
			'posts_per_page' => -1,
			'orderby'        => 'none',
			'post_status'    => $statuses,
			'tax_query'      => array(
				array(
					'taxonomy' => $this->plugin->get_taxonomy_name(),
					'field'    => 'term_id',
					'terms'    => (int) $issue_id
				)
			)
		);
		return get_posts( $args );
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
			WHERE meta_key = '%s'
		", $key );
		$values = $wpdb->get_col( $sql );

		return $values;
	}

	/**
	 * Retrieves the newest (i.e. highest-numbered) published Issue.
	 *
	 * Used for setting a new Current Issue when the previous one is removed.
	 *
	 * See note about $excludes in {@see $this->get_ordered_issue_IDs()}.
	 *
	 * @since 1.0.0
	 *
	 * @global wpdb $wpdb The WordPress database class.
	 *
	 * @param array $excludes Optional. Term IDs that shouldn't count because
	 *                        they are being removed. Default array().
	 * @return object The newest Issue's term object.
	 */
	public function get_newest_issue( $excludes = array() ) {

		$tax_name = $this->plugin->get_taxonomy_name();

		// Retrieve the term object and return.
		return get_term( $this->get_newest_issue_id( $excludes ), $tax_name );
	}

	/**
	 * Retrieves the ID of the newest (i.e. highest-numbered) published Issue.
	 *
	 * Used for setting a new Current Issue when the previous one is removed,
	 * and for getting the most recent issue when a Current Issue is not set
	 * (which should not happen in ordinary circumstances).
	 *
	 * See note about $excludes in {@see $this->get_ordered_issue_IDs()}.
	 *
	 * @since 1.0.0
	 *
	 * @global wpdb $wpdb The WordPress database class.
	 *
	 * @param array $excludes Optional. Term IDs that shouldn't count because
	 *                        they are being removed. Default array().
	 * @return int The newest Issue's ID.
	 */
	public function get_newest_issue_id( $excludes = array() ) {

		$tax_name = $this->plugin->get_taxonomy_name();

		$term_ids = $this->get_ordered_issue_IDs( $excludes );

		return isset( $term_ids[0] )
			? intval( $term_ids[0] )
			: 0;
	}

	/**
	 * Retrieves the published Issues in descending order of issue.
	 *
	 * Note that the result of this function is cached, without including any
	 * Issues that were left out using the `$excludes` param. So **only use
	 * `$excludes` to leave out Issues that are about to be deleted or
	 * unpublished** and therefore shouldn't be included in the cached results
	 * anyway.
	 *
	 * @since 1.0.0
	 *
	 * @global wpdb $wpdb The WordPress database class.
	 *
	 * @param array $excludes Optional. Term IDs that shouldn't count because
	 *                        they are being removed. Default array().
	 * @return array An ordered array of Issue term IDs.
	 */
	public function get_ordered_issue_IDs( $excludes = array() ) {
		global $wpdb;

		// Try the cached values first.
		$term_ids = get_transient( 'periodicalpress_ordered_issues' );
		if ( ! $term_ids ) {

			$tax_name = $this->plugin->get_taxonomy_name();

			/*
			 * Escape the $excludes strings since we're not using
			 * wpdb::prepare().
			 */
			$excludes = array_map( 'esc_sql', (array) $excludes );

			// Get the Current Issue for use in the query.
			$current_issue = (int) get_option( 'pp_current_issue' , 0 );

			// Assemble query for the term_ids of all published issues.
			$sql = "
				SELECT m1.pp_term_id
				FROM {$wpdb->pp_termmeta} m1, {$wpdb->pp_termmeta} m2
				WHERE m1.meta_key = 'pp_issue_number'
				AND m2.meta_key = 'pp_issue_status'
				AND m1.pp_term_id = m2.pp_term_id
				AND m2.meta_value = 'publish'
			";

			// Which Issues to leave out of the results.
			if ( ! empty( $excludes ) ) {
				$sql .= "
					AND m1.pp_term_id NOT IN ('" . implode( $excludes, "','" ) . "')
				";
			}

			// Ordering. Current Issue comes first.
			$sql_current_issue = ( $current_issue )
				? " (m1.pp_term_id = $current_issue) DESC,"
				: '';
			$sql .= "
				ORDER BY$sql_current_issue LENGTH(m1.meta_value) DESC, m1.meta_value DESC
			";

			// Run the query.
			$term_id_strings = $wpdb->get_col( $sql );
			if ( ! empty( $term_id_strings ) ) {

				// Cast results to integers.
				$term_ids = array_map( 'intval', $term_id_strings );

				// Save for reuse.
				set_transient( 'periodicalpress_ordered_issues', $term_ids, HOUR_IN_SECONDS );

			}

		}

		return $term_ids;
	}

	/**
	 * Delete all cached Issue-related values.
	 *
	 * Called whenever Issue numbers are edited.
	 *
	 * @since 1.0.0
	 */
	public function delete_issue_transients() {

		// The ordered list of Issue term_ids.
		delete_transient( 'periodicalpress_ordered_issues' );

		// The highest Issue number.
		delete_transient( 'periodicalpress_highest_issue_num' );

	}

}
