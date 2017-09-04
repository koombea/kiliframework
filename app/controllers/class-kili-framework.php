<?php
#-----------------------------------------------------------------
# Include required files
#-----------------------------------------------------------------
// Defaults constants for the parent and child theme
include_once ( get_template_directory() . '/config/defaults.php' );

include_once ( get_template_directory() . '/app/controllers/class-kili-router.php' );

//Autoload Helpers.
foreach ( glob(get_template_directory() . '/app/helpers/*/*.php') as $module ) {
  if ( !$modulepath = $module ) {
    trigger_error(sprintf(__('Error locating %s for inclusion', 'kiliframework'), $module), E_USER_ERROR);
  }
  require_once $modulepath;
}
unset($module, $filepath);

// Theme blocks config
include_once ( 'class-kili-theme-blocks.php' );

#-----------------------------------------------------------------
# Define the main class
#-----------------------------------------------------------------
/**
 * Kili Main Class
 */
if (!class_exists('Kili_Framework')) {
  class Kili_Framework {
    protected $default_kili_blocks;
    protected $base_blocks_style;
    public $kili_router;
  
    public function __construct() {
      $this->kili_router = new Kili_Router();
      $this->default_kili_blocks = new Kili_Theme_Blocks();
      $this->base_blocks_style = '';
      $this->add_actions();
      if ( $this->verify_timber_installation() ) {
        add_filter( 'timber_context', array( $this, 'add_to_context' ) );
        Timber::$dirname = array('blocks/styles', 'views', 'views/partials', 'views/layout');
      }
      else {
        add_action( 'admin_notices', function() {
          echo '<div class="error"><p>Timber not activated. Make sure you activate the plugin in <a href="' . esc_url( admin_url( 'plugins.php#timber' ) ) . '">' . esc_url( admin_url( 'plugins.php' ) ) . '</a></p></div>';
        } );
      }
    }
  
    /**
     * Add actions to WordPress
     *
     * @return void
     */
    protected function add_actions() {
      add_action( 'wp_enqueue_script', array($this->default_kili_blocks, 'enqueueAdmin') );
      add_action( 'page_blocks', array( $this, 'page_blocks_content' ) );
    }
    
    /**
     * Add blocks to admin layout builder
     *
     * @param array $block_options Array of block options
     * @return void
     */
    protected function kili_pages_blocks_init_admin( $block_options = array() ) {
      foreach ( $block_options as $key => $value ) {
        $this->default_kili_blocks->add_blocks_to_wp($value);
      }
    }

    /**
     * Render theme pages from blocks
     *
     * @param [array] $settings Blocks settings
     * @return void
     */
    public function render_pages( $settings ) {
      $context = Timber::get_context();
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
          'numberposts' => -1
        );

        foreach ($settings as $key => $value) {
          $context[$key] = $value;
        }

        $all_pages = get_posts( $args );
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
     * @param [array] $context Page context (timber)
     * @return void
     */
    function page_blocks_content( $context ){
      $block_position = 0;
      while ( have_rows('kili_block_builder') ) : the_row();
        KILI_Layout::render( get_row_layout(), $block_position, $context, 'kili_block_builder' );
        $block_position++;
      endwhile;
    }

    /**
     * Check if Timber is activated
     *
     * @return boolean If timber is enabled or no
     */
    public function verify_timber_installation() {
      return class_exists( 'Timber' );
    }

    /**
     * Add data to timber context
     *
     * @param [array] $context Timber pages content
     * @return array Context variable updated
     */
    public function add_to_context( $context ) {
      /* Add extra data */
      $context['options'] = function_exists('get_fields') ? get_fields('option') : '';
      /* Menu */
      $context['menu']['primary'] = new TimberMenu('primary_navigation');
      /* Site info */
      $context['site'] = $this;
      /* Assets path */
      $context['dist']['images'] = $this->theme->link . '/dist/images/';
      $context['dist']['css'] = $this->theme->link . '/dist/styles/';
      $context['dist']['js'] = $this->theme->link . '/dist/scripts/';
      $context['sidebar_primary'] = Timber::get_widgets('sidebar-1');

      add_action('custom_asset', array( $this, 'custom_asset_args' ), 10, 2);
      if ( function_exists('icl_get_languages') ) {
        $languages = icl_get_languages('skip_missing=0&orderby=code');
        if( !empty($languages) ) {
          $context['languages'] = $languages;
        }
      }

      return $context;
    }

    /**
     * Set the custom assets
     *
     * @param [type] $base_folder Base folder
     * @return void
     */
    public function custom_asset_args( $base_folder ){
      echo get_template_directory_uri() . '/dist/styles/' . $base_folder;
    }
  }
}
