<?php

/**
 * Fired during plugin deactivation
 *
 * @link http://github.com/cjbarnes/periodicalpress
 *
 * @package WordPress
 * @subpackage PeriodicalPress
 * @since PeriodicalPress 1.0.0
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's
 * deactivation.
 *
 * @package WordPress
 * @subpackage PeriodicalPress
 * @since PeriodicalPress 1.0.0
 *
 * @author cJ barnes <mail@cjbarnes.co.uk>
 */
class PeriodicalPress_Deactivator {

	/**
	 * Run on plugin deactivation. Cleans up after the plugin by removing custom
	 * capabilities and other reversible changes.
	 *
	 * @since PeriodicalPress 1.0.0
	 */
	public static function deactivate() {

		self::destroy_capabilities();

	}

	/**
	 * Removes the plugin-specific capabilities from each role in turn.
	 *
	 * @since PeriodicalPress 1.0.0
	 * @access private
	 *
	 * @global WP_Roles $wp_roles The WordPress roles and capabilities class
	 */
	private static function destroy_capabilities() {
		global $wp_roles;

		$all_roles = $wp_roles->roles;

		foreach ( $all_roles as $role_name ) {

			// Get the WP_Role object
			$role = get_role( $role_name );

			// Remove all plugin-set capabilities
			if ( array_key_exists( 'assign_pp_issues', $role->capabilities ) ) {
				$role->remove_cap( 'assign_pp_issues' );
			}
			if ( array_key_exists( 'edit_pp_issues', $role->capabilities ) ) {
				$role->remove_cap( 'edit_pp_issues' );
			}
			if ( array_key_exists( 'manage_pp_issues', $role->capabilities ) ) {
				$role->remove_cap( 'manage_pp_issues' );
			}
			if ( array_key_exists( 'delete_pp_issues', $role->capabilities ) ) {
				$role->remove_cap( 'delete_pp_issues' );
			}

		}


	}

}
