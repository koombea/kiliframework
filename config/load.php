<?php
/**
 * Load the main class
 *
 * @package kiliframework
 */

/**
 * Default constants for the parent and child theme
 */
require_once( get_template_directory() . '/config/defaults.php' );

/**
 * Include main class
*/
include_once( FRAMEWORK_DIR . 'app/controllers/class-kili-framework.php' );

/**
 * Initializate Kili Class
*/
$kili_framework = new Kili_Framework();

/**
 * Validate if child is present render twig files
*/
if ( strcasecmp( THEME_NAME, FRAMEWORK_NAME ) === 0 ) {
	$kili_framework->render_pages();
}
