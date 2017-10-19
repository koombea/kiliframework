<?php
/**
 * Array of templates and context for page view.
 *
 * @package kiliframework
 */

$object = get_queried_object();
$templates[] = $this->get_protected_view( $object, $type );
$templates[] = "{$type}.twig";
