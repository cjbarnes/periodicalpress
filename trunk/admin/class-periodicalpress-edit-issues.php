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

}
