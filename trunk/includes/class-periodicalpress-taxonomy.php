<?php

/**
 * The Issues taxonomy registration class
 *
 * This is kept in a separate class (and file) to streamline the plugin
 * activation process. It is loaded and used statically during activation to
 * allow flushing of rewrite rules.
 *
 * @since 1.0.0
 *
 * @package PeriodicalPress
 */

/*
 * Reinclude parent class. We need to do that here for plugin activation, since
 * the rest of the plugin classes aren't loaded when the activation hook runs.
 */
if ( ! class_exists( 'PeriodicalPress_Singleton' ) ) {
	/**
	 * Get the parent class for all Singleton classes.
	 */
	require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-periodicalpress-singleton.php';
}

/**
 * The Issues taxonomy registration class.
 *
 * Registers the taxonomy. Called by both plugin activation (to allow flushing
 * of rewrite rules) and the init hook.
 *
 * @since 1.0.0
 */
class PeriodicalPress_Taxonomy extends PeriodicalPress_Singleton {

	/**
	 * Register all hooks for actions and filters in this class.
	 *
	 * Called by the parent class's Constructor.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function define_hooks() {

		// Register the taxonomy.
		add_action( 'init', array( $this, 'register_taxonomy' ), 0 );

	}

	/**
	 * Register the custom taxonomy for Issues.
	 *
	 * @since 1.0.0
	 *
	 * @see PeriodicalPress
	 */
	public static function register_taxonomy() {

		/*
		 * Cannot use the class property $this->plugin here, because on
		 * activation this method is called statically.
		 */
		$plugin = PeriodicalPress::get_instance();
		$tax_name = $plugin->get_taxonomy_name();

		// Setup the Issue custom post type that is the basis for this plugin.
		$labels = array(
			'name'                       => _x( 'Issues',
													'Taxonomy General Name',
													'periodicalpress' ),
			'singular_name'              => _x( 'Issue',
													'Taxonomy Singular Name',
													'periodicalpress' ),
			'menu_name'                  => __( 'Issues',
													'periodicalpress' ),
			'all_items'                  => __( 'All Issues',
													'periodicalpress' ),
			'parent_item'                => __( 'Parent Issue',
													'periodicalpress' ),
			'parent_item_colon'          => __( 'Parent Issue:',
													'periodicalpress' ),
			'new_item_name'              => __( 'New Issue Name',
													'periodicalpress' ),
			'add_new_item'               => __( 'Add New Issue',
													'periodicalpress' ),
			'edit_item'                  => __( 'Edit Issue',
													'periodicalpress' ),
			'update_item'                => __( 'Update Issue',
													'periodicalpress' ),
			'separate_items_with_commas' => __( 'Separate issues with commas',
													'periodicalpress' ),
			'search_items'               => __( 'Search Issues',
													'periodicalpress' ),
			'add_or_remove_items'        => __( 'Add or remove issues',
													'periodicalpress' ),
			'choose_from_most_used' => __( 'Choose from the most used issues',
													'periodicalpress' ),
			'not_found'                  => __( 'Not Found',
													'periodicalpress' ),
		);
		$rewrite = array(
			'slug'         => 'issue',
			'with_front'   => false,
			'hierarchical' => false,
		);
		$capabilities = array(
			'manage_terms' => 'manage_pp_issues',
			'edit_terms'   => 'manage_pp_issues',
			'delete_terms' => 'manage_pp_issues',
			'assign_terms' => 'assign_pp_issue',
		);
		$args = array(
			'labels'                => $labels,
			'hierarchical'          => true,
			'public'                => true,
			'show_ui'               => false,
			'show_admin_column'     => true,
			'show_in_nav_menus'     => true,
			'show_tagcloud'         => false,
			'query_var'             => 'issue',
			'rewrite'               => $rewrite,
			'capabilities'          => $capabilities,
			'update_count_callback' => array( self::get_instance( $plugin ), 'update_issue_post_count' )
		);
		register_taxonomy( $tax_name, array( 'post' ), $args );

	}

	/**
	 * Callback for updating Issues' post counts in the DB.
	 *
	 * A modified version of Core {@see _update_generic_term_count()}. The
	 * original's main query has been extended to restrict counted post statuses
	 * to 'publish', 'pending', and 'future' only. Borrows from
	 * {@see _update_post_term_count()}.
	 *
	 * @since 1.0.0
	 *
	 * @global wpdb $wpdb The WordPress database class.
	 *
	 * @param int|array $terms    The IDs of the Issues being updated.
	 * @param string    $taxonomy The Issues taxonomy name.
	 */
	public function update_issue_post_count( $terms, $taxonomy ) {
		global $wpdb;

		foreach ( (array) $terms as $term ) {

			$sql = $wpdb->prepare( "
				SELECT COUNT(*)
				FROM {$wpdb->term_relationships}, {$wpdb->posts} p1
				WHERE p1.ID = {$wpdb->term_relationships}.object_id
				AND post_status IN ('publish','pending','future')
				AND term_taxonomy_id = %d
			", $term );
			$count = (int) $wpdb->get_var( $sql );

			/** This action is documented in wp-includes/taxonomy.php */
			do_action( 'edit_term_taxonomy', $term, $taxonomy );

			$wpdb->update( $wpdb->term_taxonomy, compact( 'count' ), array( 'term_taxonomy_id' => $term ) );

			/** This action is documented in wp-includes/taxonomy.php */
			do_action( 'edited_term_taxonomy', $term, $taxonomy );

		}

	}

}
