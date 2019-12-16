<?php
/**
 * Array of templates and context for archive view.
 *
 * @package kiliframework
 */

// Include common actions.
include_once( 'inc/context-query.php' );
$post_types = array_filter( (array) get_query_var( 'post_type' ) );
if ( count( $post_types ) === 1 ) {
	$post_type   = reset( $post_types );
	$templates[] = "{$type}-{$post_type}.twig";
}
$templates[] = "{$type}.twig";
