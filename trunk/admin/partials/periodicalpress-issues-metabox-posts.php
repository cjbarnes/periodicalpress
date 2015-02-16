<?php

/**
 * Display the Issue Posts viewing/ordering metabox
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

$pp_common = PeriodicalPress_Common::get_instance( $this->plugin );

// Get posts for this issue.
$posts = ! empty( $issue )
	? $pp_common->get_issue_posts( $issue->term_id, 'any' )
	: array();

// Order the posts.
usort( $posts, array( $pp_common, 'ascending_sort_issue_posts' ) );

?>

<div id="pp-posts-wrap">
<?php if ( is_array( $posts ) && count( $posts ) ) : ?>
	<ul id="pp-posts-list">
		<?php foreach ( $posts as $n => $post ) : ?>
			<?php
			// Prepare post data for display.
			switch ( $post->post_status ) {
				case 'publish':
					$post_status = esc_html_x( 'Published', 'Edit Issue: post status', 'periodicalpress' );
					break;
				case 'pending':
					$post_status = esc_html_x( 'Pending', 'Edit Issue: post status', 'periodicalpress' );
					break;
				case 'draft':
					$post_status = esc_html_x( 'Draft', 'Edit Issue: post status', 'periodicalpress' );
					break;
				case 'private':
					$post_status = esc_html_x( 'Private', 'Edit Issue: post status', 'periodicalpress' );
					break;
				case 'future':
					$post_status = esc_html_x( 'Future', 'Edit Issue: post status', 'periodicalpress' );
					break;
				default:
					$post_status = '';
			}
			?>
			<li id="post-<?php echo $post->ID; ?>" class="issue-post">
				<strong class="issue-post-title">
					<!-- Post title and view/Preview link -->
					<?php
					if ( ( 'publish' === $post->post_status )
					|| ( 'private' === $post->post_status ) ) :
					?>
						<a href="<?php echo get_permalink( $post->ID ) ?>" class="row-title" title="<?php printf( _x( 'View “%s”', 'Edit Issue: post actions title attribute', 'periodicalpress' ), esc_attr( $post->post_title ) ); ?>">
							<?php echo esc_html( $post->post_title ); ?>
						</a>
					<?php else : ?>
						<a href="#todo" class="row-title" title="<?php printf( _x( 'Preview “%s”', 'Edit Issue: post actions title attribute', 'periodicalpress' ), esc_attr( $post->post_title ) ); ?>">
							<?php echo esc_html( $post->post_title ); ?>
						</a>
					<?php endif; ?>
					<!-- Post status -->
					– <span class="post-state"><?php echo $post_status; ?></span>
				</strong>
				<span class="issue-post-row-actions">
					<!-- Edit link -->
					<?php if ( current_user_can( 'edit_post', $post->ID ) ) : ?>
						<a href="<?php echo get_edit_post_link( $post->ID ); ?>" class="issue-post-row-action-edit">
							<?php esc_html_e( _x( 'Edit', 'Edit Issue: post actions', 'periodicalpress' ) ); ?>
						</a>
					<?php endif; ?>

					<!-- Ordering -->
					<?php if ( 1 < count( $posts ) ) : ?>
						<?php if ( current_user_can( 'edit_post', $post->ID ) ) : ?>
							<span class="separator">|</span>
						<?php endif; ?>
						<span class="issue-post-order-area">
							<label class="screen-reader-text" for="issue-posts-order-<?php echo $post->ID; ?>">Order</label>
							<input type="number" name="pp_issue_posts_order[<?php echo $post->ID; ?>]" id="issue-posts-order-<?php echo $post->ID; ?>" class="issue-posts-order" min="1" max="500" value="<?php echo 1 + $n; ?>" />
						</span>
					<?php endif; ?>
				</span>
			</li>
		<?php endforeach; ?>
	</ul><!-- /pp-posts-list -->
<?php else : ?>
	<p>
		<?php echo _x( 'No posts in this issue.', 'Edit Issue', 'periodicalpress' ); ?>
		<?php
		/*
		 * Don't show the Add New Post link if this is a New Issue, as we don't
		 * want users to accidentally navigate away from here.
		 */
		if ( ! empty( $issue ) ) : ?>
			<a href="<?php echo esc_url( admin_url( 'post-new.php' ) ) ?>" class="add-new-post">
				<?php echo _x( 'Add New', 'Edit Issue: Add New Post', 'periodicalpress' ); ?>
			</a>
		<?php endif; ?>
	</p>
<?php endif; ?>

<?php
/*
 * Only show these instructions if this Issue is not published yet and none of
 * the posts are set to status Pending Review.
 * @todo Refactor this code to make it more readable and avoid using an inline
 * function.
 */
$pp_common = PeriodicalPress_Common::get_instance( $this->plugin );
$status = ! empty( $issue )
	? $pp_common->get_issue_meta( $issue->term_id, 'pp_issue_status' )
	: 'draft';
/**
 * Array filtering function to get WP_Post objects with a status of 'pending'.
 *
 * @since 1.0.0
 *
 * @param WP_Post $item Post object to test.
 * @return bool Whether this post has a status of 'pending'.
 */
function count_pending_issues( $item ) {
	return ( 'pending' === $item->post_status );
}
$pending = array_filter( $posts, 'count_pending_issues' );
if ( ( 'publish' !== $status )
&& ! count( $pending ) ) {
	echo _x( 'You cannot publish an issue until it has at least one post in it marked "Pending" (which means submitted for publication).', 'Edit Issue: Posts meta box', 'periodicalpress' );
}
?>
</div><!-- /pp-posts-wrap -->

<?php
