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

		/**
		 * Filters the column names and display names for the list table.
		 *
		 * @since 1.0.0
		 *
		 * @param array $columns Associative array of column names and labels.
		 */
		$columns = apply_filters( "manage_{$this->tax->name}_columns", array(
			'id'          => 'ID',
			'name'        => esc_html_x( 'Name', $context, $domain ),
			'date'        => esc_html_x( 'Date', $context, $domain ),
			'number'      => esc_html_x( 'Number', $context, $domain ),
			'title'       => esc_html_x( 'Title', $context, $domain ),
			'description' => esc_html_x( 'Description', $context, $domain ),
			'slug'        => esc_html_x( 'Slug', $context, $domain ),
			'posts'       => esc_html_x( 'Posts', $context, $domain ),
			'status'      => esc_html_x( 'Status', $context, $domain ),
			'ssid'        => esc_html_x( 'ID', $context, $domain )
		) );

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

		/**
		 * Filters the columns that are hidden (via CSS) in the list table.
		 *
		 * @since 1.0.0
		 *
		 * @param array $hidden_columns Array of column names.
		 */
		$hidden_columns = apply_filters( "manage_{$this->tax->name}_hidden_columns", array(
			'id',
			'slug',
			'ssid'
		) );

		return $hidden_columns;
	}

	/**
	 * Define which table columns are sortable, and what their sort state is on
	 * first load.
	 *
	 * @since 1.0.0
	 *
	 * @return array Associative array ( name => array( name, order ) ) of
	 *               table columns to allow sorting on.
	 */
	public function get_sortable_columns() {

		/**
		 * Filters the columns that are sortable and what their initial sort
		 * orders are.
		 *
		 * @since 1.0.0
		 *
		 * @param array $sortable_columns Associative array of columns in form
		 *                                ( name => array( name, order ) ).
		 */
		$sortable_columns = apply_filters( "manage_{$this->tax->name}_hidden_columns", array(
			'name'  => array( 'name', false ),
			'posts' => array( 'posts', false )
		) );

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
				? $meta['pp_issue_title'][0]
				: '';
			$status = ! empty( $meta['pp_issue_status'][0] )
				? $meta['pp_issue_status'][0]
				: '';

			$data[] = array(
				'id'          => $n,
				'number'      => $number,
				'name'        => $issue->name,
				'date'        => $date,
				'title'       => $title,
				'description' => $issue->description,
				'slug'        => $issue->slug,
				'posts'       => $issue->count,
				'status'      => $status,
				'ssid'        => $issue->term_id
			);

		}

		return $data;
	}

	/**
	 * Formats the data for cells in the Name column.
	 *
	 * @since 1.0.0
	 *
	 * @param array $item The current row as an associative array.
	 * @return string The output for this cell.
	 */
	public function column_name( $item ) {

		$domain = $this->plugin->get_plugin_name();
		$the_url = admin_url( 'admin.php?page=pp_edit_issues' );

		$name = $item['name'];
		$term_id = +$item['ssid'];

		$can_edit = current_user_can( $this->tax->cap->edit_terms );

		// Assemble the title attribute.
		$edit_title = sprintf( __( 'Edit &lsquo;%s&rsquo;', $domain ), esc_attr( $name ) );

		/*
		 * The row of action links (appears on hover).
		 */
		$actions = array();

		// Edit action link
		if ( $can_edit ) {
			$edit_url = get_edit_term_link( $term_id, $this->tax->name );
			$edit_label = _x( 'Edit', $domain );

			$actions['edit'] = "<a href='$edit_url'>$edit_label</a>";
		}

		// Delete action link
		if ( current_user_can( $this->tax->cap->delete_terms ) ) {
			$delete_url = wp_nonce_url( $the_url . "&amp;action=delete&amp;tag_id=$term_id&amp;delete-tag_$term_id", "delete-tag_$term_id" );
			$delete_label = _x( 'Delete', $domain );

			$actions['delete'] = "<a class='delete-tag' href='$delete_url'>$delete_label</a>";
		}

		/*
		 * View link. The term ID is passed to get_term_link() as an integer to
		 * avoid it being confused with the term slug.
		 */
		$view_url = get_term_link( $term_id, $this->tax->name );
		if ( ! is_wp_error( $view_url ) ) {
			$view_label = _x( 'View', $domain );
			$actions['view'] = "<a href='$view_url'>$view_label</a>";
		}

		$row_actions = $this->row_actions( $actions );

		// Assemble the output
		$out = '<strong>';
		if ( $can_edit ) {
			$out .= "<a class='row-title' href='$edit_url' title='$edit_title'>";
			$out .= esc_html( $name );
			$out .= '</a>';
		} else {
			$out .= esc_html( $name );
		}
		$out .= '</strong>';
		$out .= $row_actions;

		return $out;
	}

	/**
	 * Formats the data for cells in the Date column.
	 *
	 * @since 1.0.0
	 *
	 * @param array $item The current row as an associative array.
	 * @return string The output for this cell.
	 */
	public function column_date( $item ) {

		$date = DateTime::createFromFormat( 'Y-m-d', $item['date'] );

		if ( $date ) {
			$out = esc_html( $date->format( 'Y/m/d' ) );
		} else {
			$out = '&mdash;';
		}

		return $out;
	}

	/**
	 * Formats the data for cells in the Posts column.
	 *
	 * @since 1.0.0
	 *
	 * @param array $item The current row as an associative array.
	 * @return string The output for this cell.
	 */
	public function column_posts( $item ) {

		$domain = $this->plugin->get_plugin_name();

		// Number of posts
		$out = ( absint( $item['posts'] ) )
			? absint( $item['posts'] )
			: '0';

		$title = sprintf( __( 'Show posts in &lsquo;%s$rsquo;', $domain ), esc_attr( $item['name'] ) );
		$tax_query = $this->tax->query_var;
		$term = $item['slug'];

		return "<a href='edit.php?$tax_query=$term' title='$title'>$out</a>";
	}

	/**
	 * Formats the data for cells in the Status column.
	 *
	 * @since 1.0.0
	 *
	 * @param array $item The current row as an associative array.
	 * @return string The output for this cell.
	 */
	public function column_status( $item ) {

		$tax_name = $this->tax->name;

		/** This filter is documented in admin/class-periodicalpress-admin.php */
		$statuses = apply_filters( "{$tax_name}_statuses", array() );

		// Get the display name for this Issue's status.
		$out = array_key_exists( $item['status'], $statuses )
			? esc_html( $statuses[ $item['status'] ] )
			: '';

		$class = esc_attr( $item['status'] );

		return "<strong class='issue-status issue-status-$class'>$out</strong>";
	}

	/**
	 * Formats the data for each individual cell of the table.
	 *
	 * Handle all columns that don't require formatting - i.e. just send
     * the column's data straight to output.
	 *
	 * @since 1.0.0
	 *
	 * @param array  $item        The current row as an associative array.
	 * @param string $column_name The array key of the current column.
	 * @return string The output for this cell.
	 */
	public function column_default( $item, $column_name ) {

		if ( ! empty ( $item[ $column_name ] ) ) {
			$out = esc_html( $item[ $column_name ] );
		} else {
			$out = '&mdash;';
		}

		return $out;
	}

}
