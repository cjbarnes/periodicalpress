<?php

/**
 * Display the PeriodicalPress HTML form for selecting the current issue
 *
 * @since 1.0.0
 *
 * @package PeriodicalPress\Admin
 */
?>

<div class="form-wrap">
	<h3><?php esc_html_e( 'Current Issue', 'periodicalpress' ); ?></h3>

	<form id="set-current-issue" method="post" action="edit-tags.php" />
	<?php wp_nonce_field( 'set-current-issue' ); ?>

		<input type="hidden" name="action" value="set-current-issue" />
		<input type="hidden" name="screen" value="edit-pp_issue" />
		<input type="hidden" name="taxonomy" value="pp_issue" />
		<input type="hidden" name="post_type" value="post" />

		<div class="form-field current-tag-wrap">
			<label for="current-issue" class="screen-reader-text"><?php esc_html_e( 'Issue', 'periodicalpress' ); ?></label>

		<?php
			// Output a dropdown list of issues
			$args = array(
				'orderby'       => 'slug',
				'order'         => 'DESC',
				'name'          => 'current-issue',
				'id'            => 'current-issue',
				'taxonomy'      => 'pp_issue',
				//'selected'      => TODO show selected term
			);
			wp_dropdown_categories( $args );
		?>

			<input type="submit" name="submit" id="submit" class="button button-primary" value="<?php esc_attr_e( 'Set Current Issue', 'periodicalpress' ); ?>" />

			<p><?php esc_html_e( 'The Current Issue is the issue featured on the homepage of the website. Usually it is the most recently published issue.', 'periodicalpress' ); ?></p>
		</div>
	</form>
</div>
