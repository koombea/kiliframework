<?php
/**
 * Array of templates and context for category view.
 *
 * @package kiliframework
 */

$slug = get_query_var( 'pagename' );
$mime_type = explode( '/', get_post_mime_type() );
$this->context['post'] = new TimberPost();
if ( $mime_type[1] ) {
	$templates[] = $mime_type[0] . '-' . $mime_type[1] . '.twig';
	$templates[] = $mime_type[1] . '.twig';
}
$templates[] = $mime_type[0] . '.twig';
$templates[] = "{$type}.twig";
$templates[] = 'single-' . $type . '-' . $slug . '.twig';
