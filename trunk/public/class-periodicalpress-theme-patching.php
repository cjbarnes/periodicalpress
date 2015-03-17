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
class PeriodicalPress_Theme_Patching extends PeriodicalPress_Singleton {

	/**
	 * Initialize the class and set its properties.
	 *
	 * Calls the Constructor {@see PeriodicalPress_Singleton::__construct()} of
	 * the parent's class first.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @var PeriodicalPress $plugin The main plugin class instance.
	 */
	protected function __construct( $plugin ) {

		// Also call the parent class Constructor.
		parent::__construct( $plugin );

		$this->do_init_actions();

	}

	/**
	 * Register all hooks required to customise the current theme.
	 *
	 * Called by the parent class's Constructor.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function define_hooks() {

		/*
		 * Redirect the blog index page to the Current Issue permalink. (Also
		 * ensures the taxonomy term layout is used.)
		 */
		add_action( 'parse_query', array( $this, 'redirect_to_current_issue' ), '1.5' );

		// CSS and JavaScript.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		// Modify the Loop to paginate properly between Issues.
		add_action( 'pre_get_posts', array( $this, 'modify_issue_query' ) );
		add_action( 'wp_head', array( $this, 'override_number_of_pages' ) );
		add_filter( 'paginate_links', array( $this, 'issue_pagination_link' ) );
		add_filter( 'get_pagenum_link', array( $this, 'issue_pagination_link' ) );

		// Modify the main posts query to use a custom sort order.
		add_filter( 'the_posts', array( $this, 'reorder_issue_query' ), 10, 2 );

	}

	/**
	 * Calls 2 class-specific init hooks at the end of the site-wide init hook.
	 *
	 * This is useful so that the define_hooks() function can add its own set
	 * of "init" actions, which can then be deregistered by other plugins/themes
	 * as normal, even though define_hooks() is itself an `init` action.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function do_init_actions() {

		/**
		 * Hook that runs immediately after the theme patching hooks have all
		 * been registered. Use this hook to remove actions or filters
		 * registered by PeriodicalPress_Theme_Patching, if needed.
		 */
		do_action( 'periodicalpress_remove_theme_patching_hooks' );

		/**
		 * Hook that substitutes for `init` just for this class. Added because
		 * `init` is already in progress at this point. A separate action is
		 * necessary, rather than just running the init actions immediately, so
		 * that themes and other plugins can deregister these default actions.
		 */
		do_action( 'periodicalpress_theme_patching_init' );

	}

	/**
	 * Register the stylesheets that accompany the theme modifications in this
	 * class.
	 *
	 * Stylesheets used:
	 * - periodicalpress-theme-patching.css - Theme patching stylesheet.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_styles() {

		$name = $this->plugin->get_plugin_name();
		$path = plugin_dir_url( __FILE__ ) . 'css/';
		$version = $this->plugin->get_version();

		wp_enqueue_style( "{$name}_theme_patching", "{$path}periodicalpress-theme-patching.css", array(), $version, 'all' );

	}

	/**
	 * Register the scripts that accompany the theme modifications in this
	 * class.
	 *
	 * Scripts used:
	 * - periodicalpress-theme-patching.js - Theme patching scripts.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_scripts() {

		$name = $this->plugin->get_plugin_name();
		$path = plugin_dir_url( __FILE__ ) . 'js/';
		$version = $this->plugin->get_version();

		wp_enqueue_script( "{$name}_theme_patching", "{$path}periodicalpress-theme-patching.js", array( 'jquery' ), $version, true );

	}

	/**
	 * Configure the Loop on Issue pages to load the entire issue on one page,
	 * and nothing else.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Query $query The query object for the forthcoming posts query.
	 */
	public function modify_issue_query( $query ) {

		$tax_name = $this->plugin->get_taxonomy_name();

		/*
		 * The standard conditional function is_main_query() does not return the
		 * right results within the pre_get_posts hook.
		 */
		if ( ! $query->is_main_query()
		|| ! $query->is_tax( $tax_name ) ) {
			return;
		}

		// Remove default pagination posts-per-page setting.
		$query->set( 'posts_per_page', -1 );

	}

	/**
	 * Reorder the main query on Issue pages to match the custom posts sort
	 * order defined in the admin.
	 *
	 * @since 1.0.0
	 *
	 * @param array    $posts The array of retrieved posts.
	 * @param WP_Query $query The WP_Query instance.
	 * @return array The modified posts array.
	 */
	public function reorder_issue_query( $posts, $query ) {

		$tax_name = $this->plugin->get_taxonomy_name();

		if ( ! is_main_query() || ! $query->is_tax( $tax_name ) ) {
			return $posts;
		}

		$pp_common = PeriodicalPress_Common::get_instance( $this->plugin );

		/**
		 * Perform the sort. The `@` is to suppress the usort PHP Warning:
		 *
		 *     usort(): Array was modified by the user comparison function
		 *
		 * which is a known bug in PHP.
		 */
		@usort( $posts, array( $pp_common, 'ascending_sort_issue_posts' ) );

		return $posts;
	}

	/**
	 * Redirects the blog index page to the Current Issue page.
	 *
	 * @since 1.0.0
	 */
	public function redirect_to_current_issue() {

		if ( is_home() ) {

			$tax_name = $this->plugin->get_taxonomy_name();

			// Get the current issue.
			$current_issue = (int) get_option( 'pp_current_issue' , 0 );
			if ( ! $current_issue ) {
				$pp_common = PeriodicalPress_Common::get_instance( $this->plugin );
				$current_issue = $pp_common->get_newest_issue_id();
			}

			/*
			 * Do the redirect. A 302 (temporary) redirect is used, since when
			 * the next issue is published the address being redirected to will
			 * change.
			 */
			if ( $current_issue ) {
				wp_redirect( get_term_link( $current_issue, $tax_name ), 302 );
				exit();
			}

		}

	}

	/**
	 * Sets pagenum and number of pages for the main query, on blog index and
	 * Issue pages.
	 *
	 * Changes the Core pagination output to page between Issues rather than
	 * posts, in descending order of Issue number (Current Issue first), one
	 * Issue per page.
	 *
	 * Sets the global WP_Query object's {@link WP_Query::max_num_pages}
	 * property, since this is checked for by Core's
	 * {@link get_the_posts_pagination()}.
	 *
	 * Hooks into the `wp_head` action. (Originally it was supposed to hook
	 * onto `wp`, but this is too early - it causes a redirect when the page
	 * number is set.)
	 *
	 * @since 1.0.0
	 *
	 * @global WP_Query $wp_query The completed main query object.
	 * @global int $paged The pagination page (must be set for Core functions
	 *                    {@link get_previous_posts_link()} and
	 *                    {@link get_next_posts_link()}).
	 */
	public function override_number_of_pages() {
		global $wp_query;
		global $paged;

		$tax_name = $this->plugin->get_taxonomy_name();

		/*
		 * Remember, {@link is_tax()} doesn't check that a taxonomy exists; it
		 * checks that the current page is a term page for that taxonomy.
		 */
		if ( ! is_home() && ! is_tax( $tax_name ) ) {
			return;
		}

		// Get the ordered list of Issues currently published.
		$pp_common = PeriodicalPress_Common::get_instance( $this->plugin );
		$issues = $pp_common->get_ordered_issue_IDs();

		// Set the total number of 'pages' (i.e. Issues).
		$wp_query->max_num_pages = count( $issues );

		// Set the 'page number' for this Issue page.
		if ( is_tax( $tax_name ) ) {

			// Get this Issue taxonomy term.
			$tax = get_taxonomy( $tax_name );
			$issue_slug = $wp_query->query[ $tax->rewrite['slug'] ];
			$issue = get_term_by( 'slug', $issue_slug, $tax_name );

			if ( false !== $issue ) {
				$pagenum = array_search( $issue->term_id, $issues ) + 1;
			}

			// Set the pagination page to match this page's Issue.
			$pagenum = ! empty( $pagenum ) ? $pagenum : 1;
			$wp_query->query_vars['paged'] = $pagenum;
			$paged = $pagenum;

		}

	}

	/**
	 * Filters pagination URLs on the blog index and Issue pages.
	 *
	 * Converts `/page/[n]` links in the standard public-area pagination into
	 * `/issue/[slug]` links. This is almost the only control we can have over
	 * the pagination output while still using the Core function.
	 *
	 * @since 1.0.0
	 *
	 * @param string $link The url of this pagination page.
	 * @return string The Issue url for the linked pagination page.
	 */
	public function issue_pagination_link( $link ) {

		$tax_name = $this->plugin->get_taxonomy_name();

		if ( is_home() || is_tax( $tax_name ) ) {

			// Use regular expressions to extract this link's page number.
			$pattern = $this->get_old_pagination_format();
			$matches = array();

			if ( preg_match( $pattern, $link, $matches ) ) {

				$pagenum = intval( $matches[1] ) - 1;

				/*
				 * Get the Issue ID that matches this page number, by using the
				 * page number as an index in the ordered-issues array.
				 * {@link PeriodicalPress_Common::get_ordered_issue_IDs()} uses
				 * transients to cache its results, so there is no need to cache
				 * here as well.
				 */
				$pp_common = PeriodicalPress_Common::get_instance( $this->plugin );
				$issues = $pp_common->get_ordered_issue_IDs();

				$issue_id = isset( $issues[ $pagenum ] )
					? $issues[ $pagenum ]
					: 0;

			} else { // This is Page 1, so get the Current Issue.

				$issue_id = intval( get_option( 'pp_current_issue' , 0 ) );

				if ( ! $issue_id ) {
					$pp_common = PeriodicalPress_Common::get_instance( $this->plugin );
					$issue_id = $pp_common->get_newest_issue_id();
				}

			}

			// Get the new pagination link.
			$link = get_term_link( $issue_id, $tax_name );

		}

		return $link;
	}

	/**
	 * Retrieves the regular expression that will match the page number of a
	 * paginated page.
	 *
	 * @since 1.0.0
	 *
	 * @global WP_Rewrite $wp_rewrite The WordPress rewrite options object.
	 *
	 * @return string A regular expression to return a url's page number.
	 */
	public function get_old_pagination_format() {
		global $wp_rewrite;

		return ( $wp_rewrite->using_permalinks() )
        	? '/' . $wp_rewrite->pagination_base . '\/(\d+)/'
			: '/[\?&]paged=(\d+)/';
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
