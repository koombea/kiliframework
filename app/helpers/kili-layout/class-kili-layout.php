<?php
/**
 * Manages block rendering
 *
 * @package kiliframework
 */

/**
 * Class for managing block rendering
 */
class Kili_Layout {
	/**
	 * Render a block
	 *
	 * @param string $layout Block layout.
	 * @param integer $block_position Block position.
	 * @param array $context Page context (timber).
	 * @param string $blocks_id Block builder id.
	 * @return void
	 */
	public static function render( $layout, $block_position, $context = array(), $blocks_id = 0 ) {
		$layout_file = '{{layout}}';
		$find = array( '{{layout}}', '_' );
		$replace = array( $layout, '-' );
		$layout_directory = '/blocks/' . ( isset( $context['is_custom_post_type'] ) && $context['is_custom_post_type'] ? 'cpt/' . $context['post_type'] . '/' : 'pages/' );
		$full_layout_directory = get_stylesheet_directory() . $layout_directory;

		if ( ! is_dir( get_stylesheet_directory() . $layout_directory ) ) {
			mkdir( get_stylesheet_directory() . $layout_directory, 0755, true );
		}

		$new_layout_file = str_replace( $find, $replace, $layout_file );
		$context['layout'] = $layout;
		$context['blocks_id'] = $blocks_id;
		$context['block_position'] = $block_position;
		$context['page_block'] = $context['post']->get_field( $blocks_id )[ $block_position ];
		$context['block_unique_class'] = $context['page_block']['acf_fc_layout'] . '-' . $block_position . '-' . $context['post']->id;

		if ( file_exists( $full_layout_directory . $new_layout_file . '.twig' ) ) {
			Timber::render( $full_layout_directory . $new_layout_file . '.twig', $context, false );
		} elseif ( isset( $context['is_custom_post_type'] ) && $context['is_custom_post_type'] ) {
			$file_name = get_stylesheet_directory() . '/blocks/pages/' . $new_layout_file . '.twig';
			if ( file_exists( $file_name ) ) {
				Timber::render( $file_name, $context, false );
			}
		} else {
			$full_layout_directory = get_template_directory() . $layout_directory;
			if ( file_exists( $full_layout_directory . $new_layout_file . '.twig' ) ) {
				Timber::render( $full_layout_directory . $new_layout_file . '.twig', $context, false );
			} else {
				$notice = '<section class="kili-missing-block"><div class="kili-container kili-soft"><b>' .
					__( 'Notice', 'kiliframework' ) . ':</b> ' . __( 'No block template found', 'kiliframework' ) . ', ' . __( 'please create file', 'kiliframework' ) .
					' ' . $new_layout_file . '.twig</div></section>';
				echo html_entity_decode( esc_html( $notice ) );
			}
		}
	}
}
