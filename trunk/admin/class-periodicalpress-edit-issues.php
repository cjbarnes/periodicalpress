<?php

/**
 * Editing screens for Issues
 *
 * @since 1.0.0
 *
 * @package PeriodicalPress\Admin
 */

/**
 * Editing screens for Issues.
 *
 * Uses the Singleton pattern.
 *
 * @since 1.0.0
 */
class PeriodicalPress_Edit_Issues extends PeriodicalPress_Singleton {

	/**
	 * Register all hooks for actions and filters in this class.
	 *
	 * Called by the parent class's Constructor.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function define_hooks() {

		// Set up screen options and help tabs for Edit Issues screen.
		add_action( 'load-toplevel_page_pp_edit_issues', array( $this, 'edit_issues_screen_options' ) );

		// Set up the metaboxes for the Add Issue and Edit Issue pages.
		add_action( 'add_meta_boxes_pp_issue', array( $this, 'add_remove_metaboxes' ) );
		add_action( 'add_meta_boxes_pp_issue_new', array( $this, 'add_remove_metaboxes' ) );

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

	public function add_issue_screen() {

		/**
		 * Output the Add Issues page.
		 */
		$path = $this->plugin->get_partials_path( 'admin' );
		require $path . 'periodicalpress-add-issue.php';

	}

	/**
	 * Register all meta boxes for the Edit Issue screen.
	 *
	 * @since 1.0.0
	 */
	public function add_remove_metaboxes() {

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
			_x( 'Meta', 'Edit Issue meta box title', 'periodicalpress' ),
			array( $this, 'render_metabox' ),
			'pp_issue',
			'side',
			'core',
			array( 'slug' => 'meta' )
		);
		add_meta_box(
			'pp_issue_submitdiv',
			_x( 'Publish', 'Edit Issue meta box title', 'periodicalpress' ),
			array( $this, 'render_metabox' ),
			'pp_issue',
			'side',
			'core',
			array( 'slug' => 'submit' )
		);


		/*
		 * Main column metaboxes:
		 * - Description
		 * - Posts
		 */
		add_meta_box(
			'pp_issue_descriptiondiv',
			_x( 'Description', 'Edit Issue meta box title', 'periodicalpress' ),
			array( $this, 'render_metabox' ),
			'pp_issue',
			'normal',
			'core',
			array( 'slug' => 'description' )
		);
		add_meta_box(
			'pp_issue_postsdiv',
			_x( 'Posts', 'Edit Issue meta box title', 'periodicalpress' ),
			array( $this, 'render_metabox' ),
			'pp_issue',
			'advanced',
			'core',
			array( 'slug' => 'posts' )
		);


	}

	/**
	 * Output an Issue metabox's contents on the Edit Issue screen.
	 *
	 * The specific HTML partial to load for this metabox is set using the
	 * `$callback_args`.
	 *
	 * @since 1.0.0
	 *
	 * @param object $issue The Issue term object.
	 * @param array  $callback_args {
	 *
	 *     @type string $slug The end of the filename for this metabox's HTML
	 *                        partial.
	 * }
	 */
	public function render_metabox( $issue, $callback_args ) {

		$path = $this->plugin->get_partials_path( 'admin' );
		$slug = ( isset( $callback_args['args']['slug'] ) )
			? $callback_args['args']['slug']
			: 'default';
		include "{$path}periodicalpress-issues-metabox-{$slug}.php";

	}

}
