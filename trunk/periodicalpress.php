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
 *
 * @package WordPress
 * @subpackage PeriodicalPress
 * @since PeriodicalPress 1.0.0
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
 * @see includes/class-periodicalpress-activator.php
 *
 * @since PeriodicalPress 1.0.0
 */
function activate_periodicalpress() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-periodicalpress-activator.php';
	PeriodicalPress_Activator::activate();
}
register_activation_hook( __FILE__, 'activate_periodicalpress' );

/**
 * The code that runs during plugin deactivation.
 * @see includes/class-periodicalpress-deactivator.php
 *
 * @since PeriodicalPress 1.0.0
 */
function deactivate_periodicalpress() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-periodicalpress-deactivator.php';
	PeriodicalPress_Deactivator::deactivate();
}
register_deactivation_hook( __FILE__, 'deactivate_periodicalpress' );

/**
 * The core plugin class that is used to define internationalization,
 * dashboard-specific hooks, and public-facing site hooks.
 *
 * @since PeriodicalPress 1.0.0
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-periodicalpress.php';

/*
 * Instantiate the main plugin class.
 *
 * This is the biggest departure from the WP Plugin Boilerplate approach: here
 * we create a persistent and globally available instance of the plugin class,
 * instead of enclosing it in a function. This approach allows us to use plugin
 * methods outside of hooks - e.g. as template tags.
 *
 * @since PeriodicalPress 1.0.0
 */
$periodicalpress = new PeriodicalPress();

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks, then kicking off
 * the plugin from this point in the file does not affect the page life cycle.
 *
 * @since PeriodicalPress 1.0.0
 */
$periodicalpress->run();
