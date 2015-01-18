<?php

/**
 * Display the Quick Edit custom box for the Issues taxonomy.
 *
 * @since 1.0.0
 *
 * @package PeriodicalPress\Admin
 */

// Don't allow direct loading of this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$domain = $this->plugin->get_plugin_name();
$tax_name = $this->plugin->get_taxonomy_name();

?>
<fieldset class="inline-edit-col-right inline-edit-<?php echo $tax_name; ?>">
	<?php
	// Output a nonce field for security.
	wp_nonce_field( 'set-post-issue', 'periodicalpress-post-issue-nonce' );
	?>
	<div class="inline-edit-col column-<?php echo $tax_name; ?>">
		<label class="inline-edit-pp-issue alignleft">
			<span class="title">
				<?php esc_html_e( 'Issue', $domain );?>
			</span>
			<?php
			/*
			 * Output a dropdown list of issues. Selection of the currently
			 * selected issue happens in JavaScript due to the way the Quick
			 * Edit functionality is structured in Core - see
			 * {@link http://codex.wordpress.org/Plugin_API/Action_Reference/quick_edit_custom_box}.
			 */
			$args = array(
				'show_option_none' => 'No issue',
				'orderby'          => 'slug',
				'order'            => 'DESC',
				'name'             => 'pp_issue',
				'id'               => 'pp-issue',
				'taxonomy'         => $tax_name,
				'hide_empty'       => 0,
				'selected'         => 0
			);
			wp_dropdown_categories( $args );
			?>
		</label>
	</div>
</fieldset>
