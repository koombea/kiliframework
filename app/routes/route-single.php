<?php
/**
 * Array of templates and context for single view.
 *
 * @package kiliframework
 */

$object = get_queried_object();
$category = get_the_category($object->ID)[0]->slug;
if ($category) {
	$templates[] = "{$type}-{$category}.twig";
}
$templates[] = $this->get_protected_view( $object, $type );
$templates[] = "{$type}.twig";
