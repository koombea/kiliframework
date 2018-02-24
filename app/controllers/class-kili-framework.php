<?php
/**
 * Define the main class and its actions
 *
 * @package kiliframework
 */

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
		 * Handler for class Kili_Dynamic_Styles
		 *
		 * @var object
		 */
		public $dynamic_styles;

		/**
		 * Handler for class Flexible_Content_Modal
		 *
		 * @var object
		 */
		protected $flexible_modal;

		/**
		 * Handler for class Kili_Router
		 *
		 * @var object
		 */
		public $kili_router;

		/**
		 * Handler for class Kili_Context
		 *
		 * @var object
		 */
		public $kili_context;

		/**
		 * Handler for class Kili_Layout
		 *
		 * @var object
		 */
		public $kili_layout;

		/**
		 * Handler for class Kili_Asset_Manifest
		 *
		 * @var object
		 */
		public $kili_asset_manifest;

		/**
		 * Class constructor
		 */
		public function __construct() {
			$this->kili_router = new Kili_Router();
			$this->default_kili_blocks = new Kili_Theme_Blocks();
			$this->search_filters = new Kili_Search_Filter();
			$this->dynamic_styles = new Kili_Dynamic_Styles();
			$this->flexible_modal = new Flexible_Content_Modal();
			$this->kili_context = new Kili_Context();
			$this->kili_layout = new Kili_Layout();
			$this->add_actions();
			$this->init_default_block_builder();
			$this->flexible_modal->init();
			if ( class_exists( 'Timber' ) ) {
				Timber::$dirname = array( 'blocks/styles', 'views', 'views/partials', 'views/layout' );
				return;
			}
			add_action( 'admin_notices', function() {
				echo '<div class="error"><p>Timber not activated. Make sure you activate the plugin in <a href="' . esc_url( admin_url( 'plugins.php#timber' ) ) . '">' . esc_url( admin_url( 'plugins.php' ) ) . '</a></p></div>';
			} );
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
			add_action( 'custom_asset', array( $this, 'custom_asset_args' ), 10, 2 );
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
		 * @return void
		 */
		public function render_pages( $context = null ) {
			$fields = array();
			$args = array(
				'hierarchical' => 1,
				'meta_key' => 'kili_block_builder',
				'meta_value' => '',
				'numberposts' => -1,
				'post_status' => 'publish',
				'post_type' => get_post_types(),
				'sort_column' => 'ID',
				'sort_order' => 'asc',
			);
			$pages_query = new WP_Query( $args );
			$all_pages = $pages_query->get_posts( $args );
			$all_pages_length = count( $all_pages );
			for ( $key = 0; $key < $all_pages_length; $key++ ) {
				$page_fields = function_exists( 'get_fields' ) ? get_fields( $all_pages[ $key ]->ID ) : '';
				if ( $page_fields ) {
					$page_fields['page_id'] = $all_pages[ $key ]->ID;
				}
				array_push( $fields, $page_fields );
			}
			$this->dynamic_styles->process_blocks_styles( $fields );
			$this->kili_router->set_current_view( $context );
		}

		/**
		 * Render page blocks
		 *
		 * @param array $context Timber pages context.
		 * @return void
		 */
		public function page_blocks_content( $context ) {
			$block_position = 0;
			while ( have_rows( 'kili_block_builder' ) ) : the_row();
				$this->kili_layout->render( get_row_layout(), $block_position, $context, 'kili_block_builder' );
				$block_position++;
			endwhile;
		}

		/**
		 * Set the custom assets
		 *
		 * @param string $base_folder Base folder.
		 * @return string Folder route
		 */
		public function custom_asset_args( $base_folder ) {
			return get_template_directory_uri() . '/dist/styles/' . $base_folder;
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

		/**
		 * Get the asset path for enqueue style and scripts files.
		 *
		 * @param string $file the asset file path.
		 * @return string asset path
		 */
		public function asset_path( $file ) {
			$dist_path = THEME_URL . 'dist/';
			$directory = dirname( $file ) . '/';
			$file = basename( $file );
			static $manifest;

			if ( empty( $manifest ) ) {
				$manifest_path = THEME_DIR . 'dist/assets.json';
				$manifest = new Kili_Asset_Manifest( $manifest_path );
			}

			if ( array_key_exists( $file, $manifest->get() ) ) {
				return $dist_path . $directory . $manifest->get()[ $file ];
			}
			return $dist_path . $directory . $file;
		}
	}
}
