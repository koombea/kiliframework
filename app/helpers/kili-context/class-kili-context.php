<?php
/**
 * Add general context for twig
 *
 * @package kiliframework
 */

/**
 * Add data to timber context
 *
 * @param array $context Timber pages context.
 * @return array Context variable updated
 */
class Kili_Context {
	/**
	 * Timber pages context.
	 *
	 * @var array
	 */
	private $context;

	/**
	 * Get context for Twig views.
	 *
	 * @return array Timber context
	 */
	public function get_context() {
		return $this->context;
	}

	/**
	 * Set the default Timber context for Twig views.
	 *
	 * @param array $context Timber context array.
	 * @return void
	 */
	public function set_context( $context = null ) {
		$this->context = $context ? array_merge( Timber::get_context(), $context ) : Timber::get_context();
		// Add extra data.
		$this->context['options'] = function_exists( 'get_fields' ) ? get_fields( 'option' ) : '';
		// Menu.
		$this->context['menu']['primary'] = new TimberMenu( 'primary_navigation' );
		// Sidebar.
		$this->context['sidebar_primary'] = Timber::get_widgets( 'sidebar-1' );
		// Site info.
		$this->context['site'] = new TimberSite();
		$this->context['admin_url'] = site_url();
		$this->context['posts_link'] = $this->slug_all_posts_link();
		// Assets path.
		$this->context['dist']['images'] = $this->context['theme']->link . '/dist/images/';
		$this->context['dist']['styles'] = $this->context['theme']->link . '/dist/styles/';
		$this->context['dist']['scripts'] = $this->context['theme']->link . '/dist/scripts/';

		if ( function_exists( 'icl_get_languages' ) ) {
			$languages = icl_get_languages( 'skip_missing=0&orderby=code' );
			if ( ! empty( $languages ) ) {
				$this->context['languages'] = $languages;
			}
		}
	}

	/**
	 * Get the blog page URL set in WordPress Options
	 * And provide a fallback to the posts archive page.
	 *
	 * @return string the blog page URL
	 */
	private function slug_all_posts_link() {
		if ( 'page' === get_option( 'show_on_front' ) ) {
			if ( get_option( 'page_for_posts' ) ) {
				return esc_url( get_permalink( get_option( 'page_for_posts' ) ) );
			}
			return esc_url( home_url( '/?post_type=post' ) );
		}
		return esc_url( home_url( '/' ) );
	}
}
