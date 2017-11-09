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
	 * @param string  $layout Block layout.
	 * @param integer $block_position Block position.
	 * @param array   $context Page context (timber).
	 * @param string  $blocks_id Block builder id.
	 * @return void
	 */
	public function render( $layout, $block_position, $context = array(), $blocks_id = 0 ) {
		$layout_file = '{{layout}}';
		$find = array( $layout_file, '_' );
		$replace = array( $layout, '-' );
		$layout_directory = '/blocks/' . ( isset( $context['is_custom_post_type'] ) && $context['is_custom_post_type'] ? 'cpt/' . $context['post_type'] . '/' : 'pages/' );
		$full_layout_directory = get_stylesheet_directory() . $layout_directory;
		$new_layout_file = str_replace( $find, $replace, $layout_file );

		$context['layout'] = $layout;
		$context['blocks_id'] = $blocks_id;
		$context['block_position'] = $block_position;
		$context['page_block'] = $context['post']->get_field( $blocks_id )[ $block_position ];
		$context['block_unique_class'] = $context['page_block']['acf_fc_layout'] . '-' . $block_position . '-' . $context['post']->id;

		$file_to_render = '';
		if ( ! is_dir( $full_layout_directory ) ) {
			wp_mkdir_p( $full_layout_directory, 0755 );
		}
		if ( file_exists( $full_layout_directory . $new_layout_file . '.twig' ) ) {
			$file_to_render = $full_layout_directory . $new_layout_file . '.twig';
		} elseif ( isset( $context['is_custom_post_type'] ) && $context['is_custom_post_type'] ) {
			$file_to_render = get_stylesheet_directory() . '/blocks/pages/' . $new_layout_file . '.twig';
		} elseif ( file_exists( get_template_directory() . $layout_directory . $new_layout_file . '.twig' ) ) {
			$file_to_render = get_template_directory() . $layout_directory . $new_layout_file . '.twig';
		}

		if ( strcasecmp( $file_to_render, '' ) === 0 ) {
			$notice = '<section class="kili-missing-block"><div class="kili-container kili-soft"><b>' .
				__( 'Notice', 'kiliframework' ) . ':</b> ' . __( 'No block template found', 'kiliframework' ) . ', ' . __( 'please create file', 'kiliframework' ) .
				' ' . $new_layout_file . '.twig</div></section>';
			echo html_entity_decode( $notice );
			return;
		}
		Timber::render( $file_to_render, $context, false );
	}
}
