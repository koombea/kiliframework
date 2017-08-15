<?php
/**
 * Base Kili Configurations
 * =============================================================================
 *
 * Sets the default kiliframework configurations such as folder locations, variables
 * and other configurations used by Kili.
 *
 */
include_once ('defaults/class-theme-data.php');

//................................................................
// Set variables
//................................................................
$theme_data = new Theme_Data();
// Child theme data
$theme_info = $theme_data->kili_get_theme_data();
$child_theme_version = $theme_info['version'];
$child_theme_title = trim( $theme_info['title'] );
$child_theme_shortname = apply_filters( 'shortname', sanitize_title( $child_theme_title . '_' ) );
$child_theme_url     = apply_filters( 'themeUrl', trailingslashit( get_stylesheet_directory_uri() ) );
$child_theme_dir     = apply_filters( 'themeDir', trailingslashit( get_stylesheet_directory() ) );

// Parent theme data
$parent_theme_info = $theme_data->kili_get_theme_data('parent');
$parent_theme_version = $parent_theme_info['version'];
$parent_theme_title = trim( $parent_theme_info['title'] );
$parent_theme_description = trim( $parent_theme_info['description'] );
$parent_theme_author = $parent_theme_info['author'];
$parent_theme_url    = apply_filters( 'frameworkUrl', trailingslashit( get_template_directory_uri() ) );
$parent_theme_dir    = apply_filters( 'frameworkDir', trailingslashit( get_template_directory() ) );

//................................................................
// set as constants
//................................................................

define( 'THEME_URL', $child_theme_url );           // URL of theme folder (includes child themes)
define( 'THEME_DIR', $child_theme_dir );           // Server path to theme folder (includes child themes)
define( 'THEME_NAME', $child_theme_title ); // Theme title
define( 'THEME_SHORT_NAME', $child_theme_shortname ); // Theme short name
define( 'THEME_VERSION', $child_theme_version ); // Theme version number

define( 'FRAMEWORK_URL', $parent_theme_url );   // URL of framework folder
define( 'FRAMEWORK_DIR', $parent_theme_dir );   // Server path to framework folder
define( 'FRAMEWORK_NAME', $parent_theme_title ); // Framework title
define( 'FRAMEWORK_VERSION', $parent_theme_version ); // Framework version number
define( 'FRAMEWORK_DESCRIPTION', $parent_theme_description ); // Framework description
define( 'FRAMEWORK_AUTHOR', $parent_theme_author ); // Framework Author name

define( 'MIN_PHP_VERSION', '5.6' );    // Min PHP version

//-----------------------------------------------------------------
// Additional framework specific options
//-----------------------------------------------------------------

// $expStyleSheetDir = explode( '/', get_stylesheet_directory() );
// $theme_name = array_pop( $expStyleSheetDir );