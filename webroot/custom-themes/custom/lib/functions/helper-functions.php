<?php
/**
 * Helper functions
 *
 * @package Custom
 */

/**
 * Require login
 *
 * Check the `REQUIRE_LOGIN` environment variable, and if it set to
 * true, then redirect non-authenticated users to the login page.
 *
 * @return void
 */
function redirect_non_logged_users_to_login_page() : void {
	if ( getenv( 'REQUIRE_LOGIN' ) ) {
		global $pagenow;
		if ( ! is_user_logged_in() && 'wp-login.php' !== $pagenow ) {
			wp_safe_redirect( wp_login_url() );
		}
	}
}
add_action( 'wp', 'redirect_non_logged_users_to_login_page' );

/**
 * Gets images by gategory slug when wp-media-library-categories plugin is enabled
 *
 * @param string $slug Slug name of a category image group.
 * @param int    $no_of_images Number of images filter.
 * @return \WP_Post|bool Object $result.
 */
function get_images_by_gategory_slug( string $slug, int $no_of_images = 1 ) {

	$category = get_category_by_slug( $slug );

	if ( ! $category ) {
		return false;
	}

	$category_id = $category->term_id;

	$args = array(
		'post_type'   => 'attachment',
		'numberposts' => $no_of_images,
		'post_status' => null,
		'category'    => $category_id,
	);

	$result = get_posts( $args );

	return $result;
}

add_action( 'init', 'disable_upload_files_for_wp_dev_user' );

/**
 * Disable upload functionality for the qobo user
 */
function disable_upload_files_for_wp_dev_user() : void {

	$dev_env = getenv( 'DEV_ENV' );
	if ( empty( $dev_env ) ) {
		$dev_env = 'localhost';
	}

	if ( $_SERVER['SERVER_NAME'] === $dev_env ) {
		return;
	}

	$user = wp_get_current_user();
	if ( getenv( 'WP_DEV_USER' ) === $user->user_login ) {
		remove_post_type_thumbnail();
		add_action( 'admin_menu', 'remove_menu_links' );
		remove_action( 'media_buttons', 'media_buttons' );
		add_action( 'admin_notices', 'image_upload_notice' );
	}
}

/**
 * Remove any links link to upload.php
 */
function remove_menu_links() : void {
	global $submenu;
	remove_menu_page( 'upload.php' );
}

/**
 * Remove for post types (post, page) the upload thumbnail functionality
 */
function remove_post_type_thumbnail() : void {
	remove_post_type_support( 'post', 'thumbnail' );
	remove_post_type_support( 'page', 'thumbnail' );
}

/**
 * Image upload notice.
 */
function image_upload_notice() : void {
	?>
	<div class="update-nag notice">
		<p><?php esc_html_e( 'Image Upload Functionality is Disabled!', 'qobo_domain' ); ?></p>
	</div>
	<?php
}

/**
 * Disable admin email verification notice.
 */
add_filter( 'admin_email_check_interval', '__return_false' );
