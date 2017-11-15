<?php
/**
 * Asset manifest for rendering the correct URLs for the assets.
 * This is especially useful for statically referencing assets
 * with dynamically changing names as in the case of cache-busting.
 *
 * @package kiliframework
 */

/**
 * Class for managing Asset Manifest File
 */
class Kili_Asset_Manifest {
	/**
	 * Json manifest content
	 *
	 * @var string
	 */
	private $manifest_content;

	/**
	 * Class constructor
	 *
	 * @param string $manifest_path Json Asset path.
	 */
	public function __construct( $manifest_path ) {
		global $wp_filesystem;
		if ( empty( $wp_filesystem ) ) {
			require_once( ABSPATH . '/wp-admin/includes/file.php' );
			WP_Filesystem();
		}
		$this->manifest_content = [];
		if ( file_exists( $manifest_path ) ) {
			$this->manifest_content = json_decode( $wp_filesystem->get_contents( $manifest_path ), true );
		}
	}

	/**
	 * Get the cache-busted filename
	 *
	 * @return string return manifest file content
	 */
	public function get() {
		return $this->manifest_content;
	}
}
