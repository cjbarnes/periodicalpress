<?php

/**
 * The Issues taxonomy registration class
 *
 * This is kept in a separate class (and file) to streamline the plugin
 * activation process. It is loaded and used statically during activation to
 * allow flushing of rewrite rules.
 *
 * @since 1.0.0
 *
 * @package PeriodicalPress
 */

/**
 * The Issues taxonomy registration class.
 *
 * Registers the taxonomy. Called by both plugin activation (to allow flushing
 * of rewrite rules) and the init hook.
 *
 * @since 1.0.0
 */
class PeriodicalPress_Taxonomy {

	/**
	 * Returns the instance of this class.
	 *
	 * The key method that enables the Singleton pattern for this class. Calls
	 * __construct() to create the class instance if it doesn't exist yet.
	 *
	 * @since 1.0.0
	 *
	 * @return PeriodicalPress_Taxonomy Instance of this class.
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
	 * Register the custom taxonomy for Issues.
	 *
	 * @since 1.0.0
	 *
	 * @see PeriodicalPress
	 */
	public static function register_taxonomy() {

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
