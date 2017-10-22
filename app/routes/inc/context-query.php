<?php
/**
 * Handle query posts and context for author and taxonomies.
 *
 * @package kiliframework
 */

$term = get_queried_object();
$globals = Theme_Data::get_wordpress_globals();
$args = array(
	'post_type' => get_post_type(),
	'post_status' => 'publish',
	'posts_per_page' => get_option( 'posts_per_page' ),
	'paged' => isset( $globals['paged'] ) ? $globals['paged'] : 1,
);
if ( isset( $term->taxonomy ) ) {
	$args['tax_query'] = array(
		array(
			'taxonomy' => $term->taxonomy,
			'field' => 'term_id',
			'terms' => $term->term_id,
		),
	);
}
//Search Context.
if ( $type === 'search' ) {
	$this->context['posts'] = Timber::get_posts();
	$this->context['current_search'] = get_search_query();
} else {
	$this->context['posts'] = Timber::get_posts( $args );
}
// Taxonomies Context.
$this->context['current_tag'] = single_tag_title( '', false );
$this->context['current_category'] = single_cat_title( '' , false );
// Pagination Context.
$this->context['pagination'] = Timber::get_pagination();
$posts_length = $globals['wp_query']->found_posts;
$posts_per_page = $globals['wp_query']->query_vars['posts_per_page'];
$current_pagination = $this->context['pagination']['current'];
$current_offset = ( ( $posts_per_page *  $current_pagination ) - ( $posts_per_page - 1 ) );
$current_offset_right = ( $current_offset + count($this->context['posts']) ) - 1;
$this->context['posts_length'] = $posts_length;
$this->context['current_pagination'] = $current_pagination;
$this->context['current_offset'] = $current_offset;
$this->context['current_offset_right'] = $current_offset_right;
$this->context['total_posts'] = $posts_length;
$this->context['pluralize'] = sprintf( _n( '%s post', '%s posts', $posts_length, 'kiliframework' ), number_format_i18n( $posts_length ) );
// Author Context.
$this->context['author'] = new TimberUser( $globals['wp_query']->query_vars['author'] );
$this->context['author_extra'] = get_user_by( 'id', $globals['wp_query']->query_vars['author'] );
$this->context['author_posts_number'] = get_the_author_posts();
