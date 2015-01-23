<?php

/**
 * Parent class for all Singleton classes
 *
 * @since 1.0.0
 *
 * @package PeriodicalPress
 */

/**
 * Parent class for all Singleton classes in the plugin.
 *
 * Enforces the Singleton pattern (by restricting access to the Constructor and
 * related methods).
 *
 * Also declares standard structural methods and variables, including the plugin
 * main class reference, the hook registration, and the dependency loading.
 *
 * @since 1.0.0
 */
abstract class PeriodicalPress_Singleton {

	/**
	 * The plugin's main class.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var PeriodicalPress $plugin
	 */
	protected $plugin;

	/**
	 * Returns the instance of this class.
	 *
	 * The key method that enables the Singleton pattern for this class. Calls
	 * __construct() to create the class instance if it doesn't exist yet.
	 *
	 * @todo Need a workaround for late static binding `new static()`, because
	 * it doesn't work on PHP 5.2 (which is still supported by WP).
	 *
	 * @since 1.0.0
	 *
	 * @param PeriodicalPress $plugin The main plugin class instance.
	 * @return PeriodicalPress_Singleton Instance of this class.
	 */
	final public static function get_instance( $plugin ) {

		static $instance = null;
		if ( null === $instance ) {
			$instance = new static( $plugin );
		}

		return $instance;
	}

	/**
	 * Initialize the class and set its properties.
	 *
	 * Access `protected` enforces the Singleton pattern by disabling the `new`
	 * operator.
	 *
	 * If overriding this function in the child class's Constructor, make sure
	 * to set `$this->plugin`, or call this Constructor like so:
	 *
	 *     parent::__construct( $plugin );
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @var PeriodicalPress $plugin The main plugin class instance.
	 */
	protected function __construct( $plugin ) {

		$this->plugin = $plugin;

		$this->define_hooks();
		$this->load_dependencies();

	}

	/**
	 * Private clone method to enforce the Singleton pattern.
	 *
	 * @since 1.0.0
	 * @access private
	 */
	final private function __clone() {

	}

	/**
	 * Private unserialize method to enforce the Singleton pattern.
	 *
	 * @since 1.0.0
	 * @access private
	 */
	final private function __wakeup() {

	}

	/**
	 * Load the required dependencies for the admin area.
	 *
	 * Override in the child class to include/require and instantiate other
	 * classes.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function load_dependencies() {

	}

	/**
	 * Register all hooks for actions and filters in this class.
	 *
	 * Override in the child class to register hooks handled by that class.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function define_hooks() {

	}

}
