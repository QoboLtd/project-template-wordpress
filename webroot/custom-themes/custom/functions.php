<?php
/**
 * Theme functions
 *
 * This file provides the place for all the necessary functions of the
 * theme.
 *
 * Instead of growing one huge file, which is hard to maintain and
 * reuse, this file is broken down into bits and pieces, which are
 * loaded automatically.  This way, classes, shortcodes, widgets and
 * other snippets can be easily copied from theme to them.
 *
 * @package Custom
 */

// Define theme's images URI.
if ( ! defined( 'IMAGES_URI' ) ) {
	define( 'IMAGES_URI', get_stylesheet_directory_uri() . '/images/' );
}

// Define theme's functions directory.
if ( ! defined( 'THEME_LIB_DIR' ) ) {
	define( 'THEME_LIB_DIR', __DIR__ . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR );
}

/**
 * Load all functions which are placed in theme's folder
 *
 * @param string $dir Directory to load files from.
 * @return void
 */
function load_includes( string $dir ) : void {
	$it = new RecursiveDirectoryIterator( $dir );
	$it = new RecursiveIteratorIterator( $it );
	$it = new RegexIterator( $it, '#.php$#' );
	foreach ( $it as $include ) {
		if ( $include->isReadable() ) {
			require_once( $include->getPathname() );
		}
	}
}

load_includes( THEME_LIB_DIR );
