<?php
/**
 * Array of templates and context for category view.
 *
 * @package kiliframework
 */

// Include common actions.
include_once( 'inc/context-query.php' );
if ( $term ) {
	$templates[] = "{$type}-{$term->slug}.twig";
	$templates[] = "{$type}-{$term->term_id}.twig";
}
$templates[] = "{$type}.twig";
