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
	 * @var string
	 */
	private $manifest_content;

	/**
	 * Class constructor
	 * @param string $manifest_path Json Asset path.
	 */
	public function __construct( $manifest_path ) {

		if ( file_exists( $manifest_path ) ) {
			$this->manifest_content = json_decode( wp_remote_get( $manifest_path ), true );
		} else {
			$this->manifest_content = [];
		}
	}

	/**
	 * Get the cache-busted filename
	 * @return string return manifest file content
	 */
	public function get() {
		return $this->manifest_content;
	}

	/**
	 * Get the asset path
	 * @param  string $key
	 * @param  array $default
	 * @return [type]
	 */
	public function getPath( $key = '', $default = null ) {

		$collection = $this->manifest_content;
		if ( is_null( $key ) ) {
			return $collection;
		}
		if ( isset( $collection[$key] ) ) {
			return $collection[$key];
		}
		foreach ( explode( '.', $key ) as $segment ) {
			if ( !isset( $collection[$segment] ) ) {
				return $default;
			} else {
				$collection = $collection[$segment];
			}
		}
		return $collection;
	}
}

