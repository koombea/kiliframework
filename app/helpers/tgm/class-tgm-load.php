<?php
/**
 * TGM Plugin Activation is a PHP library that allows you to easily require or recommend plugins for your WordPress themes (and plugins).
 */
require_once FRAMEWORK_DIR . 'vendor/tgm/class-tgm-plugin-activation.php';
final class Tgm_Load {

  public function __construct() {
    add_action( 'tgmpa_register', array($this, 'kili_register_required_plugins') );
  }
  
  public function kili_register_required_plugins() {
    $plugins = array(
      array(
        'name'      => 'Timber Library',
        'slug'      => 'timber-library',
        'required'  => true,
      ),
      array(
        'name'               => 'TinyMCE Advanced',
        'slug'               => 'tinymce-advanced',
        'required'           => true,
      ),
    );
    $config = array(
      'id'           => 'kili_tgmpa',
      'default_path' => '',
      'menu'         => 'tgmpa-install-plugins',
      'parent_slug'  => 'themes.php',
      'capability'   => 'edit_theme_options',
      'has_notices'  => true,
      'dismissable'  => true,
      'dismiss_msg'  => '',
      'is_automatic' => true,
      'message'      => '',
    );
    tgmpa( $plugins, $config );
  }
}

$tgm_load = new Tgm_Load();