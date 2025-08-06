<?php
/**
 * Uninstall script for WP Package Manager
 *
 * Cleans up database tables and options on plugin uninstall.
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

global $wpdb;

// Define table names
$prefix = $wpdb->prefix;
$tables = [
    "{$prefix}pm_packages",
    "{$prefix}pm_submissions",
    "{$prefix}pm_payments",
    "{$prefix}pm_user_subscriptions",
    "{$prefix}pm_logs",
    "{$prefix}pm_email_logs",
];

// Drop tables
foreach ( $tables as $table ) {
    $wpdb->query( "DROP TABLE IF EXISTS {$table}" );
}

// Delete plugin options
$options = [
    'pm_currency_symbol',
    'pm_decimals',
    'pm_currency_position',
    'pm_listing_style',
    'pm_per_page',
    'pm_section_general',
    // Shortcodes
    'pm_shortcode_listing',
    'pm_shortcode_single',
    'pm_shortcode_partner',
    'pm_shortcode_interest',
    'pm_shortcode_payment',
    'pm_shortcode_afm_lookup',
    'pm_shortcode_wizard',
];
foreach ( $options as $opt ) {
    delete_option( $opt );
}

// Clear transients
if ( class_exists( 'PM_Dashboard_Stats' ) ) {
    delete_transient( PM_Dashboard_Stats::TRANSIENT_KEY );
}
