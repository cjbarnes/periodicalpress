<?php

/**
 * Fired during plugin activation
 *
 * @link http://github.com/cjbarnes/periodicalpress
 *
 * @package WordPress
 * @subpackage PeriodicalPress
 * @since PeriodicalPress 1.0.0
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @package WordPress
 * @subpackage PeriodicalPress
 * @since PeriodicalPress 1.0.0
 *
 * @author cJ barnes <mail@cjbarnes.co.uk>
 */
class PeriodicalPress_Activator {

	/**
	 * Run on plugin activation. Carries out all one-time setup changes
	 * (especially to the database).
	 *
	 * @since PeriodicalPress 1.0.0
	 */
	public static function activate() {

		self::create_capabilities();
		self::set_rewrite_rules();

	}

	/**
	 * Setup plugin-specific capabilities and apply them to the existing roles
	 * in WordPress, based on which roles have each capability’s ‘nearest
	 * neighbour’ already applied to them.
	 *
	 * @since PeriodicalPress 1.0.0
	 * @access private
	 *
	 * @global WP_Roles $wp_roles The WordPress roles and capabilities class
	 */
	private static function create_capabilities() {
		global $wp_roles;

		$all_roles = $wp_roles->roles;

		foreach ( $all_roles as $role_name ) {

			// Get the WP_Role object
			$role = get_role( $role_name );

			/*
			 * Create new capability: assign_pp_issues (initially set for users
			 * with edit_posts capability).
			 */
			if ( $role->capabilities['edit_posts'] ) {
				$role->add_cap( 'assign_pp_issues' );
			}

			/*
			 * Create new capabilities: edit_pp_issues and manage_pp_issues
			 * (initially set for users with edit_others_posts capability).
			 */
			if ( $role->capabilities['edit_others_posts'] ) {
				$role->add_cap( 'edit_pp_issues' );
				$role->add_cap( 'manage_pp_issues' );
			}

			/*
			 * Create new capability: delete_pp_issues (initially set for users
			 * with delete_others_posts capability).
			 */
			if ( $role->capabilities['delete_others_posts'] ) {
				$role->add_cap( 'delete_pp_issues' );
			}

		}

	}

	/**
	 * Setup permalinks for all declared custom post types and taxonomies, by
	 * loading in the new types/taxonomies and then flushing rewrite rules.
	 *
	 * @since PeriodicalPress 1.0.0
	 * @access private
	 */
	private static function set_rewrite_rules() {

		/*
		 * Load the class that creates custom post types and taxonomies, so
		 * their URL rules can be applied using flush_rewrite_rules.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-periodicalpress-common.php';
		PeriodicalPress_Common::register_taxonomies();

		flush_rewrite_rules();

	}

}
