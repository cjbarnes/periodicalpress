<?php

/**
 * Modifications for WordPress themes that do not have explicit support for this
 * plugin
 *
 * @since 1.0.0
 *
 * @package PeriodicalPress\Public
 */

/**
 * Modifications for WordPress themes that do not have explicit support for this
 * plugin.
 *
 * Changes The Loop, pagination, and post layouts to make the Issues-based site
 * structure visible to the user.
 *
 * Uses the Singleton pattern.
 *
 * @since 1.0.0
 */
class PeriodicalPress_Theme_Patching {

	/**
	 * The plugin's main class.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var PeriodicalPress $plugin
	 */
	protected $plugin;

	/**
	 * The loader that's responsible for maintaining and registering all hooks
	 * in this class, except define_hooks().
	 *
	 * Cannot reuse the main plugin class's PeriodicalPress_Loader instance,
	 * because its run() method (which registers all its hooks) has already been
	 * run once. So we would end up with repeated actions and filters
	 * registered everywhere.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var PeriodicalPress_Loader $loader
	 */
	protected $loader;

	/**
	 * Returns the instance of this class.
	 *
	 * The key method that enables the Singleton pattern for this class. Calls
	 * __construct() to create the class instance if it doesn't exist yet.
	 *
	 * @since 1.0.0
	 *
	 * @param PeriodicalPress $plugin The main plugin class instance.
	 * @return PeriodicalPress_Theme_Patching Instance of this class.
	 */
	public static function get_instance( $plugin ) {

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
	 * @since 1.0.0
	 * @access protected
	 *
	 * @var PeriodicalPress $plugin The main plugin class instance.
	 */
	protected function __construct( $plugin ) {

		$this->plugin = $plugin;

		$this->loader = new PeriodicalPress_Loader();

	}

	/**
	 * Private clone method to enforce the Singleton pattern.
	 *
	 * @since 1.0.0
	 * @access private
	 */
	private function __clone() {

	}

	/**
	 * Private unserialize method to enforce the Singleton pattern.
	 *
	 * @since 1.0.0
	 * @access private
	 */
	private function __wakeup() {

	}

	/**
	 * Register all hooks required to customise the current theme.
	 *
	 * @since 1.0.0
	 */
	public function define_hooks() {

		/*
		 * Do not register any actions or filters if this theme is designed to
		 * use PeriodicalPress without modifications.
		 */
		if ( current_theme_supports( 'periodicalpress' ) ) {
			return;
		}

		/*
		 * CSS and JavaScript.
		 */
		$this->loader->add_action(
			'wp_enqueue_scripts',
			$this,
			'enqueue_styles'
		);
		$this->loader->add_action(
			'wp_enqueue_scripts',
			$this,
			'enqueue_scripts'
		);

		/*
		 * Modify the blog index Loop.
		 */
		$this->loader->add_action(
			'pre_get_posts',
			$this,
			'modify_home_query'
		);

		$this->loader->run();

	}

	/**
	 * Register the stylesheets that accompany the theme modifications in this
	 * class.
	 *
	 * @since 1.0.0
	 *
	 * @see PeriodicalPress_Loader
	 */
	public function enqueue_styles() {

		wp_enqueue_style(
			$this->plugin->get_plugin_name(),
			plugin_dir_url( __FILE__ ) . 'css/periodicalpress-theme-patching.css',
			array(),
			$this->plugin->get_version(),
			'all'
		);

	}

	/**
	 * Register the scripts that accompany the theme modifications in this
	 * class.
	 *
	 * @since 1.0.0
	 *
	 * @see PeriodicalPress_Loader
	 */
	public function enqueue_scripts() {

		wp_enqueue_script(
			$this->plugin->get_plugin_name(),
			plugin_dir_url( __FILE__ ) . 'js/periodicalpress-theme-patching.js',
			array( 'jquery' ),
			$this->plugin->get_version(),
			true
		);

	}

	/**
	 * Configure the main Loop on the blog index page to load the entire current
	 * issue and nothing else.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Query $query The query object for the forthcoming posts query.
	 */
	public function modify_home_query( $query ) {

		/*
		 * The standard conditional function is_main_query() does not return the
		 * right results within the pre_get_posts hook.
		 */
		if ( ! $query->is_home() || ! $query->is_main_query() ) {
			return;
		}

		$current_issue = (int) get_option( 'pp_current_issue' , 0 );
		if ( ! $current_issue ) {
			$current_issue = PeriodicalPress_Common::get_instance()->get_newest_issue_id();
		}

		$current_issue_query = array(
			array(
				'taxonomy' => $this->plugin->get_taxonomy_name(),
				'field'    => 'id',
				'terms'    => $current_issue
			)
		);

		$query->set( 'tax_query', $current_issue_query );

	}

	/**
	 * The reference to the object that orchestrates the hooks for this class.
	 *
	 * @since 1.0.0
	 *
	 * @return PeriodicalPress_Loader Orchestrates this class's hooks.
	 */
	public function get_loader() {
		return $this->loader;
	}

}
