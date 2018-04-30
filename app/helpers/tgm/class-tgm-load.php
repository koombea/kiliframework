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
			'name'               => 'SVG Support',
			'slug'               => 'svg-support',
			'required'           => false,
		),
		array(
			'name'               => 'TinyMCE Advanced',
			'slug'               => 'tinymce-advanced',
			'required'           => false,
		),
		array(
			'name'                  => 'Kili. Automatic Updater',
			'slug'                  => 'kili-automatic-updater',
			'source'                => 'https://github.com/fabolivark/kili-automatic-updater/archive/master.zip',
			'required'              => false,
			'version'               => '0.0.2',
			'force_activation'      => false,
			'force_deactivation'    => false,
			'external_url'          => 'https://github.com/fabolivark/kili-automatic-updater',
		),
    );

    include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

    if ( ! is_plugin_active( 'advanced-custom-fields-pro/acf.php' ) ) {
		$plugins[] = array(
						'name'                  => 'Advanced Custom Fields',
						'slug'                  => 'advanced-custom-fields',
						'source'                => 'https://github.com/AdvancedCustomFields/acf/archive/master.zip',
						'required'              => false,
						'version'               => '5.6.5',
						'force_activation'      => false,
						'force_deactivation'    => false,
						'external_url'          => 'https://github.com/AdvancedCustomFields/acf',
					);

		$plugins[] = array(
						'name'                  => 'Advanced Custom Fields: Options Page',
						'slug'                  => 'acf-options-page',
						'source'                => 'https://connect.advancedcustomfields.com/index.php?a=download&p=options-page&k=OPN8-FA4J-Y2LW-81LS',
						'required'              => false,
						'version'               => '2.0.1',
						'force_activation'      => false,
						'force_deactivation'    => false,
						'external_url'          => 'https://www.advancedcustomfields.com/add-ons/options-page/',
					);
	}

    $config = array(
      'id'           => 'kili_tgmpa',
      'default_path' => '',
      'menu'         => 'tgmpa-install-plugins',
      'parent_slug'  => 'plugins.php',
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
