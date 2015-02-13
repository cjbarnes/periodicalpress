<?php

/**
 * The Plugin Settings page rendering, validation, and saving.
 *
 * @since 1.0.0
 *
 * @package PeriodicalPress\Admin
 */

/**
 * The Plugin Settings page rendering, validation, and saving.
 *
 * Uses the Singleton pattern.
 *
 * @since 1.0.0
 */
class PeriodicalPress_Settings extends PeriodicalPress_Singleton {

	/**
	 * Register all hooks for actions and filters in this class.
	 *
	 * Called by the parent class's Constructor.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function define_hooks() {

		// Admin menu item setup.
		add_action( 'admin_menu', array( $this, 'admin_menu_setup' ) );

		// Settings fields setup.
		add_action( 'admin_init', array( $this, 'register_settings' ) );

		// Plugins table link to Settings page.
		$plugin_name = $this->plugin->get_plugin_name();
		$actions_filter = "plugin_action_links_$plugin_name/$plugin_name.php";
		add_filter( $actions_filter, array( $this, 'add_plugin_row_actions' ), 10, 4 );

	}

	/**
	 * Add Plugin Settings page to the Issues menu.
	 *
	 * @since 1.0.0
	 */
	public function admin_menu_setup() {

		$tax = get_taxonomy( $this->plugin->get_taxonomy_name() );

		add_submenu_page(
			'pp_edit_issues',
			/*
			 * For translators: HTML title of the plugin settings page. %s is
			 * the plugin name.
			 */
 			sprintf( __( '%s Settings', 'periodicalpress' ), 'PeriodicalPress' ),
			_x( 'Settings', 'Admin menu link for plugin settings page', 'periodicalpress' ),
			$tax->cap->manage_terms,
			'periodicalpress_settings',
			array( $this, 'render_plugin_settings_page' )
		);

	}

	/**
	 * Add link to Plugin Settings page to the Plugins list table's row actions.
	 *
	 * Filters the row-action links for this plugin's entry in the Plugins page
	 * list table.
	 *
	 * @since 1.0.0
	 *
	 * @param array  $actions     The currently set row-actions link elements.
	 * @param string $plugin_file Path to the plugin file.
	 * @param array  $plugin_data Array of plugin data.
	 * @param string $context     The plugin context (e.g. Must-Use,
	 *                            'Inactive').
	 */
	public function add_plugin_row_actions( $actions, $plugin_file, $plugin_data, $context ) {

		$link = sprintf( '<a href="%s" title="%s" class="settings">%s</a>',
			admin_url( 'admin.php?page=periodicalpress_settings' ),
			esc_attr__( 'PeriodicalPress Settings', 'periodicalpress' ),
			esc_html_x( 'Settings', 'Plugins table link to plugin settings page', 'periodicalpress' )
		);

		array_unshift( $actions, $link );
		return $actions;
	}

	/**
	 * Set up the Plugin Settings page contents and callbacks.
	 *
	 * @since 1.0.0
	 */
	public function register_settings() {

		// The basic field data to register settings fields from.
		$fields = array(
			array( 'name' => 'pp_current_issue', 'id' => 'current-issue', 'label' => __( 'Current Issue', 'periodicalpress' ) ),
			array( 'name' => 'pp_issue_naming', 'id' => 'issue-naming', 'label' => __( 'Issue Names Format', 'periodicalpress' ) ),
			array( 'name' => 'pp_issue_date_format', 'id' => 'issue-date-format', 'label' => __( 'Issue Date Format', 'periodicalpress' ) )
		);

		add_settings_section(
			'default',
			'',
			array( $this, 'render_plugin_settings_section' ),
			'periodicalpress_settings'
		);

		// Register the fields, their render callbacks, and their validation.
		foreach ( $fields as $field ) {
			register_setting( 'periodicalpress_settings', $field['name'], array( $this, "validate_{$field['name']}" ) );
			add_settings_field(
				$field['id'],
				$field['label'],
				array( $this, 'render_plugin_settings_field' ),
				'periodicalpress_settings',
				'default',
				$field
			);
		}

	}

	/**
	 * Output the contents of the Plugin Settings page.
	 *
	 * @since 1.0.0
	 */
	public function render_plugin_settings_page() {

		$tax = get_taxonomy( $this->plugin->get_taxonomy_name() );

		if ( current_user_can( $tax->cap->manage_terms ) ) {

			/**
			 * Output the Settings page partial.
			 */
			$path = $this->plugin->get_partials_path( 'admin' );
			require $path . 'periodicalpress-settings.php';

		}

	}

	/**
	 * Callback for the default Plugin Settings section.
	 *
	 * The individual fields have their own separate partials, so this callback
	 * doesn't need to do anything other than exist...
	 *
	 * @since 1.0.0
	 */
	public function render_plugin_settings_section() {

	}

	/**
	 * Output the contents of a single Plugin Settings field.
	 *
	 * Loads the partial for this field. Generic function that finds the correct
	 * partial based on the field data passed in via the `add_settings_field()`
	 * call.
	 *
	 * @since 1.0.0
	 *
	 * @param array $field {
	 *     Associative array containing this field's main identifiers.
	 *
	 *     @type string $name  The HTML name of the setting (must match its key
	 *                         in the database as well as the partial filename).
	 *     @type string $id    HTML ID for the field.
	 *     @type string $label The label text for the field. Should already be
	 *                         translated when passed into this method.
	 * }
	 */
	public function render_plugin_settings_field( $field ) {

		if ( isset( $field['name'] ) ) {
			$path = $this->plugin->get_partials_path( 'admin' );
			require $path . "periodicalpress_field_{$field['name']}.php";
		}

	}

	/**
	 * Sanitize callback for the Current Issue settings field.
	 *
	 * @since 1.0.0
	 *
	 * @param string $input The unsanitized field input.
	 * @return int A valid Current Issue value.
	 */
	public function validate_pp_current_issue( $input ) {

		$tax_name = $this->plugin->get_taxonomy_name();
		$tax = get_taxonomy( $tax_name );

		// Check current user has sufficient permissions.
		if ( ! current_user_can( $tax->cap->manage_terms ) ) {
			add_settings_error(
				'pp_current_issue',
				'not-permitted',
				__( 'You do not have permission to edit the Current Issue.', 'periodicalpress' )
			);
		}

		/**
		 * Check a valid (and existing) term was inputted. If not, fallback to
		 * the previous DB value.
		 */
		if ( ! empty( $input )
		&& get_term( +$input, $tax_name ) ) {

			$result = +$input;

			// Remove now out-of-date cached ordered-Issues data.
			PeriodicalPress_Common::get_instance( $this->plugin )->delete_issue_transients();

		} else {
			add_settings_error(
				'pp_current_issue',
				'invalid-input',
				__( 'Could not change Current Issue: the chosen issue no longer exists.', 'periodicalpress' )
			);
			$result = get_option( 'pp_current_issue', 0 );
		}

		return $result;
	}

	/**
	 * Sanitize callback for the Issue Name Format settings field.
	 *
	 * @since 1.0.0
	 *
	 * @param string $input The unsanitized field input.
	 * @return int A valid Issue Name Format.
	 */
	public function validate_pp_issue_naming( $input ) {

		$tax_name = $this->plugin->get_taxonomy_name();
		$tax = get_taxonomy( $tax_name );

		// Check current user has sufficient permissions.
		if ( ! current_user_can( $tax->cap->manage_terms ) ) {
			add_settings_error(
				'pp_issue_naming',
				'not-permitted',
				__( 'You do not have permission to edit the Issue Names Format.', 'periodicalpress' )
			);
		}

		if ( ! empty( $input ) ) {
			$naming = strtolower( $input );

			// Whitelist of allowed name formats. Fallback to previous DB value.
			switch ( $naming ) {
				case 'numbers':
				case 'dates':
				case 'titles':
					$result = $naming;
					break;

				default:
					add_settings_error(
						'pp_issue_naming',
						'invalid-input',
						__( 'Could not change Issue Names Format: that format does not exist.', 'periodicalpress' )
					);
					$result = get_option( 'pp_issue_naming', '' );
			}
		}

			}

			return $result;
		} else {
			return $old_val;
		}

	}

	/**
	 * Sanitize callback for the Issue Date Format settings field.
	 *
	 * @since 1.0.0
	 *
	 * @param string $input The unsanitized field input.
	 * @return int A valid date format.
	 */
	public function validate_pp_issue_date_format( $input ) {

		$tax_name = $this->plugin->get_taxonomy_name();
		$tax = get_taxonomy( $tax_name );

		// Check current user has sufficient permissions.
		if ( ! current_user_can( $tax->cap->manage_terms ) ) {
			add_settings_error(
				'pp_issue_date_format',
				'not-permitted',
				__( 'You do not have permission to edit the Issue Date Format.', 'periodicalpress' )
			);
		}

		if ( ! empty( $input ) ) {

			// Use custom date if that's the chosen date format.
			// CHECK: may need to unslash this.
			if ( '\c\u\s\t\o\m' === $input ) {
				$input = ( ! empty( $_POST['pp_issue_date_format_custom'] ) )
					? $_POST['pp_issue_date_format_custom']
					: '';
			}

			$result = sanitize_text_field( $input );
		}

		// Fallback to previous DB value if the result is now empty.
		if ( empty( $result ) ) {
			add_settings_error(
				'pp_issue_date_format',
				'no-input',
				__( 'The Issue Date Format cannot be empty. Please either choose a suggested format or type a custom format into the "Custom" field.', 'periodicalpress' )
			);
			$result = get_option( 'pp_issue_date_format', get_option( 'date_format' ) );
		}

		return $result;
	}

}
