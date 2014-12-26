<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the
 * plugin Dashboard. This file also includes all of the dependencies used by
 * the plugin, registers the activation and deactivation functions, and defines
 * a function that starts the plugin.
 *
 * @link http://github.com/cjbarnes/periodicalpress
 * @since 1.0.0
 * @package PeriodicalPress
 *
 * @wordpress-plugin
 * Plugin Name:  PeriodicalPress
 * Plugin URI:   http://github.com/cjbarnes/periodicalpress
 * Description:  Turns a WordPress website into an issues-based magazine site,
 *               where posts are grouped into issues that are displayed and
 *               published together.
 * Version:      1.0.0
 * Author:       cJ barnes
 * Author URI:   http://cjbarnes.co.uk/
 * License:      GPL-2.0+
 * License URI:  http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:  periodicalpress
 * Domain Path:  /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-periodicalpress-activator.php
 */
function activate_periodicalpress() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-periodicalpress-activator.php';
	PeriodicalPress_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-periodicalpress-deactivator.php
 */
function deactivate_periodicalpress() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-periodicalpress-deactivator.php';
	PeriodicalPress_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_periodicalpress' );
register_deactivation_hook( __FILE__, 'deactivate_periodicalpress' );

/**
 * The core plugin class that is used to define internationalization,
 * dashboard-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-periodicalpress.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since 1.0.0
 */
function run_periodicalpress() {

	$plugin = new PeriodicalPress();
	$plugin->run();

}
run_periodicalpress();
