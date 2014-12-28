<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin so that it
 * is ready for translation.
 *
 * @link http://github.com/cjbarnes/periodicalpress
 *
 * @package WordPress
 * @subpackage PeriodicalPress
 * @since PeriodicalPress 1.0.0
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin so that it
 * is ready for translation.
 *
 * @package WordPress
 * @subpackage PeriodicalPress
 * @since PeriodicalPress 1.0.0
 *
 * @author cJ barnes <mail@cjbarnes.co.uk>
 */
class PeriodicalPress_i18n {

	/**
	 * The domain specified for this plugin.
	 *
	 * @since PeriodicalPress 1.0.0
	 * @access private
	 *
	 * @var string $domain The domain identifier for this plugin.
	 */
	private $domain;

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since PeriodicalPress 1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			$this->domain,
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}

	/**
	 * Set the domain equal to that of the specified domain.
	 *
	 * @since PeriodicalPress 1.0.0
	 *
	 * @param string $domain The domain that represents the locale of this
	 *                       plugin.
	 */
	public function set_domain( $domain ) {
		$this->domain = $domain;
	}

}
