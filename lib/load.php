<?php
/**
 * Main plugin file to load other classes
 *
 * @package CFFGF
 */

namespace CFFGF;

use CFFGF\Helpers\ColorfulFields;
use CFFGF\Helpers\AssetsLoader;

/**
 * Init function of the plugin
 */
function init() {
	/*
	 * Only initialize the plugin when GravityForms is active.
	 */
	if ( ! class_exists( 'GFCommon' ) ) {
		return;
	}

	/*
	 * Construct all modules to initialize.
	 */
	$modules = [
		'helpers_assets_loader'   => new AssetsLoader(),
		'helpers_colorful_fields' => new ColorfulFields(),
	];

	/*
	 * Initialize all modules.
	 */
	foreach ( $modules as $module ) {
		if ( is_callable( [ $module, 'init' ] ) ) {
			call_user_func( [ $module, 'init' ] );
		}
	}
}

add_action( 'plugins_loaded', 'CFFGF\init' );
