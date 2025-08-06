<?php
// includes/classes/class-pm-admin-menu.php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class PM_Admin_Menu
 *
 * Registers the MANAGER admin menu and its subpages.
 */
class PM_Admin_Menu {
    /** Capability required to access menus */
    const CAPABILITY = 'manage_options';
    /** Parent slug for MANAGER menu */
    const MENU_SLUG  = 'pm-dashboard';

    public function __construct() {
        add_action( 'admin_menu', [ $this, 'register_menu' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
    }

    /** Register top-level menu and submenus */
    public function register_menu() {
        // Top-level menu
        add_menu_page(
            __( 'MANAGER', 'wc-pm' ),
            __( 'MANAGER', 'wc-pm' ),
            self::CAPABILITY,
            self::MENU_SLUG,
            [ $this, 'render_dashboard' ],
            'dashicons-admin-generic',
            56
        );

        // Submenus
        $subs = [
            [ 'Dashboard',          'render_dashboard',           self::MENU_SLUG ],
            [ 'Πακέτα',            'render_packages',            'pm-packages' ],
            [ 'Υποβολές',          'render_submissions',         'pm-submissions' ],
            [ 'Πληρωμές',          'render_payments',            'pm-payments' ],
            [ 'Συνδρομές Χρηστών', 'render_user_subscriptions',  'pm-user-subscriptions' ],
            [ 'Email Templates',   'render_email_templates',     'pm-email-templates' ],
            [ 'Settings',          'render_settings_general',    'pm-settings-general' ],
            [ 'Backup/Export',     'render_settings_backup',     'pm-settings-backup' ],
            [ 'Trash',             'render_trash',               'pm-trash' ],
            [ 'Debug',             'render_debug',               'pm-debug' ],
        ];

        foreach ( $subs as $sub ) {
            list( $title, $method, $slug ) = $sub;
            // Skip re-adding the parent page
            if ( $slug === self::MENU_SLUG ) {
                continue;
            }
            add_submenu_page(
                self::MENU_SLUG,
                __( $title, 'wc-pm' ),
                __( $title, 'wc-pm' ),
                self::CAPABILITY,
                $slug,
                [ $this, $method ]
            );
        }
    }

    /** Enqueue admin CSS/JS on our plugin admin pages */
    public function enqueue_assets( $hook ) {
        $screen = get_current_screen();
        if ( isset( $screen->parent_file ) && $screen->parent_file === 'admin.php' && strpos( $screen->id, 'pm-' ) !== false ) {
            wp_enqueue_style( 'wc-pm-admin', WC_PM_URL . 'assets/css/admin.css', [], WC_PM_VERSION );
            wp_enqueue_script( 'wc-pm-admin', WC_PM_URL . 'assets/js/admin-dashboard.js', [ 'jquery' ], WC_PM_VERSION, true );
        }
    }

    /** Render Dashboard page */
    public function render_dashboard() {
        include WC_PM_PATH . 'templates/admin/stats.php';
    }

    /** Render Packages management page */
    public function render_packages() {
        include WC_PM_PATH . 'templates/admin/packages-list.php';
    }

    /** Render Submissions page */
    public function render_submissions() {
        include WC_PM_PATH . 'templates/admin/submissions.php';
    }

    /** Render Payments page */
    public function render_payments() {
        include WC_PM_PATH . 'templates/admin/payments.php';
    }

    /** Render User Subscriptions page */
    public function render_user_subscriptions() {
        include WC_PM_PATH . 'templates/admin/user-dashboard.php';
    }

    /** Render Email Templates page */
    public function render_email_templates() {
        include WC_PM_PATH . 'templates/admin/settings-emails.php';
    }

    /** Render General Settings page */
    public function render_settings_general() {
        include WC_PM_PATH . 'templates/admin/settings-general.php';
    }

    /** Render Backup/Export page */
    public function render_settings_backup() {
        include WC_PM_PATH . 'templates/admin/settings-backup.php';
    }

    /** Render Trash page */
    public function render_trash() {
        include WC_PM_PATH . 'templates/admin/trash.php';
    }

    /** Render Debug page */
    public function render_debug() {
        include WC_PM_PATH . 'templates/admin/debug.php';
    }
}

