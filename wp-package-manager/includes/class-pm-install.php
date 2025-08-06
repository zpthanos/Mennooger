<?php
// includes/class-pm-install.php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class PM_Install
 *
 * Handles plugin activation and deactivation: database table creation and cleanup.
 */
class PM_Install {

    /**
     * Run on plugin activation.
     */
    public static function activate() {
        global $wpdb;
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $charset_collate = $wpdb->get_charset_collate();
        $prefix = $wpdb->prefix;

        // Table: packages
        $sql[] = "CREATE TABLE {$prefix}pm_packages (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            title VARCHAR(255) NOT NULL,
            description LONGTEXT NOT NULL,
            price DECIMAL(10,2) NOT NULL DEFAULT '0.00',
            status VARCHAR(20) NOT NULL DEFAULT 'draft',
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY  (id)
        ) {$charset_collate};";

        // Table: submissions
        $sql[] = "CREATE TABLE {$prefix}pm_submissions (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            type VARCHAR(50) NOT NULL,
            user_id BIGINT UNSIGNED DEFAULT NULL,
            data LONGTEXT NOT NULL,
            status VARCHAR(20) NOT NULL DEFAULT 'pending',
            created_at DATETIME NOT NULL,
            PRIMARY KEY  (id)
        ) {$charset_collate};";

        // Table: payments
        $sql[] = "CREATE TABLE {$prefix}pm_payments (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            submission_id BIGINT UNSIGNED NOT NULL,
            gateway VARCHAR(50) NOT NULL,
            txn_id VARCHAR(100) NOT NULL,
            status VARCHAR(20) NOT NULL DEFAULT 'pending',
            amount DECIMAL(10,2) NOT NULL DEFAULT '0.00',
            created_at DATETIME NOT NULL,
            PRIMARY KEY  (id),
            KEY submission_idx (submission_id)
        ) {$charset_collate};";

        // Table: user_subscriptions
        $sql[] = "CREATE TABLE {$prefix}pm_user_subscriptions (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT UNSIGNED DEFAULT NULL,
            subscription_type VARCHAR(50) NOT NULL,
            start_date DATETIME NOT NULL,
            end_date DATETIME NOT NULL,
            status VARCHAR(20) NOT NULL DEFAULT 'pending',
            label VARCHAR(100) DEFAULT '',
            afm VARCHAR(20) DEFAULT '',
            meta_data LONGTEXT NOT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY  (id),
            KEY afm_idx (afm)
        ) {$charset_collate};";

        // Table: logs
        $sql[] = "CREATE TABLE {$prefix}pm_logs (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            timestamp DATETIME NOT NULL,
            user_id BIGINT UNSIGNED DEFAULT NULL,
            action VARCHAR(100) NOT NULL,
            object_type VARCHAR(50) NOT NULL,
            object_id BIGINT UNSIGNED DEFAULT NULL,
            ip VARCHAR(45) DEFAULT '',
            PRIMARY KEY  (id)
        ) {$charset_collate};";

        // Table: email_logs
        $sql[] = "CREATE TABLE {$prefix}pm_email_logs (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            timestamp DATETIME NOT NULL,
            template_key VARCHAR(100) NOT NULL,
            recipient VARCHAR(255) NOT NULL,
            subject VARCHAR(255) NOT NULL,
            status VARCHAR(20) NOT NULL,
            error_msg TEXT,
            PRIMARY KEY  (id)
        ) {$charset_collate};";

        // Execute all SQL statements
        foreach ( $sql as $statement ) {
            dbDelta( $statement );
        }

        // Clear any existing stats cache
        if ( class_exists( 'PM_Dashboard_Stats' ) ) {
            delete_transient( PM_Dashboard_Stats::TRANSIENT_KEY );
        }
    }

    /**
     * Run on plugin deactivation.
     */
    public static function deactivate() {
        if ( class_exists( 'PM_Dashboard_Stats' ) ) {
            delete_transient( PM_Dashboard_Stats::TRANSIENT_KEY );
        }
    }
}
