<?php
// includes/classes/class-pm-init.php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class PM_Init
 *
 * Bootstraps the WC Package Manager plugin:
 * - Loads text domain for translations
 * - Registers activation/deactivation hooks
 * - Initializes core components (shortcodes, settings, admin menu, etc.)
 */
class PM_Init {
    /** Singleton instance */
    private static $instance = null;

    /** Plugin version */
    public $version;

    /** Constructor is private to enforce singleton */
    private function __construct() {
        $this->version = WC_PM_VERSION;
    }

    /**
     * Get singleton instance
     *
     * @return PM_Init
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Kick off plugin initialization
     */
    public function run() {
        $this->load_textdomain();
        $this->register_hooks();
        $this->init_components();
    }

    /**
     * Load plugin textdomain for translations
     */
    private function load_textdomain() {
        load_plugin_textdomain( 'wc-pm', false, dirname( plugin_basename( __FILE__ ) ) . '/../../languages' );
    }

    /**
     * Register activation and deactivation hooks
     */
    private function register_hooks() {
        register_activation_hook( WC_PM_PATH . 'wc-package-manager.php', [ $this, 'activate' ] );
        register_deactivation_hook( WC_PM_PATH . 'wc-package-manager.php', [ $this, 'deactivate' ] );
    }

    /**
     * Activation callback: create/update DB tables
     */
    public function activate() {
        if ( class_exists( 'PM_Updater' ) ) {
            PM_Updater::activate();
        }
    }

    /**
     * Deactivation callback: clear scheduled tasks if any
     */
    public function deactivate() {
        // Currently no scheduled tasks
    }

    /**
     * Initialize core components
     */
    private function init_components() {
        // Logger
        new PM_Logger();

        // Settings
        new PM_Settings();

        // Shortcodes
        new PM_Shortcodes();

        // Admin menu
        if ( is_admin() ) {
            new PM_Admin_Menu();
            new PM_Email_Settings_Page();
        }

        // Frontend forms & listing
        new PM_Package_CRUD();
        new PM_Form_Renderer();
        new PM_Subscription_Wizard();
        new PM_Partner_Form();
        new PM_Interest_Form();
        new PM_Payment_Form();
        new PM_AFM_Lookup();

        // Payment gateway callbacks
        new PM_Payment_Gateway();

        // Dashboard stats
        if ( is_admin() ) {
            new PM_Dashboard_Stats();
        }
    }
}

// Kick off PM_Init singleton via PM_Init::get_instance()->run()
add_action( 'plugins_loaded', function() {
    PM_Init::get_instance()->run();
});
