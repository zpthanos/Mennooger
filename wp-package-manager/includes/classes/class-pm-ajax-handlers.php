<?php
// includes/class-pm-ajax-handlers.php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class PM_Ajax_Handlers
 *
 * Handles AJAX and admin-post endpoints:
 * - wp_ajax_pm_clear_dashboard_cache
 * - admin_post_pm_export
 * - admin_post_pm_import
 */
class PM_Ajax_Handlers {

    public function __construct() {
        add_action( 'wp_ajax_pm_clear_dashboard_cache', [ $this, 'clear_dashboard_cache' ] );
        add_action( 'admin_post_pm_export',               [ $this, 'handle_export' ] );
        add_action( 'admin_post_pm_import',               [ $this, 'handle_import' ] );
    }

    public function clear_dashboard_cache() {
        check_ajax_referer( 'pm_dashboard_clear', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( 'Insufficient permissions', 403 );
        }
        if ( class_exists( 'PM_Dashboard_Stats' ) ) {
            delete_transient( PM_Dashboard_Stats::TRANSIENT_KEY );
        }
        wp_send_json_success();
    }

    public function handle_export() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( __( 'Δεν έχετε άδεια', 'wc-pm' ) );
        }
        $type = isset( $_GET['type'] ) ? sanitize_text_field( $_GET['type'] ) : '';
        global $wpdb;
        switch ( $type ) {
            case 'packages':
                $table = $wpdb->prefix . 'pm_packages';
                break;
            case 'submissions':
                $table = $wpdb->prefix . 'pm_submissions';
                break;
            case 'payments':
                $table = $wpdb->prefix . 'pm_payments';
                break;
            default:
                wp_die( __( 'Μη έγκυρος τύπος εξαγωγής', 'wc-pm' ) );
        }
        $rows = $wpdb->get_results( "SELECT * FROM {$table}", ARRAY_A );
        if ( empty( $rows ) ) {
            wp_die( __( 'Δεν υπάρχουν δεδομένα προς εξαγωγή', 'wc-pm' ) );
        }
        header( 'Content-Type: text/csv; charset=utf-8' );
        header( 'Content-Disposition: attachment; filename=' . $type . '-' . date( 'Y-m-d' ) . '.csv' );
        $out = fopen( 'php://output', 'w' );
        fputcsv( $out, array_keys( $rows[0] ) );
        foreach ( $rows as $row ) {
            fputcsv( $out, array_values( $row ) );
        }
        fclose( $out );
        exit;
    }

    public function handle_import() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( __( 'Δεν έχετε άδεια', 'wc-pm' ) );
        }
        if ( empty( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'pm_import_nonce' ) ) {
            wp_die( __( 'Μη έγκυρο nonce', 'wc-pm' ) );
        }
        if ( empty( $_POST['import_type'] ) || empty( $_FILES['import_file'] ) ) {
            wp_die( __( 'Παρακαλώ επιλέξτε τύπο και αρχείο', 'wc-pm' ) );
        }
        $type = sanitize_text_field( $_POST['import_type'] );
        $file = $_FILES['import_file']['tmp_name'];
        global $wpdb;
        switch ( $type ) {
            case 'packages':
                $table = $wpdb->prefix . 'pm_packages';
                break;
            case 'submissions':
                $table = $wpdb->prefix . 'pm_submissions';
                break;
            case 'payments':
                $table = $wpdb->prefix . 'pm_payments';
                break;
            default:
                wp_die( __( 'Μη έγκυρος τύπος εισαγωγής', 'wc-pm' ) );
        }
        $ext = pathinfo( $_FILES['import_file']['name'], PATHINFO_EXTENSION );
        $data = [];
        if ( 'json' === strtolower( $ext ) ) {
            $data = json_decode( file_get_contents( $file ), true ) ?: [];
        } else {
            $rows   = array_map( 'str_getcsv', file( $file ) );
            $header = array_shift( $rows );
            foreach ( $rows as $row ) {
                $data[] = array_combine( $header, $row );
            }
        }
        if ( empty( $data ) ) {
            wp_die( __( 'Δεν βρέθηκαν δεδομένα προς εισαγωγή', 'wc-pm' ) );
        }
        foreach ( $data as $row ) {
            $cols = array_map( [ $wpdb, 'esc_sql' ], array_keys( $row ) );
            $vals = array_values( $row );
            $placeholders = implode( ',', array_fill( 0, count( $vals ), '%s' ) );
            $col_list     = '`' . implode( '`,`', $cols ) . '`';
            $wpdb->query(
                $wpdb->prepare(
                    "INSERT INTO {$table} ({$col_list}) VALUES ({$placeholders})",
                    $vals
                )
            );
        }
        wp_redirect( admin_url( 'admin.php?page=pm-settings-general&pm_import=success' ) );
        exit;
    }
}

new PM_Ajax_Handlers();
