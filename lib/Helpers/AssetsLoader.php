<?php
/**
 * Class to register client-side assets (scripts and stylesheets) for the Gutenberg block.
 *
 * @package CFFGF\Helpers
 */

namespace CFFGF\Helpers;

/**
 * Class AssetsLoader
 */
class AssetsLoader {
	/**
	 * Registers all block assets so that they can be enqueued through Gutenberg in the corresponding context.
	 *
	 * @see https://wordpress.org/gutenberg/handbook/blocks/writing-your-first-block-type/#enqueuing-block-scripts
	 */
	public function init() {
		add_action( 'init', [ $this, 'register_assets' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'wp_enqueue_scripts' ], 11 );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_assets' ], 11, 1 );
		add_action( 'enqueue_block_editor_assets', [ $this, 'enqueue_block_editor_assets' ], 11 );
		add_action( 'gform_preview_init', [ $this, 'enqueue_preview_assets' ], 11 );
		add_action( 'gform_editor_js', [ $this, 'enqueue_admin_cffgf_assets' ], 9999 );
	}

	/**
	 * Load a WordPress asset metadata file with safe defaults.
	 *
	 * @param string $relative_path Relative asset metadata path.
	 * @param array  $fallback Fallback metadata.
	 *
	 * @return array
	 */
	private function load_asset_metadata( $relative_path, $fallback ) {
		$asset = $fallback;
		$path  = CFFGF_PATH . $relative_path;

		if ( file_exists( $path ) ) {
			$loaded = require $path;

			if ( is_array( $loaded ) ) {
				$asset = array_merge( $fallback, $loaded );
			}
		}

		if ( ! isset( $asset['dependencies'] ) || ! is_array( $asset['dependencies'] ) ) {
			$asset['dependencies'] = $fallback['dependencies'];
		}

		if ( ! isset( $asset['version'] ) || ! is_string( $asset['version'] ) ) {
			$asset['version'] = $fallback['version'];
		}

		return $asset;
	}

	/**
	 * Register the assets for the plugin.
	 */
	public function register_assets() {
		$admin_assets_path        = 'build/index.asset.php';
		$admin_cffgf_path         = 'build/cffgf.asset.php';
		$frontend_assets_path     = 'build/frontend.asset.php';
		$admin_editor_style_path  = 'build/index.css';
		$admin_cffgf_scripts_path = 'build/cffgf.js';
		$admin_cffgf_style_path   = 'build/cffgf.css';
		$frontend_style_path      = 'build/frontend.css';

		$admin_asset = $this->load_asset_metadata(
			$admin_assets_path,
			[
				'dependencies' => [
					'wp-i18n',
					'jquery',
					'wp-util',
					'block-editor',
				],
				'version'      => CFFGF_VERSION,
			]
		);

		$gf_editor_asset = $this->load_asset_metadata(
			$admin_cffgf_path,
			[
				'dependencies' => [
					'wp-i18n',
					'jquery',
					'wp-util',
					'block-editor',
				],
				'version'      => CFFGF_VERSION,
			]
		);

		if ( ! in_array( 'jquery', $gf_editor_asset['dependencies'], true ) ) {
			$gf_editor_asset['dependencies'][] = 'jquery';
		}

		$frontend_asset = $this->load_asset_metadata(
			$frontend_assets_path,
			[
				'dependencies' => [],
				'version'      => CFFGF_VERSION,
			]
		);

		// Register optional editor only styles.
		if ( file_exists( CFFGF_PATH . $admin_editor_style_path ) ) {
			wp_register_style(
				'cffgf-admin',
				CFFGF_URL . $admin_editor_style_path,
				[],
				$admin_asset['version']
			);
		}

		// Register the bundled gravity forms editor JS file.
		if ( file_exists( CFFGF_PATH . $admin_cffgf_scripts_path ) ) {
			wp_register_script(
				'cffgf-gf-admin',
				CFFGF_URL . $admin_cffgf_scripts_path,
				$gf_editor_asset['dependencies'],
				$gf_editor_asset['version'],
				true
			);
		}

		// Register optional gravity forms editor styles.
		if ( file_exists( CFFGF_PATH . $admin_cffgf_style_path ) ) {
			wp_register_style(
				'cffgf-gf-admin',
				CFFGF_URL . $admin_cffgf_style_path,
				[],
				$gf_editor_asset['version']
			);
		}

		// Register optional frontend only styles.
		if ( file_exists( CFFGF_PATH . $frontend_style_path ) ) {
			wp_register_style(
				'cffgf-frontend',
				CFFGF_URL . $frontend_style_path,
				[],
				$frontend_asset['version']
			);
		}

		wp_set_script_translations( 'cffgf-gf-admin', 'colorful-fields-for-gravity-forms', plugin_dir_path( CFFGF_FILE ) . 'languages' );
	}

	/**
	 * Enqueue the cffgf admin assets.
	 *
	 * @param string $hook_suffix Current admin hook suffix.
	 */
	public function enqueue_admin_assets( $hook_suffix = '' ) {
		if ( ! $this->is_gravity_forms_admin_screen( $hook_suffix ) ) {
			return;
		}

		wp_enqueue_style( 'cffgf-admin' );
	}

	/**
	 * Enqueue the GF preview assets.
	 */
	public function enqueue_preview_assets() {
		wp_enqueue_style( 'cffgf-admin' );
	}

	/**
	 * Enqueue the block editor assets.
	 */
	public function enqueue_block_editor_assets() {
		wp_enqueue_style( 'cffgf-admin' );
	}

	/**
	 * Enqueue the gravity forms editor assets.
	 */
	public function enqueue_admin_cffgf_assets() {
		wp_enqueue_script( 'cffgf-gf-admin' );
		wp_enqueue_style( 'cffgf-gf-admin' );
	}

	/**
	 * Enqueue the frontend assets.
	 */
	public function wp_enqueue_scripts() {
		wp_enqueue_style( 'cffgf-frontend' );
	}

	/**
	 * Check whether the current admin screen belongs to Gravity Forms.
	 *
	 * @param string $hook_suffix Current admin hook suffix.
	 *
	 * @return bool
	 */
	private function is_gravity_forms_admin_screen( $hook_suffix ) {
		if ( false !== strpos( (string) $hook_suffix, 'gf_' ) || false !== strpos( (string) $hook_suffix, 'gravityforms' ) ) {
			return true;
		}

		if ( ! function_exists( 'get_current_screen' ) ) {
			return false;
		}

		$screen = get_current_screen();

		if ( ! $screen ) {
			return false;
		}

		return false !== strpos( (string) $screen->id, 'gf_' ) || false !== strpos( (string) $screen->id, 'gravityforms' );
	}
}
