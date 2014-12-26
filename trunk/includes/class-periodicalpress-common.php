<?php

/**
 * The functionality of the plugin that affects both Dashboard and public-
 * facing pages.
 *
 * @link http://github.com/cjbarnes/periodicalpress
 * @since 1.0.0
 *
 * @package PeriodicalPress
 * @subpackage PeriodicalPress/includes
 */

/**
 * The shared functionality of the plugin.
 *
 * Defines the plugin name, version, and custom post types.
 *
 * @package PeriodicalPress
 * @subpackage PeriodicalPress/includes
 * @author cJ barnes <mail@cjbarnes.co.uk>
 */
class PeriodicalPress_Common {

	/**
	 * The ID of this plugin.
	 *
	 * @since 1.0.0
	 * @access private
	 * @var string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since 1.0.0
	 * @access private
	 * @var string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 1.0.0
	 * @var string $plugin_name The name of this plugin.
	 * @var string $version     The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the custom post type for Issues.
	 *
	 * @since 1.0.0
	 */
	public function register_custom_post_types() {

		// Setup the Issue custom post type that is the basis for this plugin.
		$labels = array(
			'name'               => _x( 'Issues', 'Post Type General Name', 'periodicalpress' ),
			'singular_name'      => _x( 'Issue', 'Post Type Singular Name', 'periodicalpress' ),
			'menu_name'          => __( 'Issue', 'periodicalpress' ),
			'parent_item_colon'  => __( 'Parent Issue:', 'periodicalpress' ),
			'all_items'          => __( 'All Issues', 'periodicalpress' ),
			'view_item'          => __( 'View Issue', 'periodicalpress' ),
			'add_new_item'       => __( 'Add New Issue', 'periodicalpress' ),
			'add_new'            => __( 'Add New', 'periodicalpress' ),
			'edit_item'          => __( 'Edit Issue', 'periodicalpress' ),
			'update_item'        => __( 'Update Issue', 'periodicalpress' ),
			'search_items'       => __( 'Search Issue', 'periodicalpress' ),
			'not_found'          => __( 'Not found', 'periodicalpress' ),
			'not_found_in_trash' => __( 'Not found in Trash', 'periodicalpress' ),
		);
		$rewrite = array(
			'slug'       => 'issue',
			'with_front' => true,
			'pages'      => true,
			'feeds'      => true,
		);
		$supports = array(
			'title',
			'editor',
			'excerpt',
			'thumbnail',
			'custom-fields',
		);
		$args = array(
			'label'               => __( 'pp_issue', 'periodicalpress' ),
			'description'         => __( 'Collections of posts published together in periodical format', 'periodicalpress' ),
			'labels'              => $labels,
			'supports'            => $supports,
			'hierarchical'        => false,
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_nav_menus'   => true,
			'show_in_admin_bar'   => true,
			'menu_position'       => 5,
			'menu_icon'           => 'dashicons-pressthis',
			'can_export'          => true,
			'has_archive'         => true,
			'exclude_from_search' => false,
			'publicly_queryable'  => true,
			'rewrite'             => $rewrite,
			'capability_type'     => 'post',
		);
		register_post_type( 'pp_issue', $args );

		/*
		 * TODO: customize messages and help - see bottom of page here:
		 * http://codex.wordpress.org/Function_Reference/register_post_type
		 */

	}

}
