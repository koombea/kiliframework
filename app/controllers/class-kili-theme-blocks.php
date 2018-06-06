<?php
/**
 * Handle page blocks
 *
 * @package kiliframework
 */

/**
 * Class for handling theme blocks
 */
class Kili_Theme_Blocks {
	/**
	 * Supported formats
	 *
	 * @var array
	 */
	private $supported_formats;

	/**
	 * Class constructor
	 */
	public function __construct() {
		$this->supported_formats = array( 'post_type', 'page_template' );
	}

	/**
	 * Add blocks to pages
	 *
	 * @param array $location Block options.
	 * @return void
	 */
	public function add_blocks_to_wp( $location ) {
		if ( ! function_exists( 'acf_add_local_field_group' ) || empty( $location ) ) {
			return;
		}
		if ( ! is_dir( $location['blocks_pages_dir'] ) ) {
			wp_mkdir_p( $location['blocks_pages_dir'], 0755 );
		}
		$blocks_file_path = $this->get_acf_json_files(
			$location['blocks_pages_dir'],
			isset( $location['excluded_page_blocks'] ) ? $location['excluded_page_blocks'] : array()
		);
		$use_alternative_blocks = isset( $location['defined_alternative_page_blocks'] );
		$layouts = array();
		$layout = array();
		if ( isset( $blocks_file_path ) && count( $blocks_file_path ) > 0 ) {
			$layout = $this->get_block_layout( $blocks_file_path );
		}
		if ( count( $layout ) ) {
			$layouts = array_merge( $layouts, $layout );
		}
		if ( $use_alternative_blocks && count( $location['defined_alternative_page_blocks'] ) > 0 ) {
			$layouts = array_merge( $layouts, $this->get_block_layout( $location['defined_alternative_page_blocks'], true ) );
		}
		$meta_options = array(
			'key' => $location['flexible_content_group'],
			'title' => $location['layout_title'],
			'flexible_key' => $location['flexible_content_key'],
			'flexible_id' => $location['flexible_content_id'],
			'layouts' => $layouts,
		);
		$meta = $this->get_meta_template( $meta_options );
		$location_index = 0;
		foreach ( $this->supported_formats as $format ) {
			if ( isset( $location[ $format ] ) ) {
				$meta['location'][ $location_index ] = array();
				foreach ( $location[ $format ] as $place ) {
					$meta['location'][ $location_index ] = array(
						'param'   => $format,
						'operator'   => '==',
						'value'   => $place,
					);
					$location_index++;
				}
			}
		}
		$meta['location'] = array( $meta['location'] );
		acf_add_local_field_group( $meta );
	}

	/**
	 * Obtain the layout of a block
	 *
	 * @param string $blocks_path Block files path.
	 * @param string $file File name.
	 * @return array Array with block layout
	 */
	private function get_file_layout( $blocks_path, $file ) {
		global $wp_filesystem;
		if ( empty( $wp_filesystem ) ) {
			require_once( ABSPATH . '/wp-admin/includes/file.php' );
			WP_Filesystem();
		}
		$current_layout = null;
		$sub_fields = null;
		$get_json_content = $wp_filesystem->get_contents( $file );
		$json_to_php = json_decode( $get_json_content, true );

		if ( null !== $json_to_php && isset( $json_to_php[0] ) ) {
			$current_layout = array(
				array(
					'key' => $json_to_php[0]['key'],
					'name' => str_replace( ' ', '_', strtolower( $json_to_php[0]['title'] ) ),
					'label' => $json_to_php[0]['title'],
					'display' => 'block',
					'sub_fields' => $json_to_php[0]['fields'],
					'min' => '',
					'max' => '',
				),
			);
			if ( isset( $json_to_php[0]['fields'][0]['layouts'] ) ) {
				$current_layout = $json_to_php[0]['fields'][0]['layouts'];
			} elseif ( isset( $json_to_php[0]['fields'][0]['layout'] ) ) {
				$current_layout = $json_to_php[0]['fields'][0]['layout'];
			}
			if ( isset( $current_layout[0]['sub_fields'] ) ) {
				$sub_fields = $current_layout[0]['sub_fields'][0];
			} elseif ( isset( $current_layout[0][0] ) ) {
				$sub_fields = $current_layout[0][0]['sub_fields'][0];
			}
			if ( null !== $sub_fields && isset( $sub_fields['sub_fields'] ) && count( $sub_fields['sub_fields'] ) > 0 ) {
				foreach ( $sub_fields['sub_fields'] as $sub_key => $value ) {
					if ( isset( $value['type'] ) && strcasecmp( $value['type'],'flexible_content' ) === 0
					&& isset( $value['layouts'] ) && count( $value['layouts'] ) === 0 ) {
						$current_layout[0]['sub_fields'][0]['sub_fields'][ $sub_key ]['layouts'] = $this->get_block_layout( $blocks_path, false, str_replace( '_', '-', $current_layout[0]['name'] ) );
					}
				}
			}
		}
		return $current_layout;
	}

	/**
	 * Get blocks layout
	 *
	 * @param array   $blocks_path Block files path.
	 * @param boolean $add_default_blocks Use default blocks (default:false).
	 * @param string  $excluded_file_name Exclude file name.
	 * @return array Array with blocks layout
	 */
	private function get_block_layout( $blocks_path, $add_default_blocks = false, $excluded_file_name = '' ) {
		$layouts = array();
		$size = count( $blocks_path );
		for ( $i = 0; $i < $size; $i++ ) {
			$file = $blocks_path[ $i ];
			if ( $add_default_blocks ) {
				$file = get_stylesheet_directory() . '/data/blocks/pages/' . $blocks_path[ $i ] . '.json';
			}
			if ( stripos( $blocks_path[ $i ], $excluded_file_name ) === false ) {
				$current_layout = $this->get_file_layout( $blocks_path, $file );
				$layouts = array_merge( $layouts, $this->push_to_layouts( $current_layout ) );
			}
		}
		return $layouts;
	}

	/**
	 * Get layout structure
	 *
	 * @param array $current_layout Layout array.
	 * @return array Array with layout structure
	 */
	private function push_to_layouts( $current_layout ) {
		$current_layout_size = count( $current_layout );
		if ( 0 === $current_layout_size ) {
			return array();
		}
		$layouts = array();
		for ( $j = 0; $j < $current_layout_size; $j++ ) {
			if ( isset( $current_layout[ $j ] ) ) {
				array_push( $layouts, $current_layout[ $j ] );
			}
		}
		return $layouts;
	}

	/**
	 * Get block structure from json files
	 *
	 * @param string $directory Blocks directory.
	 * @param array  $excluded Excluded blocks.
	 * @return array Array with blocks structure
	 */
	private function get_acf_json_files( $directory, $excluded = array() ) {
		$settings_path_child = $directory;
		$settings_child = Theme_Data::kili_scandir( $settings_path_child );
		if ( ! $settings_child ) {
			return array();
		}
		$settings = array();
		$size = count( $settings_child );
		for ( $i = 0; $i < $size; $i++ ) {
			$file_directory = is_file( $settings_path_child . $settings_child[ $i ] ) ? $settings_path_child : '';
			$setting_name = explode( '.', $settings_child[ $i ] );
			if ( in_array( $setting_name[0], $excluded, true ) === false ) {
				array_push( $settings, $file_directory . $settings_child[ $i ] );
			}
		}
		return $settings;
	}

	/**
	 * Get metafield structure
	 *
	 * @param array $options Keys for the structure.
	 * @return array Field structure
	 */
	private function get_meta_template( $options = array() ) {
		$template = array(
			'key' => $options['key'],
			'title' => $options['title'],
			'fields' => array(
				array(
					'key' => $options['flexible_key'],
					'label' => '',
					'name' => $options['flexible_id'],
					'type' => 'flexible_content',
					'instructions' => __( 'Place and edit your blocks. Add photos to your blocks, change text colors and fonts.', 'kiliframework' ),
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '',
						'class' => 'kiliframework',
						'id' => '',
					),
					'button_label' => __( 'Add Section','kiliframework' ),
					'min' => '',
					'max' => '',
					'layouts' => $options['layouts'],
				),
			),
		);
		return array_merge( $template, $this->get_template_extra_vars() );
	}

	/**
	 * Get template extra variables
	 *
	 * @return array The additional options
	 */
	private function get_template_extra_vars() {
		return array(
			'menu_order' => 0,
			'position' => 'normal',
			'style' => 'default',
			'label_placement' => 'top',
			'instruction_placement' => 'label',
			'hide_on_screen' => array(
				0 => 'the_content',
				1 => 'excerpt',
				2 => 'custom_fields',
			),
			'location' => array(),
			'active' => 1,
			'description' => __( 'Kili Framework page builder', 'kiliframework' ),
		);
	}
}
