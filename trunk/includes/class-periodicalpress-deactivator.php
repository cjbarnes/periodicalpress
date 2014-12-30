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

		foreach ( $all_roles as $role_name => $role_contents ) {

			// Remove all plugin-set capabilities
			$wp_roles->remove_cap( $role_name, 'assign_pp_issues' );
			$wp_roles->remove_cap( $role_name, 'edit_pp_issues' );
			$wp_roles->remove_cap( $role_name, 'manage_pp_issues' );
			$wp_roles->remove_cap( $role_name, 'delete_pp_issues' );

		}


	}

}
