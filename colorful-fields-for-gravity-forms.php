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
 * Version: 1.1.0
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

if ( ! defined( 'CFFGF_VERSION' ) ) {
	define( 'CFFGF_VERSION', '1.1.0' );
}
if ( ! defined( 'CFFGF_FILE' ) ) {
	define( 'CFFGF_FILE', __FILE__ );
}
if ( ! defined( 'CFFGF_PATH' ) ) {
	define( 'CFFGF_PATH', plugin_dir_path( CFFGF_FILE ) );
}
if ( ! defined( 'CFFGF_URL' ) ) {
	define( 'CFFGF_URL', plugin_dir_url( CFFGF_FILE ) );
}

/*
 * The pre_init functions check the compatibility of the plugin and calls the init function, if check were successful.
 */
add_action( 'plugins_loaded', 'cffgf_load_textdomain' );
cffgf_pre_init();

/**
 * Pre-init function to check compatibility and bootstrap plugin modules.
 *
 * @return string
 */
function cffgf_pre_init() {
	$state = cffgf_ensure_ready( true );

	if ( 'ok' !== $state ) {
		if ( 'php_too_old' === $state ) {
			add_action( 'admin_notices', 'cffgf_min_php_version_error' );
		} elseif ( 'autoloader_missing' === $state ) {
			add_action( 'admin_notices', 'cffgf_autoloader_missing' );
		}

		return $state;
	}

	return 'ok';
}

/**
 * Ensure requirements are met and plugin files are loaded once.
 *
 * @param bool $load Whether to load plugin files after successful checks.
 *
 * @return string
 */
function cffgf_ensure_ready( $load = true ) {
	static $bootstrapped = false;

	if ( version_compare( PHP_VERSION, '7.4', '<' ) ) {
		return 'php_too_old';
	}

	$composer_file = CFFGF_PATH . 'composer.json';
	$autoloader    = CFFGF_PATH . 'vendor/autoload.php';

	if ( file_exists( $composer_file ) && ! is_readable( $autoloader ) ) {
		return 'autoloader_missing';
	}

	if ( ! $load || $bootstrapped ) {
		return 'ok';
	}

	if ( is_readable( $autoloader ) ) {
		require_once $autoloader;
	}

	require_once CFFGF_PATH . 'lib/load.php';

	$bootstrapped = true;

	return 'ok';
}

/**
 * Return a translated error message for a requirement state.
 *
 * @param string $state Requirement state.
 *
 * @return string
 */
function cffgf_get_error_message( $state ) {
	if ( 'php_too_old' === $state ) {
		return __( 'Colorful Fields for Gravity Forms requires PHP version 7.4 or higher to function properly. Please upgrade PHP or deactivate Colorful Fields for Gravity Forms.', 'colorful-fields-for-gravity-forms' );
	}

	if ( 'autoloader_missing' === $state ) {
		return __( 'Colorful Fields for Gravity Forms is missing the Composer autoloader file. Please run "composer install --no-dev -o" in the root folder of the plugin or use a release version including the "vendor" folder.', 'colorful-fields-for-gravity-forms' );
	}

	return __( 'Colorful Fields for Gravity Forms cannot be initialized due to an unknown requirement error.', 'colorful-fields-for-gravity-forms' );
}

/**
 * Load plugin textdomain.
 *
 * @since 1.0.0
 */
function cffgf_load_textdomain() {
	load_plugin_textdomain( 'colorful-fields-for-gravity-forms', false, basename( __DIR__ ) . '/languages' );
}

/**
 * Show an admin notice error message, if the PHP version is too low.
 */
function cffgf_min_php_version_error() {
	if ( ! is_admin() || ! current_user_can( 'activate_plugins' ) ) {
		return;
	}

	echo '<div class="notice notice-error"><p>';
	echo esc_html( cffgf_get_error_message( 'php_too_old' ) );
	echo '</p></div>';
}

/**
 * Show an admin notice error message, if the Composer autoloader is missing.
 */
function cffgf_autoloader_missing() {
	if ( ! is_admin() || ! current_user_can( 'activate_plugins' ) ) {
		return;
	}

	echo '<div class="notice notice-error"><p>';
	echo esc_html( cffgf_get_error_message( 'autoloader_missing' ) );
	echo '</p></div>';
}
