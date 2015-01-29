<?php

/**
 * Class for rendering a multiple-input date form field in the admin
 *
 * Based on Core `touch_time()` {@link wp-admin/includes/templates.php}, which
 * which sadly isn't usable for anything other than posts or comments.
 *
 * @since 1.0.0
 *
 * @package PeriodicalPress\Admin
 */

/**
 * Class for rendering a multiple-input date form field in the admin.
 *
 * @since 1.0.0
 */
class PeriodicalPress_Touch_Time {

	/**
	 * The date-time value for the form fields.
	 *
	 * Is used as the default/initial/placeholder value in the HTML form fields
	 * outputted by the display() method.
	 *
	 * Stored as a timestamp (number of seconds since Unix Epoch).
	 *
	 * Use (int) `-1` for no datetime.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var int $datetime
	 */
	protected $datetime;

	/**
	 * Constructor.
	 *
	 * The datetime to be used as the fields' default value is supplied here.
	 * Output formatting options are params of the display() method instead.
	 *
	 * @since 1.0.0
	 *
	 * @param int|string $datetime  Optional. The initial date-time for this
	 *                              class to display, as a timestamp or a string
	 *                              parseable by strtotime(). -1 = no initial
	 *                              value.
	 */
	public function __construct( $datetime = -1 ) {

		// Sanitize the date value.
		$this->datetime = $this->sanitize_date( $datetime );

	}

	/**
	 * Convert an inputted datetime into a valid timestamp.
	 *
	 * @param int|string $new_datetime The date-time to check and convert, as a
	 *                                 timestamp or a string parseable by
	 *                                 strtotime().
	 * @return int The timestamp, or -1 on failure.
	 */
	protected function sanitize_date( $new_datetime ) {

		if ( is_numeric( $new_datetime ) ) {
			return (int) $new_datetime;
		} elseif ( is_string( $new_datetime ) ) {
			return strtotime( $new_datetime );
		} else {
			return -1;
		}
	}

	/**
	 * Convert a date precision string into an integer (for use by the display()
	 * method).
	 *
	 * Possible results:
	 * - 0 = 'day' (the default)
	 * - 1 = 'month'
	 * - 2 = 'year'
	 *
	 * @since 1.0.0
	 *
	 * @param string $precision The precision argument to convert.
	 * @return int A valid precision (0|'day' if the precision is invalid).
	 */
	protected function precision_to_int( $precision ) {

		// The default.
		$result = 0;

		switch ( strtolower( $precision ) ) {
			case 'month':
				$result = 1;
				break;
			case 'year':
				$result = 2;
				break;
		}

		return $result;
	}

	/**
	 * Echo the HTML form fields for this date-time.
	 *
	 * @since 1.0.0
	 *
	 * @param string $precision The level of precision of the date that should
	 *                          be inputted. Default 'day'. Accepts 'time',
	 *                          'day', 'week', 'month', 'year'.
	 * @param int    $tab_index The HTML tabindex attribute. Default 0.
	 * @param bool   $return    Whether to return (TRUE) or echo (FALSE) the
	 *                          output. Default FALSE.
	 * @return string|null The HTML output (if $return is true).
	 */
	public function display( $precision = 'day', $tab_index = 0, $return ) {
		global $wp_locale;

		$out = '';

		$tab_index_attribute = '';
		if ( (int) $tab_index > 0 ) {
			$tab_index_attribute = " tabindex=\"$tab_index\"";
		}

		$p_int = $this->precision_to_int( $precision );

		$day   = '';
		$month = '';
		$year  = '';

		// Prepare the days input.
		if ( 0 >= $p_int ) {

			// Get the current day value, or blank if none.
			$jj = ( -1 !== $this->datetime )
				? date( 'd', $this->datetime )
				: '';

			$day = sprintf( '<label for="pp-jj" class="screen-reader-text">%s</label>', __( 'Day' ) );
			$day .= "<input type='text' id='pp-jj' name='jj' value='$jj' size='2' maxlength='2'{$tab_index_attribute} placeholder='day' autocomplete='off' />";

		}

		// Prepare the months dropdown.
		if ( 1 >= $p_int ) {

			// Get the current month value, or blank if none.
			$mm =  ( -1 !== $this->datetime )
				? date( 'm', $this->datetime )
				: '';

			$month = sprintf( "<label for='pp-mm' class='screen-reader-text'>%s</label>\n", __( 'Month' ) );
			$month .= "<select id='pp-mm' name='mm'{$tab_index_attribute} >\n";

			// Empty date option.
			$month .= "\t<option value='-1'>--</option>\n";

			// Assemble the twelve month options.
			for ( $i = 1; $i < 13; $i = $i +1 ) {
				$monthnum = zeroise($i, 2);
				$month .= sprintf( "\t<option value='$monthnum' %s>", selected( $monthnum, $mm, false ) );
				/*
				 * translators:
				 * 1: month number (01, 02, etc.),
				 * 2: month abbreviation
				 */
				$month .= sprintf( __( '%1$s-%2$s' ), $monthnum, $wp_locale->get_month_abbrev( $wp_locale->get_month( $i ) ) );
				$month .= "</option>\n";
			}

			$month .= '</select>';

		}

		// Get the current year value.
		$aa = ( -1 !== $this->datetime )
			? date( 'Y', $this->datetime )
			: '';

		// Prepare the year input.
		$year = sprintf( '<label for="pp-aa" class="screen-reader-text">%s</label>', __( 'Year' ) );
		$year .= "<input type='text' id='pp-aa' name='aa' value='$aa' size='4' maxlength='4'{$tab_index_attribute} placeholder='year' autocomplete='off' />";

		// Begin output.
		$out .= '<div class="timestamp-wrap">';

		/*
		 * Translators: 1: month, 2: day, 3: year. Use this to localize the
		 * order of date fields.
		 */
		$out .= sprintf( _x( ' %2$s/%1$s/%3$s', 'Date field order and separators', 'periodicalpress' ), $month, $day, $year );
		$out .= '</div>';

		if ( ! empty( $return ) ) {
			return $out;
		} else {
			echo $out;
		}
	}

	/**
	 * Retrieve the date/time value that would be outputted by this class.
	 *
	 * @since 1.0.0
	 *
	 * @return string The timestamp.
	 */
	public function get() {
		return $this->datetime;
	}

	/**
	 * Change the date/time value that would be outputted by this class.
	 *
	 * @since 1.0.0
	 *
	 * @param int|string $new_datetime The new date-time, as a timestamp or a
	 *                                 string parseable by strtotime().
	 * @return string The new timestamp.
	 */
	public function set( $new_datetime ) {
		$this->datetime = $this->sanitize_date( $new_datetime );
		return $this->datetime;
	}
}
