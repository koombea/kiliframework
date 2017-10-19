<?php
/**
 * Array of templates and context for Twig views.
 *
 * @package kiliframework
 */
 
$object = get_queried_object();
if ( $object ) {
	$templates[] = $this->get_protected_view( $object, $type );
} else {
	include_once( "inc/context-query.php" );
}
$templates = array( 'front-page.twig' );