<?php

/**
 * List table class for the Issues management screen.
 *
 * @since 1.0.0
 *
 * @package PeriodicalPress\admin
 */


if ( ! class_exists( 'PeriodicalPress_List_Table' ) ) {
	require_once( plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-periodicalpress-list-table.php' );
}

/**
 * List table class for the Issues management screen.
 *
 * @since 1.0.0
 *
 * @link http://www.paulund.co.uk/wordpress-tables-using-wp_list_table
 * @see PeriodicalPress_List_Table
 */
class PeriodicalPress_Issues_List_Table extends PeriodicalPress_List_Table {

	// /**
	//  * The plugin's main class.
	//  *
	//  * @since 1.0.0
	//  * @access protected
	//  * @var PeriodicalPress $plugin
	//  */
	protected $plugin;

	/**
	 * The Issues taxonomy object.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var object $tax
	 */
	protected $tax;

	/**
	 * Constructor.
	 *
	 * Calls the parent class's constructor with arguments specific to this
	 * taxonomy.
	 *
	 * @since 1.0.0
	 *
	 * @see PeriodicalPress_List_Table::__construct()
	 */
	public function __construct() {

		// Get the main plugin class and taxonomy
		$this->plugin = PeriodicalPress::get_instance();
		$this->tax = get_taxonomy( $this->plugin->get_taxonomy_name() );

		// Call the parent constructor and pass in the setup arguments.
		parent::__construct( array(
			'plural' => 'issues',
			'singular' => 'issue'
		) );

	}

	/**
	 * Prepare the items for the table to display.
	 *
	 * @since 1.0.0
	 */
	public function prepare_items() {

		// Prepare lists of columns
		$columns = $this->get_columns();
		$hidden = $this->get_hidden_columns();
		$sortable = $this->get_sortable_columns();

		// Prepare data
		$data = $this->table_data();

		// Store results in class properties
		$this->_column_headers = array( $columns, $hidden, $sortable );
		$this->items = $data;

	}

	/**
	 * Define the table columns.
	 *
	 * @since 1.0.0
	 *
	 * @return array Associative array ( name => label ) of table columns.
	 */
	public function get_columns() {

		$domain = $this->plugin->get_plugin_name();
		$context = 'Issues Table';

		$checkbox_header = current_user_can( $this->tax->cap->delete_terms )
			? '&nbsp;'
			: '';

		$columns = array(
			'id'          => 'ID',
			'cb'          => $checkbox_header,
			'name'        => esc_html_x( 'Name', $context, $domain ),
			'date'        => esc_html_x( 'Date', $context, $domain ),
			'number'      => esc_html_x( 'Number', $context, $domain ),
			'title'       => esc_html_x( 'Title', $context, $domain ),
			'slug'        => esc_html_x( 'Slug', $context, $domain ),
			'posts'       => esc_html_x( 'Posts', $context, $domain ),
			'status'      => esc_html_x( 'Status', $context, $domain ),
			'ssid'        => esc_html_x( 'ID', $context, $domain )
		);

		return $columns;
	}

	/**
	 * Define which table columns should be hidden.
	 *
	 * @since 1.0.0
	 *
	 * @return array Associative array ( name => label ) of table columns to
	 *               hide.
	 */
	public function get_hidden_columns() {

		$hidden_columns = array(
			'id',
			'slug',
			'ssid'
		);

		return $hidden_columns;
	}

	/**
	 * Define which table columns are sortable, and what their sort state is on
	 * first load.
	 *
	 * @since 1.0.0
	 *
	 * @return array Associative array ( name => label ) of table columns to
	 *               allow sorting on.
	 */
	public function get_sortable_columns() {

		$sortable_columns = array(
			'name'  => array( 'name', false ),
			'posts' => array( 'posts', false )
		);

		return $sortable_columns;
	}

	/**
	 * Get the table data.
	 *
	 * @since 1.0.0
	 *
	 * @return array The complete table data as an array of arrays (each sub-
	 *               array contains a single row, arranged as an associative
	 *               array ( column-name => data ) ).
	 */
	private function table_data() {

		$data = array();

		// Default sort order
		$orderby = 'name';
		$order = 'DESC';

		// Get user-set sort column
		if ( ! empty( $_GET['orderby'] ) ) {

			switch ( $_GET['orderby'] ) {
				case 'posts':
					$orderby = 'count';
					break;
			}

		}

		// Get ascending or descending sort order
		if ( ! empty( $_GET['order'] )
		&& ( ( 'asc' === $_GET['order'] ) || ( 'desc' === $_GET['order'] ) ) ) {
			$order = strtoupper( $_GET['order'] );
		}

		// Get pagination numbers
		$page_size = 20;
		$page_number = $this->get_pagenum();


		// Get the term object for each Issue to display.
		$args = array(
			'hide_empty'   => 0,
			'cache_domain' => 'periodicalpress',
			'orderby'      => $orderby,
			'order'        => $order,

			// TODO: check pagination is working properly
			'number'       => $page_size,
			'offset'       => ( $page_number - 1 ) * $page_size
		);
		$issues = get_terms( $this->tax->name, $args );

		// Add each term as a new row.
		foreach ( $issues as $n => $issue ) {

			// Get all metadata for this term.
			$meta = get_metadata( 'pp_term', $issue->term_id );

			// Prep metadata empty states.
			$number = ! empty( $meta['pp_issue_number'][0] )
				? $meta['pp_issue_number'][0]
				: '';
			$date = ! empty( $meta['pp_issue_date'][0] )
				? $meta['pp_issue_date'][0]
				: '';
			$title = ! empty( $meta['pp_issue_title'][0] )
				? esc_html( $meta['pp_issue_title'][0] )
				: '';
			$status = ! empty( $meta['pp_issue_status'][0] )
				? $meta['pp_issue_status'][0]
				: '';

			$data[] = array(
				'id'          => $n,
				'cb'          => '',
				'number'      => $number,
				'name'        => esc_html( $issue->name ),
				'date'        => $date,
				'title'       => "<em>$title</em>",
				'slug'        => esc_html( $issue->slug ),
				'posts'       => $issue->count,
				'status'      => esc_html( $status ),
				'ssid'        => $issue->term_id
			);

		}

		return $data;
	}

	/**
	 * Output the row checkbox.
	 *
	 * @param  array $issue The current row data (associative array).
	 * @return string The checkbox output.
	 */
	public function column_cb( $item ) {

		if ( current_user_can( $this->tax->cap->delete_terms ) ) {

			$id = $item['id'];
			$checkbox = "<label class='screen-reader-text' for='cb-select-$id'>";
			$checkbox .= sprintf( __( 'Select %s' ), esc_html( $item['name'] ) );
			$checkbox .= '</label>';
			$checkbox .= "<input type='checkbox' name='delete_tags[]' value='$id' id='cb-select-$id' />";

		} else {

			$checkbox = $nbsp;

		}

		return $checkbox;
	}

	/**
	 * Formats the data for each individual cell of the table.
	 *
	 * @since 1.0.0
	 *
	 * @param array  $item        The current row as an associative array.
	 * @param string $column_name The array key of the current column.
	 * @return string The output for this cell.
	 */
	public function column_default( $item, $column_name ) {

		switch ( $column_name ) {

			case 'name':
				$domain = $this->plugin->get_plugin_name();

				$name = $item[ $column_name ];
				$title = sprintf( __( 'Edit &lsquo;%s&rsquo;', $domain ), $name);

				// TODO assemble the URL
				return  "<strong><a class='row-title' href='#' title='$title'>$name</a></strong>";
				break;

			case 'date':
				$date = DateTime::createFromFormat( 'Y-m-d', $item[ $column_name ] );
				write_log($date);
				if ( $date ) {
					$out = $date->format( 'Y/m/d' );
				} else {
					$out = '';
				}
				return $out;
				break;

			case 'status':
				$tax_name = $this->tax->name;
				/** This filter is documented in admin/class-periodicalpress-admin.php */
				$statuses = apply_filters( "{$tax_name}_statuses", array() );

				// Get the display name for this Issue's status.
				$out = array_key_exists( $item[ $column_name ], $statuses )
					? $statuses[ $item[ $column_name ] ]
					: '';
				return $out;
				break;

			/*
			 * Handle all columns that don't require formatting - i.e. just send
			 * the column's data straight to output.
			 */
			case 'id':
			case 'number':
			case 'title':
			case 'description':
			case 'slug':
			case 'posts':
			case 'ssid':
				return $item[ $column_name ];
				break;

			/*
			 * If this column is unknown, just dump the whole row array into the
			 * page.
			 */
			default:
				return print_r( $item, true );

		}

	}

}
