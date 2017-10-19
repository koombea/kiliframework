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

	/**
	 * Initialize function
	 */
	public function init() {
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
			wp_enqueue_style( 'flexible-input', FRAMEWORK_URL . 'app/helpers/flexible-content-modal/css/flexible-input.css', array( 'acf-input' ) );
		}
		wp_enqueue_style( 'acf-fc-modal', FRAMEWORK_URL . 'app/helpers/flexible-content-modal/css/style.min.css', array( 'acf-input' ) );
	}

	/**
	 * Register Admin Scripts
	 *
	 * @return void
	 */
	public function admin_script() {
		if ( ! class_exists( 'acf_pro' ) ) {
			wp_enqueue_script( 'acf-fc-modal-flexible', FRAMEWORK_URL . 'app/helpers/flexible-content-modal/js/flexible-content.min.js', array( 'acf-input' ) );
		}
		wp_enqueue_script( 'acf-fc-modal', FRAMEWORK_URL . 'app/helpers/flexible-content-modal/js/script.min.js', array( 'acf-input' ) );
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
