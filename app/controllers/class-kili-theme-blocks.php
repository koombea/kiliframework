<?php
/**
 * Handle page blocks
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
		$alternative_page_blocks_file_path = array();
		if ( function_exists( 'acf_add_local_field_group' ) && $location ) {
			if ( ! is_dir( $location['blocks_pages_dir'] ) ) {
				mkdir( $location['blocks_pages_dir'], 0755, true );
			}

			$blocks_file_path = $this->get_acf_json_files(
				$location['blocks_pages_dir'],
				isset( $location['excluded_page_blocks'] ) ? $location['excluded_page_blocks'] : array()
			);
			$is_defined_alternative_page_blocks = isset( $location['defined_alternative_page_blocks'] );

			$layouts = array();
			if ( $blocks_file_path || $is_defined_alternative_page_blocks ) {
				if ( count( $blocks_file_path ) > 0 ) {
					$layout = $this->get_block_layout( $blocks_file_path );
					if ( count( $layout ) ) {
						$layouts = array_merge( $layouts, $layout );
					}
				}

				if ( $is_defined_alternative_page_blocks && count( $location['defined_alternative_page_blocks'] ) > 0 ) {
					$layout = $this->get_block_layout( $location['defined_alternative_page_blocks'], true );
					if ( count( $layout ) ) {
						$layouts = array_merge( $layouts, $layout );
					}
				}

				$meta = array(
					'key' => $location['flexible_content_group'],
					'title' => $location['layout_title'],
					'fields' => array(
						array(
							'key' => $location['flexible_content_key'],
							'label' => '',
							'name' => $location['flexible_content_id'],
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
							'layouts' => $layouts,
						),
					),
					'menu_order' => 0,
					'position' => 'normal',
					'style' => 'default',
					'label_placement' => 'top',
					'instruction_placement' => 'label',
					'hide_on_screen' => array(
						0 => 'the_content',
						1 => 'excerpt',
						2 => 'custom_fields',
						5 => 'categories',
						6 => 'tags',
					),
					'location' => array(),
					'active' => 1,
					'description' => __( 'Kili Framework page builder', 'kiliframework' ),
				);

				$location_index = 0;
				foreach ( $this->supported_formats as $format ) {
					if ( empty( $location[ $format ] ) || ! isset( $location[ $format ] ) ) continue;
					$meta['location'][ $location_index ] = array();
					foreach ( $location[ $format ] as $place ) {
						$meta['location'][ $location_index ][] = array(
							'param'   => $format,
							'operator'   => '==',
							'value'   => $place,
						);
						$location_index++;
					}
				}
			}
			if ( isset( $layout ) && count( $layout ) ) {
				acf_add_local_field_group( $meta );
			}
		}
	}

	/**
	 * Obtain the layout of a block
	 *
	 * @param string $blocks_path Block files path.
	 * @param string $file File name.
	 * @return array Array with block layout
	 */
	private function get_file_layout( $blocks_path, $file ) {
		$current_layout = array();
		$get_json_content = file_get_contents( $file );
		$json_to_php = json_decode( $get_json_content, true );
		if ( null !== $json_to_php ) {
			if ( isset( $json_to_php[0]['fields'][0]['layouts'] ) ) {
				$current_layout = $json_to_php[0]['fields'][0]['layouts'];
			} else if ( isset( $json_to_php[0]['fields'][0]['layout'] ) ) {
				$current_layout = $json_to_php[0]['fields'][0]['layout'];
			} else {
				$current_layout = array(
					array(
						'key' => $json_to_php[0]['key'],
						'name' => str_replace( ' ','_',strtolower( $json_to_php[0]['title'] ) ),
						'label' => $json_to_php[0]['title'],
						'display' => 'block',
						'sub_fields' => $json_to_php[0]['fields'],
						'min' => '',
						'max' => '',
					)
				);
			}

			$sub_fields = null;
			if ( isset( $current_layout[0]['sub_fields'] ) ) {
				$sub_fields = $current_layout[0]['sub_fields'][0];
			} elseif ( isset( $current_layout[0][0] ) ) {
				$sub_fields = $current_layout[0][0]['sub_fields'][0];
			}
			if ( isset( $sub_fields['sub_fields'] ) && count( $sub_fields['sub_fields'] ) > 0 ) {
				foreach ( $sub_fields['sub_fields'] as $sub_key => $value ) {
					if ( isset( $value['type'] ) && strcasecmp( $value['type'],'flexible_content' ) === 0 ) {
						if ( isset( $value['layouts'] ) && count( $value['layouts'] ) === 0 ) {
							$current_layout[0]['sub_fields'][0]['sub_fields'][ $sub_key ]['layouts'] = $this->get_block_layout( $blocks_path, false, str_replace( '_', '-', $current_layout[0]['name'] ) );
						}
					}
				}
			}
		}
		return $current_layout;
	}

	/**
	 * Get blocks layout
	 *
	 * @param array $blocks_path Block files path.
	 * @param boolean $add_default_blocks Use default blocks (default:false).
	 * @param string $excluded_file_name Exclude file name.
	 * @return array Array with blocks layout
	 */
	private function get_block_layout( $blocks_path, $add_default_blocks = false, $excluded_file_name = '' ) {
		$layouts = array();
		foreach ( $blocks_path as $key => $value ) {
			if ( stripos( $value, $excluded_file_name ) === false ) {
				$file = $value;
				if ( $add_default_blocks ) {
					$file = get_stylesheet_directory() . '/data/blocks/pages/' . $value . '.json';
				}
				$current_layout = $this->get_file_layout( $blocks_path, $file );
				if ( count( $current_layout ) > 0 ) {
					foreach ( $current_layout as $key2 => $layout ) {
						array_push( $layouts, $layout );
					}
				}
			}
		}
		return $layouts;
	}

	/**
	 * Get block structure from json files
	 *
	 * @param string $directory Blocks directory.
	 * @param array $excluded Excluded blocks.
	 * @return array Array with blocks structure
	 */
	private function get_acf_json_files( $directory, $excluded = array() ) {
		$settings = array();
		$settings_path_child = $directory;
		$settings_child = Theme_Data::kili_scandir( $settings_path_child );
		if ( $settings_child ) {
			$real_path = '';
			foreach ( $settings_child as $key => $setting ) {
				if ( is_file( $settings_path_child . $setting ) ) {
					$real_path = $settings_path_child;
				}
				$setting_name = explode( '.', $setting );
				if ( in_array( $setting_name[0], $excluded, true ) === false ) {
					array_push( $settings, $real_path . $setting );
				}
			}
		}
		return $settings;
	}
}