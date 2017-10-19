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

$this->context['posts'] = Timber::get_posts( $args );
$this->context['pagination'] = Timber::get_pagination();

$posts_length = count( $this->context['posts'] );
$current_pagination = $this->context['pagination']['current'];
$current_offset = $posts_length * $current_pagination;
$total_posts = $posts_length * $this->context['pagination']['total'];
// translators: placeholders are for post quantity.
$pluralize = sprintf( _n( '%s Post', '%s Posts', $total_posts, 'kiliframework' ), number_format_i18n( $total_posts ) );
// Taxonomies Context.
$this->context['current_tag'] = single_tag_title( '', false );
$this->context['current_category'] = single_cat_title( '' , false );
$this->context['posts_length'] = $posts_length;
$this->context['current_pagination'] = $current_pagination;
$this->context['current_offset'] = $current_offset;
$this->context['total_posts'] = $total_posts;
$this->context['pluralize'] = $pluralize;
// Author Context.
$this->context['author'] = new TimberUser( $globals['wp_query']->query_vars['author'] );
$this->context['author_extra'] = get_user_by( 'id', $globals['wp_query']->query_vars['author'] );
$this->context['author_posts_number'] = get_the_author_posts();
