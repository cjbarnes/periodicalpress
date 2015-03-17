<?php

/**
 * Saving and deleting functionality for Issues
 *
 * Handles database updates for Issue admin tasks.
 *
 * @since 1.0.0
 *
 * @package PeriodicalPress\Admin
 */

/**
 * Saving and deleting functionality for Issues.
 *
 * Handles database updates for Issue admin tasks.
 *
 * Uses the Singleton pattern.
 *
 * @since 1.0.0
 */
class PeriodicalPress_Save_Issues extends PeriodicalPress_Singleton {

	/**
	 * Register all hooks for actions and filters in this class.
	 *
	 * Called by the parent class's Constructor.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function define_hooks() {

		// Check whether a rename-all-Issues flag has been set.
		add_action( 'init', array( $this, 'rename_all_issues_check' ) );

		// Unpublish an Issue when all posts within it are unpublished.
		add_action( 'transition_post_status', array( $this, 'unpublish_post_issues_if_empty' ), 10, 3 );
		add_action( 'edited_term_taxonomy', array( $this, 'unpublish_issue_if_empty' ), 10, 2 );

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

		$pp_common = PeriodicalPress_Common::get_instance( $this->plugin );
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
		$old_status = $pp_common->get_issue_meta( $term_id, "{$tax_name}_status" );
		if ( 'publish' === $old_status ) {
			return true;
		}

		// Get a free issue number if there isn't one yet.
		$issue_num = $pp_common->get_issue_meta( $term_id, "{$tax_name}_number" );
		if ( ! is_int( $issue_num ) ) {
			$issue_num = $this->create_issue_number();
		}

		// Metadata changes.
		$result = $pp_common->update_issue_meta( $term_id, "{$tax_name}_number", $issue_num );

		// Get the ready-to-publish posts attached to this Issue.
		$post_statuses = array(
			'pending',
			'future'
		);
		$term_posts = $pp_common->get_issue_posts( $term_id, $post_statuses );

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

		$pp_common->delete_issue_transients();

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
		if ( $result ) {
			$result = $pp_common->update_issue_meta( $term_id, "{$tax_name}_status", 'publish' );
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

		$pp_common = PeriodicalPress_Common::get_instance( $this->plugin );
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
		$old_status = $pp_common->get_issue_meta( $term_id, "{$tax_name}_status" );

		if ( 'publish' !== $old_status ) {
			return true;
		}

		// Metadata changes.
		$result = $pp_common->update_issue_meta( $term_id, "{$tax_name}_status", 'draft' );

		// Get the already published posts attached to this Issue.
		$term_posts = $pp_common->get_issue_posts( $term_id, 'publish' );

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

		/** This action is documented in admin/class-periodicalpress-admin.php */
		do_action( 'periodicalpress_transition_issue_status', 'draft', $old_status, $term_id );

		$pp_common->delete_issue_transients();

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
			$this->unpublish_issue_if_empty( $issue, $tax_name );
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

		$pp_common = PeriodicalPress_Common::get_instance( $this->plugin );

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

		$status = $pp_common->get_issue_meta( $issue->term_id, "{$tax_name}_status" );

		// Unpublish if the Issue both is published and has no posts.
		if ( ( 'publish' === $status ) && ( 0 === $issue->count ) ) {
			$ret = $this->unpublish_issue( $issue );
		} else {
			$ret = true;
		}

		return $ret;
	}

	/**
	 * Create a new Issue from user-submitted data.
	 *
	 * @since 1.0.0
	 *
	 * @param array $data The Issue's data (usually a $_POST object).
	 * @return array|WP_Error Associative array of the new Issue's $term_id and
	 *                        $term_taxonomy_id, or error object on failure.
	 */
	public function create_issue( $data ) {

		$pp_common = PeriodicalPress_Common::get_instance( $this->plugin );
		$tax_name = $this->plugin->get_taxonomy_name();
		$tax = get_taxonomy( $tax_name );

		/**
		 * Hook for just before an Issue is created.
		 *
		 * @since 1.0.0
		 *
		 * @param array $data Data (eg $_POST) passed into this method.
		 */
		do_action( 'periodicalpress_before_create_issue', $data );

		// Sanitize and prep for wp_update_term().
		$new_term_data = array();

		$new_term_name = ! empty( $data['name'] )
			? sanitize_text_field( $data['name'] )
			: _x( '(No Title)', 'Title for issues missing a title', 'periodicalpress' );

		$new_term_data['description'] = isset( $data['description'] )
			? wp_kses_data( $data['description'] )
			: '';

		// Prep the slug based on the title (if not user-specified).
		if ( ! empty( $data['slug'] ) ) {
			$new_term_data['slug'] = sanitize_title_with_dashes( $data['slug'], null, 'save' );
		}

		// Update the terms table. End here if the DB doesn't update properly.
		$result = wp_insert_term( $new_term_name, $tax_name, $new_term_data );
		if ( is_wp_error( $result ) || ! isset( $result['term_id'] ) ) {
			return $result;
		}

		$term_id = $result['term_id'];

		// Set Issue Status.
		$pp_common->add_issue_meta( $term_id, 'pp_issue_status', 'draft' );

		// Set Issue Number metadata.
		if ( isset( $data['number'] ) ) {

			// @todo: handle return False (i.e. this number is a duplicate).
			$result = $pp_common->update_issue_number( $term_id, $data['number'] );

			// Also update the Issue name/slug if the number is part of that.
			if ( 'numbers' === get_option( 'pp_issue_naming' ) ) {
				$this->update_issue_names( $term_id );
			}

		}

		// Issue Date update.
		if ( ! empty( $data['aa'] ) && is_numeric( $data['aa'] ) ) {

			// Sanitize date input. 1 is the default for anything missing.
			$year = absint( $data['aa'] );
			$month = ( ! empty( $data['mm'] ) && ( 13 > intval( $data['mm'] ) ) )
				? absint( $data['mm'] )
				: 1;
			$day = ( ! empty( $data['jj'] ) && ( 32 > intval( $data['jj'] ) ) )
				? absint( $data['jj'] )
				: 1;

			$new_date = date_create();
			$new_date->setDate( $year, $month, $day );

			$pp_common->add_issue_meta( $term_id, 'pp_issue_date', $new_date->format( 'Y-m-d' ) );

			// Also update the Issue name/slug if the date is included in that.
			if ( 'dates' === get_option( 'pp_issue_naming' ) ) {
				$this->update_issue_names( $term_id );
			}

		}

		/**
		 * Hook for after an Issue is created.
		 *
		 * @since 1.0.0
		 *
		 * @param int    $term_id The ID of the Issue taxonomy term that was
		 *                        created.
		 * @param array  $data    Data (eg $_POST) passed into this method.
		 */
		do_action( 'periodicalpress_create_issue', $term_id, $data );

		return $result;
	}

	/**
	 * Save changes to an Issue.
	 *
	 * @since 1.0.0
	 *
	 * @param int|object $term Either the Issue's term ID or its term object.
	 * @param array      $data The changes to be made to the issue (usually a
	 *                         $_POST object).
	 * @return bool|WP_Error Success/failure of term updating, or error object.
	 */
	public function update_issue( $term, $data ) {

		if ( empty( $term ) ) {
			return false;
		}

		$pp_common = PeriodicalPress_Common::get_instance( $this->plugin );
		$tax_name = $this->plugin->get_taxonomy_name();
		$tax = get_taxonomy( $tax_name );

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
		 * Hook for just before an Issue is updated.
		 *
		 * @since 1.0.0
		 *
		 * @param int    $term_id     The ID of the Issue taxonomy term to be
		 *                            updated.
		 * @param object $term_object The complete term object for the Issue.
		 * @param array  $data        Data (eg $_POST) passed into this method.
		 */
		do_action( 'periodicalpress_before_update_issue', $term_id, $term_object, $data );

		// Sanitize and prep for wp_update_term().
		$new_term_data = array();

		$new_term_data['name'] = ! empty( $data['name'] )
			? sanitize_text_field( $data['name'] )
			: $term_object->name;

		$new_term_data['description'] = isset( $data['description'] )
			? wp_kses_data( $data['description'] )
			: $term_object->description;

		// Prep the slug based on the title (if not user-specified).
		$new_term_data['slug'] = ! empty( $data['slug'] )
			? sanitize_title_with_dashes( $data['slug'], null, 'save' )
			: sanitize_title_with_dashes( $new_term_data['name'], null, 'save' );

		// Slug must be unique. If not, go back to the old slug.
		if ( ( $new_term_data['slug'] !== $term_object->slug )
		&& get_term_by( 'slug', $new_term_data['slug'], $tax_name ) ) {
			$new_term_data['slug'] = $term_object->slug;
		}

		// Update the terms table. End here if the DB doesn't update properly.
		$result = wp_update_term( $term_id, $tax_name, $new_term_data );
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		// Issue Number update.
		if ( isset( $data['number'] ) ) {

			// @todo: handle return False (i.e. this number is a duplicate).
			$result = $pp_common->update_issue_number( $term_id, $data['number'] );

			// Also update the Issue name/slug if the number is part of that.
			if ( 'numbers' === get_option( 'pp_issue_naming' ) ) {
				$this->update_issue_names( $term_object );
			}

		}

		// Issue Date update.
		if ( ! empty( $data['aa'] ) && is_numeric( $data['aa'] ) ) {

			// Sanitize date input. 1 is the default for anything missing.
			$year = absint( $data['aa'] );
			$month = ( ! empty( $data['mm'] ) && ( 13 > intval( $data['mm'] ) ) )
				? absint( $data['mm'] )
				: 1;
			$day = ( ! empty( $data['jj'] ) && ( 32 > intval( $data['jj'] ) ) )
				? absint( $data['jj'] )
				: 1;

			$new_date = date_create();
			$new_date->setDate( $year, $month, $day );

			$pp_common->update_issue_meta( $term_id, 'pp_issue_date', $new_date->format( 'Y-m-d' ) );

			// Also update the Issue name/slug if the date is included in that.
			if ( 'dates' === get_option( 'pp_issue_naming' ) ) {
				$this->update_issue_names( $term_object );
			}

		}

		// Publish this if the submit button clicked was the 'Publish' one.
		if ( isset( $data['publish'] )
		&& current_user_can( $tax->cap->manage_terms ) ) {
			$result = $this->publish_issue( $term_object );

			// Return error or false if failed publish.
			if ( is_null( $result ) || is_wp_error( $result ) ) {
				if ( is_null( $result ) ) {
					return false;
				}
				return $result;
			}

		}

		// Posts ordering update.
		if ( isset( $data['pp_issue_posts_order'] ) ) {

			/*
			 * Make the ordering data into a simple sorted array of post IDs.
			 * Prior to this step, the data is an associative array of:
			 *
			 *     post_id => order
			 *
			 * By converting into a simple array, sorted by `order`, we ensure
			 * a simple ordered sequence of post numbers with no duplicate
			 * positions and no gaps. Then we can use the indexes of the new
			 * array as sanitized order values, ready for saving to the DB.
			 */
			$order_data = $data['pp_issue_posts_order'];
			asort( $order_data );
			$order = array_keys( $order_data );

			/*
			 * Update the DB. Note that the order value saved is `500 - order`,
			 * so that we can use a descending-order sort to get posts from the
			 * DB in the correct order. We want to use descending-order to mimic
			 * ascending-order in this way, so that empty sort-order values will
			 * be at the **bottom** not the top.
			 */
			foreach( $order as $value => $post_id ) {
				update_post_meta( $post_id, 'pp_issue_sort_order', (500 - $value) );
			}

		}

		/**
		 * Hook for after an Issue is updated.
		 *
		 * @since 1.0.0
		 *
		 * @param int    $term_id     The ID of the Issue taxonomy term that was
		 *                            updated.
		 * @param object $term_object The complete term object for the Issue.
		 * @param array  $data        Data (eg $_POST) passed into this method.
		 */
		do_action( 'periodicalpress_update_issue', $term_id, $term_object, $data );

		return $result;
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

		$pp_common = PeriodicalPress_Common::get_instance( $this->plugin );

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
		$term_posts = $pp_common->get_issue_posts( $term_id, $post_statuses );

		/*
		 * Update posts in this Issue. The Issue term is removed, as are meta
		 * items associated with Issues, and the post is moved to Trash.
		 */
		foreach ( $term_posts as $post ) {
			wp_remove_object_terms( $post->ID, $term_id, $tax_name );
			delete_post_meta( $post->ID, 'pp_issue_sort_order' );
			$new_post_data = array(
				'ID'          => $post->ID,
				'post_status' => 'trash'
			);
			wp_update_post( $new_post_data );
		}

		// Returns boolean for success/failure or WP_Error on error.
		$result = wp_delete_term( $term_id, $tax_name );

		$pp_common->delete_issue_transients();

		/*
		 * Only proceed to delete metadata if the term it attaches to was first
		 * deleted successfully.
		 */
		if ( ! is_wp_error( $result ) && $result ) {
			$meta_to_delete = $pp_common->get_issue_meta( $term_id );
			foreach ( $meta_to_delete as $meta_key => $meta_values ) {
				$pp_common->delete_issue_meta( $term_id, $meta_key );
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
	 * Checks for a 'rename all' settings flag, and if it is present, updates
	 * all Issue names to match the chosen Issue name format.
	 *
	 * @since 1.0.0
	 */
	public function rename_all_issues_check() {

		if ( get_option( 'pp_rename_issues_on_next_load' ) ) {

			/*
			 * Call the Issue name updater, with no arguments (meaning that all
			 * Issues will be updated).
			 */
			$this->update_issue_names();

			delete_option( 'pp_rename_issues_on_next_load' );
		}

	}

	/**
	 * Change name and slug of an array of Issues to match the current site-wide
	 * Issue naming format.
	 *
	 * If no array of Issues is passed in, renames **all** Issues.
	 *
	 * @since 1.0.0
	 *
	 * @param array $terms The Issues to be renamed (either their term objects
	 *                     or their term_ids).
	 */
	public function update_issue_names( $terms = array() ) {

		if ( ! is_array( $terms ) ) {
			$terms = array( $terms );
		}

		$pp_common = PeriodicalPress_Common::get_instance( $this->plugin );
		$tax_name = $this->plugin->get_taxonomy_name();

		// If no list of Issues is provided, all Issues should be retitled.
		if ( empty( $terms ) ) {
			$terms = get_terms( $tax_name, array( 'hide_empty' => false ) );
		}

		/*
		 * Get the Issue Name formatting settings. If custom titles are being
		 * used, end here.
		 */
		$name_format = get_option( 'pp_issue_naming', '' );
		if ( 'titles' === $name_format ) {
			return;
		} elseif ( 'dates' === $name_format ) {
			$date_format = get_option( 'pp_issue_date_format', get_option( 'date_format' ) );
		}

		// Loop through the terms to be retitled.
		foreach ( $terms as $term ) {

			// If a term ID was passed in, get the term object.
			$term_object = get_term( $term, $tax_name );
			if ( is_null( $term_object ) || is_wp_error( $term_object ) ) {
				continue;
			}
			$term_id = $term_object->term_id;

			// Set the data that must be included in all wp_update_term() calls.
			$new_data = array(
				'description' => $term_object->description
			);

			// Set name and slug.
			switch ( $name_format ) {

				case 'numbers':
					$issue_num = absint( $pp_common->get_issue_meta( $term_id, 'pp_issue_number' ) );

					if ( $issue_num ) {
						$new_data['name'] = number_format_i18n( $issue_num );
						$new_data['slug'] = $new_data['name'];
					} else {
						$new_data['name'] = _x( '(No Number)', 'Title for issues missing a number', 'periodicalpress' );
						$new_data['slug'] = _x( 'no-number', 'Slug for issues missing a number', 'periodicalpress' );
					}

					break;

				case 'dates':
					$issue_date = $pp_common->get_issue_meta( $term_id, 'pp_issue_date' );

					if ( $issue_date ) {
						$d = DateTime::createFromFormat( 'Y-m-d', $issue_date );
						$new_data['name'] = date_i18n( $date_format, $d->getTimestamp() );
						$new_data['slug'] = sanitize_title_with_dashes( $new_data['name'], null, 'save' );
					} else {
						$new_data['name'] = _x( '(No Date)', 'Title for issues missing a date', 'periodicalpress' );
						$new_data['slug'] = _x( 'no-date', 'Slug for issues missing a date', 'periodicalpress' );
					}

					break;

				// End here if the name format is not a whitelisted option.
				default:
					return;
			}

			// Make sure the new slug is unique, by appending `-N` if necessary.
			$suffix = 2;
			$new_slug = $new_data['slug'];
			while ( get_term_by( 'slug', $new_slug, $tax_name ) ) {
				/*
				 * Translators: %1$s is the original slug, %2$s is the
				 * (localized) number added to distinguish it from the other
				 * slug it clashes with.
				 */
				$new_slug = sprintf(
					_x( '%1$s-%2$s', 'Format for duplicate Issue slugs', 'periodicalpress' ),
					$new_data['slug'],
					number_format_i18n( $suffix++ )
				);
			}
			$new_data['slug'] = $new_slug;

			/**
			 * Filter to change Issue title/slug data before saving to DB.
			 *
			 * @param array  $new_data {
			 *     The Issue data to be saved using {@link wp_update_term()}.
			 *
			 *     @type string $name        The new term name.
			 *     @type string $slug        The new term slug.
			 *     @type string $description The term description (which is not
			 *                               edited by this function).
			 * }
			 * @param object $term_object The term object for this Issue, prior
			 *                            to all edits.
			 */
			$new_data = apply_filters( 'periodicalpress_update_issue_names', $new_data, $term_object );

			// Update the DB with this term.
			wp_update_term( $term_id, $tax_name, $new_data );

		} // end foreach.

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

		$pp_common = PeriodicalPress_Common::get_instance( $this->plugin );

		$transient = 'periodicalpress_highest_issue_num';
		$tax_name = $this->plugin->get_taxonomy_name();

		// Get the highest existing Issue number.
		$highest_num = (int) get_transient( $transient );
		if ( empty( $highest_num ) ) {

			$existing_issues = $pp_common->get_issues_metadata_column( 'pp_issue_number' );
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

		$pp_common = PeriodicalPress_Common::get_instance( $this->plugin );

		$excluded_id = (int) $excluded_id;

		// Check whether the Issue passed in is the Current Issue.
		if ( (int) get_option( 'pp_current_issue' ) === $excluded_id ) {

			$new_current_issue = $pp_common->get_newest_issue( array( $excluded_id ) );

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
