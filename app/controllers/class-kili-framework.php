<?php
/**
 * Define the main class and its actions
 *
 * @package kiliframework
 */

/**
 * Default constants for the parent and child theme
 */
require_once( get_template_directory() . '/config/defaults.php' );

/**
 * Load the router class
 */
require_once( get_template_directory() . '/app/controllers/class-kili-router.php' );

/**
 * Autoload Helpers
 */
foreach ( glob( get_template_directory() . '/app/helpers/*/*.php' ) as $module ) {
	if ( file_exists( $module ) ) {
		require_once( $module );
	}
}
unset( $module, $filepath );

/**
 * Theme blocks config
 */
require_once( 'class-kili-theme-blocks.php' );

/**
 * Theme search filters
 */
require_once( 'class-kili-search-filter.php' );

/**
 * SVG support
 */
require_once( 'class-kili-svg-support.php' );

/**
 * Dynamic styles
 */
require_once( 'class-kili-dynamic-styles.php' );

if ( ! class_exists( 'Kili_Framework' ) ) {
	/**
	 * The framework's main class
	 */
	class Kili_Framework {
		/**
		 * Handler for class Kili_Theme_Blocks
		 *
		 * @var object
		 */
		protected $default_kili_blocks;

		/**
		 * Base blocks css
		 *
		 * @var string
		 */
		protected $base_blocks_style;

		/**
		 * Handler for class Kili_Search_Filter
		 *
		 * @var object
		 */
		protected $search_filters;

		/**
		 * Handler for class Kili_Svg_Support
		 *
		 * @var object
		 */
		protected $svg_support;

		/**
		 * Handler for class Kili_Dynamic_Styles
		 *
		 * @var object
		 */
		protected $dynamic_styles;

		/**
		 * Handler for class Flexible_Content_Modal
		 *
		 * @var object
		 */
		protected $flexible_content_modal;

		/**
		 * Handler for class Kili_Router
		 *
		 * @var object
		 */
		public $kili_router;

		/**
		 * Class constructor
		 */
		public function __construct() {
			$this->kili_router = new Kili_Router();
			$this->default_kili_blocks = new Kili_Theme_Blocks();
			$this->search_filters = new Kili_Search_Filter();
			$this->svg_support = new Kili_Svg_Support();
			$this->dynamic_styles = new Kili_Dynamic_Styles();
			$this->flexible_content_modal = new Flexible_Content_Modal();
			$this->base_blocks_style = '';

			$this->add_actions();
			if ( class_exists( 'Timber' ) ) {
				add_filter( 'timber_context', array( $this, 'add_to_context' ) );
				Timber::$dirname = array( 'blocks/styles', 'views', 'views/partials', 'views/layout' );
			} else {
				add_action( 'admin_notices', function() {
					echo '<div class="error"><p>Timber not activated. Make sure you activate the plugin in <a href="' . esc_url( admin_url( 'plugins.php#timber' ) ) . '">' . esc_url( admin_url( 'plugins.php' ) ) . '</a></p></div>';
				} );
			}
			$this->init_default_block_builder();
			$this->flexible_content_modal->init();
		}

		/**
		 * Add actions to WordPress
		 *
		 * @return void
		 */
		protected function add_actions() {
			add_action( 'wp_enqueue_script', array( $this->default_kili_blocks, 'enqueueAdmin' ) );
			add_action( 'page_blocks', array( $this, 'page_blocks_content' ) );
			add_action( 'after_setup_theme', array( $this, 'load_text_domain' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'include_parent_assets' ) );
		}

		/**
		 * Add the default layout builder template to the admin
		 *
		 * @return void
		 */
		private function init_default_block_builder() {
			$default_block = array(
				'page_template' => array( 'page-templates/layout-builder.php' ),
				'layout_title' => __( 'Layout Builder','kiliframework' ),
				'flexible_content_id' => 'kili_block_builder',
				'flexible_content_group' => 'kili_group_container',
				'flexible_content_key' => 'kili_field_container',
				'flexible_content_button_label' => 'Add New Section',
				'blocks_pages_dir' => get_stylesheet_directory() . '/data/blocks/pages/',
				'excluded_page_blocks' => array(),
			);
			$this->default_kili_blocks->add_blocks_to_wp( $default_block );
		}

		/**
		 * Add blocks to admin layout builder
		 *
		 * @param array $block_options Array of block options.
		 * @return void
		 */
		public function kili_pages_blocks_init_admin( $block_options = array() ) {
			foreach ( $block_options as $key ) {
				$this->default_kili_blocks->add_blocks_to_wp( $block_options[ $key ] );
			}
		}

		/**
		 * Render theme pages from blocks
		 *
		 * @param array $presets Blocks settings.
		 * @return void
		 */
		public function render_pages( $presets ) {
			$context = Timber::get_context();
			$settings = $this->kili_router->get_current_view_settings( $presets );
			if ( $settings ) {
				$template = $settings['template'];
				$fields = array();
				$args = array(
					'sort_order' => 'asc',
					'sort_column' => 'ID',
					'hierarchical' => 1,
					'exclude' => '',
					'include' => '',
					'meta_key' => 'kili_block_builder',
					'meta_value' => '',
					'authors' => '',
					'child_of' => 0,
					'parent' => -1,
					'exclude_tree' => '',
					'number' => '',
					'offset' => 0,
					'post_type' => get_post_types(),
					'post_status' => 'publish',
					'numberposts' => -1,
				);

				foreach ( $settings as $key => $value ) {
					$context[ $key ] = $value;
				}
				$pages_query = new WP_Query( $args );
				$all_pages = $pages_query->get_posts( $args );
				foreach ( $all_pages as $key => $page ) {
					$page_fields = get_fields( $page->ID );
					$page_fields['page_id'] = $page->ID;
					array_push( $fields, $page_fields );
				}
				$this->dynamic_styles->set_base_styles( $this->base_blocks_style );
				$this->dynamic_styles->process_blocks_styles( $fields );
				Timber::render( $template . '.twig', $context );
			}
		}

		/**
		 * Render page blocks
		 *
		 * @param array $context Timber pages context.
		 * @return void
		 */
		function page_blocks_content( $context ) {
			$block_position = 0;
			while ( have_rows( 'kili_block_builder' ) ) : the_row();
				KILI_Layout::render( get_row_layout(), $block_position, $context, 'kili_block_builder' );
				$block_position++;
			endwhile;
		}

		/**
		 * Add data to timber context
		 *
		 * @param array $context Timber pages context.
		 * @return array Context variable updated
		 */
		public function add_to_context( $context ) {
			// Add extra data.
			$context['options'] = function_exists( 'get_fields' ) ? get_fields( 'option' ) : '';
			// Menu.
			$context['menu']['primary'] = new TimberMenu( 'primary_navigation' );
			// Site info.
			$context['site'] = $context['site'];
			// Assets path.
			$context['dist']['images'] = $context['theme']->link . '/dist/images/';
			$context['dist']['css'] = $context['theme']->link . '/dist/styles/';
			$context['dist']['js'] = $context['theme']->link . '/dist/scripts/';
			$context['sidebar_primary'] = Timber::get_widgets( 'sidebar-1' );

			add_action( 'custom_asset', array( $this, 'custom_asset_args' ), 10, 2 );
			if ( function_exists( 'icl_get_languages' ) ) {
				$languages = icl_get_languages( 'skip_missing=0&orderby=code' );
				if ( ! empty( $languages ) ) {
					$context['languages'] = $languages;
				}
			}
			return $context;
		}

		/**
		 * Set the custom assets
		 *
		 * @param string $base_folder Base folder.
		 * @return void
		 */
		public function custom_asset_args( $base_folder ) {
			$folder = get_template_directory_uri() . '/dist/styles/' . $base_folder;
			echo $folder;
		}

		/**
		 * Load the theme text domain
		 *
		 * @return void
		 */
		public function load_text_domain() {
			load_theme_textdomain( 'kiliframework', get_template_directory() . '/languages' );
		}

		/**
		 * Include parent theme assets into the child theme
		 *
		 * @return void
		 */
		public function include_parent_assets() {
			wp_enqueue_style( 'parent-theme-style', FRAMEWORK_URL . 'style.css', array(), false, null );
		}
	}
}
