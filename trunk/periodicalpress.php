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
 *
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
 *
 * @since 1.0.0
 *
 * @see includes/class-periodicalpress-activator.php
 */
function activate_periodicalpress() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-periodicalpress-activator.php';
	PeriodicalPress_Activator::activate();
}
register_activation_hook( __FILE__, 'activate_periodicalpress' );

/**
 * The code that runs during plugin deactivation.
 *
 * @since 1.0.0
 *
 * @see includes/class-periodicalpress-deactivator.php
 */
function deactivate_periodicalpress() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-periodicalpress-deactivator.php';
	PeriodicalPress_Deactivator::deactivate();
}
register_deactivation_hook( __FILE__, 'deactivate_periodicalpress' );

/**
 * The core plugin class that is used to define internationalization,
 * dashboard-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-periodicalpress.php';

/*
 * Instance of the main plugin class.
 *
 * This is the biggest departure from the WP Plugin Boilerplate approach: here
 * we create a persistent and globally available instance of the plugin class,
 * instead of enclosing it in a function. This approach allows us to use plugin
 * methods outside of hooks - e.g. as template tags.
 *
 * @since 1.0.0
 * @var PeriodicalPress $periodicalpress The main plugin class.
 */
$periodicalpress = new PeriodicalPress();

/*
 * Begin execution of the plugin. Since everything within the plugin is
 * registered via hooks, kicking off the plugin from this point in the file
 * does not affect the page life cycle.
 */
$periodicalpress->run();
