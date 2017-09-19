<?php
/**
 * Handle WordPress Routes
 *
 */
class Kili_Router {
	/**
	 * Get current Twig view based on WordPress page hierarchy.
	 * Aditional add to context required views data.
	 *
	 * @param array $presets Preset options for the context
	 * @return array View settings
	 */
	public function get_current_view_settings( $presets = null ) {
		global $wp_query, $paged, $post;
		$settings = array();
		$settings['post_type'] = get_post_type();

		if ( is_front_page() ) {
			$settings['template'] = is_home() && locate_template( 'views/home.twig' ) ? 'home' : 'front-page';
		} elseif ( is_404() ) {
			$settings['template'] = locate_template( 'views/error/404.twig' ) ? 'error/404' : ( locate_template( 'views/404.twig' ) ? '404' : 'index' );
		} elseif ( is_search() ) {
			$post_type = isset( $_GET['post_type'] ) ? wp_unslash( $_GET['post_type'] ) : get_post_type();
			$settings['posts'] = Timber::get_posts();
			$settings['pagination'] = Timber::get_pagination();
			$settings['current_search'] = get_search_query();
			$settings['template'] = locate_template( 'views/search-' . $post_type . '.twig' ) ? 'search-' . $post_type : 'search';
		} elseif ( is_archive() ) {
			$settings['current_archive'] = get_the_date( is_day() ? 'F j, Y' : ( is_month() ? 'F Y' : 'Y' ) );
			if ( is_category() ) {
				$category = get_category( get_query_var( 'cat' ) );
				$settings['current_category'] = single_cat_title( '' , false );
				if ( locate_template( 'views/category' . $category->slug . '.twig' ) ) {
					$settings['template'] = 'category-' . $category->slug;
				} else {
					$settings['template'] = locate_template( 'views/category.twig' ) ? 'category' : 'archive';
				}
			} elseif( is_tag() ) {
				$tag = get_queried_object();
				$settings['current_tag'] = single_tag_title( '', false );
				if ( locate_template( 'views/tag' . $tag->slug . '.twig' ) ) {
					$settings['template'] = 'tag-' . $tag->slug;
				} else {
					$settings['template'] = locate_template( 'views/tag.twig' ) ? 'tag' : 'archive';
				}
			} elseif( is_date() ) {
				$settings['template'] = locate_template( 'views/date.twig' ) ? 'date' : 'archive';
			} elseif ( is_author() ) {
				$settings['author'] = new TimberUser( $wp_query->query_vars['author'] );
				$settings['author_extra'] = get_user_by( 'id', $wp_query->query_vars['author'] );
				$settings['author_posts_number'] = get_the_author_posts();
				$author_roles = get_user_by( 'slug', get_query_var( 'author_name' ) )->roles ;
				if ( in_array( 'subscriber', $author_roles ) ) {
					$settings['template'] = 'error/404';
				} else {
					$settings['template'] = locate_template( 'views/author.twig' ) ? 'author' : 'archive';
				}
			} elseif ( is_tax() ) {
				$queried_object = get_queried_object();
				$term_id = $queried_object->term_id;
				$taxonomy = $queried_object->taxonomy;
				$term = get_term( $term_id, $taxonomy );
				if ( locate_template( 'views/taxonomy-' . $taxonomy . '-' . $term->slug . '.twig' ) ) {
					$settings['template'] ='taxonomy-' . $taxonomy . '-' . $term->slug;
				} elseif ( locate_template( 'views/taxonomy-' . $taxonomy . '.twig' ) ) {
					$settings['template'] = 'taxonomy-' . $taxonomy;
				} elseif ( locate_template( 'views/taxonomy.twig' ) ) {
					$settings['template'] = 'taxonomy';
				} else {
					$settings['template'] = 'archive';
				}
			} else {
				$settings['template'] = $this->is_custom_post_type() && locate_template( 'views/archive-' . get_post_type() . '.twig' ) ? 'archive-' . get_post_type() : 'archive';
			}
		}
		elseif ( is_singular() ) {
			$settings['post'] = new TimberPost();
			if ( is_single() || is_page() ) {
				$settings['is_custom_post_type'] = $this->is_custom_post_type();
				if ( $settings['is_custom_post_type'] && locate_template( 'views/single-' . get_post_type() . '.twig' ) ) {
					$settings['template'] = (is_single() ? 'single-' : 'page-' ) . get_post_type();
				} else {
					$settings['template'] = is_page() ? ( post_password_required( $post->ID ) ? 'page-password' : 'page') : (post_password_required( $post->ID ) ? 'single-password' : 'single' );
				}
			} else {
				if ( is_attachment() ) {
					$settings['template'] = locate_template( 'views/attachment.twig' ) ? 'attachment' : 'single';
				} else {
					$settings['template'] = locate_template( 'views/singular.twig' ) ? 'singular' : 'single';
				}
			}
		}
		if ( is_home() || is_category() || is_tag() || is_author() ) {
			if ( ! isset( $paged ) || ! $paged ) {
				$paged = 1;
			}
			$args = array(
				'post_type' => 'post',
				'post_status' => 'publish',
				'posts_per_page' => get_option( 'posts_per_page' ),
				'paged' => $paged,
			);
			if ( is_tag() ) {
				$args['tag'] = sanitize_title( single_tag_title( '', false ) );
			}
			if ( is_category() ) {
				$args['category_name'] = sanitize_title( single_cat_title( '' , false ) );
			}
			if ( is_author() ) {
				$args['author_name'] = $author->nickname;
			}

			$settings['posts'] = Timber::get_posts( $args );
			$settings['pagination'] = Timber::get_pagination();
			$protected = array();
			$posts_length = count( $settings['posts'] );
			for ( $i = 0; $i < $posts_length; $i++ ) {
				$protected[ $i ] = post_password_required( $settings['posts'][ $i ]->id );
			}
			$settings['protected'] = $protected;
			$current_pagination = $settings['pagination']['current'];
			$current_offset = $posts_length * $current_pagination;
			$total_posts = $posts_length * $settings['pagination']['total'];
			$pluralize = sprintf( _n( '%s Post', '%s Posts', $total_posts, 'kiliframework' ), number_format_i18n( $total_posts ) );
			$settings['posts_length'] = $posts_length;
			$settings['current_pagination'] = $current_pagination;
			$settings['current_offset'] = $current_offset;
			$settings['total_posts'] = $total_posts;
			$settings['pluralize'] = $pluralize;
			$settings['show_return_link'] = is_front_page() ? ( is_home() ? false : true ) : true;
		}
		if ( $presets ) {
			$context_settings = array_merge( $settings, $presets );
			return $context_settings;
		}
		return $settings;
	}

	/**
	 * Verify if post is custom post type
	 *
	 * @param [type] $post Post object. default: global post from WordPress
	 * @return boolean
	 */
	private function is_custom_post_type( $post = null ) {
		$all_custom_post_types = get_post_types( array( '_builtin' => false ) );
		// there are no custom post types
		if ( empty( $all_custom_post_types ) ) return false;
		$custom_types      = array_keys( $all_custom_post_types );
		$current_post_type = get_post_type( $post );
		// could not detect current type
		if ( ! $current_post_type ) return false;
		return in_array( $current_post_type, $custom_types );
	}
}
