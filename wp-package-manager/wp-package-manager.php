<?php
/**
 * Plugin Name:       WP Package Manager
 * Plugin URI:        https://github.com/zpthanos/Mennooger.git
 * Description:       Package manager – Διαχείριση πακέτων / πληρωμών
 * Version:           0.0.1
 * Author:            Entercity
 * Author URI:        https://example.com
 * Text Domain:       wc-pm
 * Domain Path:       /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/* ------------------------------------------------------------------ */
/*  Constants                                                         */
/* ------------------------------------------------------------------ */
define( 'WC_PM_VERSION',      '1.0.0' );
define( 'WC_PM_PLUGIN_FILE',  __FILE__ );
define( 'WC_PM_PATH',         plugin_dir_path( __FILE__ ) );
define( 'WC_PM_URL',          plugin_dir_url(  __FILE__ ) );
define( 'WC_PM_TEXT_DOMAIN',  'wc-pm' );

/* ------------------------------------------------------------------ */
/*  Translations (load on init)                                       */
/* ------------------------------------------------------------------ */
add_action( 'init', function () {
    load_plugin_textdomain(
        WC_PM_TEXT_DOMAIN,
        false,
        dirname( plugin_basename( WC_PM_PLUGIN_FILE ) ) . '/languages'
    );
}, 0 );

/* ------------------------------------------------------------------ */
/*  Helper: safe include (avoids WSOD on missing file)                */
/* ------------------------------------------------------------------ */
function wc_pm_safe_require( $file ) {
    if ( file_exists( $file ) ) {
        require_once $file;
        return true;
    }
    error_log( "[WP-Package-Manager] Missing file: {$file}" );
    return false;
}

/* ------------------------------------------------------------------ */
/*  Core includes                                                     */
/* ------------------------------------------------------------------ */
$includes_ok =
      wc_pm_safe_require( WC_PM_PATH . 'includes/classes/class-pm-install.php' )
  &&  wc_pm_safe_require( WC_PM_PATH . 'includes/classes/class-pm-settings.php' )
  &&  wc_pm_safe_require( WC_PM_PATH . 'includes/classes/class-pm-admin-menu.php' )
  &&  wc_pm_safe_require( WC_PM_PATH . 'includes/classes/class-pm-shortcodes.php' )
  &&  wc_pm_safe_require( WC_PM_PATH . 'includes/classes/class-pm-email-templates.php' )
  &&  wc_pm_safe_require( WC_PM_PATH . 'includes/classes/class-pm-logger.php' )
  &&  wc_pm_safe_require( WC_PM_PATH . 'includes/classes/class-pm-ajax-handlers.php' )
  &&  wc_pm_safe_require( WC_PM_PATH . 'includes/classes/class-pm-package-crud.php' )
  &&  wc_pm_safe_require( WC_PM_PATH . 'includes/classes/class-pm-partner-form.php' )
  &&  wc_pm_safe_require( WC_PM_PATH . 'includes/classes/class-pm-interest-form.php' )
  &&  wc_pm_safe_require( WC_PM_PATH . 'includes/classes/class-pm-payment-form.php' );

/*  List-table classes */
foreach ( glob( WC_PM_PATH . 'includes/classes/class-pm-*-table.php' ) as $file ) {
    $includes_ok &= wc_pm_safe_require( $file );
}

/*  Helpers */
$includes_ok &=
      wc_pm_safe_require( WC_PM_PATH . 'includes/helpers/validate.php' )
  &&  wc_pm_safe_require( WC_PM_PATH . 'includes/helpers/sanitize.php' )
  &&  wc_pm_safe_require( WC_PM_PATH . 'includes/helpers.php' );

/* ------------------------------------------------------------------ */
/*  Abort if a required file was missing                              */
/* ------------------------------------------------------------------ */
if ( ! $includes_ok ) {
    // De-activate plugin to keep the site online
    add_action( 'admin_init', function () {
        deactivate_plugins( plugin_basename( WC_PM_PLUGIN_FILE ) );
    } );
    return;
}

/* ------------------------------------------------------------------ */
/*  Activation / Deactivation                                         */
/* ------------------------------------------------------------------ */
register_activation_hook(   WC_PM_PLUGIN_FILE, [ 'PM_Install', 'activate' ] );
register_deactivation_hook( WC_PM_PLUGIN_FILE, [ 'PM_Install', 'deactivate' ] );

/* ------------------------------------------------------------------ */
/*  Initialise inside try/catch                                       */
/* ------------------------------------------------------------------ */
add_action( 'plugins_loaded', function () {

    try {

        new PM_Admin_Menu();
        new PM_Settings();
        new PM_Shortcodes();
        new PM_Email_Templates();
        new PM_Logger();
        new PM_Ajax_Handlers();
        new PM_Package_CRUD();

        /* Front-end forms */
        new PM_Partner_Form();
        new PM_Interest_Form();
        new PM_Payment_Form();

    } catch ( Throwable $e ) {

        error_log( '[WP-Package-Manager] Bootstrap fatal: ' . $e->getMessage() );

        // De-activate to avoid WSOD
        deactivate_plugins( plugin_basename( WC_PM_PLUGIN_FILE ) );
    }
}, 1 );

/* ------------------------------------------------------------------ */
/*  Optional: SMTP fallback (comment out if you install WP Mail SMTP) */
/* ------------------------------------------------------------------ */
/*
add_action( 'phpmailer_init', function ( $phpmailer ) {
    $phpmailer->isSMTP();
    $phpmailer->Host       = 'smtp.example.com';
    $phpmailer->SMTPAuth   = true;
    $phpmailer->Username   = 'user@example.com';
    $phpmailer->Password   = 'password';
    $phpmailer->SMTPSecure = 'tls';
    $phpmailer->Port       = 587;
} );
*/
