<?php
/**
 * Base Kili Configurations
 * =============================================================================
 *
 * Sets the default kiliframework configurations such as folder locations, variables
 * and other configurations used by Kili.
 *
 */
//................................................................
// Set variables
//................................................................
$child_theme_url     = apply_filters( 'themeUrl', trailingslashit( get_stylesheet_directory_uri() ) );
$child_theme_dir     = apply_filters( 'themeDir', trailingslashit( get_stylesheet_directory() ) );
$parent_theme_url    = apply_filters( 'frameworkUrl', trailingslashit( get_template_directory_uri() ) );
$parent_theme_dir    = apply_filters( 'frameworkDir', trailingslashit( get_template_directory() ) );

//................................................................
// set as constants
//................................................................

define( 'THEME_URL', $child_theme_url );           // URL of theme folder (includes child themes)
define( 'THEME_DIR', $child_theme_dir );           // Server path to theme folder (includes child themes)
define( 'FRAMEWORK_URL', $parent_theme_url );   // URL of framework folder
define( 'FRAMEWORK_DIR', $parent_theme_dir );   // Server path to framework folder
define( 'MIN_PHP_VERSION', '5.6' );    // Min PHP version

//-----------------------------------------------------------------
// Additional framework specific options
//-----------------------------------------------------------------

// $expStyleSheetDir = explode( '/', get_stylesheet_directory() );
// $theme_name = array_pop( $expStyleSheetDir );