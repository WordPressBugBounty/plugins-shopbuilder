<?php
/**
 * AssetBundler class.
 *
 * Dynamically merges and minifies CSS/JS files into a single versioned file stored in uploads.
 *
 * @package  RadiusTheme\SB
 * @since    1.0.0
 */

namespace RadiusTheme\SB\Models;

use MatthiasMullie\Minify\JS;
use MatthiasMullie\Minify\CSS;

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

/**
 * AssetBundler class.
 */
class AssetBundler {
	/**
	 * Handle for this bundle (used in filename/versioning).
	 *
	 * @var string
	 */
	protected $handle;

	/**
	 * List of source file paths to bundle.
	 *
	 * @var array
	 */
	protected $source_files = [];

	/**
	 * Directory where a minified bundle will be saved.
	 *
	 * @var string
	 */
	protected $upload_dir;

	/**
	 * Generated filename of the minified JS file.
	 *
	 * @var string
	 */
	protected $filename;

	/**
	 * File type (JS or CSS).
	 *
	 * @var string
	 */
	protected $type;

	/**
	 * AssetBundler constructor.
	 *
	 * @param string $handle Unique identifier for the bundle.
	 * @param array  $files  List of absolute file paths to include in the bundle.
	 * @param string $type   File type (JS or CSS).
	 *
	 * @return void
	 */
	public function __construct( string $handle, array $files, string $type = 'js' ) {
		$this->handle       = sanitize_key( $handle );
		$this->source_files = $files;
		$this->type         = in_array( $type, [ 'js', 'css' ], true ) ? $type : 'js';

		$upload           = wp_upload_dir();
		$this->upload_dir = trailingslashit( $upload['basedir'] ) . "shopbuilder_uploads/cache/$this->type/";

		$this->maybe_create_cache_dir();
	}

	/**
	 * Creates the cache directory if it doesn't exist.
	 *
	 * @return void
	 */
	protected function maybe_create_cache_dir() {
		if ( ! file_exists( $this->upload_dir ) ) {
			wp_mkdir_p( $this->upload_dir );
		}
	}

	/**
	 * Generates the full path to the cached JS file based on handle and file hash.
	 *
	 * @return string
	 */
	protected function get_cache_file_path() {
		$this->filename = $this->generate_filename();

		return $this->upload_dir . $this->filename;
	}

	/**
	 * Checks if the cached version of the JS file already exists.
	 *
	 * @return bool
	 */
	protected function is_cached() {
		return file_exists( $this->get_cache_file_path() );
	}

	/**
	 * Builds the minified JS bundle if not cached and returns the URL.
	 *
	 * @return string
	 */
	public function build() {
		$cache_path = $this->get_cache_file_path();

		if ( ! $this->is_cached() ) {
			$minifier = ( 'css' === $this->type ) ? new CSS() : new JS();

			foreach ( $this->source_files as $file ) {
				$this->safely_add_file_to_minifier( $file, $minifier );
			}

			$minifier->minify( $cache_path );
		}

		return $this->get_url();
	}

	/**
	 * Returns the URL to the cached JS bundle.
	 *
	 * @return string
	 */
	public function get_url() {
		$upload = wp_upload_dir();

		return trailingslashit( $upload['baseurl'] ) . "shopbuilder_uploads/cache/$this->type/" . $this->filename;
	}

	/**
	 * Clears all cached files related to this bundle handle.
	 *
	 * @return void
	 */
	public function clear_cache() {
		foreach ( glob( $this->upload_dir . "$this->handle-*.min.$this->type" ) as $file ) {
			if ( file_exists( $file ) ) {
				wp_delete_file( $file );
			}
		}
	}

	/**
	 * Generates the filename for the minified JS file.
	 *
	 * @return string
	 */
	protected function generate_filename() {
		$hash = md5( $this->handle . wp_json_encode( $this->source_files ) );

		return "$this->handle-$hash.min.$this->type";
	}

	/**
	 * Rewrites relative font URLs in CSS to absolute plugin URLs.
	 *
	 * @param string $css       The CSS content.
	 * @param string $base_path The base URL for resolving relative paths.
	 * @return string Modified CSS content with absolute font URLs.
	 */
	protected function fix_font_urls( string $css, string $base_path ) {
		$base_path = trailingslashit( $base_path );

		return preg_replace_callback(
			'/url\((["\']?)(?!https?:\/\/|\/)([^)]+?\.(?:eot|woff2?|ttf|svg|otf))(\?[^)]+)?\1\)/i',
			function ( $matches ) use ( $base_path ) {
				$relative_path = $matches[2];
				$query         = isset( $matches[3] ) ? $matches[3] : '';
				$absolute_url  = $base_path . ltrim( $relative_path, './\\' );
				return 'url("' . esc_url( $absolute_url . $query ) . '")';
			},
			$css
		);
	}

	/**
	 * Safely add file contents or path to the minifier.
	 *
	 * @param string $file     File path.
	 * @param object $minifier Minifier instance.
	 *
	 * @return void
	 */
	private function safely_add_file_to_minifier( $file, $minifier ) {
		if ( ! is_string( $file ) ) {
			return;
		}

		if ( ! is_readable( $file ) ) {
			error_log( "[ShopBuilder Minify] Skipping unreadable file: $file" ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log

			return;
		}

		if ( 'css' === $this->type ) {
			$content = @file_get_contents( $file ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged, WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents

			if ( false === $content ) {
				error_log( "[ShopBuilder Minify] Failed to read file: $file" ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log

				return;
			}

			$base_url  = dirname( plugins_url( '', $file ), 2 );
			$processed = $this->fix_font_urls( $content, $base_url );

			$minifier->add( $processed );
		} else {
			$minifier->add( $file );
		}
	}
}
