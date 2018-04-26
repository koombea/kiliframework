<?php
/**
 * Array of templates and context for author view.
 *
 * @package kiliframework
 */

 // Include common actions.
include_once( 'inc/context-query.php' );
$author_roles = get_user_by( 'id', get_query_var( 'author' ) )->roles ;
if ( in_array( 'subscriber', $author_roles, true ) ) {
	$templates[] = '404.twig';
} else {
	if ( $term ) {
		$templates[] = "author-{$term->user_nicename}.twig";
		$templates[] = "author-{$term->ID}.twig";
	}
	$templates[] = "{$type}.twig";
}
