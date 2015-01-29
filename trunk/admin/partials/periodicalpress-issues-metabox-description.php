<?php

/**
 * Display the Issue Description editing box
 *
 * Used on the Edit Issue screen.
 *
 * @since 1.0.0
 *
 * @package PeriodicalPress\Admin
 */

/*
 * Don't allow direct loading of this file, and make sure we have an Issue to
 * work with.
 */
if ( ! defined( 'ABSPATH' ) || ! isset( $issue ) ) {
	exit;
}

?>
<label for="description" class="screen-reader-text">
	<?php echo _x( 'Description', 'Edit Issue', 'periodicalpress' ); ?>
</label>
<textarea rows="1" cols="40" name="description" id="description"><?php echo esc_textarea( $issue->description ); ?></textarea>
<p><?php echo _x( 'The description is not prominent by default; however, some themes may show it.', 'Edit Issue', 'periodicalpress' ); ?></p>
<?php
