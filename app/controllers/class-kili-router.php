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
	 * @param array $context Page context data.
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
		if ( ! $this->do_render ) {
			return;
		}
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
		$template = empty( $template ) ? $fallback : $template;
		if ( strcasecmp( $template, '' ) !== 0 ) {
			$this->do_render = false;
			if ( strpos( $template, 'index.php' ) === false ) {
				$kili_context = new Kili_Context();
				$kili_context->set_context( $this->context );
				$this->context = $kili_context->get_context();
				Timber::render( $template, $this->context );
				return ;
			}
			include_once( $template );
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
			$located = $this->get_template_filename( $template_name );
			if ( strcasecmp( $located, '' ) !== 0 ) {
				return $located;
			}
		}
		return $located;
	}

	/**
	 * Check if the template exists and return its path
	 *
	 * @param string $template_name The template name.
	 * @return string The path to the template file
	 */
	private function get_template_filename( $template_name ) {
		$filename = '';
		if ( ! $template_name ) {
			$filename = '';
		} elseif ( file_exists( STYLESHEETPATH . '/views/' . $template_name ) ) {
			$filename = STYLESHEETPATH . '/views/' . $template_name;
		} elseif ( file_exists( TEMPLATEPATH . '/views/' . $template_name ) ) {
			$filename = TEMPLATEPATH . '/views/' . $template_name;
		} elseif ( file_exists( ABSPATH . WPINC . '/views/theme-compat/' . $template_name ) ) {
			$filename = ABSPATH . WPINC . '/views/theme-compat/' . $template_name;
		}
		return $filename;
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
		$is_user_logged_in = is_user_logged_in();
		$is_preview = get_query_var( 'preview' );
		$this->context['post'] = new TimberPost();
		$current_user = wp_get_current_user();
		if ( is_page_template( 'page-templates/layout-builder.php' ) ) {
			$this->context['is_kili'] = true;
		}
		if ( strcasecmp( $object->post_status, 'private' ) === 0 || strcasecmp( $object->post_status, 'draft' ) === 0 || strcasecmp( $object->post_status, 'future' ) === 0 || strcasecmp( $object->post_status, 'pending' ) === 0 ) {
			$view = $this->get_protected_post_view( array(
				'default' => '404.twig',
				'is_preview' => $is_preview,
				'is_user_logged_in' => $is_user_logged_in,
				'object' => $object,
				'show' => strcasecmp( '' . $current_user->ID, $object->post_author ) === 0,
				'type' => $type,
			) );
		} elseif ( post_password_required( $object->ID ) ) {
			$view = $this->get_protected_post_view( array(
				'default' => "{$type}-password.twig",
				'is_preview' => $is_preview,
				'is_user_logged_in' => $is_user_logged_in,
				'object' => $object,
				'show' => strcasecmp( '' . $current_user->ID, $object->post_author ) === 0,
				'type' => $type,
			) );
		} elseif ( ! $pagename && $object->ID ) {
			$view = $this->get_page_view_name( array(
				'object' => $object,
				'type' => $type,
			) );
		} elseif ( is_page_template( get_page_template_slug( $object->id ) ) ) {
			$view = str_ireplace( 'php', 'twig', basename( get_page_template_slug( $object->id ) ) );
		}
		return $view;
	}

	/**
	 * Return the view file name for protected pages and posts
	 *
	 * @param array $options View options.
	 * @return string View name
	 */
	private function get_protected_post_view( $options = array() ) {
		$response = $options['default'] ? $options['default'] : '';
		if ( ! $options['show'] ) {
			return $response;
		}
		if ( $options['is_preview'] && $options['is_user_logged_in'] ) {
			$response = "{$options['type']}.twig";
		} elseif ( $options['object'] ) {
			$response = "{$options['type']}-{$options['object']->post_type}.twig";
		}
		return $response;
	}

	/**
	 * Return the view file name
	 *
	 * @param array $options View options.
	 * @return string View file name
	 */
	private function get_page_view_name( $options ) {
		$response = '';
		$object = $options['object'];
		$type = $options['type'];
		$pagename = $object->post_name;
		$is_custom_post = Kili_Layout::is_custom_post_type( $object );
		if ( $is_custom_post ) {
			$response = "{$type}-{$object->post_type}.twig";
		} elseif ( $pagename ) {
			$response = "{$type}-{$pagename}.twig";
		} elseif ( $object->ID ) {
			$response = "{$type}-{$object->ID}.twig";
		} elseif ( $object ) {
			$response = "{$type}-{$object->post_type}.twig";
		}
		return $response;
	}
}
