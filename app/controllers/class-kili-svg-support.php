<?php
/**
 * For SVG files support
 *
 * @package kiliframework
 */

/**
 * Class for handling SVG files support
 */
class Kili_Svg_Support {

	/**
	 * Class constructor
	 */
	public function __construct() {
		if ( current_theme_supports( 'kili-enable-svg-myme-types' ) ) {
			$this->add_filters();
		}
	}

	/**
	 * Add filters for supporting svg file type
	 *
	 * @return void
	 */
	public function add_filters() {
		$wordpress_version = get_bloginfo( 'version' );
		if ( $wordpress_version < '4.7.3' ) {
			add_filter( 'wp_check_filetype_and_ext', array( $this, 'ksource_disable_real_mime_check' ), 10, 4 );
		}

		add_filter( 'upload_mimes', array( $this, 'ksource_allow_svg_uploads' ) );
		add_filter( 'wp_prepare_attachment_for_js', array( $this, 'ksource_set_dimensions' ), 10, 3 );
		add_action( 'admin_enqueue_scripts', array( $this, 'ksource_administration_styles' ) );
		add_action( 'wp_head', array( $this, 'ksource_public_styles' ) );
	}

	/**
	 * Allow SVG uploads
	 *
	 * @param array $existing_mime_types Current site mime types.
	 * @return array Array with svg type included
	 */
	public function ksource_allow_svg_uploads( $existing_mime_types = array() ) {
		return $existing_mime_types + array(
			'svg' => 'image/svg+xml',
		);
	}

	/**
	 * Get svg file dimensions
	 *
	 * @param string $svg File full path.
	 * @return object Object with file dimensions
	 */
	public function ksource_get_dimensions( $svg ) {
		$svg = simplexml_load_file( $svg );
		$attributes = $svg->attributes();
		$width = (string) $attributes->width;
		$height = (string) $attributes->height;
		return (object) array(
			'width' => $width,
			'height' => $height,
		);
	}

	/**
	 * Set dimensions for the file
	 *
	 * @param array $response http response.
	 * @param object $attachment file attachment object.
	 * @param mixed $meta meta for svg object.
	 * @return array Modified response
	 */
	public function ksource_set_dimensions( $response, $attachment, $meta ) {
		if ( strcasecmp( $response['mime'], 'image/svg+xml' ) === 0 && empty( $response['sizes'] ) ) {
			$svg_file_path = get_attached_file( $attachment->ID );
			$dimensions = $this->ksource_get_dimensions( $svg_file_path );
			$response['sizes'] = array(
				'full' => array(
					'url' => $response['url'],
					'width' => $dimensions->width,
					'height' => $dimensions->height,
					'orientation' => $dimensions->width > $dimensions->height ? 'landscape' : 'portrait',
				),
			);
		}
		return $response;
	}

	/**
	 * Add styles for administration view
	 *
	 * @return void
	 */
	public function ksource_administration_styles() {
		// Media Listing Fix.
		wp_add_inline_style( 'wp-admin', ".media .media-icon img[src$='.svg'] { width: auto; height: auto; }" );
		// Featured Image Fix.
		wp_add_inline_style( 'wp-admin', "#postimagediv .inside img[src$='.svg'] { width: 100%; height: auto; }" );
	}

	/**
	 * Add styles for the theme (Featured Image Fix)
	 *
	 * @return void
	 */
	public function ksource_public_styles() {
		echo "<style>.post-thumbnail img[src$='.svg'] { width: 100%; height: auto; }</style>";
	}

	/**
	 * Disable mime type check for WordPress versions < 4.7.3
	 *
	 * @param array $data File data.
	 * @param array $file File object.
	 * @param string $filename File name.
	 * @param array $mimes Mime types.
	 * @return array Array with all the values
	 */
	public function ksource_disable_real_mime_check( $data, $file, $filename, $mimes ) {
		$wp_filetype = wp_check_filetype( $filename, $mimes );
		$ext = $wp_filetype['ext'];
		$type = $wp_filetype['type'];
		$proper_filename = $data['proper_filename'];
		return compact( 'ext', 'type', 'proper_filename' );
	}
}
