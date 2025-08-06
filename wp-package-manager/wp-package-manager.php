<?php
/**
 * Plugin Name:  WP Package Manager – Phase-2
 * Version:      0.0.0-phase2
 * Author:       Entercity
 * Text Domain:  wc-pm
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* -------------------------------------------------------------------------- */
/*  Constants                                                                 */
/* -------------------------------------------------------------------------- */
define( 'WC_PM_VERSION',      '0.0.0-phase2' );
define( 'WC_PM_PLUGIN_FILE',  __FILE__ );
define( 'WC_PM_PATH',         plugin_dir_path( __FILE__ ) );
define( 'WC_PM_TEXT_DOMAIN',  'wc-pm' );

/* -------------------------------------------------------------------------- */
/*  Translations                                                              */
/* -------------------------------------------------------------------------- */
add_action( 'init', function () {
	load_plugin_textdomain(
		WC_PM_TEXT_DOMAIN,
		false,
		dirname( plugin_basename( WC_PM_PLUGIN_FILE ) ) . '/languages'
	);
}, 0 );

/* -------------------------------------------------------------------------- */
/*  Safe-require helper                                                       */
/* -------------------------------------------------------------------------- */
function wc_pm_safe_require( $file ) {
	if ( file_exists( $file ) ) {
		require_once $file;
		return true;
	}
	error_log( "[WP-Package-Manager] Missing file: $file" );
	return false;
}

/* -------------------------------------------------------------------------- */
/*  Phase-1 : core-admin libraries                                            */
/* -------------------------------------------------------------------------- */
$includes_ok  =  true;  // initialise once

$includes_ok &= wc_pm_safe_require( WC_PM_PATH . 'includes/class-pm-install.php' );
$includes_ok &= wc_pm_safe_require( WC_PM_PATH . 'includes/classes/class-pm-settings.php' );
$includes_ok &= wc_pm_safe_require( WC_PM_PATH . 'includes/classes/class-pm-admin-menu.php' );
$includes_ok &= wc_pm_safe_require( WC_PM_PATH . 'includes/classes/class-pm-shortcodes.php' );
$includes_ok &= wc_pm_safe_require( WC_PM_PATH . 'includes/classes/class-pm-email-templates.php' );
$includes_ok &= wc_pm_safe_require( WC_PM_PATH . 'includes/classes/class-pm-logger.php' );
$includes_ok &= wc_pm_safe_require( WC_PM_PATH . 'includes/classes/class-pm-ajax-handlers.php' );

/* Helpers */
$includes_ok &= wc_pm_safe_require( WC_PM_PATH . 'includes/helpers/validate.php' );
$includes_ok &= wc_pm_safe_require( WC_PM_PATH . 'includes/helpers/sanitize.php' );
$includes_ok &= wc_pm_safe_require( WC_PM_PATH . 'includes/helpers.php' );

if ( ! $includes_ok ) {
	add_action( 'admin_init', function () {
		deactivate_plugins( plugin_basename( WC_PM_PLUGIN_FILE ) );
	} );
	return;
}

/* -------------------------------------------------------------------------- */
/*  Activation / Deactivation                                                 */
/* -------------------------------------------------------------------------- */
register_activation_hook( WC_PM_PLUGIN_FILE, [ 'PM_Install', 'activate' ] );
register_deactivation_hook( WC_PM_PLUGIN_FILE, [ 'PM_Install', 'deactivate' ] );

/* -------------------------------------------------------------------------- */
/*  Boot Phase-1 classes (admin)                                              */
/* -------------------------------------------------------------------------- */
add_action( 'plugins_loaded', function () {
	try {
		new PM_Admin_Menu();
		new PM_Settings();
		new PM_Shortcodes();
		new PM_Email_Templates();
		new PM_Logger();
		new PM_Ajax_Handlers();
	} catch ( Throwable $e ) {
		error_log( '[WP-Package-Manager] Phase-1 fatal: ' . $e->getMessage() );
		deactivate_plugins( plugin_basename( WC_PM_PLUGIN_FILE ) );
	}
}, 1 );

/* -------------------------------------------------------------------------- */
/*  Phase-2 : front-end form classes                                          */
/* -------------------------------------------------------------------------- */
add_action( 'plugins_loaded', function () {

	$ok = true;
	$ok &= wc_pm_safe_require( WC_PM_PATH . 'includes/classes/class-pm-partner-form.php' );
	$ok &= wc_pm_safe_require( WC_PM_PATH . 'includes/classes/class-pm-interest-form.php' );
	$ok &= wc_pm_safe_require( WC_PM_PATH . 'includes/classes/class-pm-payment-form.php' );

	if ( ! $ok ) {
		error_log( '[WP-Package-Manager] Missing form class file.' );
		deactivate_plugins( plugin_basename( WC_PM_PLUGIN_FILE ) );
		return;
	}

	try {
		new PM_Partner_Form();
		new PM_Interest_Form();
		new PM_Payment_Form();
	} catch ( Throwable $e ) {
		error_log( '[WP-Package-Manager] Phase-2 fatal: ' . $e->getMessage() );
		deactivate_plugins( plugin_basename( WC_PM_PLUGIN_FILE ) );
	}
}, 2 );
/* -------------------------------------------------------------------------- */
/*  Phase-3 : admin list-table classes (Packages, Payments, Submissions, UX)  */
/* -------------------------------------------------------------------------- */
add_action( 'admin_init', function () {

	/*
	 * Ensure the WP core list-table base class is available.
	 * On front-end requests this hook never runs, so no overhead.
	 */
	if ( ! class_exists( 'WP_List_Table' ) ) {
		require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
	}

	$ok = true;

	foreach ( [
		'includes/classes/class-pm-packages-table.php',
		'includes/classes/class-pm-payments-table.php',
		'includes/classes/class-pm-submissions-table.php',
		'includes/classes/class-pm-user-subscriptions-table.php',
	] as $rel_path ) {
		$ok &= wc_pm_safe_require( WC_PM_PATH . $rel_path );
	}

	if ( ! $ok ) {
		error_log( '[WP-Package-Manager] One or more list-table files missing.' );
		deactivate_plugins( plugin_basename( WC_PM_PLUGIN_FILE ) );
		return;
	}

	 
}, 3 );   

