<?php
/**
 * Fixed an issue with wp-color-picker on plugins after upgrading to wordpress ^5.5.
 *
 * @package Custom
 */

if ( is_admin() ) {
	/**
	 * Register the wp-color-picker.
	 *
	 * @param object $scripts The scripts.
	 */
	function custom_wp_default_scripts( $scripts ) {
		$scripts->add( 'wp-color-picker', '/wp-admin/js/color-picker.js', array( 'iris' ), false, 1 );
		did_action( 'init' ) && $scripts->localize(
			'wp-color-picker',
			'wpColorPickerL10n',
			array(
				'clear'            => __( 'Clear' ),
				'clearAriaLabel'   => __( 'Clear color' ),
				'defaultString'    => __( 'Default' ),
				'defaultAriaLabel' => __( 'Select default color' ),
				'pick'             => __( 'Select Color' ),
				'defaultLabel'     => __( 'Color value' ),
			)
		);
	}
	add_action( 'wp_default_scripts', 'custom_wp_default_scripts' );
}
