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
 * Defines the plugin name, version, and custom post types.
 *
 * Uses the Singleton pattern.
 *
 * @since 1.0.0
 */
class PeriodicalPress_Common {

	/**
	 * Returns the instance of this class.
	 *
	 * The key method that enables the Singleton pattern for this class. Calls
	 * __construct() to create the class instance if it doesn't exist yet.
	 *
	 * @since 1.0.0
	 *
	 * @return PeriodicalPress Instance of this class.
	 */
	public static function get_instance() {

		static $instance = null;
		if ( null === $instance ) {
			$instance = new static();
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
	 */
	protected function __construct() {

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
	 * Register the custom taxonomy for Issues.
	 *
	 * Is static so it can be accessed before instantiation by the plugin
	 * activator class.
	 *
	 * @since 1.0.0
	 */
	public static function register_taxonomies() {

		// Setup the Issue custom post type that is the basis for this plugin.
		$labels = array(
			'name'                       => _x( 'Issues', 'Taxonomy General Name', 'periodicalpress' ),
			'singular_name'              => _x( 'Issue', 'Taxonomy Singular Name', 'periodicalpress' ),
			'menu_name'                  => __( 'Issues', 'periodicalpress' ),
			'all_items'                  => __( 'All Issues', 'periodicalpress' ),
			'parent_item'                => __( 'Parent Issue', 'periodicalpress' ),
			'parent_item_colon'          => __( 'Parent Issue:', 'periodicalpress' ),
			'new_item_name'              => __( 'New Issue Name', 'periodicalpress' ),
			'add_new_item'               => __( 'Add New Issue', 'periodicalpress' ),
			'edit_item'                  => __( 'Edit Issue', 'periodicalpress' ),
			'update_item'                => __( 'Update Issue', 'periodicalpress' ),
			'separate_items_with_commas' => __( 'Separate issues with commas', 'periodicalpress' ),
			'search_items'               => __( 'Search Issues', 'periodicalpress' ),
			'add_or_remove_items'        => __( 'Add or remove issues', 'periodicalpress' ),
			'choose_from_most_used'      => __( 'Choose from the most used issues', 'periodicalpress' ),
			'not_found'                  => __( 'Not Found', 'periodicalpress' ),
		);
		$rewrite = array(
			'slug'         => 'issue',
			'with_front'   => false,
			'hierarchical' => false,
		);
		$capabilities = array(
			'manage_terms' => 'manage_pp_issues',
			'edit_terms'   => 'manage_pp_issues',
			'delete_terms' => 'manage_pp_issues',
			'assign_terms' => 'assign_pp_issue',
		);
		$args = array(
			'labels'            => $labels,
			'hierarchical'      => true,
			'public'            => true,
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_nav_menus' => true,
			'show_tagcloud'     => false,
			'query_var'         => 'issue',
			'rewrite'           => $rewrite,
			'capabilities'      => $capabilities,
		);
		register_taxonomy( 'pp_issue', array( 'post' ), $args );

	}

}
