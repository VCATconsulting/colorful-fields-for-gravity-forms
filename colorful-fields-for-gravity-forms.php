<?php
/**
 * Colorful Fields for Gravity Forms
 *
 * @package CFFGF
 * @author  VCAT Consulting GmbH
 * @license GPLv3
 *
 * @wordpress-plugin
 * Plugin Name: Colorful Fields for Gravity Forms
 * Plugin URI: https://github.com/VCATconsulting/colorful-fields-for-gravity-forms
 * Description: Colorful Fields for Gravity Forms allow you to select a color for field labels and a background color for fields.
 * Version: 1.0.3
 * Author: VCAT Consulting GmbH - Team WordPress
 * Author URI: https://www.vcat.de
 * Text Domain: colorful-fields-for-gravity-forms
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl-3.0.txt
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'CFFGF_VERSION', '1.0.3' );
define( 'CFFGF_FILE', __FILE__ );
define( 'CFFGF_PATH', plugin_dir_path( CFFGF_FILE ) );
define( 'CFFGF_URL', plugin_dir_url( CFFGF_FILE ) );

/*
 * The pre_init functions check the compatibility of the plugin and calls the init function, if check were successful.
 */
cffgf_pre_init();

/**
 * Pre init function to check the plugin's compatibility.
 */
function cffgf_pre_init() {
	/*
	 * Check, if the min. required PHP version is available and if not, show an admin notice.
	 */
	if ( version_compare( PHP_VERSION, '7.4', '<' ) ) {
		add_action( 'admin_notices', 'cffgf_min_php_version_error' );

		/*
		 * Stop the further processing of the plugin.
		 */
		return;
	}

	if ( file_exists( CFFGF_PATH . 'composer.json' ) && ! file_exists( CFFGF_PATH . 'vendor/autoload.php' ) ) {
		add_action( 'admin_notices', 'cffgf_autoloader_missing' );

		/*
		 * Stop the further processing of the plugin.
		 */
		return;
	} else {
		$autoloader = CFFGF_PATH . 'vendor/autoload.php';

		if ( is_readable( $autoloader ) ) {
			include $autoloader;
		}
	}

	/*
	 * If all checks were succcessful, load the plugin.
	 */
	require_once CFFGF_PATH . 'lib/load.php';
}

/**
 * Show an admin notice error message, if the PHP version is too low.
 */
function cffgf_min_php_version_error() {
	printf(
		'<div class="error"><p>%s</p></div>',
		esc_html__( 'Colorful Fields for Gravity Forms requires PHP version 7.4 or higher to function properly. Please upgrade PHP or deactivate Colorful Fields for Gravity Forms.', 'colorful-fields-for-gravity-forms' )
	);
}

/**
 * Show an admin notice error message, if the Composer autoloader is missing.
 */
function cffgf_autoloader_missing() {
	printf(
		'<div class="error"><p>%s</p></div>',
		esc_html__( 'Colorful Fields for Gravity Forms is missing the Composer autoloader file. Please run `composer install` in the root folder of the plugin or use a release version including the `vendor` folder.', 'colorful-fields-for-gravity-forms' )
	);
}
