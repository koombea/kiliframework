<?php
$object = get_queried_object();
$templates[] = $this->get_protected_view( $object, $type );
$templates[] = 'singular.twig';
