<?php
// templates/admin/stats.php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div class="wrap pm-dashboard">
    <h1><?php esc_html_e( 'Dashboard', 'wc-pm' ); ?></h1>

    <div class="pm-kpis">
        <div class="pm-kpi-card">
            <h2><?php esc_html_e( 'Συνολικές Πωλήσεις', 'wc-pm' ); ?></h2>
            <p class="pm-kpi-value"><?php echo esc_html( PM_Dashboard_Data['kpis']['total_sales'] ); ?></p>
        </div>
        <div class="pm-kpi-card">
            <h2><?php esc_html_e( 'Συνολικά Έσοδα', 'wc-pm' ); ?></h2>
            <p class="pm-kpi-value"><?php echo esc_html( number_format_i18n( PM_Dashboard_Data['kpis']['total_revenue'], intval( get_option( 'pm_decimals', 2 ) ) ) ); ?></p>
        </div>
        <div class="pm-kpi-card">
            <h2><?php esc_html_e( 'Επιστροφές', 'wc-pm' ); ?></h2>
            <p class="pm-kpi-value"><?php echo esc_html( PM_Dashboard_Data['kpis']['total_refunds'] ); ?></p>
        </div>
        <div class="pm-kpi-card">
            <h2><?php esc_html_e( 'Εκκρεμείς Πληρωμές', 'wc-pm' ); ?></h2>
            <p class="pm-kpi-value"><?php echo esc_html( PM_Dashboard_Data['kpis']['pending_payments'] ); ?></p>
        </div>
    </div>

    <h2><?php esc_html_e( 'Πωλήσεις τον τελευταίο μήνα', 'wc-pm' ); ?></h2>
    <canvas id="pm-sales-chart" width="600" height="200"></canvas>

    <h2><?php esc_html_e( 'Δημοφιλία Πακέτων', 'wc-pm' ); ?></h2>
    <canvas id="pm-popularity-chart" width="600" height="200"></canvas>

    <h2><?php esc_html_e( 'Πρόσφατη Δραστηριότητα', 'wc-pm' ); ?></h2>
    <table class="widefat fixed striped">
        <thead>
            <tr>
                <th><?php esc_html_e( 'Ημερομηνία', 'wc-pm' ); ?></th>
                <th><?php esc_html_e( 'Χρήστης', 'wc-pm' ); ?></th>
                <th><?php esc_html_e( 'Δράση', 'wc-pm' ); ?></th>
                <th><?php esc_html_e( 'Τύπος Αντικειμένου', 'wc-pm' ); ?></th>
                <th><?php esc_html_e( 'ID Αντικειμένου', 'wc-pm' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ( PM_Dashboard_Data['recent'] as $log ) : ?>
                <tr>
                    <td><?php echo esc_html( $log['timestamp'] ); ?></td>
                    <td><?php echo esc_html( $log['user_id'] ? $log['user_id'] : '-' ); ?></td>
                    <td><?php echo esc_html( $log['action'] ); ?></td>
                    <td><?php echo esc_html( $log['object_type'] ); ?></td>
                    <td><?php echo esc_html( $log['object_id'] ); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
