<?php

/**
 * Display the post-editing metabox for the Issues taxonomy
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

// Output a nonce field for security.
wp_nonce_field( 'set-post-issue', 'periodicalpress-post-issue-nonce' );
?>
<label class="screen-reader-text" for="pp-issue">
	<?php esc_html_e( 'Issue', $domain );?>
</label>
<p>
	<?php
	/*
	 * Determine which issue should be selected. No Issue is the default for new
	 * posts. Multiple issues can't be selected. If the DB has more than one
	 * issue for this post, ignore.
	 */
	if ( 'add' === get_current_screen()->action ) {
		$selected_issue = 0;
	} else {
		$get_args = array(
			'fields' => 'ids'
		);
		$post_issue_ids = wp_get_post_terms( $post->ID, $tax_name, $get_args );
		if ( 1 === count( $post_issue_ids ) ) {
			$selected_issue = $post_issue_ids[0];
		} else {
			$selected_issue = 0;
		}
	}

	// Output a dropdown list of issues
	$args = array(
		'show_option_none' => 'No issue',
		'orderby'          => 'slug',
		'order'            => 'DESC',
		'name'             => 'pp_issue',
		'id'               => 'pp-issue',
		'taxonomy'         => $tax_name,
		'hide_empty'       => 0,
		'selected'         => $selected_issue
	);
	wp_dropdown_categories( $args );
	?>
</p>
