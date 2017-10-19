<?php
/**
 * Array of templates and context for Twig views.
 *
 * @package kiliframework
 */
 
include_once( "inc/context-query.php" );
$author_roles = get_user_by( 'slug', get_query_var( 'author_name' ) )->roles ;
if ( in_array( 'subscriber', $author_roles, true ) ) {
	$templates[] = '404.twig';
} else {
	if ( $term ) {
		$templates[] = "author-{$term->user_nicename}.twig";
		$templates[] = "author-{$term->ID}.twig";
	}
	$templates[] = "{$type}.twig";
}
