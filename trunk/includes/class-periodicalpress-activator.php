<?php

/**
 * Fired during plugin activation
 *
 * @link http://github.com/cjbarnes/periodicalpress
 * @since 1.0.0
 *
 * @package PeriodicalPress
 * @subpackage PeriodicalPress/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since 1.0.0
 * @package PeriodicalPress
 * @subpackage PeriodicalPress/includes
 * @author cJ barnes <mail@cjbarnes.co.uk>
 */
class PeriodicalPress_Activator {

	/**
	 * Flush rewrite rules on plugin activation, so that permalinks for the
	 * custom post types are properly set.
	 *
	 * @since 1.0.0
	 */
	public static function activate() {

		/*
		 * Make sure the custom post type creation class is loaded
		 *
		 * TODO: test whether we need this
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-periodicalpress-common.php';
		PeriodicalPress_Common::register_custom_post_types();

		flush_rewrite_rules();

	}

}
