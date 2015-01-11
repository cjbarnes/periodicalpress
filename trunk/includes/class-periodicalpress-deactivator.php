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
		$old_publishers = get_option( 'pp_old_publishers', array() );

		foreach ( $all_roles as $role_name => $role_contents ) {

			$wp_roles->remove_cap( $role_name, 'assign_pp_issue' );
			$wp_roles->remove_cap( $role_name, 'manage_pp_issues' );

			// Restore `publish_post` capability to pre-activation state.
			if ( in_array( $role_name, $old_publishers ) ) {
				$wp_roles->add_cap( $role_name, 'publish_post' );
			}

		}

	}

}
