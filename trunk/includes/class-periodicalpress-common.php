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
	 * Register the custom taxonomy for Issues.
	 *
	 * Is static so it can be accessed before instantiation by the plugin
	 * activator class.
	 *
	 * @since 1.0.0
	 *
	 * @see PeriodicalPress
	 */
	public static function register_taxonomies() {

		/*
		 * Don't use the class property `$this->plugin`, because when this
		 * method is called statically (i.e. on plugin activation) it has not
		 * been set.
		 */
		$plugin = PeriodicalPress::get_instance();
		$domain = $plugin->get_plugin_name();
		$tax_name = $plugin->get_taxonomy_name();

		// Setup the Issue custom post type that is the basis for this plugin.
		$labels = array(
			'name'                       => _x( 'Issues', 'Taxonomy General Name', $domain ),
			'singular_name'              => _x( 'Issue', 'Taxonomy Singular Name', $domain ),
			'menu_name'                  => __( 'Issues', $domain ),
			'all_items'                  => __( 'All Issues', $domain ),
			'parent_item'                => __( 'Parent Issue', $domain ),
			'parent_item_colon'          => __( 'Parent Issue:', $domain ),
			'new_item_name'              => __( 'New Issue Name', $domain ),
			'add_new_item'               => __( 'Add New Issue', $domain ),
			'edit_item'                  => __( 'Edit Issue', $domain ),
			'update_item'                => __( 'Update Issue', $domain ),
			'separate_items_with_commas' => __( 'Separate issues with commas', $domain ),
			'search_items'               => __( 'Search Issues', $domain ),
			'add_or_remove_items'        => __( 'Add or remove issues', $domain ),
			'choose_from_most_used'      => __( 'Choose from the most used issues', $domain ),
			'not_found'                  => __( 'Not Found', $domain ),
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
		register_taxonomy( $tax_name, array( 'post' ), $args );

	}

}
