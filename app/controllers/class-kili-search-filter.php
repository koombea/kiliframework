<?php
/**
 * Handle the site's search filter
 *
 */
class Kili_Search_Filter {
	/**
	 * Class constructor
	 */
	public function __construct() {
		add_filter( 'pre_get_posts', array( $this, 'search_filter' ) );
		if ( current_theme_supports( 'kili-enable-acf-search' ) ) {
			$this->add_cf_search_filters();
		}
		if ( current_theme_supports( 'kili-nice-search' ) ) {
			$this->add_nice_search_filters();
		}
	}

	/**
	 * Filters the search query by its post type
	 *
	 * @param object $query The search query object
	 * @return object Search query object with the post type filter
	 */
	public function search_filter( $query ) {
		if ( $query->is_search && ! is_admin() ) {
			$post_type = isset( $_GET['post_type'] ) ? sanitize_key( wp_unslash( $_GET['post_type'] ) ) : ( isset( $_POST['post_type'] ) ? sanitize_key( wp_unslash( $_POST['post_type'] ) ) : 'post');
			$query->set( 'post_type', array( $post_type ) );
		}
		return $query;
	}

	/**
	 * Add search filters for custom fields
	 *
	 * @author adambalee (http://adambalee.com)
	 * @return void
	 */
	private function add_cf_search_filters() {
		add_filter( 'posts_join', array( $this, 'cf_search_join' ) );
		add_filter( 'posts_where', array( $this, 'cf_search_where' ) );
		add_filter( 'posts_distinct', array( $this, 'cf_search_distinct' ) );
	}

	/**
	 * Join posts and postmeta tables
	 * http://codex.wordpress.org/Plugin_API/Filter_Reference/posts_join
	 *
	 * @param object $join The search query string
	 * @return string Modified search query string
	 */
	public function cf_search_join( $join ) {
		global $wpdb;
		if ( is_search() ) {
			$join .= ' LEFT JOIN '.$wpdb->postmeta. ' ON '. $wpdb->posts . '.ID = ' . $wpdb->postmeta . '.post_id ';
		}
		return $join;
	}

	/**
	 * Modify the search query with posts_where
	 * http://codex.wordpress.org/Plugin_API/Filter_Reference/posts_where
	 *
	 * @param object $where The search query string
	 * @return string Modified search query string
	 */
	public function cf_search_where( $where ) {
		global $pagenow, $wpdb;
		if ( is_search() ) {
			$where = preg_replace(
				"/\(\s*" . $wpdb->posts . ".post_title\s+LIKE\s*(\'[^\']+\')\s*\)/",
				"(" . $wpdb->posts . ".post_title LIKE $1) OR (" . $wpdb->postmeta . ".meta_value LIKE $1)", $where );
		}
		return $where;
	}

	/**
	 * Prevent duplicates
	 * http://codex.wordpress.org/Plugin_API/Filter_Reference/posts_distinct
	 *
	 * @param object $where The search query string
	 * @return string Modified search query string
	 */
	public function cf_search_distinct( $where ) {
		global $wpdb;
		if ( is_search() ) {
			return 'DISTINCT';
		}
		return $where;
	}

	/**
	 * Add filters for nice search
	 *
	 * @return void
	 */
	private function add_nice_search_filters() {
		add_action( 'template_redirect', array( $this, 'ksource_search_redirect' ) );
		add_filter( 'wpseo_json_ld_search_url', array( $this, 'ksource_search_rewrite' ) );
		add_action( 'init', array( $this, 'search_base_slug' ) );
	}

	/**
	 * Redirect search results
	 *
	 * @return void
	 */
	public function ksource_search_redirect() {
		global $wp_rewrite;
		if ( ! isset( $wp_rewrite ) || ! is_object( $wp_rewrite ) || ! $wp_rewrite->get_search_permastruct() ) {
			return;
		}
		if ( is_search() && ! is_admin() ) {
			if ( isset( $_SERVER['REQUEST_URI'] ) ) {
				if ( strpos( wp_unslash( $_SERVER['REQUEST_URI'] ), "/{$wp_rewrite->search_base}/" ) === false && strpos( wp_unslash( $_SERVER['REQUEST_URI'] ), '&' ) === false ) {
					wp_redirect( get_search_link() );
					exit();
				}
			}
		}
	}

	/**
	 * Rewrite search url
	 *
	 * @param string $url The URL string
	 * @return string The new URL string
	 */
	public function ksource_search_rewrite( $url ) {
		return str_replace( '/?s=', '/' . __( 'search' ) . '/', $url );
	}

	/**
	 * Change search slug
	 *
	 * @return void
	 */
	public function search_base_slug() {
		$search_slug = __( 'search' ); // change slug name
		$GLOBALS['wp_rewrite']->search_base = $search_slug;
		$GLOBALS['wp_rewrite']->flush_rules();
	}
}
