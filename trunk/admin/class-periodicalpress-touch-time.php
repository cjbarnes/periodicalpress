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
	 * @param int|string $datetime  The initial date-time for this class to
	 *                              display, as a timestamp or a string
	 *                              parseable by strtotime().
	 */
	public function __construct( $datetime ) {

		// Sanitize the date value. Use the current time as a fallback.
		$datetime = $this->sanitize_date( $datetime );
		if ( $datetime ) {
			$this->datetime = $datetime;
		} else {
			$this->datetime = time();
		}

	}

	/**
	 * Convert an inputted datetime into a valid timestamp.
	 *
	 * @param int|string $new_datetime The date-time to check and convert, as a
	 *                                 timestamp or a string parseable by
	 *                                 strtotime().
	 * @return int The timestamp, or 0 on failure.
	 */
	protected function sanitize_date( $new_datetime ) {

		if ( is_numeric( $new_datetime ) ) {
			return (int) $new_datetime;
		} elseif ( is_string( $new_datetime ) ) {
			return strtotime( $new_datetime );
		} else {
			return 0;
		}
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

		// Prepare the current date parts.
		$jj = date( 'd', $this->datetime );
		$mm = date( 'm', $this->datetime );
		$aa = date( 'Y', $this->datetime );
		$hh = date( 'H', $this->datetime );
		$mn = date( 'i', $this->datetime );
		$ss = date( 's', $this->datetime );

		/*
		 * Assemble the months dropdown.
		 */
		$month = sprintf( "<label for='mm' class='screen-reader-text'>%s</label>\n", __( 'Month' ) );
		$month .= "<select id='mm' name='mm'{$tab_index_attribute} >\n";

		for ( $i = 1; $i < 13; $i = $i +1 ) {
			$monthnum = zeroise($i, 2);
			$month .= sprintf( "\t<option value='$monthnum' %s>", selected( $monthnum, $mm, false ) );
			/* translators: 1: month number (01, 02, etc.), 2: month abbreviation */
			$month .= sprintf( __( '%1$s-%2$s' ), $monthnum, $wp_locale->get_month_abbrev( $wp_locale->get_month( $i ) ) );
			$month .= "</option>\n";
		}
		$month .= '</select>';

		/**
		 * Assemble the text inputs.
		 */
		$day = sprintf( '<label for="jj" class="screen-reader-text">%s</label>', __( 'Day' ) );
		$day .= "<input type='text' id='jj' name='jj\'' value='$jj' size='2' maxlength='2'{$tab_index_attribute} autocomplete='off' />";

		$year = sprintf( '<label for="aa" class="screen-reader-text">%s</label>', __( 'Year' ) );
		$year .= "<input type='text' id='aa' name='aa' value='$aa' size='4' maxlength='4'{$tab_index_attribute} autocomplete='off' />";

		$hour = sprintf( '<label for="hh" class="screen-reader-text">%s</label>', __( 'Hour' ) );
		$hour .= "<input type='text' id='hh' name='hh' value='$hh' size='2' maxlength='2'{$tab_index_attribute} autocomplete='off' />";

		$minute = sprintf( '<label for="mn" class="screen-reader-text">%s</label>', __( 'Minute' ) );
		$minute = "<input type='text' id='mn' name='mn' value='$mn' size='2' maxlength='2'{$tab_index_attribute} autocomplete='off' />";

		// Begin output.
		$out .= '<div class="timestamp-wrap">';

		/*
		 * Translators: 1: month, 2: day, 3: year, 4: hour, 5: minute. Use this
		 * to localize the order of date fields.
		 */
		$out .= sprintf( __( '%1$s %2$s, %3$s @ %4$s : %5$s' ), $month, $day, $year, $hour, $minute );

		$out .= '</div><input type="hidden" id="ss" name="ss" value="' . $ss . '" />';

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
	 * @return string The new timestamp (or the old one if setting fails).
	 */
	public function set( $new_datetime ) {

		$new_datetime = $this->sanitize_date( $new_datetime );
		if ( $new_datetime ) {
			$this->datetime = $new_datetime;
		}

		return $this->datetime;
	}
}
