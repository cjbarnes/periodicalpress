<?php

/**
 * List table class for the Issues management screen.
 *
 * @since 1.0.0
 *
 * @package PeriodicalPress\admin
 */

if ( ! class_exists( 'PeriodicalPress_List_Table' ) ) {
	/**
	 * Get the parent class for List Tables.
	 */
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
	 * The URL of this list table page.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var object $url
	 */
	protected $url;

	/**
	 * Constructor.
	 *
	 * Calls the parent class's constructor with arguments specific to this
	 * taxonomy.
	 *
	 * @since 1.0.0
	 *
	 * @see PeriodicalPress_List_Table::__construct()
	 *
	 * @param string $url The URL of this list table page, for use in action
	 *                    links.
	 */
	public function __construct( $url ) {

		$this->url = admin_url( $url );

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
			'name'        => esc_html( $this->tax->labels->singular_name ),
			'number'      => esc_html_x( 'Number', 'Issues Table', 'periodicalpress' ),
			'date'        => esc_html_x( 'Date', 'Issues Table', 'periodicalpress' ),
			'description' => esc_html_x( 'Description', 'Issues Table', 'periodicalpress' ),
			'slug'        => esc_html_x( 'Slug', 'Issues Table', 'periodicalpress' ),
			'posts'       => esc_html_x( 'Posts', 'Issues Table', 'periodicalpress' ),
			'status'      => esc_html_x( 'Status', 'Issues Table', 'periodicalpress' ),
			'ssid'        => esc_html_x( 'ID', 'Issues Table', 'periodicalpress' )
		) );

		return $columns;
	}

	/**
	 * Define which table columns should be hidden.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return array Associative array ( name => label ) of table columns to
	 *               hide.
	 */
	protected function get_hidden_columns() {

		/**
		 * Filters the columns that are hidden (via CSS) in the list table.
		 *
		 * @since 1.0.0
		 *
		 * @param array $hidden_columns Array of column names.
		 */
		$hidden_columns = apply_filters( "manage_{$this->tax->name}_hidden_columns", array(
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
	 * @access protected
	 *
	 * @return array Associative array ( name => array( name, order ) ) of
	 *               table columns to allow sorting on.
	 */
	protected function get_sortable_columns() {

		/**
		 * Filters the columns that are sortable and what their initial sort
		 * orders are.
		 *
		 * @since 1.0.0
		 *
		 * @param array $sortable_columns Associative array of columns in form
		 *                                ( name => array( name, order ) ).
		 */
		$sortable_columns = apply_filters( "manage_{$this->tax->name}_sortable_columns", array(
			'number' => array( 'number',  false ),
			'posts'  => array( 'posts',   false ),
		) );

		return $sortable_columns;
	}

	/**
	 * Get the table data.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return array The complete table data as an array of arrays (each sub-
	 *               array contains a single row, arranged as an associative
	 *               array ( column-name => data ) ).
	 */
	protected function table_data() {

		$pp_common = PeriodicalPress_Common::get_instance( $this->plugin );

		$data = array();

		// Default sort order.
		$orderby = 'number';
		$order = 'DESC';

		// Get user-set sort column.
		if ( ! empty( $_GET['orderby'] ) ) {

			switch ( $_GET['orderby'] ) {
				case 'posts':
					$orderby = 'count';
					break;
			}

		}

		// Get ascending or descending sort order.
		if ( ! empty( $_GET['order'] )
		&& ( ( 'asc' === $_GET['order'] ) || ( 'desc' === $_GET['order'] ) ) ) {
			$order = strtoupper( $_GET['order'] );
		}

		// Get Issues-per-page setting.
		$page_size = $this->get_items_per_page( 'edit_pp_issue_per_page' );
		$page_number = $this->get_pagenum();
		$offset = ( $page_number - 1 ) * $page_size;


		// Get the term object for each Issue to display.
		$args = array(
			'hide_empty'   => 0,
			'cache_domain' => 'periodicalpress',
			'orderby'      => $orderby,
			'order'        => $order
		);

		/*
		 * Only paginate the Issues list using the get_terms() args list if we
		 * don't need to use a 'natural' sort order. If we do need natural sort,
		 * load the complete list of Issues from the DB.
		 */
		if ( 'number' !== $orderby ) {
			$args = array_merge( $args, array(
				'number'       => $page_size,
				'offset'       => $offset
			) );
		}

		$issues = get_terms( $this->tax->name, $args );
		if ( is_wp_error( $issues ) ) {
			return array();
		}

		$this->set_pagination_args( array(
			'total_items' => count( $issues ),
			'per_page' => $page_size
		) );

		/*
		 * Add metadata to the Issues data. Added here, rather than in the next
		 * foreach loop, so that we can sort by metadata.
		 */
		foreach ( $issues as $issue ) {
			$meta = $pp_common->get_issue_meta( $issue->term_id );
			$issue->number = isset( $meta['pp_issue_number'] )
				? $meta['pp_issue_number']
				: '';
			$issue->date = ! empty( $meta['pp_issue_date'] )
				? $meta['pp_issue_date']
				: '';
			$issue->status = ! empty( $meta['pp_issue_status'] )
				? $meta['pp_issue_status']
				: '';
			$issue->created_date = ! empty( $meta['pp_issue_created_date'] )
				? $meta['pp_issue_created_date']
				: 0;
		}

		/*
		 * Sort Issues data if 'number' (the default) is the orderby choice.
		 * Although get_terms() allows ordering, we need to order manually so
		 * we can do a natural sort on Issue numbers - e.g. 'Issue 10' > 'Issue
		 * 2'.
		 */
		if ( 'number' === $orderby ) {
			if ( 'DESC' === $order ) {
				usort( $issues, array( $pp_common, 'descending_sort_issues' ) );
			} else {
				usort( $issues, array( $pp_common, 'ascending_sort_issues' ) );
			}
		}

		// Discard all Issue terms not needed for this pagination page.
		$issues = array_slice( $issues, $offset, $page_size );

		// Get the current issue from the database.
		$current_issue = (int) get_option( 'pp_current_issue', 0 );

		// Add each term as a new row.
		foreach ( $issues as $issue ) {

			// Prep current issue boolean.
			$current = ( (int) $issue->term_id === $current_issue );

			$data[] = array(
				'number'        => $issue->number,
				'name'          => $issue->name,
				'date'          => $issue->date,
				'description'   => $issue->description,
				'slug'          => $issue->slug,
				'posts'         => $issue->count,
				'status'        => $issue->status,
				'ssid'          => $issue->term_id,
				'current_issue' => $current
			);

		}

		return $data;
	}

	/**
	 * Generates content for a single row of the table.
	 *
	 * @since 1.0.0
	 *
	 * @param object $item The current item.
	 */
	public function single_row( $item ) {

		// Get the alternate-rows class.
		static $row_class = '';
		$row_class = ( $row_class == '' ? 'alternate' : '' );

		$classes = array();

		if ( $row_class ) {
			$classes[] = $row_class;
		}

		// Hook for styling different Issue statuses.
		if ( ! empty( $item['status'] ) ) {
			$classes[] = 'issue-' . esc_attr( $item['status'] );
		}

		// Hook for styling the Current Issue's row.
		if ( ! empty( $item['current_issue'] ) ) {
			$classes[] = 'issue-current';
		}

		/**
		 * Filter for classes to be applied to each Issues list table row.
		 *
		 * @since 1.0.0
		 *
		 * @param array $classes The classnames to apply to the tr element.
		 * @param array $item    All data for this row.
		 */
		$classes = apply_filters( "{$this->tax->name}_row_classes", $classes, $item );

		echo '<tr class="' . implode( ' ', $classes ) . '"">';
		$this->single_row_columns( $item );
		echo '</tr>';

	}

	/**
	 * Formats the data for cells in the Name column.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param array $item The current row as an associative array.
	 * @return string The output for this cell.
	 */
	protected function column_name( $item ) {

		$name = $item['name'];
		$term_id = +$item['ssid'];

		$can_edit = current_user_can( $this->tax->cap->edit_terms );

		// Assemble the title attribute.
		$edit_title = sprintf( __( 'Edit &lsquo;%s&rsquo;', 'periodicalpress' ), esc_attr( $name ) );

		/*
		 * The row of action links (appears on hover).
		 */
		$actions = array();

		// Edit action link
		if ( $can_edit ) {
			$edit_url = $this->url . "&amp;action=edit&amp;tag_id=$term_id";
			$edit_label = _x( 'Edit', 'periodicalpress' );

			$actions['edit'] = "<a href='$edit_url'>$edit_label</a>";
		}

		/*
		 * Toggle post status (publish/unpublish). Which link is shown depends
		 * on the $item['status'].
		 */
		if ( current_user_can( $this->tax->cap->manage_terms ) ) {
			if ( 'publish' === $item['status'] ) {
				$action = 'unpublish';
				$action_label = 'Unpublish';
			} else {
				$action = 'publish';
				$action_label = 'Publish';
			}

			// Don't show Publish link if there are no posts.
			if ( ( 'unpublish' === $action ) || ( 0 < (int) $item['posts'] ) ) {
				$action_url = wp_nonce_url( $this->url . "&amp;action=$action&amp;tag_id=$term_id&amp;$action-tag_$term_id", "$action-tag_$term_id" );
				$actions[ $action ] = "<a class='$action-tag' href='$action_url'>$action_label</a>";
			}
		}

		// Delete action link.
		if ( current_user_can( $this->tax->cap->delete_terms ) ) {
			$delete_url = wp_nonce_url( $this->url . "&amp;action=delete&amp;tag_id=$term_id&amp;delete-tag_$term_id", "delete-tag_$term_id" );
			$delete_label = __( 'Delete', 'periodicalpress' );

			$actions['delete'] = "<a class='delete-tag' href='$delete_url'>$delete_label</a>";
		}

		/*
		 * View link. The term ID is passed to get_term_link() as an integer to
		 * avoid it being confused with the term slug.
		 */
		$view_url = get_term_link( $term_id, $this->tax->name );
		if ( ! is_wp_error( $view_url ) ) {
			if ( 'publish' === $item['status'] ) {
				$view_label = _x( 'View', 'periodicalpress' );
			} else {
				$view_label = _x( 'Preview', 'periodicalpress' );
				$view_url .= '?preview=true';
			}
			$actions['view'] = "<a href='$view_url'>$view_label</a>";
		}

		$row_actions = $this->row_actions( $actions );

		// Assemble the output.
		$out = '<strong>';
		if ( $can_edit ) {
			$out .= "<a class='row-title' href='$edit_url' title='$edit_title'>";
			$out .= esc_html( $name );
			$out .= '</a>';
		} else {
			$out .= '<span class="row-title">' . esc_html( $name ) . '</span>';
		}

		// Flag the current issue.
		if ( ! empty( $item['current_issue'] ) ) {
			$current_label = _x( 'Current', 'periodicalpress' );
			$out .= " <span class='issue-status-current-issue post-state'>- $current_label</span>";
		}
		$out .= '</strong>';

		$out .= $row_actions;

		return $out;
	}

	/**
	 * Formats the data for cells in the Date column.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param array $item The current row as an associative array.
	 * @return string The output for this cell.
	 */
	protected function column_date( $item ) {

		$date = DateTime::createFromFormat( 'Y-m-d', $item['date'] );

		if ( $date ) {
			$out = esc_html( date_i18n( _x( 'Y/m/d', 'Issue Date - PHP date format', 'periodicalpress' ), $date->getTimestamp() ) );
		} else {
			$out = '&mdash;';
		}

		return $out;
	}

	/**
	 * Formats the data for cells in the Posts column.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param array $item The current row as an associative array.
	 * @return string The output for this cell.
	 */
	protected function column_posts( $item ) {

		// Number of posts.
		$out = ( absint( $item['posts'] ) )
			? absint( $item['posts'] )
			: '0';

		$title = sprintf( __( 'Show posts in &lsquo;%s$rsquo;', 'periodicalpress' ), esc_attr( $item['name'] ) );
		$tax_query = $this->tax->query_var;
		$term = $item['slug'];

		// Localize the posts count.
		$out = number_format_i18n( $out );

		return "<a href='edit.php?$tax_query=$term' title='$title'>$out</a>";
	}

	/**
	 * Formats the data for cells in the Status column.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param array $item The current row as an associative array.
	 * @return string The output for this cell.
	 */
	protected function column_status( $item ) {

		$tax_name = $this->tax->name;
		$term_id = +$item['ssid'];

		/** This filter is documented in admin/class-periodicalpress-admin.php */
		$statuses = apply_filters( "{$tax_name}_statuses", array() );

		// Check that a valid status has been passed in.
		if ( ! array_key_exists( $item['status'], $statuses ) ) {
			return '&mdash;';
		}

		$display_name = esc_html( $statuses[ $item['status'] ] );
		$class = esc_attr( $item['status'] );

		$out = "<strong class='issue-status issue-status-$class'>$display_name</strong>";

		return $out;
	}

	/**
	 * Formats the data for each individual cell of the table.
	 *
	 * Handle all columns that don't require formatting - i.e. just send
     * the column's data straight to output.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param array  $item        The current row as an associative array.
	 * @param string $column_name The array key of the current column.
	 * @return string The output for this cell.
	 */
	protected function column_default( $item, $column_name ) {

		if ( ! empty ( $item[ $column_name ] ) || ( '0' === $item[ $column_name ] ) ) {
			$out = esc_html( $item[ $column_name ] );
		} else {
			$out = '&mdash;';
		}

		return $out;
	}

}
