<?php
/**
 * Array of templates and context for taxonomy views.
 *
 * @package kiliframework
 */

// Include common actions.
include_once( 'inc/context-query.php' );
if ( $term ) {
	$taxonomy    = $term->taxonomy;
	$templates[] = "taxonomy-{$taxonomy}-{$term->slug}.twig";
	$templates[] = "taxonomy-{$taxonomy}.twig";
}
$templates[] = 'taxonomy.twig';
