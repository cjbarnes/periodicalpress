<?php

/**
 * Fired during plugin activation
 *
 * @since 1.0.0
 *
 * @package PeriodicalPress
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since 1.0.0
 */
class PeriodicalPress_Activator {

	/**
	 * Run on plugin activation.
	 *
	 * Carries out all one-time setup changes (especially to the database).
	 *
	 * @since 1.0.0
	 */
	public static function activate() {

		self::create_capabilities();
		self::create_termmeta_table();
		self::default_settings();
		self::set_rewrite_rules();

		// Todo: create a default Issue, add all the posts, and set as Current.

		// Todo: show the welcome screen and/or message to all users.

	}

	/**
	 * Register capabilities.
	 *
	 * Setup plugin-specific capabilities and apply them to the existing roles
	 * in WordPress that have these capabilities:
	 * - `assign_pp_issue`  - edit_posts        - allows per-post Issue choice
	 * - `manage_pp_issues` - edit_others_posts - gives access to Issues admin
	 *
	 * Also removes `publish_post` capability across all roles, because post
	 * publication is handled automatically by the plugin.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @global WP_Roles $wp_roles The WordPress roles and capabilities class
	 */
	protected static function create_capabilities() {
		global $wp_roles;

		$all_roles = $wp_roles->roles;

		$publishers = array();

		foreach ( $all_roles as $role_name => $role_contents ) {

			$role_caps = $role_contents['capabilities'];

			/*
			 * Create new capability: assign_pp_issue (initially set for users
			 * with edit_posts capability, i.e. Contributors and up).
			 */
			if ( isset( $role_caps['edit_posts'] ) ) {
				$wp_roles->add_cap( $role_name, 'assign_pp_issue' );
			}

			/*
			 * Create new capability: manage_pp_issues (initially set for users
			 * with edit_others_posts capability, i.e. Editors and up).
			 */
			if ( isset( $role_caps['edit_others_posts'] ) ) {
				$wp_roles->add_cap( $role_name, 'manage_pp_issues' );
			}

			// Remove `publish_posts` capability from everyone.
			if ( isset( $role_caps['publish_posts'] ) ) {
				$wp_roles->remove_cap( $role_name, 'publish_posts' );
				$publishers[] = $role_name;
			}

		}

		// Save which roles had `publish_post` capability for later restoration.
		add_option( 'pp_old_publishers', $publishers, '', 'no' );

	}

	/**
	 * Create a DB table for taxonomy term metadata.
	 *
	 * Used by add_metadata(), update_metadata(), delete_metadata(), and
	 * get_metadata() when applied to taxonomy terms (i.e. Issues).
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @global wpdb $wpdb The database interaction wrapper.
	 */
	protected static function create_termmeta_table() {
		global $wpdb;

		/*
		 * Assemble table creation SQL.
		 */

		$table_name = $wpdb->prefix . 'pp_termmeta';

		$charset_collate = '';
		if ( ! empty ( $wpdb->charset ) ) {
			$charset_collate .= "DEFAULT CHARACTER SET {$wpdb->charset}";
		}
		if ( ! empty ( $wpdb->collate ) ) {
			$charset_collate .= " COLLATE {$wpdb->collate}";
		}

		$sql = "CREATE TABLE IF NOT EXISTS `$table_name` (
			`meta_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			`pp_term_id` bigint(20) unsigned NOT NULL DEFAULT '0',
			`meta_key` varchar(255) DEFAULT NULL,
			`meta_value` longtext,
			PRIMARY KEY (`meta_id`),
			KEY `pp_term_id` (`pp_term_id`),
			KEY `meta_key` (`meta_key`)
		) $charset_collate;";

		// Add to DB
		$wpdb->query( $sql );


	}

	/**
	 * Create default values for all plugin settings.
	 *
	 * Settings created:
	 * - (none)
	 *
	 * Use of add_option() here means that we don't override any settings that
	 * exist from a previous activation of this plugin.
	 *
	 * Each setting can be accessed using get_option() and saved using
	 * update_option().
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected static function default_settings() {

		// add_option( 'periodicalpress_x', 'default', true );

	}

	/**
	 * Flush rewrite rules on plugin activation.
	 *
	 * Setup permalinks for all declared custom post types and taxonomies, by
	 * loading in the new types/taxonomies and then flushing rewrite rules.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected static function set_rewrite_rules() {

		/*
		 * Load the class that creates custom post types and taxonomies, so
		 * their URL rules can be applied using flush_rewrite_rules.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-periodicalpress-taxonomy.php';
		PeriodicalPress_Taxonomy::register_taxonomy();

		flush_rewrite_rules();

	}

}
