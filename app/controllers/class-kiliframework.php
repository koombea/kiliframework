<?php
#-----------------------------------------------------------------
# Include required files
#-----------------------------------------------------------------
// Defaults constants for the parent and child theme
include_once ( get_template_directory() . '/config/defaults.php' );

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
class KiliFramework {
  protected $default_kili_blocks;

  public function __construct() {
    $default_kili_blocks = new Kili_Theme_Blocks();
    $this->add_actions();
  }

  /**
   * Add actions to WordPress
   *
   * @return void
   */
  protected function add_actions() {
    add_action('wp_enqueue_script', array($this->default_kili_blocks, 'enqueueAdmin') );
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

}