<?php
/**
 * Array of templates and context for single view.
 *
 * @package kiliframework
 */

$object = get_queried_object();
$category_object = get_the_category($object->ID);
if ( isset( $category_object ) && count( $category_object ) > 0 ) {
	$category = $category_object[0]->slug;
	$templates[] = "{$type}-{$category}.twig";
}
$templates[] = $this->get_protected_view( $object, $type );
$templates[] = "{$type}.twig";
