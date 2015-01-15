<?php

/**
 * Class for the Issue box when editing a post
 *
 * @since 1.0.0
 *
 * @package PeriodicalPress
 */

/**
 * Class for the Issue box when editing a post.
 *
 * Used by both the Edit Post screen meta box (see
 * {@link render_issue_metabox()}) and the Quick Edit Posts custom box
 * (see {@link render_issue_quick_edit_box()}).
 *
 * @since 1.0.0
 */
class PeriodicalPress_Post_Issue_Box {

	/**
	 * The plugin's main class.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var PeriodicalPress $plugin
	 */
	protected $plugin;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 1.0.0
	 *
	 * @param PeriodicalPress $plugin The main plugin class instance.
	 */
	public function __construct( $plugin ) {

		$this->plugin = $plugin;

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
	 * Output the Issue metabox contents.
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
	 * Used to save changes made in both the Edit Post metabox and the Posts
	 * list table Quick Edit.
	 *
	 * @since 1.0.0
	 * @link http://codex.wordpress.org/Function_Reference/add_meta_box
	 *
	 * @param int $post_id ID of the post being saved.
	 * @return int ID of the post being saved.
	 */
	public function save_post_issue( $post_id ) {

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
