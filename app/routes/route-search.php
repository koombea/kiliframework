<?php
/**
 * Array of templates and context for front page view.
 *
 * @package kiliframework
 */

// Include common actions.
include_once( 'inc/context-query.php' );
// Search Context.
$this->context['current_search'] = get_search_query();
$post_types = array_filter( (array) get_query_var( 'post_type' ) );
if ( count( $post_types ) === 1 ) {
	$post_type   = reset( $post_types );
	$templates[] = "{$type}-{$post_type}.twig";
}
$templates[] = "{$type}.twig";
