<?php

/**
 * Class for the Issue box when editing a post
 *
 * @since 1.0.0
 *
 * @package PeriodicalPress\Admin
 */

/**
 * Class for the Issue box when editing a post.
 *
 * Used by both the Edit Post screen meta box (see
 * {@link render_issue_metabox()}) and the Quick Edit Posts custom box
 * (see {@link render_issue_quick_edit_box()}).
 *
 * Uses the Singleton pattern.
 *
 * @since 1.0.0
 */
class PeriodicalPress_Post_Issue_Box extends PeriodicalPress_Singleton {

	/**
	 * Register all hooks for actions and filters in this class.
	 *
	 * Called by the parent class's Constructor.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function define_hooks() {

		// Reorder the Posts table columns (and Quick Edit boxes).
		add_action( 'manage_posts_columns', array( $this, 'posts_move_issue_column' ) );

		// Add a custom Issues box to the Quick Edit for posts.
		add_action( 'quick_edit_custom_box', array( $this, 'render_issue_quick_edit_box' ), 10, 2 );

		// Replace the Issues box on the Post Add/Edit page.
		add_action( 'add_meta_boxes_post', array( $this, 'add_remove_metaboxes' ) );

		/*
		 * Save the Issue for a post, whether set by the Edit Post screen or
		 * the Quick Edit.
		 */
		add_action( 'save_post', array( $this, 'save_post_issue' ), 10, 2 );

		// Manually add the Issue column to the Posts list table.
		add_action( 'manage_posts_custom_column', array( $this, 'list_table_column_pp_issue' ), 10, 2 );

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
				$tax->labels->singular_name,
				array( $this, 'render_issue_metabox' ),
				'post',
				'side',
				'high'
			);

		}

	}

	/**
	 * Move the Issue taxonomy column so that it appears before the Categories
	 * and Tags columns.
	 *
	 * Used on the Posts list table screen to make Issue data more prominent,
	 * and to change which column its Quick Edit box appears in.
	 *
	 * @since 1.0.0
	 *
	 * @param array $columns Associative array of all columns in the list table.
	 * @return array The reordered array of columns.
	 */
	public function posts_move_issue_column( $columns ) {

		$tax_name = $this->plugin->get_taxonomy_name();
		$tax = get_taxonomy( $tax_name );

		$tax_col = "taxonomy-$tax_name";
		$tax_col_label = $columns[ $tax_col ];

		// Only make changes if the Issue column exists.
		if ( ! empty( $tax_col_label ) ) {

			unset( $columns[ $tax_col ] );

			/*
			 * Reinsert the original column data just before the Categories
			 * column.
			 */
			$insert_pos = array_search( 'categories', array_keys( $columns ) );
			if ( $insert_pos ) {

				$middle_column = array(
					$tax_name => $tax->labels->singular_name
				);
				$end_columns = array_splice( $columns, $insert_pos );

				$columns += $middle_column + $end_columns;
			}

		}

		return $columns;
	}

	/**
	 * Output the Issue column contents in the Posts list table.
	 *
	 * The reason we override the default rendering of this column (which is
	 * visibly identical to this) is so we can add the `data-issue-id` attribute
	 * to the DOM, for use in the Quick Edit JavaScript.
	 *
	 * @since 1.0.0
	 *
	 * @param string $column_name The name of the column being outputted.
	 * @param int    $post_id     The ID of the post being outputted.
	 */
	public function list_table_column_pp_issue( $column_name, $post_id ) {

		if ( 'pp_issue' === $column_name ) {

			$tax_name = $this->plugin->get_taxonomy_name();
			$tax = get_taxonomy( $tax_name );

			$terms = get_the_terms( $post_id, $tax_name );
			$out = array();

			if ( $terms ) {
				foreach ( $terms as $t ) {

					// Assemble the URL for viewing posts in an Issue.
					$posts_in_term_qv = array();
					$posts_in_term_qv[ $tax->query_var ] = $t->slug;
					$url = esc_url( add_query_arg( $posts_in_term_qv, 'edit.php' ) );

					$attr = "data-issue-id='{$t->term_id}'";
					$out[] = "<a href='$url' $attr>{$t->name}</a>";

				}
			}

			// Output all the Issues.
			echo join( __( ', ' ), $out );

		}

	}

	/**
	 * Output the Issue metabox contents.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Post $post The post being edited.
	 */
	public function render_issue_metabox( $post ) {

		$path = $this->plugin->get_partials_path( 'admin' );
		require $path . 'periodicalpress-posts-metabox-issue.php';

	}

	/**
	 * Output the Quick Edit custom box contents.
	 *
	 * Only used for the Issue column, and only if the post type is Post.
	 *
	 * @since 1.0.0
	 *
	 * @param string $column_name The name of the column this Quick Edit box
	 *                            relates to.
	 * @param string $post_type   The Post Type, e.g. 'post'.
	 */
	public function render_issue_quick_edit_box( $column_name, $post_type ) {

		$tax_name = $this->plugin->get_taxonomy_name();

		if ( ( $tax_name === $column_name )
		&& ( 'post' === $post_type ) ) {
			$path = $this->plugin->get_partials_path( 'admin' );
			require $path . 'periodicalpress-posts-quick-edit-issue.php';
		}

	}

	/**
	 * Save a post’s chosen Issue to the DB.
	 *
	 * Used to save changes made in both the Edit Post metabox and the Posts
	 * list table Quick Edit.
	 *
	 * @since 1.0.0
	 * @link http://codex.wordpress.org/Function_Reference/add_meta_box
	 *
	 * @param int $post_id ID of the post being saved.
	 * @return int ID of the post being saved.
	 */
	public function save_post_issue( $post_id, $post ) {

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
		 * Update the DB if the new issue exists or if 'No issue' was selected,
		 * and if the new issue doesn't match the old one.
		 */
		$new_issue = intval( $_POST['pp_issue'] );
		$old_issues = wp_get_post_terms( $post_id, $tax_name, array( 'fields' => 'ids' ) );

		// End if Issue hasn't changed.
		if ( isset( $old_issues[0] ) && ( $old_issues[0] === $new_issue ) ) {
			return $post_id;
		}

		// Update the stored Issue number.
		if ( -1 === $new_issue ) {

			wp_delete_object_term_relationships( $post_id, $tax_name );

		} else {

			$term_object = get_term( $new_issue, $tax_name );

			if ( ! is_null( $term_object ) ) {

				wp_set_post_terms( $post_id, $new_issue, $tax_name );

				$issue_status = get_metadata( 'pp_term', $new_issue, "{$tax_name}_status", true );

				/*
				 * Update whether this post should be published, based on
				 * whether its new Issue is published.
				 */
				if ( 'publish' === $issue_status ) {

					$this->update_post_status( $post_id, 'publish' );

				} else {

					/*
					 * Revert post to Pending Review if it used to be published
					 * and now shouldn't be.
					 */
					if ( 'publish' === $post->post_status ) {
						$this->update_post_status( $post_id, 'pending' );
					}

				}

			}

		}

		return $post_id;
	}

	/**
	 * Update a post's publication status.
	 *
	 * Abbreviated version of Core's {@link wp_publish_post()} that won't rerun
	 * the `edit_post` and `save_post` actions.
	 *
	 * @since 1.0.0
	 *
	 * @global wpdb $wpdb The WordPress database object.
	 *
	 * @param int|WP_Post $post   The post's ID or post object.
	 * @param string      $status The new post status.
	 * @return bool Result of update.
	 */
	public function update_post_status( $post, $status ) {
		global $wpdb;

		if ( ! $post = get_post( $post ) ) {
			return false;
		}
		if ( $status === $post->post_status ) {
			return true;
		}

		$wpdb->update( $wpdb->posts, array( 'post_status' => $status ), array( 'ID' => $post->ID ) );

		clean_post_cache( $post->ID );

		$old_status = $post->post_status;
		$post->post_status = $status;
		wp_transition_post_status( $status, $old_status, $post );

		return true;
	}


}
