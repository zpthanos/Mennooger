<?php
// includes/classes/class-pm-updater.php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class PM_Updater
 *
 * Handles database table creation and migrations for WC Package Manager.
 */
class PM_Updater {
    /** Current schema version */
    const DB_VERSION = '1.0.0';

    /**
     * Run on plugin activation.
     * Creates or updates database tables.
     */
    public static function activate() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        $prefix = $wpdb->prefix;

        // Include WordPress upgrade functions
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        // wp_pm_packages
        $sql[] = "CREATE TABLE {$prefix}pm_packages (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            title varchar(191) NOT NULL,
            description text NOT NULL,
            price decimal(10,2) NOT NULL,
            status enum('published','draft') NOT NULL DEFAULT 'draft',
            created_at datetime NOT NULL,
            updated_at datetime NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        // wp_pm_submissions
        $sql[] = "CREATE TABLE {$prefix}pm_submissions (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            type enum('partner','interest','payment','subscription') NOT NULL,
            user_id bigint(20) unsigned NULL,
            data longtext NOT NULL,
            status enum('pending','completed','cancelled') NOT NULL DEFAULT 'pending',
            created_at datetime NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        // wp_pm_payments
        $sql[] = "CREATE TABLE {$prefix}pm_payments (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            submission_id bigint(20) unsigned NOT NULL,
            gateway varchar(32) NOT NULL,
            amount decimal(10,2) NOT NULL,
            recurring_period enum('μηνιαία','ετήσια') NULL,
            status enum('pending','success','failed','refunded') NOT NULL DEFAULT 'pending',
            txn_id varchar(128) NULL,
            created_at datetime NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        // wp_pm_user_subscriptions
        $sql[] = "CREATE TABLE {$prefix}pm_user_subscriptions (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            user_id bigint(20) unsigned NOT NULL,
            subscription_type enum('μηνιαία','ετήσια') NOT NULL,
            start_date date NOT NULL,
            end_date date NOT NULL,
            status enum('active','expired','pending') NOT NULL DEFAULT 'pending',
            label varchar(50) NULL,
            afm varchar(20) NULL,
            last_payment_id bigint(20) unsigned NULL,
            meta_data longtext NULL,
            created_at datetime NOT NULL,
            updated_at datetime NOT NULL,
            PRIMARY KEY  (id),
            KEY user_id (user_id),
            KEY status (status),
            KEY end_date (end_date)
        ) $charset_collate;";

        // wp_pm_logs
        $sql[] = "CREATE TABLE {$prefix}pm_logs (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            user_id bigint(20) unsigned NULL,
            action varchar(64) NOT NULL,
            object_type varchar(32) NOT NULL,
            object_id bigint(20) unsigned NOT NULL,
            ip varchar(45) NOT NULL,
            timestamp datetime NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        // wp_pm_email_logs
        $sql[] = "CREATE TABLE {$prefix}pm_email_logs (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            template_key varchar(64) NOT NULL,
            recipient longtext NOT NULL,
            subject longtext NOT NULL,
            status enum('queued','sent','failed') NOT NULL DEFAULT 'queued',
            error_msg longtext NULL,
            timestamp datetime NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        // Execute all table creation / updates
        foreach ( $sql as $table_sql ) {
            dbDelta( $table_sql );
        }

        // Store the DB version in options for future migrations
        add_option( 'wc_pm_db_version', self::DB_VERSION );
    }

    /**
     * Perform migration routines on plugin update.
     * Should be called on plugins_loaded.
     */
    public static function maybe_update() {
        $current_version = get_option( 'wc_pm_db_version', '0' );
        if ( version_compare( $current_version, self::DB_VERSION, '<' ) ) {
            // Example: future schema changes go here

            // Finally, update the stored version
            update_option( 'wc_pm_db_version', self::DB_VERSION );
        }
    }
}

// Hook migrations to plugins_loaded
add_action( 'plugins_loaded', [ 'PM_Updater', 'maybe_update' ] );
