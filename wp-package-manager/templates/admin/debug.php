<?php
// templates/admin/debug.php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Fetch logs and email logs
global $wpdb;
$action_logs = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}pm_logs ORDER BY timestamp DESC LIMIT 50", ARRAY_A );
$email_logs  = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}pm_email_logs ORDER BY timestamp DESC LIMIT 50", ARRAY_A );
?>
<div class="wrap">
    <h1><?php esc_html_e( 'Debug', 'wc-pm' ); ?></h1>

    <h2><?php esc_html_e( 'Action Logs', 'wc-pm' ); ?></h2>
    <table class="widefat fixed striped">
        <thead>
            <tr>
                <th><?php esc_html_e( 'Χρόνος', 'wc-pm' ); ?></th>
                <th><?php esc_html_e( 'User ID', 'wc-pm' ); ?></th>
                <th><?php esc_html_e( 'Δράση', 'wc-pm' ); ?></th>
                <th><?php esc_html_e( 'Τύπος', 'wc-pm' ); ?></th>
                <th><?php esc_html_e( 'Αντικείμενο ID', 'wc-pm' ); ?></th>
                <th><?php esc_html_e( 'IP', 'wc-pm' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ( $action_logs as $log ) : ?>
                <tr>
                    <td><?php echo esc_html( $log['timestamp'] ); ?></td>
                    <td><?php echo esc_html( $log['user_id'] ?: '-' ); ?></td>
                    <td><?php echo esc_html( $log['action'] ); ?></td>
                    <td><?php echo esc_html( $log['object_type'] ); ?></td>
                    <td><?php echo esc_html( $log['object_id'] ); ?></td>
                    <td><?php echo esc_html( $log['ip'] ); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h2><?php esc_html_e( 'Email Logs', 'wc-pm' ); ?></h2>
    <table class="widefat fixed striped">
        <thead>
            <tr>
                <th><?php esc_html_e( 'Χρόνος', 'wc-pm' ); ?></th>
                <th><?php esc_html_e( 'Template Key', 'wc-pm' ); ?></th>
                <th><?php esc_html_e( 'Παραλήπτης', 'wc-pm' ); ?></th>
                <th><?php esc_html_e( 'Θέμα', 'wc-pm' ); ?></th>
                <th><?php esc_html_e( 'Κατάσταση', 'wc-pm' ); ?></th>
                <th><?php esc_html_e( 'Σφάλμα', 'wc-pm' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ( $email_logs as $e ) : ?>
                <tr>
                    <td><?php echo esc_html( $e['timestamp'] ); ?></td>
                    <td><?php echo esc_html( $e['template_key'] ); ?></td>
                    <td><?php echo esc_html( $e['recipient'] ); ?></td>
                    <td><?php echo esc_html( $e['subject'] ); ?></td>
                    <td><?php echo esc_html( $e['status'] ); ?></td>
                    <td><?php echo esc_html( $e['error_msg'] ?: '-' ); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
