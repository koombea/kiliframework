<?php
$object = get_queried_object();
$templates[] = $this->get_protected_view( $object, $type );
$templates = array( 'front-page.twig' );
