<?php

/**
 * Fired during plugin deactivation
 *
 * @since 1.0.0
 *
 * @package PeriodicalPress
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's
 * deactivation.
 *
 * @since 1.0.0
 */
class PeriodicalPress_Deactivator {

	/**
	 * Run on plugin deactivation.
	 *
	 * Cleans up after the plugin by removing custom capabilities and other
	 * reversible changes.
	 *
	 * @since 1.0.0
	 */
	public static function deactivate() {

		self::destroy_capabilities();

	}

	/**
	 * Removes the plugin’s role capabilities.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @global WP_Roles $wp_roles The WordPress roles and capabilities class
	 */
	private static function destroy_capabilities() {
		global $wp_roles;

		$all_roles = $wp_roles->roles;

		/*
		 * Loop through each role in turn, removing the added capabilities.
		 */
		foreach ( $all_roles as $role_name => $role_contents ) {

			$wp_roles->remove_cap( $role_name, 'assign_pp_issues' );
			$wp_roles->remove_cap( $role_name, 'edit_pp_issues' );
			$wp_roles->remove_cap( $role_name, 'manage_pp_issues' );
			$wp_roles->remove_cap( $role_name, 'delete_pp_issues' );

		}

	}

}
