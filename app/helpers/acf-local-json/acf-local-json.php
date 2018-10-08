<?php
/**
 * ACF Local JSON
 *
 * @package kiliframework
 */

if( isset( $_REQUEST['post_type'] ) && $_REQUEST['post_type'] == 'acf-field-group' ) {

	add_filter('acf/settings/save_json', 'kili_acf_json_save_point');
	add_filter('acf/settings/load_json', 'kili_acf_json_load_point');
}

function kili_acf_json_save_point( $path ) {

    // update path
    $path = get_stylesheet_directory() . '/data/blocks/pages';
    // return
    return $path;

}

function kili_acf_json_load_point( $paths ) {

    // remove original path (optional)
    unset($paths[0]);
    // append path
    $paths[] = get_stylesheet_directory() . '/data/blocks/pages';
    // return
    return $paths;

}
