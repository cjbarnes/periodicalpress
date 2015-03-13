<?php

/**
 * The plugin’s template tags class
 *
 * @since 1.0.0
 *
 * @package PeriodicalPress
 */

/**
 * Class containing the template tags.
 *
 * To use these, instantiate at the top of the template file with this PHP code:
 *
 *     if ( class_exists( 'PeriodicalPress_Template_Tags' ) ) {
 *         $pp = new PeriodicalPress_Template_Tags();
 *     }
 *
 * Then the template tags can be used within your HTML like so:
 *
 *     <?php $pp->the_issue_title(); ?>
 *
 * @since 1.0.0
 */
class PeriodicalPress_Template_Tags {

	/**
	 * The plugin's main class.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var PeriodicalPress $plugin
	 */
	protected $plugin;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->plugin = PeriodicalPress::get_instance();
	}

	/**
	 * Reusable Issue term object getter.
	 *
	 * Starts with ID manually passed in, or failing that, tries to get the
	 * right Issue from the current taxonomy-query or post.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param int $issue_id Optional. The specific Issue to get (if known).
	 * @return object|null|WP_Error The Issue term object, or null/error on
	 *                              failure.
	 */
	protected function get_the_issue( $issue_id = 0 ) {
		$tax_name = $this->plugin->get_taxonomy_name();

		if ( ! empty( $issue_id ) ) {
			$issue = get_term( absint( $issue_id ), $tax_name );
		} else {
			$queried_object = get_queried_object();

			if ( isset( $queried_object->term_id )
			&& ( $tax_name === $queried_object->taxonomy ) ) {

				$issue = get_term( absint( $queried_object->term_id ), $tax_name );

			} elseif ( is_a( $queried_object, 'WP_Post' ) ) {

				$issues = wp_get_post_terms( $queried_object->ID, $tax_name, array( 'fields' => 'ids' ) );

				if ( ! empty( $issues ) && ! is_wp_error( $issues ) ) {
					$issue = get_term( $issues[0], $tax_name );
				}

			}
		}

		if ( ! empty( $issue ) ) {
			return $issue;
		} else {
			return null;
		}
	}

	/**
	 * Display or retrieve the current Issue's title.
	 *
	 * @since 1.0.0
	 *
	 * @param string $before Optional. Content to prepend to title.
	 * @param string $after  Optional. Content to append to title.
	 * @param bool   $echo   Optional. Whether to display or return. Default
	 *                       true.
	 * @return string|null The title HTML, or null if echoing the title.
	 */
	public function the_issue_title( $before = '', $after = '', $echo = true ) {
		$issue_title = $this->get_the_issue_title();

	    if ( strlen( $issue_title ) == 0 ) {
	        return;
	    }

	    $issue_title = $before . $issue_title . $after;

	    if ( $echo ) {
	        echo $issue_title;
	    } else {
	        return $issue_title;
	    }
	}

	/**
	 * Retrieve the current Issue's title.
	 *
	 * @since 1.0.0
	 *
	 * @param int $issue_id Optional. The Issue's term ID.
	 * @return string The Issue title.
	 */
	public function get_the_issue_title( $issue_id = 0 ) {

		// Get the Issue object to work with.
		$issue = $this->get_the_issue( $issue_id );
		if ( empty( $issue ) || is_wp_error( $issue ) ) {
			return '';
		}


		/**
		 * Filter the Issue title for front-end outputting.
		 *
		 * @since 1.0.0
		 *
		 * @param string $issue_title The Issue title.
		 * @param int    $issue_id    The taxonomy term ID for this Issue.
		 */
		return apply_filters( 'periodicalpress_the_issue_title', $issue->name, intval( $issue->term_id ) );
	}

}
