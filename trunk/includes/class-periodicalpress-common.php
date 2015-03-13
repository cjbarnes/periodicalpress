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
		require_once $path . 'includes/class-periodicalpress-template-tags.php';

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
	public function delete_issue_meta( $issue_id, $meta_key, $meta_value = '' ) {
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
			'posts_per_page'   => -1,
			'orderby'          => 'none',
			'post_status'      => $statuses,
			'tax_query'        => array(
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
	 * Reversed sorting function for Issues.
	 *
	 * Comparison function for use by `usort()` etc.
	 *
	 * Sorts by:
	 * 1. Unpublished issues (newest first)
	 * 2. Unpublished issues with no created date
	 * 3. Published issues (highest number first)
	 *
	 * Generates the default sort order of the Issues for list tables and
	 * dropdowns etc.
	 *
	 * The term objects passed in **must** include the Issue metadata if set,
	 * specifically:
	 * - `number`       (meta key `pp_issue_number`)
	 * - `created_date` (meta key `pp_issue_created_date`)
	 *
	 * @since 1.0.0
	 *
	 * @param  object $obj1 Term object to compare.
	 * @param  object $obj2 Term object to compare.
	 * @return int The comparison result: -1 = greater than, 1 = lesser than,
	 *             0 = equal to.
	 */
	public function descending_sort_issues( $obj1, $obj2 ) {

		$compare = array();

		foreach( array( $obj1, $obj2 ) as $n => $obj ) {
			if ( ! empty( $obj->number ) ) {
				$compare[ $n ] = $obj->number;
			} else {
				// All non-numbered issues should be at the top.
				$compare[ $n ] = 9999;

				// Sort non-numbered issues by created date.
				if ( ! empty( $obj->created_date ) ) {
					$compare[ $n ] += $obj->created_date;
				}
			}
		}

		return strnatcmp( $compare[1], $compare[0] );
	}

	/**
	 * Natural sorting function for Issues.
	 *
	 * The opposite of {@see descending_sort_issues()}.
	 *
	 * @since 1.0.0
	 * @see PeriodicalPress_Common::descending_sort_issues()
	 *
	 * @param  object $obj1 Term object to compare.
	 * @param  object $obj2 Term object to compare.
	 * @return int The comparison result: -1 = greater than, 1 = lesser than,
	 *             0 = equal to.
	 */
	public function ascending_sort_issues( $obj1, $obj2 ) {
		return $this->descending_sort_issues( $obj2, $obj1 );
	}

	/**
	 * Comparison function for posts' ordering within an Issue.
	 *
	 * Ascending (this function's result) is the default ordering for posts
	 * within an Issue.
	 *
	 * Note that althought this function calls `get_post_meta()` **twice for
	 * every individual comparison**, this is not a significant performance
	 * concern because metadata is cached {@see get_metadata()}. So we're not
	 * constantly round-tripping to the DB.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Post $post1 Post object to compare.
	 * @param WP_Post $post2 Post object to compare.
	 * @return int Result of comparison.
	 */
	public function ascending_sort_issue_posts( $post1, $post2 ) {

		$o1_raw = get_post_meta( $post1->ID, 'pp_issue_sort_order', true );
		$o2_raw = get_post_meta( $post2->ID, 'pp_issue_sort_order', true );

		/*
		 * If this post has just been added to this Issue, move it to the
		 * bottom.
		 */
		$o1 = ! empty( $o1_raw )
			? (int) $o1_raw
			: 0;
		$o2 = ! empty( $o2_raw )
			? (int) $o2_raw
			: 0;

		/*
		 * Perform the actual comparison. Sort is stored in descending-order,
		 * so the comparison operators used are the opposites of what you would
		 * expect.
		 */
		if ( $o1 == $o2 ) {
			return 0;
		}
		return ( $o1 < $o2 ) ? 1 : -1;
	}

	/**
	 * Comparison function for posts' ordering within an Issue.
	 *
	 * The opposite of {@see ascending_sort_issue_posts()}.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Post $post1 Post object to compare.
	 * @param WP_Post $post2 Post object to compare.
	 * @return int Result of comparison.
	 */
	public function descending_sort_issue_posts( $post1, $post2 ) {
		return $this->ascending_sort_issue_posts( $post2, $post1 );
	}


	/**
	 * Check if this Issue is published.
	 *
	 * Use as `array_filter()` callback with an array of Issues to remove all
	 * not-published Issues.
	 *
	 * If an Issue Status meta value is not present in the Issue object, we try
	 * to fetch it, then as a fallback we assume it is not published.
	 *
	 * When using this method to check an array of Issues, **always add the
	 * Issue Status metadata to the `$issue` object before passing it to this
	 * function!** Otherwise, every single use of this callback will separately
	 * query the database for a single metadata value, which is expensive.
	 * Instead, first setup the $issue objects to include the meta value in a
	 * property named `$status`, **then** call `array_filter()` or similar.
	 *
	 * @since 1.0.0
	 *
	 * @param object $issue Term object for the Issue to be checked, with added
	 *                      metadata.
	 * @return bool True if published, false if not.
	 */
	public function is_published( $issue ) {
		if ( ! isset( $issue->status ) ) {
			$issue->status = $this->get_issue_meta( $issue->term_id, 'pp_issue_status' );
		}
		return ( 'publish' === $issue->status );
	}

	/**
	 * Check if this Issue is not published.
	 *
	 * Exact reverse of {@see is_published()}. This means that this method
	 * doesn't just match Issues that are explicitly set as Draft; instead, it
	 * matches all Issues that are **not** explicitly set as Published.
	 *
	 * @since 1.0.0
	 * @see PeriodicalPress_Common::is_published()
	 *
	 * @param object $issue Term object for the Issue to be checked, with added
	 *                      metadata.
	 * @return bool True if not published, False if published.
	 */
	public function is_unpublished( $issue ) {
		return ( ! $this->is_published( $issue ) );
	}


	/**
	 * Return or output a dropdown of Issues.
	 *
	 * Revised version of `wp_dropdown_categories()`. Designed to retrieve
	 * Issues data, and also makes sure the list items are sorted in proper
	 * Issue order.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args {
	 *     Optional. An array of arguments. Most of the arguments from Core's
	 *     {@link wp_dropdown_categories()} are supported and work as documented
	 *     for that function.
	 *
	 *     @type bool   $hide_published    Don't show published Issues.
	 *                                     Default 0/false.
	 *     @type bool   $hide_unpublished  Don't show Issues that aren't
	 *                                     published. Default 0/false.
	 *     @type string $show_option_all   Text to display for showing all
	 *                                     Issues. Default ''.
	 *     @type string $show_option_none  Text to display for showing no
	 *                                     Issues. Default ''.
	 *     @type int|string $option_none_value The value attribute for the None
	 *                                     option. Default -1.
	 *     @type string $order             Sort order direction. Default 'DESC'.
	 *                                     Accepts 'ASC', 'DESC'.
	 *     @type bool   $show_count        Whether to show the number of posts
	 *                                     in each Issue. Default 0/false.
	 *     @type string $exclude           Issue IDs to exclude.
	 *     @type string $include           Issue IDs to include.
	 *     @type bool   $echo              Whether to output the dropdown's HTML
	 *                                     or just return it. Default 1/true.
	 *     @type int    $tab_index         Select element's `tabindex`
	 *                                     attribute.
	 *     @type string $name              Select element's `name`. Default is
	 *                                     the Issue taxonomy name.
	 *     @type string $id                Select element's `id`.
	 *     @type string $class             Select element's `class` attribute.
	 *                                     Default 'postform'.
	 *     @type int    $selected          Which Issue ID should be selected.
	 *     @type bool   $hide_if_empty     Whether to hide the dropdown if there
	 *                                     are no Issues to choose from.
	 *	                                   Default 0/false.
	 * }
	 * @return string HTML content only if 'echo' argument is false.
	 */
	public function dropdown_issues( $args = '' ) {

		$tax_name = $this->plugin->get_taxonomy_name();

		$defaults = array(
			'hide_published' => 0,
			'hide_unpublished' => 0,
			'show_option_all' => '',
			'show_option_none' => '',
			'option_none_value' => -1,
			'order' => 'DESC',
			'show_count' => 0,
			'exclude' => '',
			'include' => '',
			'echo' => 1,
			'tab_index' => 0,
			'name' => $tax_name,
			'id' => '',
			'class' => 'postform',
			'selected' => 0,
			'hide_if_empty' => 0
		);
		$args = wp_parse_args( $args, $defaults );

		// Set arguments used by {@link get_terms()} that cannot be overridden.
		$args = array_merge( $args, array(
			'taxonomy' => $tax_name,
			'hide_empty' => false,
			'orderby' => 'ID',
			'hierarchical' => 0,
			'depth' => 0,
			'pad_counts' => 0
		) );

		// Get the Issue data.
		$issues = get_terms( $tax_name, $args );

		// Add metadata to the data, so we can filter and sort by it.
		foreach ( $issues as $issue ) {
			$meta = $this->get_issue_meta( $issue->term_id );
			$issue->number = ! empty( $meta['pp_issue_number'] )
				? $meta['pp_issue_number']
				: '';
			$issue->date = ! empty( $meta['pp_issue_date'] )
				? $meta['pp_issue_date']
				: '';
			$issue->status = ! empty( $meta['pp_issue_status'] )
				? $meta['pp_issue_status']
				: '';
			$issue->created_date = ! empty( $meta['pp_issue_created_date'] )
				? $meta['pp_issue_created_date']
				: 0;
		}

		// Filter the data.
		if ( $args['hide_published'] ) {
			$issues = array_filter( $issues, array( $this, 'is_unpublished' ) );
		}
		if ( $args['hide_unpublished'] ) {
			$issues = array_filter( $issues, array( $this, 'is_published' ) );
		}

		// If `hide_if_empty` and there's no Issues to display, end here.
		if ( $args['hide_if_empty'] && empty( $issues ) ) {
			return '';
		}

		// Sort the data.
		if ( 'ASC' === $args['order'] ) {
			usort( $issues, array( $this, 'ascending_sort_issues' ) );
		} else {
			usort( $issues, array( $this, 'descending_sort_issues' ) );
		}

		// Assemble the select element's opening tag.
		$name = esc_attr( $args['name'] );
		$class = esc_attr( $args['class'] );
		$id = $args['id']
			? esc_attr( $args['id'] )
			: $name;
		$tab_index_attr = ( 0 < (int) $args['tab_index'] )
			? "tabindex='{$args['tab_index']}'"
			: '';

		$out = "<select name='$name' id='$id' class='$class' $tab_index_attr>\n";

		// All option.
		if ( $args['show_option_all'] ) {
			/** This filter is documented in wp-includes/category-template.php */
			$show_option_all = apply_filters( 'list_cats', $args['show_option_all'] );
			$selected = ( '0' === strval($args['selected']) )
				? " selected='selected'"
				: '';
			$out .= "\t<option value='0'$selected>$show_option_all</option>\n";
		}

		// None option.
		if ( $args['show_option_none'] ) {
			/** This filter is documented in wp-includes/category-template.php */
			$show_option_none = apply_filters( 'list_cats', $args['show_option_none'] );
			$selected = selected( $args['option_none_value'], $args['selected'], false );
			$out .= "\t<option value='" . esc_attr( $args['option_none_value'] ) . "'$selected>$show_option_none</option>\n";
		}

		// Output the individual Issue option elements.
		$out .= walk_category_dropdown_tree( $issues, -1, $args );

		$out .= "</select>\n";

		/**
		 * Filter for the complete Issues dropdown HTML.
		 *
		 * @since 1.0.0
		 *
		 * @param string $out  The HTML.
		 * @param array  $args The arguments (both passed in and defaults) used.
		 */
		$out = apply_filters( 'periodicalpress_dropdown_issues', $out, $args );

		// Output.
		if ( $args['echo'] ) {
			echo $out;
		}
		return $out;
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
