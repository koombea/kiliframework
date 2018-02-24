<?php
/**
 * Handle dynamic block styles
 *
 * @package kiliframework
 */

/**
 * Class for dynamic block styles
 */
class Kili_Dynamic_Styles {
	/**
	 * Base styles string
	 *
	 * @var string
	 */
	private $base_styles;

	/**
	 * Styles directory
	 *
	 * @var string
	 */
	public $style_dir;

	/**
	 * Styles file name
	 *
	 * @var string
	 */
	public $style_file_name;

	/**
	 * Class constructor
	 */
	public function __construct() {
		$this->base_styles = '';
		$this->style_dir = THEME_DIR . 'dist/styles/';
		$this->style_file_name = $this->style_dir . 'block-styles.css';
	}

	/**
	 * Set the base styles string
	 *
	 * @param string $styles String containing your own style base.
	 * @return void
	 */
	public function set_base_styles( $styles ) {
		$this->base_styles = $styles;
	}

	/**
	 * Get the base styles string
	 *
	 * @return string Base styles string
	 */
	public function get_base_styles() {
		return $this->base_styles;
	}

	/**
	 * Read block custom fields for styling and generate css for those blocks
	 * You should provide the base styles (this class set_base_styles function) before using this method
	 *
	 * @param array $fields ACF Block fields.
	 * @return void
	 */
	public function process_blocks_styles( $fields ) {
		global $wp_filesystem;
		if ( empty( $wp_filesystem ) ) {
			require_once( ABSPATH . '/wp-admin/includes/file.php' );
			WP_Filesystem();
		}
		$style = '';
		$size = count( $fields );
		$page_option_field = function_exists( 'get_fields' ) ? get_fields( 'option' ) : [];
		for ( $i = 0; $i < $size; $i++ ) {
			$field = $fields[ $i ];
			$page_extra_field = array_filter( $field ? $field : [], function( $key ) { return $key != 'kili_block_builder'; }, ARRAY_FILTER_USE_KEY );
			if ( isset( $field['kili_block_builder'] ) && is_array( $field['kili_block_builder'] ) ) {
				foreach ( $field['kili_block_builder'] as $page_key => $page_field ) {
					$style .= $this->replace_placeholders( $page_field, $page_extra_field, $page_option_field, $field['page_id'], ( isset( $page_field['acf_fc_layout'] ) ? $page_field['acf_fc_layout'] : '' ), $page_key );

				}
			}
		}
		$current_style = file_exists( $this->style_file_name ) ? $wp_filesystem->get_contents( $this->style_file_name ) : '';
		if ( strcasecmp( $current_style, $style ) !== 0 ) {
			if ( ! is_dir( $this->style_dir ) ) {
				wp_mkdir_p( $this->style_dir, FS_CHMOD_DIR );
			}
			$wp_filesystem->put_contents( $this->style_file_name, $this->clean_style( $style ), FS_CHMOD_FILE );
		}
	}

	/**
	 * Minifies css and removes empty rules
	 *
	 * @param string $css CSS string to be cleaned.
	 * @return string Clean CSS string
	 */
	private function clean_style( $css ) {
		// Remove comments.
		$css = preg_replace( '!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css );
		// Remove space after colons.
		$css = str_replace( ': ', ':', $css );
		// Remove whitespaces.
		$css = str_replace( array( "\r\n", "\r", "\n", "\t", '  ' ), '', $css );
		// Collapse adjacent spaces into a single space.
		$css = preg_replace( ' {2,}', ' ',$css );
		// Remove spaces that might still be left where we know they aren't needed.
		$css = str_replace( '} ', '}', $css );
		$css = str_replace( '{ ', '{', $css );
		$css = str_replace( '; ', ';', $css );
		$css = str_replace( ', ', ',', $css );
		$css = str_replace( ' }', '}', $css );
		$css = str_replace( ' {', '{', $css );
		$css = str_replace( ' ;', ';', $css );
		$css = str_replace( ' ,', ',', $css );
		// remove empty rules.
		$css = preg_replace( '/\.([\w,\d]+)((\s+)((\.*)[\w,\d,-]+))*{}/', '', $css );
		$css = preg_replace( '/@([\w,\s,\d,\-,:,\(,\)]+){}/', '', $css );
		return $css;
	}

	/**
	 * Replace block placeholders with the real styles
	 *
	 * @param array  $field Block field.
	 * @param array  $field Page field.
	 * @param array  $field Option field.
	 * @param mixed  $page_id Page id.
	 * @param string $layout Block layout.
	 * @param mixed  $block_position Block position.
	 * @return string CSS string for the block field
	 */
	private function replace_placeholders( $field, $extra_field, $page_option_field, $page_id, $layout, $block_position ) {
		$styles = $this->base_styles;
		if ( strcasecmp( $styles, '' ) !== 0 ) {

			$replacements['{{page_id}}'] = $page_id;
			$replacements['{{block_position}}'] = $block_position;
			$replacements['{{acf_fc_layout}}'] = $layout;
			$replacements = array_merge( $replacements, $this->get_array_replacement( $field ) );
			$replacements = array_merge( $replacements, $this->get_array_replacement( $extra_field ) );
			$replacements = array_merge( $replacements, $this->get_array_replacement( $page_option_field ? $page_option_field : [] ) );

			foreach ( $replacements as $placeholder => $replacement ) {
				$styles = str_replace( $placeholder, $replacement, $styles );
			}

			$styles = preg_replace( '/\.(.*){{(.)+}}/', '.no-apply', $styles );
			$styles = preg_replace( '/(.*){{(.+)}}(.*)/', '', $styles );
			$styles = str_replace( array( '\t' ), '', $styles );
		}
		return $styles;
	}

	/**
	 * Get the replacement for provided field placeholder
	 *
	 * @param array  $array Placeholders array.
	 * @param string $prefix Prefix for replacement (default:'').
	 * @return array Array with replacements done
	 */
	private function get_array_replacement( $array, $prefix = '' ) {
		$replacements = array();
		foreach ( $array as $key => $value ) {
			if ( is_array( $value ) ) {
				$replacements = array_merge( $replacements, $this->get_array_replacement( $value, $key . '_' ) );
			} elseif ( is_string( $value ) && strcasecmp( trim( $value ), '' ) !== 0 ) {
				$replacements[ '{{' . $prefix . $key . '}}' ] = trim( $value );
			}
		}
		return $replacements;
	}
}
