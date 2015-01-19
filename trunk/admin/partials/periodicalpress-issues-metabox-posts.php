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

$domain = $this->plugin->get_plugin_name();

// Get posts for this issue.
$posts = $pp_common->get_issue_posts( $issue->term_id, 'any' );

// Order the posts.
// @todo sort by manually-set order, or date (ascending) as secondary sort.

?>

<div id="pp-posts-wrap">
<?php if ( is_array( $posts ) && count( $posts ) ) : ?>
	<p><?php echo _x( 'Click and drag posts to reorder them.', 'Edit Issue: posts list', $domain ); ?></p>
	<ul id="pp-posts-list">
		<?php foreach ( $posts as $n => $post ) : ?>
			<?php
			// Prepare post data for display.
			$x = 'Edit Issue: post status';
			switch ( $post->post_status ) {
				case 'publish':
					$post_status = esc_html_x( 'Published', $x, $domain );
					break;
				case 'pending':
					$post_status = esc_html_x( 'Pending', $x, $domain );
					break;
				case 'draft':
					$post_status = esc_html_x( 'Draft', $x, $domain );
					break;
				case 'private':
					$post_status = esc_html_x( 'Private', $x, $domain );
					break;
				case 'future':
					$post_status = esc_html_x( 'Future', $x, $domain );
					break;
				default:
					$post_status = '';
			}
			?>
			<li id="post-<?php echo $post->ID; ?>" class="issue-post">
				<strong class="issue-post-title">
					<?php echo esc_html( $post->post_title ); ?>
				</strong>
				<span class="issue-post-status">
					<?php echo $post_status; ?>
				</span>
				<span class="issue-post-row-actions">
					<!-- View/Preview link -->
					<?php
					if ( ( 'publish' === $post->post_status )
					|| ( 'private' === $post->post_status ) ) :
					?>
						<a href="<?php echo get_permalink( $post->ID ) ?>" class="issue-post-row-action-view">
							<?php esc_html_e ( _x( 'View', 'Edit Issue: post actions', $domain ) ); ?>
						</a>
					<?php else : ?>
						<a href="#todo" class="issue-post-row-action-preview">
							<?php esc_html_e( _x( 'Preview', 'Edit Issue: post actions', $domain ) ); ?>
						</a>
					<?php endif; ?>

					<!-- Edit link -->
					<?php if ( current_user_can( 'edit_post', $post->ID ) ) : ?>
						<span class="separator">|</span>
						<a href="<?php echo get_edit_post_link( $post->ID ); ?>" class="issue-post-row-action-edit">
							<?php esc_html_e( _x( 'Edit', 'Edit Issue: post actions', $domain ) ); ?>
						</a>
					<?php endif; ?>
				</span>
			</li>
		<?php endforeach; ?>
	</ul><!-- /pp-posts-list -->
<?php else : ?>
	<p>
		<?php echo _x( 'No posts in this issue.', 'Edit Issue', $domain ); ?>
		<a href="<?php echo esc_url( admin_url( 'post-new.php' ) ) ?>" class="add-new-post">
			<?php echo _x( 'Add New', 'Edit Issue: Add New Post', $domain ); ?>
		</a>
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
$status = $pp_common->get_issue_meta( $issue->term_id, 'pp_issue_status' );
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
	echo _x( 'You cannot publish an issue until it has at least one post in it marked "Pending" (which means submitted for publication).', 'Edit Issue: Posts meta box', $domain );
}
?>
</div><!-- /pp-posts-wrap -->

<?php
