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
 * @since 1.0.0
 */
class PeriodicalPress_Common {

	/**
	 * The ID of this plugin.
	 *
	 * @since 1.0.0
	 * @access private
	 * @var string $plugin_name
	 */
	private $plugin_name;

	/**
	 * The current version of this plugin.
	 *
	 * @since 1.0.0
	 * @access private
	 * @var string $version
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 1.0.0
	 *
	 * @var string $plugin_name The name of this plugin.
	 * @var string $version     The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

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
