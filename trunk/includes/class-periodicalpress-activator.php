<?php

/**
 * Fired during plugin activation
 *
 * @since 1.0.0
 *
 * @package PeriodicalPress
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since 1.0.0
 */
class PeriodicalPress_Activator {

	/**
	 * Run on plugin activation.
	 *
	 * Carries out all one-time setup changes (especially to the database).
	 *
	 * @since 1.0.0
	 */
	public static function activate() {

		self::create_capabilities();
		self::set_rewrite_rules();

	}

	/**
	 * Register capabilities.
	 *
	 * Setup plugin-specific capabilities and apply them to the existing roles
	 * in WordPress that have these capabilities:
	 * - `assign_pp_issue`  - edit_posts        - allows per-post Issue choice
	 * - `manage_pp_issues` - edit_others_posts - gives access to Issues admin
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @global WP_Roles $wp_roles The WordPress roles and capabilities class
	 */
	private static function create_capabilities() {
		global $wp_roles;

		$all_roles = $wp_roles->roles;

		foreach ( $all_roles as $role_name => $role_contents ) {

			$role_caps = $role_contents['capabilities'];

			/*
			 * Create new capability: assign_pp_issue (initially set for users
			 * with edit_posts capability, i.e. Contributors and up).
			 */
			if ( isset( $role_caps['edit_posts'] ) ) {
				$wp_roles->add_cap( $role_name, 'assign_pp_issue' );
			}

			/*
			 * Create new capability: manage_pp_issues (initially set for users
			 * with edit_others_posts capability, i.e. Editors and up).
			 */
			if ( isset( $role_caps['edit_others_posts'] ) ) {
				$wp_roles->add_cap( $role_name, 'manage_pp_issues' );
			}

		}

	}

	/**
	 * Flush rewrite rules on plugin activation.
	 *
	 * Setup permalinks for all declared custom post types and taxonomies, by
	 * loading in the new types/taxonomies and then flushing rewrite rules.
	 *
	 * @since 1.0.0
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
