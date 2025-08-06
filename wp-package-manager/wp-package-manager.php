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
/*  Text-domain (load at init, not too early)                         */
/* ------------------------------------------------------------------ */
add_action( 'init', function () {
    load_plugin_textdomain(
        WC_PM_TEXT_DOMAIN,
        false,
        dirname( plugin_basename( WC_PM_PLUGIN_FILE ) ) . '/languages'
    );
}, 0 );

/* ------------------------------------------------------------------ */
/*  Core includes                                                     */
/* ------------------------------------------------------------------ */
require_once WC_PM_PATH . 'includes/class-pm-install.php';

require_once WC_PM_PATH . 'includes/classes/class-pm-settings.php';
require_once WC_PM_PATH . 'includes/classes/class-pm-admin-menu.php';
require_once WC_PM_PATH . 'includes/classes/class-pm-shortcodes.php';
require_once WC_PM_PATH . 'includes/classes/class-pm-email-templates.php';
require_once WC_PM_PATH . 'includes/classes/class-pm-logger.php';
require_once WC_PM_PATH . 'includes/classes/class-pm-ajax-handlers.php';

/*  Front-end form classes (if they no longer self-instantiate) */
require_once WC_PM_PATH . 'includes/classes/class-pm-partner-form.php';
require_once WC_PM_PATH . 'includes/classes/class-pm-interest-form.php';
require_once WC_PM_PATH . 'includes/classes/class-pm-payment-form.php';

/*  Any extra WP_List_Table or helper classes */
foreach ( glob( WC_PM_PATH . 'includes/classes/class-pm-*-table.php' ) as $file ) {
    require_once $file;
}

/*  Helpers  */
require_once WC_PM_PATH . 'includes/helpers/validate.php';
require_once WC_PM_PATH . 'includes/helpers/sanitize.php';
require_once WC_PM_PATH . 'includes/helpers.php';

/* ------------------------------------------------------------------ */
/*  Activation / Deactivation                                         */
/* ------------------------------------------------------------------ */
register_activation_hook(   WC_PM_PLUGIN_FILE, [ 'PM_Install', 'activate' ] );
register_deactivation_hook( WC_PM_PLUGIN_FILE, [ 'PM_Install', 'deactivate' ] );

/* ------------------------------------------------------------------ */
/*  Single instantiation                                              */
/* ------------------------------------------------------------------ */
new PM_Admin_Menu();
new PM_Settings();
new PM_Shortcodes();
new PM_Email_Templates();
new PM_Logger();
new PM_Ajax_Handlers();

new PM_Partner_Form();
new PM_Interest_Form();
new PM_Payment_Form();
