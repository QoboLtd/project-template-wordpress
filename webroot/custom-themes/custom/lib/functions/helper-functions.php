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
function redirect_non_logged_users_to_login_page() {
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
 * @return boolen/WP_Post Object $result.
 */
function get_images_by_gategory_slug( $slug, $no_of_images = 1 ) {

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

add_action( 'init', 'disable_upload_files_for_user_qobo' );

/**
 * Disable upload functionality for the qobo user
 */
function disable_upload_files_for_user_qobo() {

	$accepted_domains_str = getenv( 'QOBO_USER_DOMAINS' );
	if ( empty( $accepted_domains_str ) || false === $accepted_domains_str ) {
		$accepted_domains_str = '::1';
	}
	$accepted_domains = explode( ',', $accepted_domains_str );
	if ( ! in_array( $_SERVER['REMOTE_ADDR'], $accepted_domains ) ) {
		return;
	}

	$user = wp_get_current_user();
	if ( getenv( 'WP_DEV_USER' ) === $user->user_login ) {
		remove_post_type_thumbnail();
		add_action( 'admin_menu', 'remove_menu_links' );
	}
}

/**
 * Remove any links link to upload.php
 */
function remove_menu_links() {
	global $submenu;
	remove_menu_page( 'upload.php' );
}

/**
 * Reomove for post types (post, page) the upload thumbnail functionality
 */
function remove_post_type_thumbnail() {
	remove_post_type_support( 'post', 'thumbnail' );
	remove_post_type_support( 'page', 'thumbnail' );
}
