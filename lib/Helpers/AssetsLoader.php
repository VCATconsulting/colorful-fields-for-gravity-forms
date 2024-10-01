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
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_assets' ], 11 );
		add_action( 'gform_preview_init', [ $this, 'enqueue_admin_assets' ], 11 );
		add_action( 'gform_editor_js', [ $this, 'enqueue_admin_cffgf_assets' ], 9999 );
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

		if ( file_exists( CFFGF_PATH . $admin_assets_path ) ) {
			$block_editor_asset = require CFFGF_PATH . $admin_assets_path;
		} else {
			$block_editor_asset = [
				'dependencies' => [
					'wp-i18n',
					'jquery',
					'wp-util',
					'block-editor',
				],
				'version'      => CFFGF_VERSION,
			];
		}

		if ( file_exists( CFFGF_PATH . $admin_cffgf_path ) ) {
			$gf_editor_asset = require CFFGF_PATH . $admin_cffgf_path;
		} else {
			$gf_editor_asset = [
				'dependencies' => [
					'wp-i18n',
					'jquery',
					'wp-util',
					'block-editor',
				],
				'version'      => CFFGF_VERSION,
			];
		}

		if ( file_exists( CFFGF_PATH . $frontend_assets_path ) ) {
			$frontend_asset = require CFFGF_PATH . $frontend_assets_path;
		} else {
			$frontend_asset = [
				'dependencies' => [],
				'version'      => CFFGF_VERSION,
			];
		}

		// Register optional editor only styles.
		if ( file_exists( CFFGF_PATH . $admin_editor_style_path ) ) {
			wp_register_style(
				'cffgf-admin',
				CFFGF_URL . $admin_editor_style_path,
				[],
				$block_editor_asset['version']
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
	 * Enqueue the block editor assets.
	 */
	public function enqueue_admin_assets() {
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
}
