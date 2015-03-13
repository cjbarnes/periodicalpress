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
	 * Conditional tag to test whether this page is an Issue page.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True if an Issue page, False if not.
	 */
	public function is_issue() {
		$tax_name = $this->plugin->get_taxonomy_name();
		$queried_object = get_queried_object();

		if ( ( isset( $queried_object->term_id ) && ( $tax_name === $queried_object->taxonomy ) ) ) {

			return true;
		} else {
			return false;
		}
	}

	/**
	 * Retrieve the current Issue's term ID.
	 *
	 * @since 1.0.0
	 *
	 * @return int|null The Issue ID.
	 */
	public function get_the_issue_id() {

		// Get the Issue object to work with.
		$issue = $this->get_the_issue_object( $issue_id );
		if ( empty( $issue ) || is_wp_error( $issue ) ) {
			return;
		}

		return $issue->term_id;
	}

	/**
	 * Retrieve the current Issue's title.
	 *
	 * @since 1.0.0
	 *
	 * @param int $issue_id Optional. The Issue's term ID.
	 * @return string|null The Issue title.
	 */
	public function get_the_issue_title( $issue_id = 0 ) {

		// Get the Issue object to work with.
		$issue = $this->get_the_issue_object( $issue_id );
		if ( empty( $issue ) || is_wp_error( $issue ) ) {
			return;
		}
		$issue_id = $issue->term_id;

		/**
		 * Filter the Issue title for front-end outputting.
		 *
		 * @since 1.0.0
		 *
		 * @param string $issue_title The Issue title.
		 * @param int    $issue_id    The taxonomy term ID for this Issue.
		 */
		return apply_filters( 'periodicalpress_the_issue_title', $issue->name, intval( $issue_id ) );
	}

	/**
	 * Retrieve the current Issue's description.
	 *
	 * @since 1.0.0
	 *
	 * @param int $issue_id Optional. The Issue's term ID.
	 * @return string|null The Issue description.
	 */
	public function get_the_issue_description( $issue_id = 0 ) {

		// Get the Issue object to work with.
		$issue = $this->get_the_issue_object( $issue_id );
		if ( empty( $issue ) || is_wp_error( $issue ) ) {
			return;
		}
		$issue_id = $issue->term_id;

		if ( empty( $issue->description ) ) {
			return;
		}

		/**
		 * Filter the Issue description for front-end outputting.
		 *
		 * @since 1.0.0
		 *
		 * @param string $description The Issue description.
		 * @param int    $issue_id    The taxonomy term ID for this Issue.
		 */
		return apply_filters( 'periodicalpress_the_issue_title', $issue->description, $issue_id );
	}

	/**
	 * Retrieve the current Issue's number.
	 *
	 * @since 1.0.0
	 *
	 * @param int $issue_id Optional. The Issue's term ID.
	 * @return string The Issue number formatted as a string.
	 */
	public function get_the_issue_number( $issue_id = 0 ) {

		// Get the Issue object to work with.
		$issue = $this->get_the_issue_object( $issue_id );
		if ( empty( $issue ) || is_wp_error( $issue ) ) {
			return;
		}
		$issue_id = $issue->term_id;

		// Get the Issue number.
		$pp_common = PeriodicalPress_Common::get_instance( $this->plugin );
		$issue_num = intval( $pp_common->get_issue_meta( $issue_id, 'pp_issue_number' ) );

		if ( empty( $issue_num ) ) {
			return;
		}

		/**
		 * Filter the Issue number for front-end outputting.
		 *
		 * @since 1.0.0
		 *
		 * @param string $issue_number  The (localized) Issue number output.
		 * @param int    $issue_num_int The Issue number value, as integer.
		 * @param int    $issue_id      The taxonomy term ID for this
		 *                              Issue.
		 */
		return apply_filters( 'periodicalpress_the_issue_number', number_format_i18n( $issue_num ), $issue_num, intval( $issue_id ) );
	}

    /**
	 * Retrieve the current Issue's formatted date string.
	 *
	 * @since 1.0.0
	 *
	 * @param string $format   Optional. Date format to output the date in. If
	 *                         '', use the user-set Issue date format.
	 *                         Default ''.
	 * @param int    $issue_id Optional. The Issue's term ID.
	 * @return string|null The Issue number formatted as a string.
	 */
	public function get_the_issue_date( $format = '', $issue_id = 0 ) {

		// Get the Issue object to work with.
		$issue = $this->get_the_issue_object( $issue_id );
		if ( empty( $issue ) || is_wp_error( $issue ) ) {
			return;
		}
		$issue_id = $issue->term_id;

		// Get the Issue date.
		$pp_common = PeriodicalPress_Common::get_instance( $this->plugin );
		$issue_date = $pp_common->get_issue_meta( $issue_id, 'pp_issue_date' );

		if ( empty( $issue_date ) ) {
			return;
		}

		// Convert the DB date format to a Unix timestamp.
		$d = DateTime::createFromFormat( 'Y-m-d', $issue_date );

		/*
		 * Fallback date formats: Issue Date Format setting, then site-wide
		 * date format setting.
		 */
		if ( empty( $format ) ) {
			$format = get_option( 'pp_issue_date_format', get_option( 'date_format' ) );
		}

		/**
		 * Filter the Issue date for front-end outputting.
		 *
		 * @since 1.0.0
		 *
		 * @param string $issue_date      The formatted Issue date.
		 * @param int    $issue_datestamp The Issue date as a Unix timestamp.
		 * @param int    $issue_id        The taxonomy term ID for this
		 *                                Issue.
		 */
		return apply_filters( 'periodicalpress_the_issue_date', date_i18n( $format, $d->getTimestamp() ), $d->getTimestamp(), $issue_id );
	}

	/**
	 * Retrieve the current Issue's permalink URL.
	 *
	 * @since 1.0.0
	 *
	 * @param int $issue_id Optional. The Issue's term ID.
	 * @return string|null The Issue URL.
	 */
	public function get_the_issue_link( $issue_id = 0 ) {

		// Get the Issue object to work with.
		$issue = $this->get_the_issue_object( $issue_id );
		if ( empty( $issue ) || is_wp_error( $issue ) ) {
			return;
		}
		$issue_id = $issue->term_id;

		$link = get_term_link( $issue );

		/**
		 * Filter the Issue link for front-end outputting.
		 *
		 * @since 1.0.0
		 *
		 * @param string $link      The Issue URL.
		 * @param int    $issue_id  The taxonomy term ID for this Issue.
		 */
		return apply_filters( 'periodicalpress_the_issue_link', $link, $issue_id );
	}

	/**
	 * Reusable Issue term object getter.
	 *
	 * Starts with ID manually passed in, or failing that, tries to get the
	 * right Issue from the current taxonomy-query or post.
	 *
	 * @since 1.0.0
	 *
	 * @param int $issue_id Optional. The specific Issue to get (if known).
	 * @return object|null|WP_Error The Issue term object, or null/error on
	 *                              failure.
	 */
	public function get_the_issue_object( $issue_id = 0 ) {
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
	 * @return string|null The title HTML.
	 */
	public function the_issue_title( $before = '', $after = '', $echo = true ) {
		return $this->the_the( 'get_the_issue_title', $before, $after, $echo );
	}

	/**
	 * Display the current Issue's description.
	 *
	 * @since 1.0.0
	 */
	public function the_issue_description() {
		$description = $this->the_the( 'get_the_issue_description', '', '', false );
		if ( ! empty( $description ) ) {
			/**
			 * Filter the description like post excerpts are formatted.
			 *
			 * @since 1.0.0
			 * @link http://codex.wordpress.org/Function_Reference/the_excerpt
			 *
			 * @param string $description The content to format like an excerpt.
			 */
			echo apply_filters( 'the_excerpt', $description );
		}
	}

	/**
	 * Display or retrieve the current Issue's number.
	 *
	 * @since 1.0.0
	 *
	 * @param string $before Optional. Content to prepend to number.
	 * @param string $after  Optional. Content to append to number.
	 * @param bool   $echo   Optional. Whether to display or return. Default
	 *                       true.
	 * @return string|null The number HTML.
	 */
	public function the_issue_number( $before = '', $after = '', $echo = true ) {
		return $this->the_the( 'get_the_issue_number', $before, $after, $echo );
	}

	/**
	 * Display or retrieve the current Issue's date.
	 *
	 * Doesn't use the generic `the_the()` method because of the additional
	 * $format param.
	 *
	 * @since 1.0.0
	 *
	 * @param string $format Optional. Date format to output the date in. If '',
	 *                       use the user-set Issue date format. Default ''.
	 * @param string $before Optional. Content to prepend to date.
	 * @param string $after  Optional. Content to append to date.
	 * @param bool   $echo   Optional. Whether to display or return. Default
	 *                       true.
	 * @return string|null The date HTML.
	 */
	public function the_issue_date( $format = '', $before = '', $after = '', $echo = true ) {

		$result = $this->get_the_issue_date( $format );
		if ( 0 == strlen( $result ) ) {
			return null;
		}

		// Add prefix and suffix.
	    $result = $before . $result . $after;

	    // Output/return.
	    if ( $echo ) {
	        echo $result;
	        return null;
	    } else {
	        return $result;
	    }
	}

	/**
	 * Display or retrieve the current Issue's permalink URL.
	 *
	 * @since 1.0.0
	 *
	 * @param bool $echo Optional. Whether to display or return. Default true.
	 * @return string|null The Issue URL.
	 */
	public function the_issue_link( $echo = true ) {
		return $this->the_the( 'get_the_issue_link', '', '', $echo );
	}

	/**
	 * Generic function for 'the_issue_XXX()' template tags.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param string $get_method Which get_the_XXXX() template tag to call.
	 * @param string $before     Optional. Content to prepend to this string.
	 * @param string $after      Optional. Content to append to this string.
	 * @param bool   $echo       Optional. Whether to display or return.
	 *                           Default true.
	 * @return string|null The resulting HTML.
	 */
	protected function the_the( $get_method, $before = '', $after = '', $echo = true ) {

		// Get the get_the_XXXX() method results.
		$result = '';
		if ( isset( $get_method ) && method_exists( $this, $get_method ) ) {
			$result = $this->$get_method();
		}

	    if ( 0 == strlen( $result ) ) {
	        return null;
	    }

	    // Add prefix and suffix.
	    $result = $before . $result . $after;

	    // Output/return.
	    if ( $echo ) {
	        echo $result;
	        return null;
	    } else {
	        return $result;
	    }
	}

}
