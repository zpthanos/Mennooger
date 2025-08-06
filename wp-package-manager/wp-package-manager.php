<?php
/**
 * Plugin Name:       wp-package-manager
 * Plugin URI:        https://github.com/zpthanos/Mennooger.git
 * Description:       Package manager - Διαχείριση πακέτων / πληρωμών
 * Version:           0.0.1
 * Author:            Entercity
 * Author URI:        https://example.com
 * Text Domain:       wc-pm
 * Domain Path:       /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Plugin version
define( 'WC_PM_VERSION', '1.0.0' );
define( 'WC_PM_PLUGIN_FILE', __FILE__ );
define( 'WC_PM_PATH',       plugin_dir_path( __FILE__ ) );
define( 'WC_PM_URL',        plugin_dir_url( __FILE__ ) );
define( 'WC_PM_TEXT_DOMAIN', 'wc-pm' );

// Load translations
function wc_pm_load_textdomain() {
    load_plugin_textdomain(
        WC_PM_TEXT_DOMAIN,
        false,
        dirname( plugin_basename( WC_PM_PLUGIN_FILE ) ) . '/languages'
    );
}
add_action( 'plugins_loaded', 'wc_pm_load_textdomain' );

// Include installer (dbDelta)
require_once WC_PM_PATH . 'includes/classes/class-pm-install.php';

// Include core settings and shortcodes
require_once WC_PM_PATH . 'includes/classes/class-pm-settings.php';
require_once WC_PM_PATH . 'includes/classes/class-pm-shortcodes.php';

// Include email template loader and logger
require_once WC_PM_PATH . 'includes/classes/class-pm-email-templates.php';
require_once WC_PM_PATH . 'includes/classes/class-pm-logger.php';

// Include AJAX & admin-post handlers
require_once WC_PM_PATH . 'includes/classes/class-pm-ajax-handlers.php';


// Include all other class files
foreach ( glob( WC_PM_PATH . 'includes/classes/class-pm-*.php' ) as $file ) {
    if ( ! in_array( basename( $file ), [ 'class-pm-email-templates.php', 'class-pm-logger.php' ], true ) ) {
        require_once $file;
    }
}

// Include helpers
require_once WC_PM_PATH . 'includes/helpers/sanitize.php';
require_once WC_PM_PATH . 'includes/helpers/validate.php';
require_once WC_PM_PATH . 'includes/helpers.php';

// Activation / Deactivation
register_activation_hook( WC_PM_PLUGIN_FILE, [ 'PM_Install', 'activate' ] );
register_deactivation_hook( WC_PM_PLUGIN_FILE, [ 'PM_Install', 'deactivate' ] );

// Initialize settings & shortcodes
new PM_Settings();
new PM_Shortcodes();

// Initialize email templates & logger & AJAX handlers
new PM_Email_Templates();
new PM_Logger();
new PM_Ajax_Handlers();
