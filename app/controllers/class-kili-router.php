<?php
/**
 * Handle WordPress Routes
 * Augment native template hierarchy with non-PHP template processing.
 *
 * @package kiliframework
 */

/**
 * Class for handling routes
 */
class Kili_Router {
	/**
	 * Template type names to be used for dynamic hooks.
	 *
	 * @var array
	 */
	private $template_types = array(
		'404',
		'search',
		'taxonomy',
		'frontpage',
		'home',
		'attachment',
		'single',
		'page',
		'singular',
		'category',
		'tag',
		'author',
		'date',
		'archive',
		'commentspopup',
		'paged',
		'index',
	);

	/**
	 * Validate if the page template has been rendered
	 *
	 * @var boolean
	 */
	private $do_render = true;

	/**
	 * Get current Twig view based on WordPress page hierarchy.
	 * Aditional add to context required views data.
	 *
	 * @return void
	 */
	public function set_current_view( $context ) {
		$this->context = $context;
		foreach ( $this->template_types as $type ) {
			add_filter( "{$type}_template", array( $this, 'query_template' ) );
		}
	}

	/**
	 * Filter for current page template
	 * and includes the correct view twig file, if exists
	 *
	 * @param string $fallback Provide file fallback if the .twig file is missing.
	 * @return void
	 */
	public function query_template( $fallback ) {
		if ( $this->do_render ) {
			// trim '_template' from end.
			$type      = substr( current_filter(), 0, - 9 );
			$templates = array();
			switch ( $type ) {
				case '404':
				case 'search':
				case 'attachment':
				case 'taxonomy':
				case 'frontpage':
				case 'home':
				case 'single':
				case 'page':
				case 'singular':
				case 'category':
				case 'tag':
				case 'author':
				case 'archive':
					include_once( FRAMEWORK_DIR . 'app/routes/route-' . $type . '.php' );
					break;
				default:
					$templates = array( "{$type}.twig" );
			}
			$template = $this->locate_template( $templates );
			if ( empty( $template ) ) {
				$template = $fallback;
			}
			if ( strcasecmp( $template, '' ) !== 0 ) {
				$this->do_render = false;
				if ( strpos( $template, 'index.php' ) === false ) {
					$kili_context = new Kili_Context();
					$kili_context->set_context( $this->context );
					$this->context = $kili_context->get_context();
					Timber::render( $template, $this->context );
				} else {
					include_once( $template );
				}
			}
		}
	}

	/**
	 * Used to quickly retrieve the path of a template without including the file
	 * extension. It will also check the parent theme, if the file exists, with
	 * the use of locate_template().
	 *
	 * @param array $template_names array with the required view names.
	 * @return string View path
	 */
	private function locate_template( $template_names ) {
		$located = '';
		foreach ( (array) $template_names as $template_name ) {
			if ( ! $template_name ) {
				continue;
			}
			if ( file_exists( STYLESHEETPATH . '/views/' . $template_name ) ) {
				$located = STYLESHEETPATH . '/views/' . $template_name;
				break;
			} elseif ( file_exists( TEMPLATEPATH . '/views/' . $template_name ) ) {
				$located = TEMPLATEPATH . '/views/' . $template_name;
				break;
			} elseif ( file_exists( ABSPATH . WPINC . '/views/theme-compat/' . $template_name ) ) {
				$located = ABSPATH . WPINC . '/views/theme-compat/' . $template_name;
				break;
			}
		}
		return $located;
	}

	/**
	 * Extend support for post status states
	 *
	 * @param object $object get the queried object.
	 * @param string $type view type.
	 * @return string View file name
	 */
	public function get_protected_view( $object, $type ) {
		$view = '';
		$pagename = get_query_var( 'pagename' );
		$is_user_login = is_user_logged_in();
		$is_preview = get_query_var( 'preview' );
		$this->context['post'] = new TimberPost();
		if ( is_page_template( 'page-templates/layout-builder.php' ) ) {
			$this->context['is_kili'] = true;
		}
		if ( $object ) {
			if ( strcasecmp( $object->post_status, 'private' ) === 0 || strcasecmp( $object->post_status, 'draft' ) === 0 || strcasecmp( $object->post_status, 'future' ) === 0 ) {
				$view = '404.twig';
				if ( $is_preview && $is_user_login ) {
					$view = "{$type}.twig";
				} elseif ( $object ) {
					$view = "{$type}-{$object->post_type}.twig";
				}
			} elseif ( post_password_required( $object->ID ) ) {
				$view = "{$type}-password.twig";
				if ( $is_preview && $is_user_login ) {
					$view = "{$type}.twig";
					if ( $object ) {
						$view = "{$type}-{$object->post_type}.twig";
					}
				}
			} elseif ( strcasecmp( $object->post_status, 'pending' ) === 0 ) {
				$current_user = wp_get_current_user();
				$view = '404.twig';
				if ( $current_user->ID === $object->post_author ) {
					if ( $is_preview && $is_user_login ) {
						$view = "{$type}.twig";
					} elseif ( $object ) {
						$view = "{$type}-{$object->post_type}.twig";
					}
				}
			} elseif ( ! $pagename && $object->ID ) {
				$pagename = $object->post_name;
				$is_custom_post = Kili_Layout::is_custom_post_type( $object );
				if ( $is_custom_post ) {
					$view = "{$type}-{$object->post_type}.twig";
				} elseif ( $pagename ) {
					$view = "{$type}-{$pagename}.twig";
				} elseif ( $object->ID ) {
					$view = "{$type}-{$object->ID}.twig";
				} elseif ( $object ) {
					$view = "{$type}-{$object->post_type}.twig";
				}
			}
		}
		return $view;
	}
}
