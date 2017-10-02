<?php
/**
 * Add data to timber context
 *
 * @param array $context Timber pages context.
 * @return array Context variable updated
 */

class Kili_Context {
	public function set_context( $context = null ) {
		$context = $context ? array_merge( Timber::get_context(), $context ) : Timber::get_context();
		// Add extra data.
		$context['options'] = function_exists( 'get_fields' ) ? get_fields( 'option' ) : '';
		// Menu.
		$context['menu']['primary'] = new TimberMenu( 'primary_navigation' );
		// Sidebar.
		$context['sidebar_primary'] = Timber::get_widgets('sidebar-1');
		// Site info.
		$context['site'] = new TimberSite();
		$context['admin_url'] = site_url();
		$context['posts_link'] = $this->slug_all_posts_link();
		// Assets path.
		$context['dist']['images'] = $context['theme']->link . '/dist/images/';
		$context['dist']['styles'] = $context['theme']->link . '/dist/styles/';
		$context['dist']['scripts'] = $context['theme']->link . '/dist/scripts/';

		if ( function_exists( 'icl_get_languages' ) ) {
			$languages = icl_get_languages( 'skip_missing=0&orderby=code' );
			if ( ! empty( $languages ) ) {
				$context['languages'] = $languages;
			}
		}

		return $context;
	}
	/**
	 * Get the blog page URL set in WordPress Options
	 * And provide a fallback to the posts archive page.
	 *
	 * @return void
	 */
	public function slug_all_posts_link() {
		if ( 'page' == get_option( 'show_on_front' ) ) {
			if ( get_option( 'page_for_posts' ) ) {
				return esc_url( get_permalink( get_option( 'page_for_posts' ) ) );
			} else {
			return esc_url( home_url( '/?post_type=post' ) );
			}
		} else {
			return esc_url( home_url( '/' ) );
		}
	}
}
