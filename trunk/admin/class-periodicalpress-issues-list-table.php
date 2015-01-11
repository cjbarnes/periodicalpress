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
			'name'        => esc_html_x( $this->tax->labels->singular_name, $context, $domain ),
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
			'number',
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
	 * @access protected
	 *
	 * @return array The complete table data as an array of arrays (each sub-
	 *               array contains a single row, arranged as an associative
	 *               array ( column-name => data ) ).
	 */
	protected function table_data() {

		$data = array();

		// Default sort order.
		$orderby = 'name';
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
		if ( 'name' !== $orderby ) {
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
		 * Sort Issues data if 'name' (the default) is the orderby choice.
		 * Although get_terms() allows ordering by name, we need to order
		 * manually so we can do a natural sort on Issue numbers - e.g.
		 * 'Issue 10' > 'Issue 2'.
		 */
		if ( 'name' === $orderby ) {
			if ( 'DESC' === $order ) {
				usort( $issues, array( $this, 'descending_sort_term_names' ) );
			} else {
				usort( $issues, array( $this, 'ascending_sort_term_names' ) );
			}
		}

		// Discard all Issue terms not needed for this pagination page.
		$issues = array_slice( $issues, $offset, $page_size );

		// Get the current issue from the database.
		$current_issue = (int) get_option( 'pp_current_issue', 0 );

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

			// Prep current issue boolean.
			$current = ( (int) $issue->term_id === $current_issue );

			$data[] = array(
				'number'        => $number,
				'name'          => $issue->name,
				'date'          => $date,
				'title'         => $title,
				'description'   => $issue->description,
				'slug'          => $issue->slug,
				'posts'         => $issue->count,
				'status'        => $status,
				'ssid'          => $issue->term_id,
				'current_issue' => $current
			);

		}

		return $data;
	}

	/**
	 * Reversed natural sorting function for terms.
	 *
	 * The opposite of {@see ascending_sort_term_names}.
	 *
	 * Generates the default sort order of the Issues list table.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param  object $obj1 Term object to compare.
	 * @param  object $obj2 Term object to compare.
	 * @return int The comparison result: -1 = greater than, 1 = lesser than,
	 *             0 = equal to.
	 */
	protected function descending_sort_term_names( $obj1, $obj2 ) {

		$str1 = isset( $obj1->name ) ? $obj1->name : '';
		$str2 = isset( $obj2->name ) ? $obj2->name : '';

		return strnatcasecmp( $str2, $str1 );
	}

	/**
	 * Natural sorting function for terms.
	 *
	 * Basically an array-of-objects extension of PHP's strnatcasecmp()
	 * {@link http://php.net/manual/en/function.strnatcasecmp.php}. Called by
	 * usort() to carry out an ascending order 'natural sort' - e.g.
	 * "Issue 10" > "Issue 2" - of an array of taxonomy terms based on their
	 * `name` properties.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param  object $obj1 Term object to compare.
	 * @param  object $obj2 Term object to compare.
	 * @return int The comparison result: -1 = greater than, 1 = lesser than,
	 *             0 = equal to.
	 */
	protected function ascending_sort_term_names( $obj1, $obj2 ) {

		$str1 = isset( $obj1->name ) ? $obj1->name : '';
		$str2 = isset( $obj2->name ) ? $obj2->name : '';

		return strnatcasecmp( $str1, $str2 );
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

		$domain = $this->plugin->get_plugin_name();

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
			$edit_url = $this->url . "&amp;action=edit&amp;tag_id=$term_id";
			$edit_label = _x( 'Edit', $domain );

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

		// Delete action link
		if ( current_user_can( $this->tax->cap->delete_terms ) ) {
			$delete_url = wp_nonce_url( $this->url . "&amp;action=delete&amp;tag_id=$term_id&amp;delete-tag_$term_id", "delete-tag_$term_id" );
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
			$out .= '<span class="row-title">' . esc_html( $name ) . '</span>';
		}

		// Flag the current issue.
		if ( ! empty( $item['current_issue'] ) ) {
			$current_label = _x( 'Current', $domain );
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
	 * @access protected
	 *
	 * @param array $item The current row as an associative array.
	 * @return string The output for this cell.
	 */
	protected function column_posts( $item ) {

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
	 * @access protected
	 *
	 * @param array $item The current row as an associative array.
	 * @return string The output for this cell.
	 */
	protected function column_status( $item ) {

		$domain = $this->plugin->get_plugin_name();
		$context = 'Issues Table';

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

		if ( ! empty ( $item[ $column_name ] ) ) {
			$out = esc_html( $item[ $column_name ] );
		} else {
			$out = '&mdash;';
		}

		return $out;
	}

}
