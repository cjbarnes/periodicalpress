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
class PeriodicalPress_Common {

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
	 * @return PeriodicalPress_Common Instance of this class.
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
	 * - (None)
	 *
	 * @since 1.0.0
	 * @access private
	 */
	private function load_dependencies() {

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
		$domain = $this->plugin->get_taxonomy_name();

		return array(
			'publish' => __( 'Published', $domain ),
			'draft' => __( 'Draft', $domain )
		);
	}

	/**
	 * Retrieve an array of the posts objects associated with a single Issue.
	 *
	 * @since 1.0.0
	 *
	 * @param int          $term_id The Issue term ID to get posts for.
	 * @param array|string $status  Which post statuses to return.
	 * @return array The post objects {@see WP_Post}.
	 */
	public function get_issue_posts( $term_id, $status ) {

		// Sanitize and prep the post statuses passed in.
		$allowed_statuses = get_post_stati();
		if ( is_array( $status ) ) {
			$statuses = array_intersect( $status, $allowed_statuses );
		} else {
			if ( in_array( $status, $allowed_statuses ) ) {
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
					'terms'    => (int) $term_id
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
	 * @since 1.0.0
	 *
	 * @global wpdb $wpdb The WordPress database class.
	 *
	 * @param array $excludes Optional. Term IDs that shouldn't count. Default
	 *                        array().
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
	 * @since 1.0.0
	 *
	 * @global wpdb $wpdb The WordPress database class.
	 *
	 * @param array $excludes Optional. Term IDs that shouldn't count. Default
	 *                        array().
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
	 * @since 1.0.0
	 *
	 * @global wpdb $wpdb The WordPress database class.
	 *
	 * @param array $excludes Optional. Term IDs that shouldn't count. Default
	 *                        array().
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
	 * Called whenever Issues are edited.
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
