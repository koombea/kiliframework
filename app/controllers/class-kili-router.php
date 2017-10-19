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
	 * Timber pages context.
	 *
	 * @var array
	 */
	private $context;

	/**
	 * Validate if the page template has been rendered
	 *
	 * @var boolean
	 */
	private $do_render = true;

	public function get_context() {
		return $this->context;
	}

	/**
	 * Get current Twig view based on WordPress page hierarchy.
	 * Aditional add to context required views data.
	 *
	 * @param array $presets Preset options for the context.
	 * @return array View settings
	 */
	public function set_current_view() {
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
			$globals = Theme_Data::get_wordpress_globals();
			switch ( $type ) {
				case '404':
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
					$this->context = $kili_context->set_context( $this->context );
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
	 * @param [type] $template_names
	 * @param boolean $load
	 * @param boolean $require_once
	 * @return void
	 */
	private function locate_template( $template_names, $load = false, $require_once = true ) {
		$located = '';
		foreach ( (array) $template_names as $template_name ) {
			if ( !$template_name )
				continue;
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

		if ( $load && '' !== $located ) {
			load_template( $located, $require_once );
		}

		return $located;
	}

	public function get_protected_view( $object, $type ) {
		$view = "";
		$page_id  = get_queried_object_id();
		$pagename = get_query_var( 'pagename' );
		$is_user_login = is_user_logged_in();
		$is_preview = get_query_var( 'preview' );
		$this->context['post'] = new TimberPost();
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
				if ( $current_user->ID == $object->post_author ) {
					if ( $is_preview && $is_user_login ) {
						$view = "{$type}.twig";
					} elseif ( $object ) {
						$view = "{$type}-{$object->post_type}.twig";
					}
				}
			} elseif ( ! $pagename && $page_id ) {
				$pagename = $object->post_name;
			} elseif ( $pagename ) {
				$view = "{$type}-{$pagename}.twig";
			} elseif ( $page_id ) {
				$view = "{$type}-{$page_id}.twig";
			} elseif ( $object ) {
				$view = "{$type}-{$object->post_type}.twig";
			}
		}
		return $view;
	}
}
