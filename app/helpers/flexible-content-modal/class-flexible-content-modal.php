<?php
/**
 * ACF Flexible Content Modal
 *
 * @package kiliframework
 */

/**
 * Class for handle flexible content modal
 */
class Flexible_Content_Modal {

	// Path
	private $path = '';

	/**
	 * Initialize function
	 */
	public function init() {
		global $acf;
		$this->path = FRAMEWORK_URL . 'app/helpers/flexible-content-modal/';

		// Path
		if( $acf ) {
			if( version_compare( $acf->version, '5.7.0', '<' ) ) {
				$this->path .= '/56/';
			}
		}

		// Hooks.
		add_action( 'admin_init', array( $this, 'admin_css' ), 1, 999 );
		add_action( 'admin_init', array( $this, 'admin_script' ), 1, 999 );
	}

	/**
	 * Process plugin activation
	 *
	 * @return void
	 */
	public function activate() {
		// no code.
	}

	/**
	 * Process plugin deactivation
	 *
	 * @return void
	 */
	public function deactivate() {
		// no code.
	}

	/**
	 * Register Admin Stylesheets
	 *
	 * @return void
	 */
	public function admin_css() {
		if ( ! class_exists( 'acf_pro' ) ) {
			wp_enqueue_style( 'flexible-input', $this->path . 'css/flexible-input.css', array( 'acf-input' ) );
		}
		wp_enqueue_style( 'acf-fc-modal', $this->path . 'css/style.min.css', array( 'acf-input' ) );
	}

	/**
	 * Register Admin Scripts
	 *
	 * @return void
	 */
	public function admin_script() {
		if ( ! class_exists( 'acf_pro' ) ) {
			wp_enqueue_script( 'acf-fc-modal-flexible', $this->path . 'js/flexible-content.min.js', array( 'acf-input' ) );
		}
		wp_enqueue_script( 'acf-fc-modal', $this->path . 'js/script.min.js', array( 'acf-input' ) );
	}

	/**
	 * Register Theme Stylesheets
	 *
	 * @return void
	 */
	public function theme_css() {
		// no code.
	}

	/**
	 * Register Theme Scripts
	 *
	 * @return void
	 */
	public function theme_script() {
		// no code.
	}
}
