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

$pp_common = PeriodicalPress_Common::get_instance( $this->plugin );
$tax_name = $this->plugin->get_taxonomy_name();

/*
 * Determine which issue should be selected. No Issue is the default for new
 * posts. Multiple issues can't be selected. If the DB has more than one issue
 * for this post, ignore.
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

// Output a nonce field for security.
wp_nonce_field( 'set-post-issue', 'periodicalpress-post-issue-nonce' );
?>
<label class="screen-reader-text" for="pp-issue">
	<?php esc_html_e( 'Issue', 'periodicalpress' );?>
</label>
<p>
	<?php
	/*
	 * Issue is read-only for posts that both are already published and have an
	 * existing Issue selection.
	 */
	if ( ( 'publish' === $post->post_status ) && $selected_issue ) {
		$issue = get_term_by( 'id', $selected_issue, $tax_name );
		$issue_link = esc_url( get_term_link( $issue ) );
		printf( "<a href='$issue_link'>%s</a>", esc_html( $issue->name ) );
	} else {
		// Output a dropdown list of issues to choose from.
		$args = array(
			'hide_published'   => 1,
			'show_option_none' => 'No issue',
			'name'             => 'pp_issue',
			'id'               => 'pp-issue',
			'selected'         => $selected_issue
		);
		$pp_common->dropdown_issues( $args );
	}
	?>
</p>
